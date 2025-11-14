<?php

namespace App\Services;

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunRealtimeReportRequest;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy;
use Google\Analytics\Data\V1beta\OrderBy\DimensionOrderBy;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class AnalyticsService
{
    private ?BetaAnalyticsDataClient $client = null;
    private string $propertyId;

    public function __construct()
    {
        $this->propertyId = config('services.google.analytics.property_id');
        $credentialsPath = config('services.google.analytics.credentials_path');

        if (!$this->propertyId) {
            Log::warning('Google Analytics Property ID chưa được cấu hình');
            return;
        }

        // Xử lý đường dẫn: nếu là đường dẫn tương đối thì convert sang tuyệt đối
        if ($credentialsPath && !is_file($credentialsPath)) {
            // Thử với base_path() nếu là đường dẫn tương đối từ root
            $absolutePath = base_path($credentialsPath);
            if (is_file($absolutePath)) {
                $credentialsPath = $absolutePath;
            } elseif (str_starts_with($credentialsPath, 'storage/app/')) {
                // Thử với storage_path() nếu bắt đầu bằng storage/app/
                $absolutePath = storage_path('app/' . str_replace('storage/app/', '', $credentialsPath));
                if (is_file($absolutePath)) {
                    $credentialsPath = $absolutePath;
                }
            }
        }

        if (!$credentialsPath || !file_exists($credentialsPath)) {
            Log::warning("File credentials Google Analytics không tồn tại: " . config('services.google.analytics.credentials_path'));
            return;
        }

        try {
            $this->client = new BetaAnalyticsDataClient([
                'credentials' => $credentialsPath,
            ]);
        } catch (Exception $e) {
            Log::error('Lỗi khởi tạo Google Analytics client: ' . $e->getMessage());
        }
    }

    /**
     * Lấy dữ liệu realtime - Người đang online và trang đang xem
     */
    public function getRealtimePages(): array
    {
        if (!$this->client) {
            return [];
        }

        return Cache::remember('analytics.realtime.pages', 60, function () {
            try {
                $request = new RunRealtimeReportRequest([
                    'property' => "properties/{$this->propertyId}",
                    'dimensions' => [
                        new Dimension(['name' => 'pagePath']),
                    ],
                    'metrics' => [
                        new Metric(['name' => 'activeUsers']),
                        new Metric(['name' => 'screenPageViews']),
                    ],
                    'limit' => 10, // Top 10 trang
                ]);
                $response = $this->client->runRealtimeReport($request);

                // Log response để debug
                Log::info('Realtime Pages API Response', [
                    'row_count' => $response->getRows()->count(),
                    'response' => json_decode($response->serializeToJsonString(), true)
                ]);

                $data = [];
                foreach ($response->getRows() as $row) {
                    $dimensionValues = $row->getDimensionValues();
                    $metricValues = $row->getMetricValues();

                    $data[] = [
                        'page' => $dimensionValues[0]->getValue(),
                        'users' => (int) $metricValues[0]->getValue(),
                        'views' => (int) $metricValues[1]->getValue(),
                    ];
                }

                Log::info('Realtime Pages Processed Data', ['data' => $data]);
                return $data;
            } catch (Exception $e) {
                Log::error('Lỗi lấy realtime pages: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Lấy dữ liệu realtime - Người đang online theo quốc gia/thành phố
     */
    public function getRealtimeLocations(): array
    {
        if (!$this->client) {
            return [];
        }

        return Cache::remember('analytics.realtime.locations', 60, function () {
            try {
                $request = new RunRealtimeReportRequest([
                    'property' => "properties/{$this->propertyId}",
                    'dimensions' => [
                        new Dimension(['name' => 'country']),
                        new Dimension(['name' => 'city']),
                    ],
                    'metrics' => [
                        new Metric(['name' => 'activeUsers']),
                    ],
                    'limit' => 20,
                ]);
                $response = $this->client->runRealtimeReport($request);

                // Log response để debug
                Log::info('Realtime Locations API Response', [
                    'row_count' => $response->getRows()->count(),
                    'response' => json_decode($response->serializeToJsonString(), true)
                ]);

                $data = [];
                foreach ($response->getRows() as $row) {
                    $dimensionValues = $row->getDimensionValues();
                    $metricValues = $row->getMetricValues();

                    $data[] = [
                        'country' => $dimensionValues[0]->getValue(),
                        'city' => $dimensionValues[1]->getValue(),
                        'users' => (int) $metricValues[0]->getValue(),
                    ];
                }

                Log::info('Realtime Locations Processed Data', ['data' => $data]);
                return $data;
            } catch (Exception $e) {
                Log::error('Lỗi lấy realtime locations: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Lấy dữ liệu realtime - Nguồn truy cập
     */
    public function getRealtimeSources(): array
    {
        if (!$this->client) {
            return [];
        }

        return Cache::remember('analytics.realtime.sources', 60, function () {
            try {
                $request = new RunRealtimeReportRequest([
                    'property' => "properties/{$this->propertyId}",
                    'dimensions' => [
                        new Dimension(['name' => 'firstUserSource']),
                        new Dimension(['name' => 'firstUserMedium']),
                    ],
                    'metrics' => [
                        new Metric(['name' => 'activeUsers']),
                    ],
                    'limit' => 20,
                ]);
                $response = $this->client->runRealtimeReport($request);

                // Log response để debug
                Log::info('Realtime Sources API Response', [
                    'row_count' => $response->getRows()->count(),
                    'response' => json_decode($response->serializeToJsonString(), true)
                ]);

                $data = [];
                foreach ($response->getRows() as $row) {
                    $dimensionValues = $row->getDimensionValues();
                    $metricValues = $row->getMetricValues();

                    $data[] = [
                        'source' => $dimensionValues[0]->getValue(),
                        'medium' => $dimensionValues[1]->getValue(),
                        'users' => (int) $metricValues[0]->getValue(),
                    ];
                }

                Log::info('Realtime Sources Processed Data', ['data' => $data]);
                return $data;
            } catch (Exception $e) {
                Log::error('Lỗi lấy realtime sources: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Lấy dữ liệu realtime - Thiết bị
     */
    public function getRealtimeDevices(): array
    {
        if (!$this->client) {
            return [];
        }

        return Cache::remember('analytics.realtime.devices', 60, function () {
            try {
                $request = new RunRealtimeReportRequest([
                    'property' => "properties/{$this->propertyId}",
                    'dimensions' => [
                        new Dimension(['name' => 'deviceCategory']),
                    ],
                    'metrics' => [
                        new Metric(['name' => 'activeUsers']),
                    ],
                ]);
                $response = $this->client->runRealtimeReport($request);

                // Log response để debug
                Log::info('Realtime Devices API Response', [
                    'row_count' => $response->getRows()->count(),
                    'response' => json_decode($response->serializeToJsonString(), true)
                ]);

                $data = [];
                foreach ($response->getRows() as $row) {
                    $dimensionValues = $row->getDimensionValues();
                    $metricValues = $row->getMetricValues();

                    $data[] = [
                        'device' => $dimensionValues[0]->getValue(),
                        'users' => (int) $metricValues[0]->getValue(),
                    ];
                }

                Log::info('Realtime Devices Processed Data', ['data' => $data]);
                return $data;
            } catch (Exception $e) {
                Log::error('Lỗi lấy realtime devices: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Lấy tổng số người đang online
     */
    public function getTotalActiveUsers(): int
    {
        if (!$this->client) {
            return 0;
        }

        return Cache::remember('analytics.realtime.total_users', 60, function () {
            try {
                $request = new RunRealtimeReportRequest([
                    'property' => "properties/{$this->propertyId}",
                    'metrics' => [
                        new Metric(['name' => 'activeUsers']),
                    ],
                ]);
                $response = $this->client->runRealtimeReport($request);

                // Log response để debug
                Log::info('Total Active Users API Response', [
                    'row_count' => $response->getRows()->count(),
                    'response' => json_decode($response->serializeToJsonString(), true)
                ]);

                if ($response->getRows()->count() > 0) {
                    $row = $response->getRows()[0];
                    $total = (int) $row->getMetricValues()[0]->getValue();
                    Log::info('Total Active Users Processed', ['total' => $total]);
                    return $total;
                }

                return 0;
            } catch (Exception $e) {
                Log::error('Lỗi lấy total active users: ' . $e->getMessage());
                return 0;
            }
        });
    }

    /**
     * Lấy báo cáo hành vi (không realtime) - 7 ngày qua
     */
    public function getBehaviorReport(int $days = 7): array
    {
        if (!$this->client) {
            return [];
        }

        try {
            $request = new RunReportRequest([
                'property' => "properties/{$this->propertyId}",
                'date_ranges' => [
                    new DateRange([
                        'start_date' => "{$days}daysAgo",
                        'end_date' => 'today',
                    ]),
                ],
                'dimensions' => [
                    new Dimension(['name' => 'pagePath']),
                ],
                'metrics' => [
                    new Metric(['name' => 'screenPageViews']),
                    new Metric(['name' => 'engagedSessions']),
                ],
                'limit' => 20,
                'order_bys' => [
                    new OrderBy([
                        'metric' => new MetricOrderBy(['metric_name' => 'screenPageViews']),
                        'desc' => true,
                    ]),
                ],
            ]);
            $response = $this->client->runReport($request);

            $data = [];
            foreach ($response->getRows() as $row) {
                $dimensionValues = $row->getDimensionValues();
                $metricValues = $row->getMetricValues();

                $data[] = [
                    'page' => $dimensionValues[0]->getValue(),
                    'views' => (int) $metricValues[0]->getValue(),
                    'engaged_sessions' => (int) $metricValues[1]->getValue(),
                ];
            }

            return $data;
        } catch (Exception $e) {
            Log::error('Lỗi lấy behavior report: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy tất cả dữ liệu realtime
     */
    public function getAllRealtime(): array
    {
        return [
            'total_active_users' => $this->getTotalActiveUsers(),
            'pages' => $this->getRealtimePages(),
            'locations' => $this->getRealtimeLocations(),
            'sources' => $this->getRealtimeSources(),
            'devices' => $this->getRealtimeDevices(),
        ];
    }

    /**
     * Lấy dữ liệu acquisition - Sessions theo channel
     */
    public function getAcquisitionChannels(int $days = 7): array
    {
        if (!$this->client) {
            return [];
        }

        $cacheKey = "analytics.acquisition.channels.{$days}";
        $cacheTime = $days <= 7 ? 300 : ($days <= 30 ? 600 : 1800); // 5min, 10min, 30min

        return Cache::remember($cacheKey, $cacheTime, function () use ($days) {
            try {
                $request = new RunReportRequest([
                    'property' => "properties/{$this->propertyId}",
                    'date_ranges' => [
                        new DateRange([
                            'start_date' => "{$days}daysAgo",
                            'end_date' => 'today',
                        ]),
                    ],
                    'dimensions' => [
                        new Dimension(['name' => 'sessionDefaultChannelGroup']),
                    ],
                    'metrics' => [
                        new Metric(['name' => 'sessions']),
                        new Metric(['name' => 'averageSessionDuration']),
                        new Metric(['name' => 'newUsers']),
                        new Metric(['name' => 'bounceRate']),
                        new Metric(['name' => 'screenPageViews']),
                    ],
                    'limit' => 20,
                    'order_bys' => [
                        new OrderBy([
                            'metric' => new MetricOrderBy(['metric_name' => 'sessions']),
                            'desc' => true,
                        ]),
                    ],
                ]);
                $response = $this->client->runReport($request);

                // Log response để debug
                Log::info('Acquisition Channels API Response', [
                    'days' => $days,
                    'row_count' => $response->getRows()->count(),
                    'response' => json_decode($response->serializeToJsonString(), true)
                ]);

                $data = [];
                foreach ($response->getRows() as $row) {
                    $dimensionValues = $row->getDimensionValues();
                    $metricValues = $row->getMetricValues();

                    $data[] = [
                        'channel' => $dimensionValues[0]->getValue(),
                        'sessions' => (int) $metricValues[0]->getValue(),
                        'avg_session_duration' => $metricValues[1]->getValue(),
                        'new_users' => (int) $metricValues[2]->getValue(),
                        'bounce_rate' => (float) $metricValues[3]->getValue(),
                        'page_views' => (int) $metricValues[4]->getValue(),
                    ];
                }

                Log::info('Acquisition Channels Processed Data', ['data' => $data]);
                return $data;
            } catch (Exception $e) {
                Log::error('Lỗi lấy acquisition channels: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Lấy dữ liệu sessions theo ngày (cho line chart)
     */
    public function getSessionsByDate(int $days = 7): array
    {
        if (!$this->client) {
            return [];
        }

        $cacheKey = "analytics.sessions.by_date.{$days}";
        $cacheTime = $days <= 7 ? 300 : ($days <= 30 ? 600 : 1800);

        return Cache::remember($cacheKey, $cacheTime, function () use ($days) {
            try {
                $request = new RunReportRequest([
                    'property' => "properties/{$this->propertyId}",
                    'date_ranges' => [
                        new DateRange([
                            'start_date' => "{$days}daysAgo",
                            'end_date' => 'today',
                        ]),
                    ],
                    'dimensions' => [
                        new Dimension(['name' => 'date']),
                    ],
                    'metrics' => [
                        new Metric(['name' => 'sessions']),
                        new Metric(['name' => 'screenPageViews']),
                    ],
                    'order_bys' => [
                        new OrderBy([
                            'dimension' => new DimensionOrderBy([
                                'dimension_name' => 'date',
                            ]),
                            'desc' => false,
                        ]),
                    ],
                ]);
                $response = $this->client->runReport($request);

                // Log response để debug
                Log::info('Sessions By Date API Response', [
                    'days' => $days,
                    'row_count' => $response->getRows()->count(),
                    'response' => json_decode($response->serializeToJsonString(), true)
                ]);

                $data = [];
                foreach ($response->getRows() as $row) {
                    $dimensionValues = $row->getDimensionValues();
                    $metricValues = $row->getMetricValues();

                    $date = $dimensionValues[0]->getValue();
                    $formattedDate = \Carbon\Carbon::createFromFormat('Ymd', $date)->format('M d');

                    $data[] = [
                        'date' => $formattedDate,
                        'sessions' => (int) $metricValues[0]->getValue(),
                        'page_views' => (int) $metricValues[1]->getValue(),
                    ];
                }

                Log::info('Sessions By Date Processed Data', ['data' => $data]);
                return $data;
            } catch (Exception $e) {
                Log::error('Lỗi lấy sessions by date: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Lấy tổng hợp metrics cho period
     */
    public function getSummaryMetrics(int $days = 7): array
    {
        if (!$this->client) {
            return [
                'sessions' => 0,
                'avg_session_duration' => 0,
                'new_sessions_percent' => 0,
                'bounce_rate' => 0,
                'goal_completions' => 0,
                'pages_per_session' => 0,
            ];
        }

        $cacheKey = "analytics.summary.metrics.{$days}";
        $cacheTime = $days <= 7 ? 300 : ($days <= 30 ? 600 : 1800);

        return Cache::remember($cacheKey, $cacheTime, function () use ($days) {
            try {
                $request = new RunReportRequest([
                    'property' => "properties/{$this->propertyId}",
                    'date_ranges' => [
                        new DateRange([
                            'start_date' => "{$days}daysAgo",
                            'end_date' => 'today',
                        ]),
                    ],
                    'metrics' => [
                        new Metric(['name' => 'sessions']),
                        new Metric(['name' => 'averageSessionDuration']),
                        new Metric(['name' => 'newUsers']),
                        new Metric(['name' => 'bounceRate']),
                        new Metric(['name' => 'screenPageViews']),
                    ],
                ]);
                $response = $this->client->runReport($request);

                // Log response để debug
                Log::info('Summary Metrics API Response', [
                    'days' => $days,
                    'row_count' => $response->getRows()->count(),
                    'response' => json_decode($response->serializeToJsonString(), true)
                ]);

                if ($response->getRows()->count() > 0) {
                    $row = $response->getRows()[0];
                    $metricValues = $row->getMetricValues();

                    $sessions = (int) $metricValues[0]->getValue();
                    $newUsers = (int) $metricValues[2]->getValue();
                    $pageViews = (int) $metricValues[4]->getValue();

                    $result = [
                        'sessions' => $sessions,
                        'avg_session_duration' => $metricValues[1]->getValue(),
                        'new_sessions_percent' => $sessions > 0 ? round(($newUsers / $sessions) * 100, 2) : 0,
                        'bounce_rate' => (float) $metricValues[3]->getValue(),
                        'goal_completions' => 0, // Cần cấu hình goals trong GA4
                        'pages_per_session' => $sessions > 0 ? round($pageViews / $sessions, 2) : 0,
                    ];

                    Log::info('Summary Metrics Processed Data', ['data' => $result]);
                    return $result;
                }

                return [
                    'sessions' => 0,
                    'avg_session_duration' => 0,
                    'new_sessions_percent' => 0,
                    'bounce_rate' => 0,
                    'goal_completions' => 0,
                    'pages_per_session' => 0,
                ];
            } catch (Exception $e) {
                Log::error('Lỗi lấy summary metrics: ' . $e->getMessage());
                return [
                    'sessions' => 0,
                    'avg_session_duration' => 0,
                    'new_sessions_percent' => 0,
                    'bounce_rate' => 0,
                    'goal_completions' => 0,
                    'pages_per_session' => 0,
                ];
            }
        });
    }

    /**
     * Lấy dữ liệu Audience - Demographics
     */
    public function getAudienceDemographics(int $days = 7): array
    {
        if (!$this->client) {
            return [];
        }

        $cacheKey = "analytics.audience.demographics.{$days}";
        $cacheTime = $days <= 7 ? 300 : ($days <= 30 ? 600 : 1800);

        return Cache::remember($cacheKey, $cacheTime, function () use ($days) {
            try {
                $request = new RunReportRequest([
                    'property' => "properties/{$this->propertyId}",
                    'date_ranges' => [
                        new DateRange([
                            'start_date' => "{$days}daysAgo",
                            'end_date' => 'today',
                        ]),
                    ],
                    'dimensions' => [
                        new Dimension(['name' => 'country']),
                        new Dimension(['name' => 'city']),
                    ],
                    'metrics' => [
                        new Metric(['name' => 'activeUsers']),
                        new Metric(['name' => 'sessions']),
                        new Metric(['name' => 'screenPageViews']),
                    ],
                    'limit' => 20,
                    'order_bys' => [
                        new OrderBy([
                            'metric' => new MetricOrderBy(['metric_name' => 'activeUsers']),
                            'desc' => true,
                        ]),
                    ],
                ]);
                $response = $this->client->runReport($request);

                Log::info('Audience Demographics API Response', [
                    'days' => $days,
                    'row_count' => $response->getRows()->count(),
                ]);

                $data = [];
                foreach ($response->getRows() as $row) {
                    $dimensionValues = $row->getDimensionValues();
                    $metricValues = $row->getMetricValues();

                    $data[] = [
                        'country' => $dimensionValues[0]->getValue(),
                        'city' => $dimensionValues[1]->getValue(),
                        'users' => (int) $metricValues[0]->getValue(),
                        'sessions' => (int) $metricValues[1]->getValue(),
                        'page_views' => (int) $metricValues[2]->getValue(),
                    ];
                }

                return $data;
            } catch (Exception $e) {
                Log::error('Lỗi lấy audience demographics: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Lấy dữ liệu Audience - Devices
     */
    public function getAudienceDevices(int $days = 7): array
    {
        if (!$this->client) {
            return [];
        }

        $cacheKey = "analytics.audience.devices.{$days}";
        $cacheTime = $days <= 7 ? 300 : ($days <= 30 ? 600 : 1800);

        return Cache::remember($cacheKey, $cacheTime, function () use ($days) {
            try {
                $request = new RunReportRequest([
                    'property' => "properties/{$this->propertyId}",
                    'date_ranges' => [
                        new DateRange([
                            'start_date' => "{$days}daysAgo",
                            'end_date' => 'today',
                        ]),
                    ],
                    'dimensions' => [
                        new Dimension(['name' => 'deviceCategory']),
                    ],
                    'metrics' => [
                        new Metric(['name' => 'activeUsers']),
                        new Metric(['name' => 'sessions']),
                        new Metric(['name' => 'averageSessionDuration']),
                    ],
                    'order_bys' => [
                        new OrderBy([
                            'metric' => new MetricOrderBy(['metric_name' => 'activeUsers']),
                            'desc' => true,
                        ]),
                    ],
                ]);
                $response = $this->client->runReport($request);

                $data = [];
                foreach ($response->getRows() as $row) {
                    $dimensionValues = $row->getDimensionValues();
                    $metricValues = $row->getMetricValues();

                    $data[] = [
                        'device' => $dimensionValues[0]->getValue(),
                        'users' => (int) $metricValues[0]->getValue(),
                        'sessions' => (int) $metricValues[1]->getValue(),
                        'avg_duration' => $metricValues[2]->getValue(),
                    ];
                }

                return $data;
            } catch (Exception $e) {
                Log::error('Lỗi lấy audience devices: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Lấy dữ liệu Conversions - Events
     */
    public function getConversions(int $days = 7): array
    {
        if (!$this->client) {
            return [];
        }

        $cacheKey = "analytics.conversions.{$days}";
        $cacheTime = $days <= 7 ? 300 : ($days <= 30 ? 600 : 1800);

        return Cache::remember($cacheKey, $cacheTime, function () use ($days) {
            try {
                $request = new RunReportRequest([
                    'property' => "properties/{$this->propertyId}",
                    'date_ranges' => [
                        new DateRange([
                            'start_date' => "{$days}daysAgo",
                            'end_date' => 'today',
                        ]),
                    ],
                    'dimensions' => [
                        new Dimension(['name' => 'eventName']),
                    ],
                    'metrics' => [
                        new Metric(['name' => 'eventCount']),
                        new Metric(['name' => 'totalUsers']),
                    ],
                    'limit' => 20,
                    'order_bys' => [
                        new OrderBy([
                            'metric' => new MetricOrderBy(['metric_name' => 'eventCount']),
                            'desc' => true,
                        ]),
                    ],
                ]);
                $response = $this->client->runReport($request);

                $data = [];
                foreach ($response->getRows() as $row) {
                    $dimensionValues = $row->getDimensionValues();
                    $metricValues = $row->getMetricValues();

                    $data[] = [
                        'event_name' => $dimensionValues[0]->getValue(),
                        'event_count' => (int) $metricValues[0]->getValue(),
                        'total_users' => (int) $metricValues[1]->getValue(),
                    ];
                }

                return $data;
            } catch (Exception $e) {
                Log::error('Lỗi lấy conversions: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Lấy dữ liệu Pages - Top pages
     */
    public function getTopPages(int $days = 7): array
    {
        if (!$this->client) {
            return [];
        }

        $cacheKey = "analytics.pages.top.{$days}";
        $cacheTime = $days <= 7 ? 300 : ($days <= 30 ? 600 : 1800);

        return Cache::remember($cacheKey, $cacheTime, function () use ($days) {
            try {
                $request = new RunReportRequest([
                    'property' => "properties/{$this->propertyId}",
                    'date_ranges' => [
                        new DateRange([
                            'start_date' => "{$days}daysAgo",
                            'end_date' => 'today',
                        ]),
                    ],
                    'dimensions' => [
                        new Dimension(['name' => 'pagePath']),
                        new Dimension(['name' => 'pageTitle']),
                    ],
                    'metrics' => [
                        new Metric(['name' => 'screenPageViews']),
                        new Metric(['name' => 'activeUsers']),
                        new Metric(['name' => 'averageSessionDuration']),
                        new Metric(['name' => 'bounceRate']),
                    ],
                    'limit' => 20,
                    'order_bys' => [
                        new OrderBy([
                            'metric' => new MetricOrderBy(['metric_name' => 'screenPageViews']),
                            'desc' => true,
                        ]),
                    ],
                ]);
                $response = $this->client->runReport($request);

                $data = [];
                foreach ($response->getRows() as $row) {
                    $dimensionValues = $row->getDimensionValues();
                    $metricValues = $row->getMetricValues();

                    $data[] = [
                        'page_path' => $dimensionValues[0]->getValue(),
                        'page_title' => $dimensionValues[1]->getValue(),
                        'page_views' => (int) $metricValues[0]->getValue(),
                        'users' => (int) $metricValues[1]->getValue(),
                        'avg_duration' => $metricValues[2]->getValue(),
                        'bounce_rate' => (float) $metricValues[3]->getValue(),
                    ];
                }

                return $data;
            } catch (Exception $e) {
                Log::error('Lỗi lấy top pages: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Lấy dữ liệu Events - All events
     */
    public function getAllEvents(int $days = 7): array
    {
        if (!$this->client) {
            return [];
        }

        $cacheKey = "analytics.events.all.{$days}";
        $cacheTime = $days <= 7 ? 300 : ($days <= 30 ? 600 : 1800);

        return Cache::remember($cacheKey, $cacheTime, function () use ($days) {
            try {
                $request = new RunReportRequest([
                    'property' => "properties/{$this->propertyId}",
                    'date_ranges' => [
                        new DateRange([
                            'start_date' => "{$days}daysAgo",
                            'end_date' => 'today',
                        ]),
                    ],
                    'dimensions' => [
                        new Dimension(['name' => 'eventName']),
                    ],
                    'metrics' => [
                        new Metric(['name' => 'eventCount']),
                        new Metric(['name' => 'totalUsers']),
                        new Metric(['name' => 'eventValue']),
                    ],
                    'limit' => 50,
                    'order_bys' => [
                        new OrderBy([
                            'metric' => new MetricOrderBy(['metric_name' => 'eventCount']),
                            'desc' => true,
                        ]),
                    ],
                ]);
                $response = $this->client->runReport($request);

                $data = [];
                foreach ($response->getRows() as $row) {
                    $dimensionValues = $row->getDimensionValues();
                    $metricValues = $row->getMetricValues();

                    $data[] = [
                        'event_name' => $dimensionValues[0]->getValue(),
                        'event_count' => (int) $metricValues[0]->getValue(),
                        'total_users' => (int) $metricValues[1]->getValue(),
                        'event_value' => (float) $metricValues[2]->getValue(),
                    ];
                }

                return $data;
            } catch (Exception $e) {
                Log::error('Lỗi lấy all events: ' . $e->getMessage());
                return [];
            }
        });
    }
}
