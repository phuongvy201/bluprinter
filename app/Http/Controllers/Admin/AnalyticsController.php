<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    private AnalyticsService $analyticsService;
    private ?string $selectedDomain = null;

    public function __construct()
    {
        // Không khởi tạo service ở đây, sẽ khởi tạo trong index() dựa trên selected_domain
    }

    public function index(Request $request)
    {
        $days = (int) $request->get('days', 7);
        $tab = $request->get('tab', 'acquisition');
        $filter = $request->get('filter', 'all'); // All, Organic Search, Paid Search, Direct, Social, Referrals, Display, Email, Other

        // Lấy danh sách domain đã cấu hình trong database
        $availableDomains = \App\Models\DomainAnalyticsConfig::getAllDomains();

        // Kiểm tra nếu không có domain nào được cấu hình
        if (empty($availableDomains)) {
            return redirect()->route('admin.settings.domain-config.index')
                ->with('error', 'Chưa có domain nào được cấu hình. Vui lòng cấu hình domain trước khi xem analytics.');
        }

        // Lấy domain được chọn từ request
        $selectedDomainParam = $request->get('selected_domain');

        // Chỉ chấp nhận domain từ database, không cho phép default
        if ($selectedDomainParam === 'default' || $selectedDomainParam === '' || $selectedDomainParam === null) {
            // Nếu không có domain được chọn, dùng domain đầu tiên trong danh sách
            $selectedDomain = $availableDomains[0];
        } else {
            // Kiểm tra domain có tồn tại trong database không
            if (!in_array($selectedDomainParam, $availableDomains)) {
                return redirect()->route('admin.analytics.index')
                    ->with('error', 'Domain không hợp lệ. Domain phải được cấu hình trong database.');
            }
            $selectedDomain = $selectedDomainParam;
        }

        $this->selectedDomain = $selectedDomain;
        $this->analyticsService = AnalyticsService::forDomain($selectedDomain);

        // Kiểm tra nếu service không được khởi tạo (do không có config)
        if (!$this->analyticsService->isInitialized()) {
            return redirect()->route('admin.settings.domain-config.index')
                ->with('error', "Domain '{$selectedDomain}' chưa được cấu hình đầy đủ. Vui lòng kiểm tra lại cấu hình.");
        }

        $displayDomain = $selectedDomain;

        $data = [
            'days' => $days,
            'tab' => $tab,
            'filter' => $filter,
            'selectedDomain' => $selectedDomain,
            'displayDomain' => $displayDomain,
            'availableDomains' => $availableDomains,
        ];

        // Dữ liệu chung cho tất cả tabs
        $data['summaryMetrics'] = $this->analyticsService->getSummaryMetrics($days);

        // Load dữ liệu cho tất cả các tab để có thể switch tab mà không cần reload
        // Acquisition data
        $data['sessionsByDate'] = $this->analyticsService->getSessionsByDate($days);
        $data['channels'] = $this->analyticsService->getAcquisitionChannels($days);
        $data['trafficSources'] = $this->analyticsService->getTrafficSources($days);

        // Filter data based on filter parameter (chỉ áp dụng khi tab là acquisition)
        if ($tab === 'acquisition' && $filter !== 'all') {
            $data['trafficSources'] = $this->filterTrafficSources($data['trafficSources'], $filter);
            $data['channels'] = $this->filterChannels($data['channels'], $filter);
        }

        $data['totalSessions'] = array_sum(array_column($data['channels'], 'sessions'));

        // Audience data
        $data['demographics'] = $this->analyticsService->getAudienceDemographics($days);
        $data['devices'] = $this->analyticsService->getAudienceDevices($days);

        // Conversions data
        $data['conversions'] = $this->analyticsService->getConversions($days);

        // Pages data
        $data['topPages'] = $this->analyticsService->getTopPages($days);

        // Events data
        $data['events'] = $this->analyticsService->getAllEvents($days);

        // Domains data
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
