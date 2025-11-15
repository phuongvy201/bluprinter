<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {}

    /**
     * Lấy dữ liệu realtime - Tất cả
     */
    public function realtime(): JsonResponse
    {
        try {
            $data = $this->analyticsService->getAllRealtime();
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy dữ liệu realtime: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lấy dữ liệu realtime - Người đang online và trang đang xem
     */
    public function realtimePages(): JsonResponse
    {
        try {
            $data = $this->analyticsService->getRealtimePages();
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy dữ liệu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lấy tổng số người đang online
     */
    public function realtimeActiveUsers(): JsonResponse
    {
        try {
            $total = $this->analyticsService->getTotalActiveUsers();
            return response()->json([
                'success' => true,
                'data' => [
                    'total_active_users' => $total,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy dữ liệu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lấy dữ liệu realtime - Vị trí địa lý
     */
    public function realtimeLocations(): JsonResponse
    {
        try {
            $data = $this->analyticsService->getRealtimeLocations();
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy dữ liệu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lấy dữ liệu realtime - Nguồn truy cập
     */
    public function realtimeSources(): JsonResponse
    {
        try {
            $data = $this->analyticsService->getRealtimeSources();
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy dữ liệu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lấy dữ liệu realtime - Thiết bị
     */
    public function realtimeDevices(): JsonResponse
    {
        try {
            $data = $this->analyticsService->getRealtimeDevices();
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy dữ liệu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lấy báo cáo hành vi (không realtime)
     */
    public function behaviorReport(Request $request): JsonResponse
    {
        try {
            $days = (int) $request->get('days', 7);
            $data = $this->analyticsService->getBehaviorReport($days);
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy báo cáo: ' . $e->getMessage(),
            ], 500);
        }
    }
}

