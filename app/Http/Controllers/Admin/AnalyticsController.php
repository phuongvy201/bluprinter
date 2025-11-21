<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {}

    public function index(Request $request)
    {
        $days = (int) $request->get('days', 7);
        $tab = $request->get('tab', 'acquisition');
        $filter = $request->get('filter', 'all'); // All, Organic Search, Paid Search, Direct, Social, Referrals, Display, Email, Other

        $data = [
            'days' => $days,
            'tab' => $tab,
            'filter' => $filter,
        ];

        // Dữ liệu chung cho tất cả tabs
        $data['summaryMetrics'] = $this->analyticsService->getSummaryMetrics($days);

        // Dữ liệu theo tab
        switch ($tab) {
            case 'acquisition':
                $data['sessionsByDate'] = $this->analyticsService->getSessionsByDate($days);
                $data['channels'] = $this->analyticsService->getAcquisitionChannels($days);
                $data['trafficSources'] = $this->analyticsService->getTrafficSources($days);

                // Filter data based on filter parameter
                if ($filter !== 'all') {
                    $data['trafficSources'] = $this->filterTrafficSources($data['trafficSources'], $filter);
                    $data['channels'] = $this->filterChannels($data['channels'], $filter);
                }

                $data['totalSessions'] = array_sum(array_column($data['channels'], 'sessions'));
                break;

            case 'audience':
                $data['demographics'] = $this->analyticsService->getAudienceDemographics($days);
                $data['devices'] = $this->analyticsService->getAudienceDevices($days);
                break;

            case 'conversions':
                $data['conversions'] = $this->analyticsService->getConversions($days);
                break;

            case 'pages':
                $data['topPages'] = $this->analyticsService->getTopPages($days);
                break;

            case 'events':
                $data['events'] = $this->analyticsService->getAllEvents($days);
                break;

            case 'domains':
                $domain = $request->get('domain');
                if ($domain) {
                    // Hiển thị chi tiết domain
                    $data['selectedDomain'] = $domain;
                    $data['domainPages'] = $this->analyticsService->getDomainPages($domain, $days);
                    $data['domainTrafficSources'] = $this->analyticsService->getDomainTrafficSources($domain, $days);
                    $data['domainDemographics'] = $this->analyticsService->getDomainDemographics($domain, $days);
                    $data['domainDevices'] = $this->analyticsService->getDomainDevices($domain, $days);
                    $data['domainTimeline'] = $this->analyticsService->getDomainTimeline($domain, $days);
                } else {
                    // Hiển thị danh sách domains
                    $data['domains'] = $this->analyticsService->getDomains($days);
                }
                break;
        }

        return view('admin.analytics.index', $data);
    }

    /**
     * Filter traffic sources by source type
     */
    private function filterTrafficSources(array $trafficSources, string $filter): array
    {
        $filterMap = [
            'organic-search' => ['Google', 'Bing', 'Organic Search'],
            'paid-search' => ['Paid Search'],
            'direct' => ['Direct'],
            'social' => ['Facebook', 'TikTok', 'Pinterest', 'Instagram', 'Twitter/X', 'YouTube', 'LinkedIn'],
            'referrals' => ['Referral'],
            'display' => ['Display'],
            'email' => ['Email'],
            'other' => ['Other'],
        ];

        $allowedTypes = $filterMap[strtolower(str_replace(' ', '-', $filter))] ?? [];

        if (empty($allowedTypes)) {
            return $trafficSources;
        }

        return array_filter($trafficSources, function ($source) use ($allowedTypes) {
            return in_array($source['source_type'] ?? '', $allowedTypes);
        });
    }

    /**
     * Filter channels by source type
     */
    private function filterChannels(array $channels, string $filter): array
    {
        $filterMap = [
            'organic-search' => ['Organic Search'],
            'paid-search' => ['Paid Search', 'Paid Social'],
            'direct' => ['Direct'],
            'social' => ['Paid Social', 'Social'],
            'referrals' => ['Referral'],
            'display' => ['Display'],
            'email' => ['Email'],
            'other' => ['Unassigned', 'Other'],
        ];

        $allowedChannels = $filterMap[strtolower(str_replace(' ', '-', $filter))] ?? [];

        if (empty($allowedChannels)) {
            return $channels;
        }

        return array_filter($channels, function ($channel) use ($allowedChannels) {
            return in_array($channel['channel'] ?? '', $allowedChannels);
        });
    }
}
