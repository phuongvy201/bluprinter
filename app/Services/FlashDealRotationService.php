<?php

namespace App\Services;

use App\Models\FlashDeal;
use App\Models\FlashDealRule;
use App\Models\FlashDealTemplate;
use App\Models\OrderItem;
use App\Models\Product;
use App\Support\Settings;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FlashDealRotationService
{
    public function __construct(
        protected FlashDealService $flashDealService,
    ) {}

    public function expireEndedDeals(): int
    {
        $count = 0;

        FlashDeal::query()
            ->where('is_active', true)
            ->where('ends_at', '<=', now())
            ->with('product')
            ->each(function ($deal) use (&$count) {
                $this->flashDealService->expireDeal($deal);
                $count++;
            });

        return $count;
    }

    public function rotate(): array
    {
        $expired = $this->expireEndedDeals();

        if (! $this->flashDealService->isEnabled()) {
            return [
                'expired' => $expired,
                'created' => 0,
                'skipped_reason' => 'Flash Deal đang tắt trong cấu hình chung.',
            ];
        }

        $usedProductIds = FlashDeal::query()
            ->where('is_active', true)
            ->pluck('product_id')
            ->all();

        $created = 0;
        $sortOrder = 0;
        $notes = [];

        foreach ($this->templatesForToday() as $template) {
            $n = $this->applyTemplate($template, $usedProductIds, $sortOrder);
            $created += $n;
            $notes[] = "Template «{$template->name}»: +{$n}";
        }

        foreach ($this->activeRules() as $rule) {
            $candidates = $this->productsForRule($rule, $usedProductIds)->count();
            $n = $this->applyRule($rule, $usedProductIds, $sortOrder);
            $created += $n;
            $typeLabel = FlashDealRule::ruleTypes()[$rule->rule_type] ?? $rule->rule_type;
            $notes[] = "Rule «{$rule->name}» ({$typeLabel}): ứng viên {$candidates}, tạo +{$n}";
            if ($candidates === 0) {
                $notes[] = $this->emptyRuleHint($rule);
            }
        }

        $auto = $this->applyAutoEnrollment($usedProductIds, $sortOrder);
        $created += $auto;
        $notes[] = "Auto-enrollment: +{$auto}";

        return [
            'expired' => $expired,
            'created' => $created,
            'notes' => $notes,
        ];
    }

    protected function emptyRuleHint(FlashDealRule $rule): string
    {
        $config = $rule->config ?? [];

        return match ($rule->rule_type) {
            FlashDealRule::TYPE_STALE_INVENTORY => sprintf(
                '→ Không có SP tồn ≥ %d ngày (và quantity ≥ %d). Hạ “Ngày tồn” hoặc chọn SP thủ công.',
                (int) ($config['min_days_stale'] ?? 30),
                (int) ($config['min_quantity'] ?? 1),
            ),
            FlashDealRule::TYPE_HIGH_MARGIN => sprintf(
                '→ Không có SP margin ≥ %d%% (base_price so với price).',
                (int) ($config['min_margin_percent'] ?? 30),
            ),
            FlashDealRule::TYPE_TRENDING => sprintf(
                '→ Không có order trong %d ngày gần đây để xếp trending.',
                (int) ($config['recent_days'] ?? 7),
            ),
            default => '→ Không có sản phẩm khớp điều kiện.',
        };
    }

    protected function templatesForToday(): Collection
    {
        return FlashDealTemplate::query()
            ->where('is_active', true)
            ->get()
            ->filter(fn (FlashDealTemplate $t) => $t->appliesToday());
    }

    protected function activeRules(): Collection
    {
        return FlashDealRule::query()
            ->where('is_active', true)
            ->orderBy('priority')
            ->get();
    }

    protected function applyTemplate(FlashDealTemplate $template, array &$usedProductIds, int &$sortOrder): int
    {
        $startsAt = $this->timeToday($template->start_time);
        $endsAt = $this->timeToday($template->end_time);

        if ($endsAt->lessThanOrEqualTo($startsAt)) {
            $endsAt = $startsAt->copy()->addHours(4);
        }

        if ($endsAt->lessThanOrEqualTo(now())) {
            return 0;
        }

        $query = Product::query()
            ->availableForDisplay()
            ->when($template->shop_id, fn ($q) => $q->where('shop_id', $template->shop_id))
            ->when($template->category_id, function ($q) use ($template) {
                $q->whereHas('template', fn ($tq) => $tq->where('category_id', $template->category_id));
            })
            ->whereNotIn('id', $usedProductIds)
            ->with(['template', 'shop'])
            ->inRandomOrder()
            ->limit($template->max_products);

        $created = 0;

        foreach ($query->get() as $product) {
            if (! $this->productAllowsAutoDeal($product)) {
                continue;
            }

            $original = $this->currentSellPrice($product);
            if ($original <= 0) {
                continue;
            }

            $salePrice = $this->flashDealService->salePriceFromDiscount($original, $template->discount_percent);

            $deal = $this->flashDealService->activateDeal(
                $product,
                $salePrice,
                $startsAt,
                $endsAt,
                'template',
                $template->id,
                $sortOrder++,
            );

            if ($deal) {
                $usedProductIds[] = $product->id;
                $created++;
            }
        }

        return $created;
    }

    protected function applyRule(FlashDealRule $rule, array &$usedProductIds, int &$sortOrder): int
    {
        $products = $this->productsForRule($rule, $usedProductIds);

        if ($products->isEmpty()) {
            return 0;
        }

        $goldenHours = $rule->golden_hours ?? [];
        $slots = empty($goldenHours) ? [null] : $goldenHours;

        $created = 0;
        $perSlot = max(1, (int) ceil($rule->max_products / count($slots)));
        $slotIndex = 0;

        foreach ($products->take($rule->max_products) as $product) {
            $hour = $slots[$slotIndex % count($slots)];
            $startsAt = $this->windowStart($hour);
            $endsAt = $this->windowEnd($hour, $startsAt);

            // Khung giờ vàng đã qua trong ngày → vẫn tạo deal đến cuối ngày
            // để "Chạy rotation ngay" không ra 0 deal oan.
            if ($endsAt->lessThanOrEqualTo(now())) {
                $startsAt = now();
                $endsAt = $this->defaultDayEnd();
                if ($endsAt->lessThanOrEqualTo(now())) {
                    continue;
                }
            }

            $original = $this->currentSellPrice($product);
            if ($original <= 0) {
                continue;
            }

            $salePrice = $this->flashDealService->salePriceFromDiscount($original, $rule->discount_percent);

            $deal = $this->flashDealService->activateDeal(
                $product,
                $salePrice,
                $startsAt,
                $endsAt,
                'rule',
                $rule->id,
                $sortOrder++,
            );

            if ($deal) {
                $usedProductIds[] = $product->id;
                $created++;

                if ($created % $perSlot === 0) {
                    $slotIndex++;
                }
            }
        }

        return $created;
    }

    protected function productsForRule(FlashDealRule $rule, array $usedProductIds): Collection
    {
        $config = $rule->config ?? [];

        $base = Product::query()
            ->availableForDisplay()
            ->whereNotIn('id', $usedProductIds)
            ->with(['template', 'shop']);

        switch ($rule->rule_type) {
            case FlashDealRule::TYPE_STALE_INVENTORY:
                $days = (int) ($config['min_days_stale'] ?? 7);
                $minQty = (int) ($config['min_quantity'] ?? 1);
                $base->where('products.updated_at', '<=', now()->subDays($days))
                    ->where('products.quantity', '>=', $minQty);
                break;

            case FlashDealRule::TYPE_HIGH_MARGIN:
                $minMargin = (float) ($config['min_margin_percent'] ?? 30);
                $base->join('product_templates', 'products.template_id', '=', 'product_templates.id')
                    ->whereRaw(
                        '(product_templates.base_price - products.price) / NULLIF(product_templates.base_price, 0) * 100 >= ?',
                        [$minMargin]
                    )
                    ->select('products.*');
                break;

            case FlashDealRule::TYPE_TRENDING:
                $days = (int) ($config['recent_days'] ?? 7);
                $topIds = OrderItem::query()
                    ->where('created_at', '>=', now()->subDays($days))
                    ->selectRaw('product_id, SUM(quantity) as sold')
                    ->groupBy('product_id')
                    ->orderByDesc('sold')
                    ->limit($rule->max_products * 3)
                    ->pluck('product_id');

                if ($topIds->isEmpty()) {
                    return collect();
                }

                $base->whereIn('products.id', $topIds)
                    ->orderByRaw('FIELD(products.id, ' . $topIds->implode(',') . ')');
                break;
        }

        return $base->limit($rule->max_products * 2)->get();
    }

    protected function applyAutoEnrollment(array &$usedProductIds, int &$sortOrder): int
    {
        $limit = (int) Settings::get(
            'flash_deal.auto_enroll_product_limit',
            config('flash_deal.auto_enroll_product_limit', 10),
        );

        $recentDays = (int) Settings::get(
            'flash_deal.auto_enroll_recent_days',
            config('flash_deal.auto_enroll_recent_days', 7),
        );

        $defaultDiscount = (int) Settings::get(
            'flash_deal.default_discount_percent',
            config('flash_deal.default_discount_percent', 20),
        );

        $startsAt = $this->defaultDayStart();
        $endsAt = $this->defaultDayEnd();

        $enrolledShopIds = \App\Models\Shop::query()
            ->where('flash_deal_auto_enroll', true)
            ->where('shop_status', 'active')
            ->pluck('id');

        if ($enrolledShopIds->isEmpty()) {
            return 0;
        }

        $trendingIds = OrderItem::query()
            ->where('created_at', '>=', now()->subDays($recentDays))
            ->selectRaw('product_id, SUM(quantity) as sold')
            ->groupBy('product_id')
            ->orderByDesc('sold')
            ->limit(50)
            ->pluck('product_id');

        $staleQuery = Product::query()
            ->availableForDisplay()
            ->whereIn('shop_id', $enrolledShopIds)
            ->whereNotIn('id', $usedProductIds)
            ->where(function ($q) {
                $q->where('flash_deal_auto_enroll', true)
                    ->orWhereHas('shop', fn ($sq) => $sq->where('flash_deal_auto_enroll', true));
            })
            ->where('products.updated_at', '<=', now()->subDays(14))
            ->with(['template', 'shop'])
            ->inRandomOrder()
            ->limit($limit);

        $trendingQuery = Product::query()
            ->availableForDisplay()
            ->whereIn('shop_id', $enrolledShopIds)
            ->whereNotIn('id', $usedProductIds)
            ->whereIn('id', $trendingIds)
            ->with(['template', 'shop']);

        $picked = collect();
        $categoryCounts = [];

        foreach ($trendingQuery->get() as $product) {
            if ($picked->count() >= $limit) {
                break;
            }

            $catId = $product->resolvedCategoryId() ?? 0;

            if (($categoryCounts[$catId] ?? 0) >= 2) {
                continue;
            }

            if (! $this->productAllowsAutoDeal($product)) {
                continue;
            }

            $picked->push($product);
            $categoryCounts[$catId] = ($categoryCounts[$catId] ?? 0) + 1;
        }

        foreach ($staleQuery->get() as $product) {
            if ($picked->count() >= $limit) {
                break;
            }

            if ($picked->contains('id', $product->id)) {
                continue;
            }

            $catId = $product->resolvedCategoryId() ?? 0;

            if (($categoryCounts[$catId] ?? 0) >= 2) {
                continue;
            }

            if (! $this->productAllowsAutoDeal($product)) {
                continue;
            }

            $picked->push($product);
            $categoryCounts[$catId] = ($categoryCounts[$catId] ?? 0) + 1;
        }

        $created = 0;

        foreach ($picked as $product) {
            $original = $this->currentSellPrice($product);
            if ($original <= 0) {
                continue;
            }

            $salePrice = $this->flashDealService->salePriceFromDiscount($original, $defaultDiscount);

            $deal = $this->flashDealService->activateDeal(
                $product,
                $salePrice,
                $startsAt,
                $endsAt,
                'auto_enrollment',
                null,
                $sortOrder++,
            );

            if ($deal) {
                $usedProductIds[] = $product->id;
                $created++;
            }
        }

        return $created;
    }

    protected function productAllowsAutoDeal(Product $product): bool
    {
        if ($product->flash_deal_auto_enroll) {
            return true;
        }

        return (bool) ($product->shop?->flash_deal_auto_enroll);
    }

    /**
     * Giá đang bán — dùng để tính % giảm (khớp activateDeal lưu original_price).
     */
    protected function currentSellPrice(Product $product): float
    {
        $price = (float) $product->getEffectivePrice();

        if ($price <= 0) {
            $price = (float) ($product->template->base_price ?? 0);
        }

        return $price;
    }

    protected function timeToday(string $time): Carbon
    {
        $parts = explode(':', $time);

        return now()->startOfDay()
            ->setTime((int) ($parts[0] ?? 0), (int) ($parts[1] ?? 0), (int) ($parts[2] ?? 0));
    }

    protected function windowStart(?int $hour): Carbon
    {
        if ($hour === null) {
            return $this->defaultDayStart();
        }

        return now()->startOfDay()->setHour($hour);
    }

    protected function windowEnd(?int $hour, Carbon $startsAt): Carbon
    {
        if ($hour === null) {
            return $this->defaultDayEnd();
        }

        return $startsAt->copy()->addHours(2);
    }

    protected function defaultDayStart(): Carbon
    {
        $time = Settings::get('flash_deal.default_start_time', config('flash_deal.default_start_time', '00:00'));

        return $this->timeToday($time . ':00');
    }

    protected function defaultDayEnd(): Carbon
    {
        $time = Settings::get('flash_deal.default_end_time', config('flash_deal.default_end_time', '23:59'));

        return $this->timeToday($time . ':00');
    }
}
