@props(['colors' => [], 'selectedColor' => null, 'onColorSelect' => 'selectAttribute'])

<div class="advanced-color-picker">
    <!-- Color Grid Display -->
    <div class="grid grid-cols-8 gap-3 mb-4">
        @foreach($colors as $color)
            @php
                $colorMap = [
                    'black' => '#000000', 'white' => '#ffffff', 'red' => '#dc2626',
                    'blue' => '#2563eb', 'green' => '#16a34a', 'yellow' => '#eab308',
                    'purple' => '#9333ea', 'pink' => '#ec4899', 'orange' => '#ea580c',
                    'brown' => '#a16207', 'gray' => '#6b7280', 'grey' => '#6b7280',
                    'navy' => '#1e3a8a', 'maroon' => '#991b1b', 'teal' => '#0d9488',
                    'lime' => '#65a30d', 'cyan' => '#06b6d4', 'indigo' => '#4f46e5',
                    'violet' => '#8b5cf6', 'rose' => '#f43f5e', 'amber' => '#f59e0b',
                    'emerald' => '#10b981', 'sky' => '#0ea5e9', 'fuchsia' => '#d946ef',
                    'gold' => '#fbbf24', 'silver' => '#9ca3af', 'copper' => '#b45309',
                    'bronze' => '#92400e', 'platinum' => '#6b7280',
                    'camo' => '#365314', 'olive' => '#65a30d', 'khaki' => '#a3a3a3',
                    'beige' => '#f5f5dc', 'tan' => '#d2b48c', 'mint' => '#a7f3d0',
                    'lavender' => '#e9d5ff', 'coral' => '#fda4af', 'turquoise' => '#5eead4',
                ];
                $colorCode = $colorMap[strtolower($color)] ?? '#6b7280';
                $isSelected = $selectedColor === $color;
            @endphp
            
            <button 
                onclick="{{ $onColorSelect }}('Color', '{{ $color }}')"
                class="advanced-color-swatch relative w-12 h-12 rounded-lg border-2 transition-all duration-200 group focus:outline-none focus:ring-2 focus:ring-[#005366] focus:ring-offset-2 {{ $isSelected ? 'border-[#005366] ring-2 ring-[#005366] ring-offset-2 scale-110' : 'border-gray-300 hover:border-gray-400 hover:scale-105' }}"
                style="background: {{ $colorCode }}; background-image: linear-gradient(45deg, {{ $colorCode }}cc, {{ $colorCode }});"
                title="{{ $color }}"
                data-color="{{ $color }}"
                data-hex="{{ $colorCode }}"
            >
                <!-- Special effects for metallic colors -->
                @if(in_array(strtolower($color), ['gold', 'silver', 'copper', 'bronze', 'platinum']))
                    <div class="absolute inset-0 rounded-lg bg-gradient-to-br from-white/30 via-transparent to-black/20"></div>
                @elseif(in_array(strtolower($color), ['white', 'cream', 'light yellow']))
                    <div class="absolute inset-0 rounded-lg border border-gray-300"></div>
                @endif
                
                <!-- Selected indicator -->
                @if($isSelected)
                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-[#005366] rounded-full flex items-center justify-center">
                        <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                @endif
                
                <!-- Hover tooltip -->
                <div class="absolute -top-12 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-20">
                    {{ $color }}
                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                </div>
            </button>
        @endforeach
    </div>
    
    <!-- Color Information Panel -->
    @if($selectedColor)
        @php
            $selectedColorCode = $colorMap[strtolower($selectedColor)] ?? '#6b7280';
        @endphp
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="flex items-center space-x-4">
                <div class="w-8 h-8 rounded-full border-2 border-gray-300" 
                     style="background: {{ $selectedColorCode }};"></div>
                <div>
                    <h4 class="font-medium text-gray-900">{{ $selectedColor }}</h4>
                    <p class="text-sm text-gray-600">{{ $selectedColorCode }}</p>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Color Palette Preview -->
    <div class="mt-4">
        <h4 class="text-sm font-medium text-gray-700 mb-2">Available Colors ({{ count($colors) }})</h4>
        <div class="flex flex-wrap gap-1">
            @foreach(array_slice($colors, 0, 20) as $color)
                @php
                    $miniColorCode = $colorMap[strtolower($color)] ?? '#6b7280';
                @endphp
                <div class="w-4 h-4 rounded border border-gray-200" 
                     style="background: {{ $miniColorCode }};"
                     title="{{ $color }}">
                </div>
            @endforeach
            @if(count($colors) > 20)
                <div class="text-xs text-gray-500 flex items-center ml-2">
                    +{{ count($colors) - 20 }} more
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.advanced-color-swatch {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.advanced-color-swatch:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.advanced-color-swatch:active {
    transform: scale(0.95);
}

/* Accessibility improvements */
.advanced-color-swatch:focus {
    outline: none;
}

.advanced-color-swatch[aria-pressed="true"] {
    transform: scale(1.1);
    box-shadow: 0 0 0 2px #005366, 0 4px 12px rgba(0, 83, 102, 0.3);
}
</style>
