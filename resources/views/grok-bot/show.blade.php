@extends('layouts.grok-bot')

@php
    $typeLabels = ['design' => 'Design', 'clone' => 'Clone', 'redesign' => 'Redesign'];
    $statusLabels = ['dat' => 'Đạt', 'lech' => 'Lệch', 'chua_kiem' => 'Chưa kiểm được'];
    $statusClass = [
        'dat' => 'bg-green-100 text-green-800',
        'lech' => 'bg-red-100 text-red-800',
        'chua_kiem' => 'bg-amber-100 text-amber-900',
    ];
    $openItems = collect($job->checklist ?? [])->filter(fn ($item) => ($item['status'] ?? '') !== 'dat');
@endphp

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('grok-bot.create') }}" class="text-sm text-blue-600 hover:underline">← Yêu cầu mới</a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">Kết quả bot file in</h1>
            <p class="mt-1 text-sm text-gray-600">
                {{ $typeLabels[$job->request_type] ?? $job->request_type }}
                @if ($job->width && $job->height && $job->dpi)
                    · {{ $job->width }}×{{ $job->height }} px · {{ $job->dpi }} dpi
                @endif
            </p>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6">
        <h2 class="text-lg font-semibold text-gray-900">Điều phối</h2>
        <p class="mt-2 text-sm text-gray-800">{{ $job->coordinator_message }}</p>
        @if ($job->coordinator_model)
            <p class="mt-2 text-xs text-gray-500">Đã gọi Grok ({{ $job->coordinator_model }}).</p>
        @endif
        @if ($job->instruction)
            <p class="mt-3 text-sm text-gray-600"><span class="font-medium text-gray-900">Yêu cầu thêm:</span> {{ $job->instruction }}</p>
        @endif
    </div>

    @if ($job->outcome === 'phase_2')
        <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
            Design và redesign mở ở giai đoạn 2. Yêu cầu này chưa xuất file in.
        </div>
    @endif

    @if ($job->outcome === 'clone')
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-semibold text-gray-900">File PNG</h2>
                @if ($job->print_path)
                    <img src="{{ route('grok-bot.file', ['grokBot' => $job, 'inline' => 1]) }}" alt="Xem trước file in" class="mt-4 max-h-80 w-full rounded-lg border border-gray-200 bg-gray-50 object-contain">
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <a href="{{ route('grok-bot.file', $job) }}" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Tải PNG</a>
                        @if ($job->checklistReady())
                            <span class="text-sm text-green-700">Mọi mục checklist đã kiểm đều đạt.</span>
                        @else
                            <span class="text-sm text-amber-800">Chưa đủ để duyệt in. Mục chưa kiểm được không tính là đạt.</span>
                        @endif
                    </div>
                    @if ($job->print_model)
                        <p class="mt-3 text-xs text-gray-500">Agent file in đã gọi Grok ({{ $job->print_model }}). Checklist đọc từ file PNG, không lấy từ câu trả lời của model.</p>
                    @endif
                @else
                    <p class="mt-3 text-sm text-gray-600">Chưa có file in.</p>
                @endif
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-semibold text-gray-900">Checklist</h2>
                <ul class="mt-4 divide-y divide-gray-100">
                    @foreach ($job->checklist ?? [] as $item)
                        <li class="py-3">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-medium text-gray-900">{{ $item['label'] ?? '' }}</span>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusClass[$item['status'] ?? ''] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ $statusLabels[$item['status'] ?? ''] ?? 'Chưa rõ' }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-600">{{ $item['detail'] ?? '' }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6">
            <h2 class="text-lg font-semibold text-gray-900">Lớp pipeline tách được</h2>
            <ul class="mt-4 space-y-3">
                @foreach ($job->layers ?? [] as $layer)
                    <li class="text-sm text-gray-700">
                        <span class="font-semibold text-gray-900">{{ $layer['kind'] ?? '' }}:</span>
                        {{ ($layer['extracted'] ?? false) ? 'có lớp' : 'không tách được' }}.
                        {{ $layer['detail'] ?? '' }}
                    </li>
                @endforeach
            </ul>
        </div>

        @if ($openItems->isNotEmpty())
            <form method="POST" action="{{ route('grok-bot.redo', $job) }}" class="rounded-xl border border-gray-200 bg-white p-6 space-y-4">
                @csrf
                <h2 class="text-lg font-semibold text-gray-900">Gửi làm lại</h2>
                <p class="text-sm text-gray-600">Chọn mục chưa đạt. Agent file in dựng lại PNG rồi checklist chạy lại. Mục chưa tách được lớp vẫn không được đánh Đạt.</p>
                @error('items')
                    <p class="text-sm text-red-700">{{ $message }}</p>
                @enderror
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($openItems as $item)
                        <label class="flex items-center gap-2 text-sm text-gray-800">
                            <input type="checkbox" name="items[]" value="{{ $item['key'] }}" class="rounded border-gray-300 text-blue-600" @checked(in_array($item['key'], old('items', []), true))>
                            {{ $item['label'] ?? $item['key'] }}
                        </label>
                    @endforeach
                </div>
                <div>
                    <label for="note" class="block text-sm font-medium text-gray-900">Ghi chú</label>
                    <textarea id="note" name="note" rows="2" maxlength="1000" class="mt-2 w-full rounded-lg border-gray-300 text-sm">{{ old('note') }}</textarea>
                </div>
                <button type="submit" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50">
                    Gửi làm lại
                </button>
            </form>
        @endif
    @endif
</div>
@endsection
