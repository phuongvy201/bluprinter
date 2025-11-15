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

        $data = [
            'days' => $days,
            'tab' => $tab,
        ];

        // Dữ liệu chung cho tất cả tabs
        $data['summaryMetrics'] = $this->analyticsService->getSummaryMetrics($days);

        // Dữ liệu theo tab
        switch ($tab) {
            case 'acquisition':
                $data['sessionsByDate'] = $this->analyticsService->getSessionsByDate($days);
                $data['channels'] = $this->analyticsService->getAcquisitionChannels($days);
                $data['trafficSources'] = $this->analyticsService->getTrafficSources($days);
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
        }

        return view('admin.analytics.index', $data);
    }
}
