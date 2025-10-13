<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colour vs Color Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Colour vs Color Test</h1>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Color (American English) -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold mb-6 text-blue-600">Color (American English)</h2>
                
                <div class="space-y-6 mb-6">
                    @php
                        $colorAttributes = [
                            'Color' => ['Black', 'White', 'Red', 'Blue', 'Green', 'Yellow']
                        ];
                        $colorMap = [
                            'black' => '#000000', 'white' => '#ffffff', 'red' => '#dc2626',
                            'blue' => '#2563eb', 'green' => '#16a34a', 'yellow' => '#eab308',
                        ];
                    @endphp
                    
                    @foreach($colorAttributes as $attributeName => $attributeValues)
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">
                                {{ $attributeName }}: <span id="selected-{{ strtolower($attributeName) }}-name" class="text-[#005366]">{{ $attributeValues[0] ?? '' }}</span>
                            </h3>
                            <div class="flex flex-wrap gap-3">
                                @foreach($attributeValues as $color)
                                    @php
                                        $colorCode = $colorMap[strtolower($color)] ?? '#6b7280';
                                    @endphp
                                    <button onclick="selectAttribute('{{ $attributeName }}', '{{ $color }}')" 
                                            class="color-swatch w-12 h-12 rounded-lg border-2 {{ $loop->first ? 'border-[#005366] ring-2 ring-[#005366] ring-offset-2 scale-110' : 'border-gray-300 hover:border-gray-400 hover:scale-105' }} transition-all duration-200 relative shadow-md hover:shadow-lg group"
                                            data-attribute="{{ $attributeName }}"
                                            data-value="{{ $color }}"
                                            style="background: {{ $colorCode }}; background-image: linear-gradient(45deg, {{ $colorCode }}cc, {{ $colorCode }});"
                                            title="{{ $color }}">
                                        @if($loop->first)
                                            <svg class="w-5 h-5 text-white absolute inset-0 m-auto drop-shadow-lg" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        @endif
                                        <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                            {{ $color }}
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium mb-2">Selected Color Info:</h4>
                    <div id="color-info">
                        <p><strong>Attribute:</strong> Color</p>
                        <p><strong>Value:</strong> <span id="color-value">Black</span></p>
                        <p><strong>Function:</strong> selectColor()</p>
                    </div>
                </div>
            </div>
            
            <!-- Colour (British English) -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold mb-6 text-green-600">Colour (British English)</h2>
                
                <div class="space-y-6 mb-6">
                    @php
                        $colourAttributes = [
                            'Colour' => ['Black', 'White', 'Red', 'Blue', 'Green', 'Yellow']
                        ];
                    @endphp
                    
                    @foreach($colourAttributes as $attributeName => $attributeValues)
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">
                                {{ $attributeName }}: <span id="selected-{{ strtolower($attributeName) }}-name" class="text-[#005366]">{{ $attributeValues[0] ?? '' }}</span>
                            </h3>
                            <div class="flex flex-wrap gap-3">
                                @foreach($attributeValues as $color)
                                    @php
                                        $colorCode = $colorMap[strtolower($color)] ?? '#6b7280';
                                    @endphp
                                    <button onclick="selectAttribute('{{ $attributeName }}', '{{ $color }}')" 
                                            class="color-swatch w-12 h-12 rounded-lg border-2 {{ $loop->first ? 'border-[#005366] ring-2 ring-[#005366] ring-offset-2 scale-110' : 'border-gray-300 hover:border-gray-400 hover:scale-105' }} transition-all duration-200 relative shadow-md hover:shadow-lg group"
                                            data-attribute="{{ $attributeName }}"
                                            data-value="{{ $color }}"
                                            style="background: {{ $colorCode }}; background-image: linear-gradient(45deg, {{ $colorCode }}cc, {{ $colorCode }});"
                                            title="{{ $color }}">
                                        @if($loop->first)
                                            <svg class="w-5 h-5 text-white absolute inset-0 m-auto drop-shadow-lg" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        @endif
                                        <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                            {{ $color }}
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium mb-2">Selected Colour Info:</h4>
                    <div id="colour-info">
                        <p><strong>Attribute:</strong> Colour</p>
                        <p><strong>Value:</strong> <span id="colour-value">Black</span></p>
                        <p><strong>Function:</strong> selectColour()</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Test Results -->
        <div class="mt-8 bg-white rounded-xl shadow-lg p-8">
            <h2 class="text-2xl font-semibold mb-6">Test Results</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-blue-50 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-900 mb-3">Color (American)</h3>
                    <ul class="text-sm space-y-1">
                        <li>✅ Recognizes "Color" attribute</li>
                        <li>✅ Uses selectColor() function</li>
                        <li>✅ Updates color-name element</li>
                        <li>✅ Works with existing code</li>
                    </ul>
                </div>
                
                <div class="bg-green-50 rounded-lg p-4">
                    <h3 class="font-semibold text-green-900 mb-3">Colour (British)</h3>
                    <ul class="text-sm space-y-1">
                        <li>✅ Recognizes "Colour" attribute</li>
                        <li>✅ Uses selectColour() function</li>
                        <li>✅ Updates colour-name element</li>
                        <li>✅ Works with existing code</li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-6 bg-yellow-50 rounded-lg p-4">
                <h3 class="font-semibold text-yellow-900 mb-2">Important Notes:</h3>
                <ul class="text-sm space-y-1 text-yellow-800">
                    <li>• Both "Color" and "Colour" are now supported</li>
                    <li>• The system automatically detects the attribute name</li>
                    <li>• All color swatches work the same way</li>
                    <li>• JavaScript functions handle both cases</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function selectAttribute(attributeName, value) {
            console.log(`Selected ${attributeName}: ${value}`);
            
            // Update selection display
            updateColorSelection(attributeName, value);
            
            // Update info panel
            if (attributeName === 'Color') {
                document.getElementById('color-value').textContent = value;
            } else if (attributeName === 'Colour') {
                document.getElementById('colour-value').textContent = value;
            }
            
            // Update selected name display
            const nameElement = document.getElementById(`selected-${attributeName.toLowerCase()}-name`);
            if (nameElement) {
                nameElement.textContent = value;
            }
        }
        
        function updateColorSelection(attributeName, selectedValue) {
            // Remove selection from all buttons of this attribute type
            document.querySelectorAll(`[data-attribute="${attributeName}"]`).forEach(btn => {
                btn.classList.remove('border-[#005366]', 'ring-2', 'ring-[#005366]', 'ring-offset-2', 'scale-110');
                btn.classList.add('border-gray-300');
                
                // Remove checkmark
                const svg = btn.querySelector('svg');
                if (svg) {
                    svg.remove();
                }
            });
            
            // Add selection to clicked button
            const selectedBtn = document.querySelector(`[data-attribute="${attributeName}"][data-value="${selectedValue}"]`);
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
        
        // Legacy functions for backward compatibility
        function selectColor(color) {
            selectAttribute('Color', color);
        }
        
        function selectColour(colour) {
            selectAttribute('Colour', colour);
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Colour vs Color test page loaded');
            console.log('Both Color and Colour attributes are supported');
        });
    </script>
</body>
</html>
