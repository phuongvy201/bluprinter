@php
    $urlName = $urlName ?? 'image';
    $fileName = $fileName ?? $urlName . '_file';
    $value = $value ?? '';
    $previewKey = $previewKey ?? null;
    $placeholder = $placeholder ?? 'Hoặc dán URL ảnh';
    $inputClass = $inputClass ?? 'w-full rounded-md border-gray-300 text-xs';
@endphp
<div class="space-y-1.5">
    @if ($value)
        <img src="{{ $value }}" alt="" class="w-full h-20 object-cover rounded-md border border-gray-200 bg-gray-50">
    @endif
    <input type="text" name="{{ $urlName }}" value="{{ $value }}"
           class="{{ $inputClass }}" placeholder="{{ $placeholder }}"
           @if($previewKey) data-home-input="{{ $previewKey }}" @endif>
    <input type="file" name="{{ $fileName }}" accept="image/jpeg,image/png,image/webp,image/gif"
           class="{{ $inputClass }} file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700"
           @if($previewKey) data-home-file="{{ $previewKey }}" @endif>
    <p class="text-[10px] text-gray-400">Upload lên AWS S3 (tối đa 5MB) hoặc dán URL</p>
</div>
