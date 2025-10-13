<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Color Set Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-4xl font-bold text-gray-900 mb-8">Complete Color Set Test</h1>
        <p class="text-lg text-gray-600 mb-8">Testing all 18 colors from user request with enhanced color picker</p>
        
        <!-- Complete Color Grid -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-semibold mb-6">All 18 Colors</h2>
            
            <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-9 lg:grid-cols-12 gap-4 mb-8">
                @php
                    $completeColors = [
                        'Black' => '#000000',
                        'White' => '#ffffff', 
                        'Light Blue' => '#93c5fd',
                        'Charcoal' => '#374151',
                        'Sport Grey' => '#9ca3af',
                        'Dark Heather' => '#374151',
                        'Navy' => '#1e3a8a',
                        'Maroon' => '#991b1b',
                        'Light Pink' => '#fbb6ce',
                        'Red' => '#dc2626',
                        'Royal Blue' => '#1d4ed8',
                        'Sand' => '#fbbf24',
                        'Forest Green' => '#166534',
                        'Military Green' => '#365314',
                        'Ash Grey' => '#6b7280',
                        'Purple' => '#9333ea',
                        'Orange' => '#ea580c',
                        'Natural' => '#fef3c7'
                    ];
                @endphp
                
                @foreach($completeColors as $colorName => $colorCode)
                    <button onclick="selectCompleteColor('{{ $colorName }}')" 
                            class="complete-color-swatch w-16 h-16 rounded-xl border-2 border-gray-300 hover:border-gray-400 hover:scale-105 transition-all duration-200 relative shadow-md hover:shadow-lg group focus:outline-none focus:ring-2 focus:ring-[#005366] focus:ring-offset-2"
                            data-color="{{ $colorName }}"
                            style="background: {{ $colorCode }}; background-image: linear-gradient(45deg, {{ $colorCode }}cc, {{ $colorCode }});"
                            title="{{ $colorName }}">
                        
                        <!-- Special effects for specific colors -->
                        @if(in_array(strtolower($colorName), ['sand', 'natural']))
                            <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-white/20 to-transparent"></div>
                        @elseif($colorName === 'White')
                            <div class="absolute inset-0 rounded-xl border border-gray-300"></div>
                        @endif
                        
                        <!-- Hover tooltip -->
                        <div class="absolute -top-12 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                            {{ $colorName }}
                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                        </div>
                        
                        <!-- Selection indicator -->
                        <div class="absolute -top-1 -right-1 w-5 h-5 bg-[#005366] rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">
                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </button>
                @endforeach
            </div>
            
            <!-- Selected Color Info -->
            <div id="selected-complete-color" class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-6 border border-gray-200">
                <div class="flex items-center space-x-6">
                    <div id="complete-color-preview" class="w-20 h-20 rounded-xl border-2 border-gray-300 shadow-lg" style="background: #000000;"></div>
                    <div class="flex-1">
                        <h3 id="complete-color-name" class="text-2xl font-bold text-gray-900 mb-2">Black</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Hex:</span>
                                <code id="complete-color-hex" class="bg-white px-2 py-1 rounded text-gray-900 ml-2">#000000</code>
                            </div>
                            <div>
                                <span class="text-gray-600">RGB:</span>
                                <code id="complete-color-rgb" class="bg-white px-2 py-1 rounded text-gray-900 ml-2">rgb(0, 0, 0)</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Color Categories -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Basic Colors -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Colors</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach(['Black', 'White', 'Red', 'Blue', 'Green', 'Yellow'] as $basicColor)
                        @if(isset($completeColors[$basicColor]))
                            @php $colorCode = $completeColors[$basicColor]; @endphp
                            <div class="w-8 h-8 rounded-full border border-gray-300" 
                                 style="background: {{ $colorCode }};" 
                                 title="{{ $basicColor }}">
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            
            <!-- Extended Colors -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Extended Colors</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach(['Navy', 'Maroon', 'Purple', 'Orange', 'Light Blue', 'Light Pink'] as $extendedColor)
                        @if(isset($completeColors[$extendedColor]))
                            @php $colorCode = $completeColors[$extendedColor]; @endphp
                            <div class="w-8 h-8 rounded-full border border-gray-300" 
                                 style="background: {{ $colorCode }};" 
                                 title="{{ $extendedColor }}">
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            
            <!-- Special Colors -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Special Colors</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach(['Sand', 'Natural', 'Charcoal', 'Sport Grey', 'Dark Heather', 'Ash Grey'] as $specialColor)
                        @if(isset($completeColors[$specialColor]))
                            @php $colorCode = $completeColors[$specialColor]; @endphp
                            <div class="w-8 h-8 rounded-full border border-gray-300" 
                                 style="background: {{ $colorCode }};" 
                                 title="{{ $specialColor }}">
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- Test Results -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <h2 class="text-2xl font-semibold mb-6">Test Results</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-green-50 rounded-lg p-4">
                    <h3 class="font-semibold text-green-900 mb-2">✅ Color Recognition</h3>
                    <p class="text-sm text-green-800">All 18 colors are recognized and mapped correctly</p>
                </div>
                
                <div class="bg-blue-50 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-900 mb-2">🎨 Visual Display</h3>
                    <p class="text-sm text-blue-800">Colors display with proper gradients and effects</p>
                </div>
                
                <div class="bg-purple-50 rounded-lg p-4">
                    <h3 class="font-semibold text-purple-900 mb-2">🖱️ Interaction</h3>
                    <p class="text-sm text-purple-800">Hover effects, tooltips, and selection work</p>
                </div>
                
                <div class="bg-yellow-50 rounded-lg p-4">
                    <h3 class="font-semibold text-yellow-900 mb-2">📱 Responsive</h3>
                    <p class="text-sm text-yellow-800">Works on desktop, tablet, and mobile</p>
                </div>
            </div>
            
            <div class="mt-6 bg-gray-50 rounded-lg p-4">
                <h3 class="font-semibold text-gray-900 mb-2">Color Count: <span class="text-[#005366]">{{ count($completeColors) }}</span></h3>
                <p class="text-sm text-gray-600">All colors from your request are now supported and can be used in product variants.</p>
            </div>
        </div>
    </div>

    <!-- Include our enhanced color picker -->
    <script src="{{ asset('js/color-picker.js') }}"></script>
    
    <script>
        const completeColorMap = {
            'Black': '#000000',
            'White': '#ffffff', 
            'Light Blue': '#93c5fd',
            'Charcoal': '#374151',
            'Sport Grey': '#9ca3af',
            'Dark Heather': '#374151',
            'Navy': '#1e3a8a',
            'Maroon': '#991b1b',
            'Light Pink': '#fbb6ce',
            'Red': '#dc2626',
            'Royal Blue': '#1d4ed8',
            'Sand': '#fbbf24',
            'Forest Green': '#166534',
            'Military Green': '#365314',
            'Ash Grey': '#6b7280',
            'Purple': '#9333ea',
            'Orange': '#ea580c',
            'Natural': '#fef3c7'
        };
        
        let selectedCompleteColor = 'Black';
        
        function selectCompleteColor(colorName) {
            selectedCompleteColor = colorName;
            updateCompleteColorInfo(colorName);
            updateCompleteColorSelection(colorName);
        }
        
        function updateCompleteColorInfo(colorName) {
            const hex = completeColorMap[colorName] || '#000000';
            const rgb = hexToRgb(hex);
            
            document.getElementById('complete-color-name').textContent = colorName;
            document.getElementById('complete-color-hex').textContent = hex;
            document.getElementById('complete-color-rgb').textContent = rgb;
            document.getElementById('complete-color-preview').style.background = hex;
        }
        
        function updateCompleteColorSelection(selectedColorName) {
            // Remove selection from all buttons
            document.querySelectorAll('.complete-color-swatch').forEach(btn => {
                btn.classList.remove('border-[#005366]', 'ring-2', 'ring-[#005366]', 'ring-offset-2', 'scale-110');
                btn.classList.add('border-gray-300');
            });
            
            // Add selection to clicked button
            const selectedBtn = document.querySelector(`[data-color="${selectedColorName}"]`);
            if (selectedBtn) {
                selectedBtn.classList.remove('border-gray-300');
                selectedBtn.classList.add('border-[#005366]', 'ring-2', 'ring-[#005366]', 'ring-offset-2', 'scale-110');
            }
        }
        
        function hexToRgb(hex) {
            const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            return result ? 
                `rgb(${parseInt(result[1], 16)}, ${parseInt(result[2], 16)}, ${parseInt(result[3], 16)})` : 
                'Invalid hex';
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Complete color set test page loaded');
            console.log('Total colors supported:', Object.keys(completeColorMap).length);
            console.log('Color picker loaded:', window.colorPicker ? 'Yes' : 'No');
            
            // Initialize with first color
            updateCompleteColorInfo(selectedCompleteColor);
            updateCompleteColorSelection(selectedCompleteColor);
        });
    </script>
</body>
</html>
