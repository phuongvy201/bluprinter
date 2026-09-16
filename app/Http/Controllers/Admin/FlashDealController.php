<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\FlashDeal;
use App\Models\FlashDealRule;
use App\Models\FlashDealTemplate;
use App\Models\Product;
use App\Services\FlashDealRotationService;
use App\Services\FlashDealService;
use App\Support\Settings;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FlashDealController extends Controller
{
    public function index(): View
    {
        $defaults = config('flash_deal');
        $settings = $this->resolvedSettings($defaults);

        $activeDeals = FlashDeal::query()
            ->with(['product.template', 'product.shop'])
            ->active()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        $rules = FlashDealRule::query()->orderBy('priority')->get();
        $templates = FlashDealTemplate::query()->with(['category', 'shop'])->latest()->get();
        $categories = Category::query()->orderBy('name')->get(['id', 'name']);

        $products = Product::query()
            ->availableForDisplay()
            ->with(['shop:id,shop_name', 'template:id,category_id,media', 'template.category:id,name'])
            ->orderByDesc('id')
            ->limit(400)
            ->get(['id', 'name', 'sku', 'price', 'shop_id', 'template_id', 'media']);

        $shops = \App\Models\Shop::query()
            ->where('shop_status', 'active')
            ->orderBy('shop_name')
            ->get(['id', 'shop_name']);

        return view('admin.flash-deals.index', compact(
            'defaults',
            'settings',
            'activeDeals',
            'rules',
            'templates',
            'categories',
            'products',
            'shops',
        ));
    }

    public function storeManual(Request $request, FlashDealService $flashDealService): RedirectResponse
    {
        $validated = $request->validate([
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'discount_percent' => ['required', 'integer', 'min:1', 'max:90'],
            'duration' => ['nullable', 'string', 'in:2h,6h,12h,1d,7d,custom'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
        ]);

        $startsAt = ! empty($validated['starts_at'])
            ? Carbon::parse($validated['starts_at'])
            : now();

        $duration = $validated['duration'] ?? 'custom';
        $endsAt = $this->resolveEndsAt($startsAt, $duration, $validated['ends_at'] ?? null);

        if ($endsAt->lte($startsAt)) {
            return back()->withInput()->withErrors([
                'ends_at' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
            ]);
        }

        $result = $this->createManualDeals(
            $flashDealService,
            $validated['product_ids'],
            (int) $validated['discount_percent'],
            $startsAt,
            $endsAt
        );

        $message = "Đã tạo {$result['created']} flash sale thủ công.";
        if ($result['skipped'] > 0) {
            $message .= " Bỏ qua {$result['skipped']} SP (giá không hợp lệ / % giảm thấp hơn tối thiểu / bị chặn bởi shop).";
        }

        return back()->with('success', $message);
    }

    /**
     * @param  array<int, int|string>  $productIds
     * @return array{created: int, skipped: int}
     */
    public function createManualDeals(
        FlashDealService $flashDealService,
        array $productIds,
        int $discountPercent,
        Carbon $startsAt,
        Carbon $endsAt
    ): array {
        $created = 0;
        $skipped = 0;

        foreach ($productIds as $productId) {
            $product = Product::with(['shop', 'template'])->find($productId);
            if (! $product) {
                $skipped++;
                continue;
            }

            $original = (float) $product->getEffectivePrice();
            if ($original <= 0) {
                $original = (float) ($product->template->base_price ?? 0);
            }

            $salePrice = $flashDealService->salePriceFromDiscount($original, $discountPercent);
            $deal = $flashDealService->activateDeal(
                $product,
                $salePrice,
                $startsAt,
                $endsAt,
                'manual',
            );

            if ($deal) {
                $created++;
            } else {
                $skipped++;
            }
        }

        return compact('created', 'skipped');
    }

    public function resolveEndsAt(Carbon $startsAt, string $duration, mixed $endsAtInput): Carbon
    {
        return match ($duration) {
            '2h' => $startsAt->copy()->addHours(2),
            '6h' => $startsAt->copy()->addHours(6),
            '12h' => $startsAt->copy()->addHours(12),
            '1d' => $startsAt->copy()->addDay(),
            '7d' => $startsAt->copy()->addDays(7),
            default => ! empty($endsAtInput)
                ? Carbon::parse($endsAtInput)
                : $startsAt->copy()->addDay(),
        };
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'product_limit' => ['required', 'integer', 'min:4', 'max:24'],
            'min_discount_percent' => ['required', 'integer', 'min:1', 'max:90'],
            'default_discount_percent' => ['required', 'integer', 'min:1', 'max:90'],
            'auto_enroll_product_limit' => ['required', 'integer', 'min:1', 'max:50'],
            'auto_enroll_recent_days' => ['required', 'integer', 'min:1', 'max:90'],
            'default_start_time' => ['required', 'date_format:H:i'],
            'default_end_time' => ['required', 'date_format:H:i'],
        ]);

        Settings::set('flash_deal.enabled', $request->boolean('enabled') ? '1' : '0');
        Settings::set('flash_deal.product_limit', (string) $validated['product_limit']);
        Settings::set('flash_deal.min_discount_percent', (string) $validated['min_discount_percent']);
        Settings::set('flash_deal.default_discount_percent', (string) $validated['default_discount_percent']);
        Settings::set('flash_deal.auto_enroll_product_limit', (string) $validated['auto_enroll_product_limit']);
        Settings::set('flash_deal.auto_enroll_recent_days', (string) $validated['auto_enroll_recent_days']);
        Settings::set('flash_deal.default_start_time', $validated['default_start_time']);
        Settings::set('flash_deal.default_end_time', $validated['default_end_time']);

        return back()->with('success', 'Cập nhật cấu hình Flash Deal thành công.');
    }

    public function storeRule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'rule_type' => ['required', 'string', 'in:stale_inventory,high_margin,trending'],
            'discount_percent' => ['required', 'integer', 'min:1', 'max:90'],
            'max_products' => ['required', 'integer', 'min:1', 'max:50'],
            'priority' => ['required', 'integer', 'min:1', 'max:100'],
            'golden_hours' => ['nullable', 'string'],
            'min_days_stale' => ['nullable', 'integer', 'min:1', 'max:365'],
            'min_quantity' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'min_margin_percent' => ['nullable', 'integer', 'min:1', 'max:99'],
            'recent_days' => ['nullable', 'integer', 'min:1', 'max:90'],
        ]);

        $config = match ($validated['rule_type']) {
            FlashDealRule::TYPE_STALE_INVENTORY => [
                'min_days_stale' => (int) ($validated['min_days_stale'] ?? 7),
                'min_quantity' => (int) ($validated['min_quantity'] ?? 1),
            ],
            FlashDealRule::TYPE_HIGH_MARGIN => [
                'min_margin_percent' => (int) ($validated['min_margin_percent'] ?? 30),
            ],
            FlashDealRule::TYPE_TRENDING => [
                'recent_days' => (int) ($validated['recent_days'] ?? 7),
            ],
            default => [],
        };

        $goldenHours = collect(explode(',', (string) ($validated['golden_hours'] ?? '')))
            ->map(fn($h) => (int) trim($h))
            ->filter(fn($h) => $h >= 0 && $h <= 23)
            ->values()
            ->all();

        FlashDealRule::create([
            'name' => $validated['name'],
            'rule_type' => $validated['rule_type'],
            'config' => $config,
            'discount_percent' => $validated['discount_percent'],
            'max_products' => $validated['max_products'],
            'priority' => $validated['priority'],
            'golden_hours' => $goldenHours ?: null,
            'is_active' => true,
        ]);

        return back()->with('success', 'Đã thêm quy tắc Flash Deal.');
    }

    public function destroyRule(FlashDealRule $rule): RedirectResponse
    {
        $rule->delete();

        return back()->with('success', 'Đã xóa quy tắc.');
    }

    public function storeTemplate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'discount_percent' => ['required', 'integer', 'min:1', 'max:90'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'recurrence' => ['required', 'in:hourly,daily,weekly'],
            'days_of_week' => ['nullable', 'array'],
            'days_of_week.*' => ['integer', 'min:0', 'max:6'],
            'max_products' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        FlashDealTemplate::create([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'] ?? null,
            'discount_percent' => $validated['discount_percent'],
            'start_time' => $validated['start_time'] . ':00',
            'end_time' => $validated['end_time'] . ':00',
            'recurrence' => $validated['recurrence'],
            'days_of_week' => $validated['recurrence'] === 'weekly'
                ? array_values($validated['days_of_week'] ?? [])
                : null,
            'max_products' => $validated['max_products'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Đã thêm template lặp lại.');
    }

    public function destroyTemplate(FlashDealTemplate $template): RedirectResponse
    {
        $template->delete();

        return back()->with('success', 'Đã xóa template.');
    }

    public function destroy(FlashDeal $flashDeal, FlashDealService $flashDealService): RedirectResponse
    {
        $flashDealService->expireDeal($flashDeal);

        return back()->with('success', 'Đã gỡ Flash Deal khỏi sản phẩm (đã trả lại giá cũ).');
    }

    public function rotate(FlashDealRotationService $rotation): RedirectResponse
    {
        $result = $rotation->rotate();
        $created = (int) ($result['created'] ?? 0);
        $message = 'Chạy rotation: tạo ' . $created . ' deal mới.';

        if (!empty($result['skipped_reason'])) {
            $message .= ' ' . $result['skipped_reason'];
        }

        if (!empty($result['notes']) && is_array($result['notes'])) {
            $message .= ' Chi tiết: ' . implode(' | ', $result['notes']);
        }

        return back()->with('success', $message);
    }

    protected function resolvedSettings(array $defaults): array
    {
        return [
            'enabled' => (bool) Settings::get('flash_deal.enabled', $defaults['enabled']),
            'product_limit' => (int) Settings::get('flash_deal.product_limit', $defaults['product_limit']),
            'min_discount_percent' => (int) Settings::get('flash_deal.min_discount_percent', $defaults['min_discount_percent']),
            'default_discount_percent' => (int) Settings::get('flash_deal.default_discount_percent', $defaults['default_discount_percent']),
            'auto_enroll_product_limit' => (int) Settings::get('flash_deal.auto_enroll_product_limit', $defaults['auto_enroll_product_limit']),
            'auto_enroll_recent_days' => (int) Settings::get('flash_deal.auto_enroll_recent_days', $defaults['auto_enroll_recent_days']),
            'default_start_time' => Settings::get('flash_deal.default_start_time', $defaults['default_start_time']),
            'default_end_time' => Settings::get('flash_deal.default_end_time', $defaults['default_end_time']),
        ];
    }
}
