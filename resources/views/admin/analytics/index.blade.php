@extends('layouts.admin')

@section('title', 'Google Analytics')

@section('content')
<div class="space-y-6" x-data="{ 
    days: {{ $days }},
    tab: '{{ $tab }}',
    changeDays(newDays) {
        this.days = newDays;
        window.location.href = '{{ route('admin.analytics.index') }}?days=' + newDays + '&tab=' + this.tab;
    },
    changeTab(newTab) {
        this.tab = newTab;
        const filter = '{{ $filter ?? 'all' }}';
        window.location.href = '{{ route('admin.analytics.index') }}?days=' + this.days + '&tab=' + newTab + (filter !== 'all' ? '&filter=' + filter : '');
    },
    changeFilter(filter) {
        window.location.href = '{{ route('admin.analytics.index') }}?days=' + this.days + '&tab={{ $tab }}&filter=' + filter;
    }
}">
    <!-- Header -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Google Analytics</h1>
                <div class="flex items-center gap-4 mt-4">
                    <button 
                        @click="changeTab('acquisition')"
                        :class="tab === 'acquisition' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-600 hover:text-gray-900'"
                        class="px-4 py-2 transition-colors">
                        Acquisition
                    </button>
                    <button 
                        @click="changeTab('audience')"
                        :class="tab === 'audience' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-600 hover:text-gray-900'"
                        class="px-4 py-2 transition-colors">
                        Audience
                    </button>
                    <button 
                        @click="changeTab('conversions')"
                        :class="tab === 'conversions' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-600 hover:text-gray-900'"
                        class="px-4 py-2 transition-colors">
                        Conversions
                    </button>
                    <button 
                        @click="changeTab('pages')"
                        :class="tab === 'pages' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-600 hover:text-gray-900'"
                        class="px-4 py-2 transition-colors">
                        Pages
                    </button>
                    <button 
                        @click="changeTab('events')"
                        :class="tab === 'events' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-600 hover:text-gray-900'"
                        class="px-4 py-2 transition-colors">
                        Events
                    </button>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <select 
                    x-model="days"
                    @change="changeDays($event.target.value)"
                    class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="7">Last 7 Days</option>
                    <option value="30">Last 30 Days</option>
                    <option value="90">Last 90 Days</option>
                </select>
                <button class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                </button>
                <button class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    @if($tab === 'acquisition')
        <!-- Acquisition Sub Navigation -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-4 overflow-x-auto">
                @php
                    $filters = ['All', 'Organic Search', 'Paid Search', 'Direct', 'Social', 'Referrals', 'Display', 'Email', 'Other'];
                    $currentFilter = $filter ?? 'all';
                @endphp
                @foreach($filters as $filterOption)
                    <button 
                        onclick="changeFilter('{{ strtolower(str_replace(' ', '-', $filterOption)) }}')"
                        class="px-4 py-2 rounded-lg font-medium whitespace-nowrap transition-colors {{ 
                            strtolower(str_replace(' ', '-', $currentFilter)) === strtolower(str_replace(' ', '-', $filterOption)) 
                                ? 'bg-blue-50 text-blue-600' 
                                : 'text-gray-600 hover:bg-gray-50' 
                        }}">
                        {{ $filterOption }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Sessions Line Chart -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Sessions</h3>
                <div class="h-64">
                    <canvas id="sessionsChart"></canvas>
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <div>
                        @php
                            $filteredSessions = array_sum(array_column($sessionsByDate, 'sessions'));
                            if (($filter ?? 'all') !== 'all' && isset($channels)) {
                                $filteredSessions = array_sum(array_column($channels, 'sessions'));
                            }
                        @endphp
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($filteredSessions) }}</p>
                        <p class="text-sm text-green-600 mt-1">+14% from previous period</p>
                    </div>
                </div>
            </div>

            <!-- Sessions Donut Chart -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Sessions</h3>
                <div class="flex items-center justify-center h-64">
                    <div class="relative w-48 h-48">
                        <canvas id="sessionsDonutChart"></canvas>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalSessions) }}</p>
                                <p class="text-sm text-gray-600">Sessions</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 space-y-2 max-h-48 overflow-y-auto">
                    @foreach($channels as $channel)
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full" style="background-color: {{ ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#6366f1', '#ef4444', '#14b8a6'][$loop->index % 8] }}"></div>
                                <span class="text-gray-700">{{ $channel['channel'] }}</span>
                            </div>
                            <span class="font-medium text-gray-900">{{ number_format($channel['sessions']) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                        <p class="text-sm font-medium text-gray-600 mb-1">Sessions</p>
                        @php
                            $filteredSessions = $summaryMetrics['sessions'];
                            if (($filter ?? 'all') !== 'all' && isset($channels)) {
                                $filteredSessions = array_sum(array_column($channels, 'sessions'));
                            }
                        @endphp
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($filteredSessions) }}</p>
                <p class="text-sm text-red-600 mt-1">-23%</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <p class="text-sm font-medium text-gray-600 mb-1">Avg. Session Duration</p>
                <p class="text-2xl font-bold text-gray-900">{{ gmdate('H:i:s', (int)$summaryMetrics['avg_session_duration']) }}</p>
                <p class="text-sm text-red-600 mt-1">-10%</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <p class="text-sm font-medium text-gray-600 mb-1">% New Sessions</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($summaryMetrics['new_sessions_percent'], 2) }}%</p>
                <p class="text-sm text-red-600 mt-1">-7%</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <p class="text-sm font-medium text-gray-600 mb-1">Bounce Rate</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($summaryMetrics['bounce_rate'], 2) }}%</p>
                <p class="text-sm text-red-600 mt-1">-68%</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <p class="text-sm font-medium text-gray-600 mb-1">Goal Completions</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($summaryMetrics['goal_completions']) }}</p>
                <p class="text-sm text-green-600 mt-1">+80%</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <p class="text-sm font-medium text-gray-600 mb-1">Pages/Session</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($summaryMetrics['pages_per_session'], 2) }}</p>
                <p class="text-sm text-red-600 mt-1">-9%</p>
            </div>
        </div>

        <!-- Traffic Sources Table - Chi tiết nguồn truy cập -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Traffic Sources - Chi tiết nguồn truy cập</h3>
                    <p class="text-sm text-gray-600 mt-1">Hiển thị chi tiết lượt truy cập từ TikTok, Facebook, Pinterest, Google, v.v.</p>
                </div>
                <div class="flex items-center gap-3">
                    <input type="text" id="sourceSearch" placeholder="Tìm kiếm nguồn..." class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full" id="trafficSourcesTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SOURCE TYPE</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SOURCE</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">MEDIUM</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SESSIONS</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">AVG. SESSION DURATION</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NEW USERS</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">BOUNCE RATE</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PAGE VIEWS</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($trafficSources ?? [] as $source)
                            <tr class="hover:bg-gray-50 source-row" data-source="{{ strtolower($source['source'] . ' ' . $source['source_type']) }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        @if(in_array($source['source_type'], ['Facebook', 'TikTok', 'Pinterest', 'Instagram', 'Twitter/X', 'YouTube', 'LinkedIn'])) bg-blue-100 text-blue-800
                                        @elseif(in_array($source['source_type'], ['Google', 'Bing'])) bg-green-100 text-green-800
                                        @elseif($source['source_type'] === 'Direct') bg-gray-100 text-gray-800
                                        @else bg-purple-100 text-purple-800
                                        @endif">
                                        {{ $source['source_type'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $source['source'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $source['medium'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($source['sessions']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ gmdate('H:i:s', (int)$source['avg_session_duration']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($source['new_users']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($source['bounce_rate'], 2) }}%</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($source['page_views']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Channels Table - Tổng quan theo kênh -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm mt-6">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Channels Overview - Tổng quan theo kênh</h3>
                    <p class="text-sm text-gray-600 mt-1">Showing {{ count($channels) }} of {{ count($channels) }} Rows</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CHANNEL</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SESSIONS</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">AVG. SESSION DURATION</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">% NEW SESSIONS</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">BOUNCE RATE</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GOAL COMPLETIONS</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PAGES/SESSION</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($channels as $channel)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $channel['channel'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($channel['sessions']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ gmdate('H:i:s', (int)$channel['avg_session_duration']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $channel['sessions'] > 0 ? number_format(($channel['new_users'] / $channel['sessions']) * 100, 2) : 0 }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($channel['bounce_rate'], 2) }}%</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $channel['sessions'] > 0 ? number_format($channel['page_views'] / $channel['sessions'], 2) : 0 }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($tab === 'audience')
        <!-- Audience Tab -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Demographics Table -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Demographics</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Country</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">City</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Users</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sessions</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Page Views</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($demographics ?? [] as $demo)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $demo['country'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $demo['city'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($demo['users']) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($demo['sessions']) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($demo['page_views']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Devices Table -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Devices</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Users</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sessions</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Avg Duration</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($devices ?? [] as $device)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $device['device'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($device['users']) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($device['sessions']) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ gmdate('H:i:s', (int)$device['avg_duration']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @elseif($tab === 'conversions')
        <!-- Conversions Tab -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Conversions (Events)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event Count</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Users</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($conversions ?? [] as $conversion)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $conversion['event_name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($conversion['event_count']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($conversion['total_users']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @elseif($tab === 'pages')
        <!-- Pages Tab -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Top Pages</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Page Path</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Page Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Page Views</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Users</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Avg Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bounce Rate</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($topPages ?? [] as $page)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $page['page_path'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $page['page_title'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($page['page_views']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($page['users']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ gmdate('H:i:s', (int)$page['avg_duration']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($page['bounce_rate'], 2) }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @elseif($tab === 'events')
        <!-- Events Tab -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">All Events</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event Count</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Users</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event Value</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($events ?? [] as $event)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $event['event_name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($event['event_count']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($event['total_users']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($event['event_value'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Sessions Line Chart
    const sessionsCtx = document.getElementById('sessionsChart');
    if (sessionsCtx) {
        const sessionsData = @json($sessionsByDate ?? []);
        new Chart(sessionsCtx, {
            type: 'line',
            data: {
                labels: sessionsData.map(d => d.date),
                datasets: [{
                    label: 'Sessions',
                    data: sessionsData.map(d => d.sessions),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Page Views',
                    data: sessionsData.map(d => d.page_views),
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Sessions Donut Chart
    const donutCtx = document.getElementById('sessionsDonutChart');
    if (donutCtx) {
        const channelsData = @json($channels ?? []);
        const colors = ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#6366f1', '#ef4444', '#14b8a6'];
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: channelsData.map(c => c.channel),
                datasets: [{
                    data: channelsData.map(c => c.sessions),
                    backgroundColor: channelsData.map((_, i) => colors[i % colors.length])
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '70%'
            }
        });
    }

    // Search functionality cho Traffic Sources Table
    const sourceSearch = document.getElementById('sourceSearch');
    if (sourceSearch) {
        sourceSearch.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#trafficSourcesTable .source-row');
            
            rows.forEach(row => {
                const sourceText = row.getAttribute('data-source');
                if (sourceText.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Filter function (global for onclick handlers)
    function changeFilter(filter) {
        const days = {{ $days ?? 7 }};
        const tab = '{{ $tab ?? 'acquisition' }}';
        window.location.href = '{{ route('admin.analytics.index') }}?days=' + days + '&tab=' + tab + '&filter=' + filter;
    }
</script>
@endpush
@endsection

