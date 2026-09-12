@php
    $index = $index ?? 0;
    $row = is_array($row ?? null) ? $row : [];
    $fallback = $fallback ?? ['x' => 30, 'y' => 22, 'width' => 40, 'height' => 42];
    $x = $row['print_area_x'] ?? $fallback['x'];
    $y = $row['print_area_y'] ?? $fallback['y'];
    $w = $row['print_area_width'] ?? $fallback['width'];
    $h = $row['print_area_height'] ?? $fallback['height'];
    $imageUrl = $row['image_url'] ?? '';
@endphp
<div class="studio-mockup-card border border-gray-200 rounded-2xl p-4 bg-gray-50 space-y-3" data-mockup-card data-mockup-index="{{ $index }}" data-print-picker>
    @if(!empty($row['id']))
        <input type="hidden" name="mockups[{{ $index }}][id]" value="{{ $row['id'] }}">
    @endif
    <div class="flex items-start justify-between gap-3">
        <p class="text-sm font-semibold text-gray-800">Mockup</p>
        @if(!empty($row['id']))
            <label class="inline-flex items-center gap-2 text-sm text-red-600">
                <input type="checkbox" name="mockups[{{ $index }}][delete]" value="1">
                Remove
            </label>
        @else
            <button type="button" class="text-sm text-red-600 hover:underline" data-remove-mockup>Remove</button>
        @endif
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Label</label>
            <input type="text" name="mockups[{{ $index }}][name]" value="{{ $row['name'] ?? '' }}" class="w-full rounded-lg border-gray-300" placeholder="Men crew neck">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Color (optional)</label>
            <input type="text" name="mockups[{{ $index }}][color]" value="{{ $row['color'] ?? '' }}" class="w-full rounded-lg border-gray-300" placeholder="White">
            <p class="text-[11px] text-gray-500 mt-1">Same name as a swatch (White, Navy…). If empty, the swatch recolors this photo.</p>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Sort</label>
            <input type="number" name="mockups[{{ $index }}][sort_order]" value="{{ $row['sort_order'] ?? $index }}" class="w-full rounded-lg border-gray-300">
        </div>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Upload mockup image</label>
        <input type="file" name="mockups[{{ $index }}][image_file]" accept="image/*" class="w-full text-sm" data-print-file>
        <p class="text-xs text-gray-500 mt-1">Best for recoloring: <strong>PNG with transparent background</strong>, shirt in white or light gray. JPG on a pure white background also works. Dark/navy photos will not recolor — upload those only if Color is set to that exact swatch.</p>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Or paste image URL</label>
        <input type="text" name="mockups[{{ $index }}][image_url]" value="{{ $imageUrl }}" class="w-full rounded-lg border-gray-300" placeholder="https://… or /images/…" data-print-url>
    </div>

    <div class="studio-print-picker">
        <div class="studio-print-picker__stage{{ $imageUrl === '' ? ' is-empty' : '' }}" data-print-stage>
            <img
                class="studio-print-picker__img{{ $imageUrl === '' ? ' is-empty' : '' }}"
                data-print-img
                alt="Mockup preview"
                @if($imageUrl !== '') src="{{ $imageUrl }}" @endif
                draggable="false"
            >
            <p class="studio-print-picker__empty" data-print-empty @if($imageUrl !== '') hidden @endif>
                Upload a mockup, then drag a box over the print area.
            </p>
            <div class="studio-print-picker__box" data-print-box @if($imageUrl === '') hidden @endif>
                <span class="studio-print-picker__handle" data-print-handle="nw"></span>
                <span class="studio-print-picker__handle" data-print-handle="ne"></span>
                <span class="studio-print-picker__handle" data-print-handle="sw"></span>
                <span class="studio-print-picker__handle" data-print-handle="se"></span>
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-2">Drag the box to move. Drag a corner to resize. Click empty mockup area to draw a new box.</p>
        <div class="grid grid-cols-4 gap-2 mt-2">
            <label class="block">
                <span class="block text-[11px] text-gray-500 mb-0.5">X %</span>
                <input type="number" step="0.1" min="0" max="96" name="mockups[{{ $index }}][print_area_x]" value="{{ $x }}" class="w-full rounded-lg border-gray-300 text-sm" data-print-x>
            </label>
            <label class="block">
                <span class="block text-[11px] text-gray-500 mb-0.5">Y %</span>
                <input type="number" step="0.1" min="0" max="96" name="mockups[{{ $index }}][print_area_y]" value="{{ $y }}" class="w-full rounded-lg border-gray-300 text-sm" data-print-y>
            </label>
            <label class="block">
                <span class="block text-[11px] text-gray-500 mb-0.5">W %</span>
                <input type="number" step="0.1" min="4" max="100" name="mockups[{{ $index }}][print_area_width]" value="{{ $w }}" class="w-full rounded-lg border-gray-300 text-sm" data-print-w>
            </label>
            <label class="block">
                <span class="block text-[11px] text-gray-500 mb-0.5">H %</span>
                <input type="number" step="0.1" min="4" max="100" name="mockups[{{ $index }}][print_area_height]" value="{{ $h }}" class="w-full rounded-lg border-gray-300 text-sm" data-print-h>
            </label>
        </div>
    </div>
</div>
