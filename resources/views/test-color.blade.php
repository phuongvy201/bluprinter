<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Color Picker Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Color Picker Test Page</h1>
        
        <!-- Test Color Swatches -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-semibold mb-6">Color Selection Test</h2>
            
            <div class="grid grid-cols-8 gap-3 mb-6">
                <!-- Test colors from the UI image -->
                <button onclick="selectColor('Black')" 
                        class="color-swatch w-12 h-12 rounded-lg border-2 border-[#005366] ring-2 ring-[#005366] ring-offset-2 scale-110 transition-all duration-200 relative shadow-md hover:shadow-lg group"
                        data-attribute="Color"
                        data-value="Black"
                        style="background: #000000; background-image: linear-gradient(45deg, #000000cc, #000000);"
                        title="Black">
                    <svg class="w-5 h-5 text-white absolute inset-0 m-auto drop-shadow-lg" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                        Black
                    </div>
                </button>
                
                <button onclick="selectColor('White')" 
                        class="color-swatch w-12 h-12 rounded-lg border-2 border-gray-300 hover:border-gray-400 hover:scale-105 transition-all duration-200 relative shadow-md hover:shadow-lg group"
                        data-attribute="Color"
                        data-value="White"
                        style="background: #ffffff; background-image: linear-gradient(45deg, #ffffffcc, #ffffff);"
                        title="White">
                    <div class="absolute inset-0 rounded-lg border border-gray-200"></div>
                    <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                        White
                    </div>
                </button>
                
                <button onclick="selectColor('Light Blue')" 
                        class="color-swatch w-12 h-12 rounded-lg border-2 border-gray-300 hover:border-gray-400 hover:scale-105 transition-all duration-200 relative shadow-md hover:shadow-lg group"
                        data-attribute="Color"
                        data-value="Light Blue"
                        style="background: #93c5fd; background-image: linear-gradient(45deg, #93c5fdcc, #93c5fd);"
                        title="Light Blue">
                    <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                        Light Blue
                    </div>
                </button>
                
                <button onclick="selectColor('Charcoal')" 
                        class="color-swatch w-12 h-12 rounded-lg border-2 border-gray-300 hover:border-gray-400 hover:scale-105 transition-all duration-200 relative shadow-md hover:shadow-lg group"
                        data-attribute="Color"
                        data-value="Charcoal"
                        style="background: #374151; background-image: linear-gradient(45deg, #374151cc, #374151);"
                        title="Charcoal">
                    <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                        Charcoal
                    </div>
                </button>
                
                <button onclick="selectColor('Sport Grey')" 
                        class="color-swatch w-12 h-12 rounded-lg border-2 border-gray-300 hover:border-gray-400 hover:scale-105 transition-all duration-200 relative shadow-md hover:shadow-lg group"
                        data-attribute="Color"
                        data-value="Sport Grey"
                        style="background: #9ca3af; background-image: linear-gradient(45deg, #9ca3afcc, #9ca3af);"
                        title="Sport Grey">
                    <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                        Sport Grey
                    </div>
                </button>
                
                <button onclick="selectColor('Dark Heather')" 
                        class="color-swatch w-12 h-12 rounded-lg border-2 border-gray-300 hover:border-gray-400 hover:scale-105 transition-all duration-200 relative shadow-md hover:shadow-lg group"
                        data-attribute="Color"
                        data-value="Dark Heather"
                        style="background: #374151; background-image: linear-gradient(45deg, #374151cc, #374151);"
                        title="Dark Heather">
                    <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                        Dark Heather
                    </div>
                </button>
                
                <button onclick="selectColor('Navy')" 
                        class="color-swatch w-12 h-12 rounded-lg border-2 border-gray-300 hover:border-gray-400 hover:scale-105 transition-all duration-200 relative shadow-md hover:shadow-lg group"
                        data-attribute="Color"
                        data-value="Navy"
                        style="background: #1e3a8a; background-image: linear-gradient(45deg, #1e3a8acc, #1e3a8a);"
                        title="Navy">
                    <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                        Navy
                    </div>
                </button>
                
                <button onclick="selectColor('Maroon')" 
                        class="color-swatch w-12 h-12 rounded-lg border-2 border-gray-300 hover:border-gray-400 hover:scale-105 transition-all duration-200 relative shadow-md hover:shadow-lg group"
                        data-attribute="Color"
                        data-value="Maroon"
                        style="background: #991b1b; background-image: linear-gradient(45deg, #991b1bcc, #991b1b);"
                        title="Maroon">
                    <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                        Maroon
                    </div>
                </button>
            </div>
            
            <div class="grid grid-cols-8 gap-3 mb-6">
                <button onclick="selectColor('Light Pink')" 
                        class="color-swatch w-12 h-12 rounded-lg border-2 border-gray-300 hover:border-gray-400 hover:scale-105 transition-all duration-200 relative shadow-md hover:shadow-lg group"
                        data-attribute="Color"
                        data-value="Light Pink"
                        style="background: #fbb6ce; background-image: linear-gradient(45deg, #fbb6cecc, #fbb6ce);"
                        title="Light Pink">
                    <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                        Light Pink
                    </div>
                </button>
                
                <button onclick="selectColor('Red')" 
                        class="color-swatch w-12 h-12 rounded-lg border-2 border-gray-300 hover:border-gray-400 hover:scale-105 transition-all duration-200 relative shadow-md hover:shadow-lg group"
                        data-attribute="Color"
                        data-value="Red"
                        style="background: #dc2626; background-image: linear-gradient(45deg, #dc2626cc, #dc2626);"
                        title="Red">
                    <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                        Red
                    </div>
                </button>
                
                <button onclick="selectColor('Royal Blue')" 
                        class="color-swatch w-12 h-12 rounded-lg border-2 border-gray-300 hover:border-gray-400 hover:scale-105 transition-all duration-200 relative shadow-md hover:shadow-lg group"
                        data-attribute="Color"
                        data-value="Royal Blue"
                        style="background: #1d4ed8; background-image: linear-gradient(45deg, #1d4ed8cc, #1d4ed8);"
                        title="Royal Blue">
                    <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                        Royal Blue
                    </div>
                </button>
                
                <button onclick="selectColor('Sand')" 
                        class="color-swatch w-12 h-12 rounded-lg border-2 border-gray-300 hover:border-gray-400 hover:scale-105 transition-all duration-200 relative shadow-md hover:shadow-lg group"
                        data-attribute="Color"
                        data-value="Sand"
                        style="background: #fbbf24; background-image: linear-gradient(45deg, #fbbf24cc, #fbbf24);"
                        title="Sand">
                    <div class="absolute inset-0 rounded-lg bg-gradient-to-br from-white/30 via-transparent to-black/20"></div>
                    <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                        Sand
                    </div>
                </button>
                
                <button onclick="selectColor('Forest Green')" 
                        class="color-swatch w-12 h-12 rounded-lg border-2 border-gray-300 hover:border-gray-400 hover:scale-105 transition-all duration-200 relative shadow-md hover:shadow-lg group"
                        data-attribute="Color"
                        data-value="Forest Green"
                        style="background: #166534; background-image: linear-gradient(45deg, #166534cc, #166534);"
                        title="Forest Green">
                    <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                        Forest Green
                    </div>
                </button>
                
                <button onclick="selectColor('Military Green')" 
                        class="color-swatch w-12 h-12 rounded-lg border-2 border-gray-300 hover:border-gray-400 hover:scale-105 transition-all duration-200 relative shadow-md hover:shadow-lg group"
                        data-attribute="Color"
                        data-value="Military Green"
                        style="background: #365314; background-image: linear-gradient(45deg, #365314cc, #365314);"
                        title="Military Green">
                    <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                        Military Green
                    </div>
                </button>
                
                <button onclick="selectColor('Ash Grey')" 
                        class="color-swatch w-12 h-12 rounded-lg border-2 border-gray-300 hover:border-gray-400 hover:scale-105 transition-all duration-200 relative shadow-md hover:shadow-lg group"
                        data-attribute="Color"
                        data-value="Ash Grey"
                        style="background: #6b7280; background-image: linear-gradient(45deg, #6b7280cc, #6b7280);"
                        title="Ash Grey">
                    <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                        Ash Grey
                    </div>
                </button>
                
                <button onclick="selectColor('Purple')" 
                        class="color-swatch w-12 h-12 rounded-lg border-2 border-gray-300 hover:border-gray-400 hover:scale-105 transition-all duration-200 relative shadow-md hover:shadow-lg group"
                        data-attribute="Color"
                        data-value="Purple"
                        style="background: #9333ea; background-image: linear-gradient(45deg, #9333eacc, #9333ea);"
                        title="Purple">
                    <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                        Purple
                    </div>
                </button>
            </div>
            
            <div class="grid grid-cols-8 gap-3">
                <button onclick="selectColor('Orange')" 
                        class="color-swatch w-12 h-12 rounded-lg border-2 border-gray-300 hover:border-gray-400 hover:scale-105 transition-all duration-200 relative shadow-md hover:shadow-lg group"
                        data-attribute="Color"
                        data-value="Orange"
                        style="background: #ea580c; background-image: linear-gradient(45deg, #ea580ccc, #ea580c);"
                        title="Orange">
                    <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                        Orange
                    </div>
                </button>
                
                <button onclick="selectColor('Natural')" 
                        class="color-swatch w-12 h-12 rounded-lg border-2 border-gray-300 hover:border-gray-400 hover:scale-105 transition-all duration-200 relative shadow-md hover:shadow-lg group"
                        data-attribute="Color"
                        data-value="Natural"
                        style="background: #fef3c7; background-image: linear-gradient(45deg, #fef3c7cc, #fef3c7);"
                        title="Natural">
                    <div class="absolute inset-0 rounded-lg border border-gray-200"></div>
                    <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                        Natural
                    </div>
                </button>
            </div>
        </div>
        
        <!-- Selected Color Info -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <h2 class="text-2xl font-semibold mb-6">Selected Color Information</h2>
            
            <div id="selected-color-info" class="bg-gray-50 rounded-lg p-6">
                <div class="flex items-center space-x-6">
                    <div id="selected-color-preview" class="w-16 h-16 rounded-lg border-2 border-gray-300" style="background: #000000;"></div>
                    <div>
                        <h3 id="selected-color-name" class="text-xl font-semibold text-gray-900">Black</h3>
                        <p id="selected-color-hex" class="text-gray-600">#000000</p>
                        <p id="selected-color-rgb" class="text-sm text-gray-500">rgb(0, 0, 0)</p>
                    </div>
                </div>
            </div>
            
            <div class="mt-6">
                <h3 class="text-lg font-semibold mb-4">Test Features:</h3>
                <ul class="space-y-2 text-sm">
                    <li>✅ Click any color to select it</li>
                    <li>✅ Hover over colors to see tooltips</li>
                    <li>✅ Double-click colors to see preview modal</li>
                    <li>✅ Use arrow keys to navigate between colors</li>
                    <li>✅ Press Enter or Space to select color</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Include our enhanced color picker -->
    <script src="{{ asset('js/color-picker.js') }}"></script>
    
    <script>
        let selectedColor = 'Black';
        
        function selectColor(colorName) {
            selectedColor = colorName;
            updateSelectedColorInfo(colorName);
            updateColorSelection(colorName);
        }
        
        function updateSelectedColorInfo(colorName) {
            const colorMap = {
                // Complete color set from user request
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
                'Natural': '#fef3c7',
                
                // Additional colors for testing
                'Green': '#16a34a',
                'Yellow': '#eab308',
                'Pink': '#ec4899',
                'Brown': '#a16207',
                'Gray': '#6b7280',
                'Grey': '#6b7280'
            };
            
            const hex = colorMap[colorName] || '#000000';
            const rgb = hexToRgb(hex);
            
            document.getElementById('selected-color-name').textContent = colorName;
            document.getElementById('selected-color-hex').textContent = hex;
            document.getElementById('selected-color-rgb').textContent = rgb;
            document.getElementById('selected-color-preview').style.background = hex;
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
            const selectedBtn = document.querySelector(`[data-value="${selectedColorName}"]`);
            if (selectedBtn) {
                selectedBtn.classList.remove('border-gray-300');
                selectedBtn.classList.add('border-[#005366]', 'ring-2', 'ring-[#005366]', 'ring-offset-2', 'scale-110');
                
                // Add checkmark
                selectedBtn.innerHTML += `
                    <svg class="w-5 h-5 text-white absolute inset-0 m-auto drop-shadow-lg" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                `;
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
            console.log('Color picker test page loaded');
            console.log('Available colors:', window.colorPicker ? 'ColorPicker loaded' : 'ColorPicker not loaded');
        });
    </script>
</body>
</html>
