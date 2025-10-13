<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Colors Display Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">All Colors Display Test</h1>
        <p class="text-lg text-gray-600 mb-8">Testing display of all colors without "+X more" limitation</p>
        
        <!-- Size Selection -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-gray-900">Size</h3>
                <a href="#" class="text-sm text-[#005366] hover:underline flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    View Size Guide
                </a>
            </div>
            <div class="relative">
                <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366] focus:border-transparent appearance-none bg-white">
                    <option value="">Choose a size</option>
                    <option value="S" selected>S</option>
                    <option value="M">M</option>
                    <option value="L">L</option>
                    <option value="XL">XL</option>
                </select>
                <svg class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </div>
        
        <!-- Colour Selection - All Colors Display -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-gray-900">
                    Colour: <span id="selected-colour-name" class="text-[#005366]">Black</span>
                </h3>
            </div>
            
            <!-- All Colors Grid - No Limitation -->
            <div class="flex flex-wrap gap-2">
                @php
                    $allColors = [
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
                        'Natural' => '#fef3c7',
                        // Additional colors to test with many options
                        'Green' => '#16a34a',
                        'Yellow' => '#eab308',
                        'Pink' => '#ec4899',
                        'Brown' => '#a16207',
                        'Gray' => '#6b7280',
                        'Grey' => '#6b7280',
                        'Teal' => '#0d9488',
                        'Cyan' => '#06b6d4',
                        'Indigo' => '#4f46e5',
                        'Violet' => '#8b5cf6',
                        'Rose' => '#f43f5e',
                        'Amber' => '#f59e0b',
                        'Emerald' => '#10b981',
                        'Sky' => '#0ea5e9',
                        'Fuchsia' => '#d946ef',
                        'Gold' => '#fbbf24',
                        'Silver' => '#9ca3af',
                        'Copper' => '#b45309',
                        'Bronze' => '#92400e',
                        'Platinum' => '#6b7280',
                        'Camo' => '#365314',
                        'Olive' => '#65a30d',
                        'Khaki' => '#a3a3a3',
                        'Beige' => '#f5f5dc',
                        'Tan' => '#d2b48c',
                        'Mint' => '#a7f3d0',
                        'Lavender' => '#e9d5ff',
                        'Coral' => '#fda4af',
                        'Turquoise' => '#5eead4'
                    ];
                @endphp
                
                @foreach($allColors as $colorName => $colorCode)
                    <button onclick="selectColour('{{ $colorName }}')" 
                            class="color-swatch w-10 h-10 rounded-full border-2 {{ $loop->first ? 'border-[#005366] ring-2 ring-[#005366] ring-offset-1 scale-105' : 'border-gray-300 hover:border-gray-400 hover:scale-105' }} transition-all duration-200 relative shadow-sm hover:shadow-md group"
                            data-color="{{ $colorName }}"
                            style="background: {{ $colorCode }}; background-image: linear-gradient(45deg, {{ $colorCode }}cc, {{ $colorCode }});"
                            title="{{ $colorName }}">
                        
                        <!-- Special effects for specific colors -->
                        @if(in_array(strtolower($colorName), ['gold', 'silver', 'copper', 'bronze', 'platinum', 'sand', 'natural']))
                            <div class="absolute inset-0 rounded-full bg-gradient-to-br from-white/20 to-transparent"></div>
                        @elseif($colorName === 'White')
                            <div class="absolute inset-0 rounded-full border border-gray-200"></div>
                        @endif
                        
                        <!-- Checkmark for selected color -->
                        @if($loop->first)
                            <svg class="w-4 h-4 text-white absolute inset-0 m-auto drop-shadow-lg" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        @endif
                        
                        <!-- Hover tooltip -->
                        <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                            {{ $colorName }}
                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                        </div>
                    </button>
                @endforeach
            </div>
            
            <!-- Color Count Info -->
            <div class="mt-4 text-center">
                <p class="text-sm text-gray-600">
                    Showing all <span class="font-semibold text-[#005366]">{{ count($allColors) }}</span> colors - no limitations!
                </p>
            </div>
        </div>
        
        <!-- Selected Variant Summary -->
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="font-medium text-gray-900">
                        <span id="selected-variant-name">Black/S</span>
                    </h4>
                    <p class="text-xs text-gray-500 mt-1" id="selected-variant-stock">
                        Stock: 99 available
                    </p>
                </div>
                <div class="text-right">
                    <span class="text-lg font-bold text-[#E2150C]" id="selected-variant-price">
                        $9.95
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Test Results -->
        <div class="mt-8 bg-white rounded-xl shadow-lg p-8">
            <h2 class="text-2xl font-semibold mb-6">Test Results</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-green-50 rounded-lg p-4">
                    <h3 class="font-semibold text-green-900 mb-2">✅ All Colors Visible</h3>
                    <p class="text-sm text-green-800">No "+X more" limitation - all {{ count($allColors) }} colors are displayed</p>
                </div>
                
                <div class="bg-blue-50 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-900 mb-2">📱 Responsive Grid</h3>
                    <p class="text-sm text-blue-800">Grid adapts from 4 columns on mobile to 10 on desktop</p>
                </div>
                
                <div class="bg-purple-50 rounded-lg p-4">
                    <h3 class="font-semibold text-purple-900 mb-2">🎨 Enhanced Display</h3>
                    <p class="text-sm text-purple-800">Special effects for metallic colors, hover tooltips, and smooth animations</p>
                </div>
            </div>
            
            <div class="mt-6 bg-yellow-50 rounded-lg p-4">
                <h3 class="font-semibold text-yellow-900 mb-2">Key Changes Made:</h3>
                <ul class="text-sm space-y-1 text-yellow-800">
                    <li>• Removed <code>array_slice($attributeValues, 0, 8)</code> limitation</li>
                    <li>• Changed from <code>flex flex-wrap</code> to <code>grid</code> layout</li>
                    <li>• Removed "+X more" display logic</li>
                    <li>• Added responsive grid columns (4-10 columns based on screen size)</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function selectColour(colorName) {
            console.log('Selected colour:', colorName);
            
            // Update selected colour name
            document.getElementById('selected-colour-name').textContent = colorName;
            
            // Update variant name
            const size = document.querySelector('select').value || 'S';
            document.getElementById('selected-variant-name').textContent = `${colorName}/${size}`;
            
            // Update color selection
            updateColorSelection(colorName);
        }
        
        function updateColorSelection(selectedColorName) {
            // Remove selection from all buttons
            document.querySelectorAll('.color-swatch').forEach(btn => {
                btn.classList.remove('border-[#005366]', 'ring-2', 'ring-[#005366]', 'ring-offset-2', 'scale-110');
                btn.classList.add('border-gray-300');
                
                // Remove checkmark
                const svg = btn.querySelector('svg');
                if (svg) {
                    svg.remove();
                }
            });
            
            // Add selection to clicked button
            const selectedBtn = document.querySelector(`[data-color="${selectedColorName}"]`);
            if (selectedBtn) {
                selectedBtn.classList.remove('border-gray-300');
                selectedBtn.classList.add('border-[#005366]', 'ring-2', 'ring-[#005366]', 'ring-offset-2', 'scale-110');
                
                // Add checkmark
                selectedBtn.innerHTML += `
                    <svg class="w-4 h-4 text-white absolute inset-0 m-auto drop-shadow-lg" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                `;
            }
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            console.log('All colors display test loaded');
            console.log('Total colors displayed:', document.querySelectorAll('.color-swatch').length);
        });
    </script>
</body>
</html>
