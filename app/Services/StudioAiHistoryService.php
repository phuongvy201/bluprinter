<?php

namespace App\Services;

use App\Models\StudioAiGeneration;
use App\Support\StudioAiSettings;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class StudioAiHistoryService
{
    public const COOKIE = 'studio_aid';

    public function visitorToken(Request $request): string
    {
        $token = trim((string) $request->cookie(self::COOKIE, ''));
        if ($token !== '' && strlen($token) <= 64) {
            return $token;
        }

        $token = (string) Str::uuid();
        Cookie::queue(cookie(self::COOKIE, $token, 60 * 24 * 400, '/', null, config('session.secure'), true, false, 'lax'));

        return $token;
    }

    public function claimVisitorHistory(Request $request): void
    {
        $user = $request->user();
        if (!$user) {
            return;
        }

        StudioAiGeneration::query()
            ->whereNull('user_id')
            ->where('visitor_token', $this->visitorToken($request))
            ->update(['user_id' => $user->id]);
    }

    /**
     * @param  array<int, array{url?: string, prompt?: string}>  $designs
     * @param  array<int, string>  $referenceUrls
     */
    public function record(Request $request, string $prompt, array $designs, array $referenceUrls = []): StudioAiGeneration
    {
        $this->claimVisitorHistory($request);
        $urls = [];
        foreach ($designs as $design) {
            $url = trim((string) ($design['url'] ?? ''));
            if ($url !== '') {
                $urls[] = $url;
            }
        }

        $settings = StudioAiSettings::resolved();

        return StudioAiGeneration::create([
            'user_id' => $request->user()?->id,
            'visitor_token' => $this->visitorToken($request),
            'prompt' => $prompt,
            'image_urls' => $urls,
            'reference_urls' => array_values(array_filter($referenceUrls)),
            'image_model' => $settings['image_model'] !== '' ? $settings['image_model'] : null,
            'ip_address' => $request->ip(),
            'hidden_from_customer' => false,
        ]);
    }

    public function queryForVisitor(Request $request): Builder
    {
        $this->claimVisitorHistory($request);
        $token = $this->visitorToken($request);
        $userId = $request->user()?->id;

        return StudioAiGeneration::query()
            ->visibleToCustomer()
            ->where(function (Builder $query) use ($userId, $token) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('visitor_token', $token)->whereNull('user_id');
                }
            })
            ->orderByDesc('id');
    }

    public function paginateForVisitor(Request $request, int $perPage = 12): LengthAwarePaginator
    {
        return $this->queryForVisitor($request)->paginate($perPage)->withQueryString();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function recentForVisitor(Request $request, int $limit = 8): array
    {
        return $this->queryForVisitor($request)
            ->limit($limit)
            ->get()
            ->map(fn (StudioAiGeneration $row) => $this->serialize($row))
            ->all();
    }

    public function findOwned(Request $request, int $id): ?StudioAiGeneration
    {
        return $this->queryForVisitor($request)->whereKey($id)->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(StudioAiGeneration $row): array
    {
        return [
            'id' => $row->id,
            'prompt' => $row->prompt,
            'images' => $row->images(),
            'created_at' => $row->created_at?->toIso8601String(),
            'created_label' => $row->created_at?->timezone(config('app.timezone'))->format('d M Y H:i'),
        ];
    }
}
