@extends('layouts.grok-bot')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Bot file in POD</h1>
        <p class="mt-2 text-sm text-gray-600">Đưa ảnh nguồn, chọn design, clone hoặc redesign, rồi khai vùng in. Mặc định ngực áo là 4500×5400 px, 300 dpi. Giai đoạn 1 chỉ xuất PNG khi chọn clone và đủ spec.</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('grok-bot.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="rounded-xl border border-gray-200 bg-white p-6 space-y-5">
            <div>
                <label for="source" class="block text-sm font-medium text-gray-900">Ảnh nguồn</label>
                <input id="source" name="source" type="file" accept="image/png,image/jpeg,image/webp" required
                       class="mt-2 block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100">
                <p class="mt-1 text-xs text-gray-500">JPG, PNG hoặc WEBP, tối đa 20 MB.</p>
            </div>

            <fieldset>
                <legend class="text-sm font-medium text-gray-900">Loại yêu cầu</legend>
                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                    @foreach (['design' => 'Design', 'clone' => 'Clone', 'redesign' => 'Redesign'] as $value => $label)
                        <label class="flex cursor-pointer items-start gap-2 rounded-lg border border-gray-200 px-3 py-3 text-sm">
                            <input type="radio" name="request_type" value="{{ $value }}" class="mt-0.5 text-blue-600" @checked(old('request_type', 'clone') === $value) required>
                            <span>
                                <span class="font-semibold text-gray-900">{{ $label }}</span>
                                <span class="mt-1 block text-xs text-gray-500">
                                    @if ($value === 'clone')
                                        Xuất một PNG đúng pixel và DPI.
                                    @else
                                        Ghi nhận, mở ở giai đoạn 2.
                                    @endif
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="width" class="block text-sm font-medium text-gray-900">Rộng (px)</label>
                    <input id="width" name="width" type="number" min="1" max="6000" inputmode="numeric"
                           value="{{ old('width', 4500) }}"
                           class="mt-2 w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label for="height" class="block text-sm font-medium text-gray-900">Cao (px)</label>
                    <input id="height" name="height" type="number" min="1" max="6000" inputmode="numeric"
                           value="{{ old('height', 5400) }}"
                           class="mt-2 w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label for="dpi" class="block text-sm font-medium text-gray-900">DPI</label>
                    <input id="dpi" name="dpi" type="number" min="1" max="1200" inputmode="numeric"
                           value="{{ old('dpi', 300) }}"
                           class="mt-2 w-full rounded-lg border-gray-300 text-sm">
                </div>
            </div>

            <div>
                <label for="instruction" class="block text-sm font-medium text-gray-900">Yêu cầu thêm</label>
                <textarea id="instruction" name="instruction" rows="3" maxlength="2000"
                          class="mt-2 w-full rounded-lg border-gray-300 text-sm"
                          placeholder="Ví dụ: clone vùng ngực áo theo ảnh này.">{{ old('instruction') }}</textarea>
            </div>
        </div>

        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Clone không vẽ lại bằng ảnh generative. Font, vị trí và art chỉ hiện Đạt khi pipeline kiểm được. Bản này không cam kết mức 99%.
        </div>

        <button type="submit" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
            Gửi cho bot
        </button>
    </form>
</div>
@endsection
