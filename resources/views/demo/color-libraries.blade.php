@extends('layouts.app')

@section('title', 'Color Libraries Demo')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Color Libraries Demo</h1>
        <p class="text-xl text-gray-600">Explore different color display libraries and implementations</p>
    </div>

    <!-- Spectrum.js Demo -->
    <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
        <div class="flex items-center space-x-3 mb-6">
            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-lg">S</span>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Spectrum.js</h2>
                <p class="text-gray-600">Professional color picker library</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div>
                <h3 class="text-lg font-semibold mb-4">Basic Color Picker</h3>
                <input type="text" id="spectrum-basic" class="w-full h-12 border border-gray-300 rounded-lg" value="#3498db">
                
                <h3 class="text-lg font-semibold mb-4 mt-6">Flat Color Picker</h3>
                <input type="text" id="spectrum-flat" class="w-full h-12 border border-gray-300 rounded-lg" value="#e74c3c">
            </div>
            
            <div>
                <h3 class="text-lg font-semibold mb-4">Advanced Color Picker</h3>
                <input type="text" id="spectrum-advanced" class="w-full h-12 border border-gray-300 rounded-lg" value="#2ecc71">
                
                <div class="mt-6">
                    <h4 class="font-medium mb-2">Selected Color Info:</h4>
                    <div id="spectrum-info" class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center space-x-4">
                            <div id="spectrum-preview" class="w-12 h-12 rounded-lg border border-gray-300" style="background: #2ecc71;"></div>
                            <div>
                                <p class="font-medium" id="spectrum-hex">#2ecc71</p>
                                <p class="text-sm text-gray-600" id="spectrum-rgb">rgb(46, 204, 113)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chroma.js Demo -->
    <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
        <div class="flex items-center space-x-3 mb-6">
            <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-teal-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-lg">C</span>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Chroma.js</h2>
                <p class="text-gray-600">Color manipulation and gradients</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div>
                <h3 class="text-lg font-semibold mb-4">Color Scale</h3>
                <div id="chroma-scale" class="h-12 rounded-lg mb-4"></div>
                
                <h3 class="text-lg font-semibold mb-4">Color Palette</h3>
                <div id="chroma-palette" class="flex space-x-2 mb-4"></div>
                
                <h3 class="text-lg font-semibold mb-4">Color Mixing</h3>
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-lg border border-gray-300" id="color1" style="background: #ff0000;"></div>
                    <span class="text-gray-500">+</span>
                    <div class="w-12 h-12 rounded-lg border border-gray-300" id="color2" style="background: #0000ff;"></div>
                    <span class="text-gray-500">=</span>
                    <div class="w-12 h-12 rounded-lg border border-gray-300" id="mixed-color" style="background: #800080;"></div>
                </div>
            </div>
            
            <div>
                <h3 class="text-lg font-semibold mb-4">Color Information</h3>
                <div id="chroma-info" class="bg-gray-50 rounded-lg p-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Luminance:</span>
                        <span id="luminance">0.5</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Temperature:</span>
                        <span id="temperature">warm</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Contrast:</span>
                        <span id="contrast">high</span>
                    </div>
                </div>
                
                <h3 class="text-lg font-semibold mb-4 mt-6">Gradient Generator</h3>
                <div class="space-y-3">
                    <div class="flex space-x-2">
                        <input type="color" id="gradient-start" value="#ff0000" class="w-12 h-10 border border-gray-300 rounded">
                        <input type="color" id="gradient-end" value="#0000ff" class="w-12 h-10 border border-gray-300 rounded">
                    </div>
                    <div id="gradient-preview" class="h-8 rounded-lg" style="background: linear-gradient(to right, #ff0000, #0000ff);"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Implementation Demo -->
    <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
        <div class="flex items-center space-x-3 mb-6">
            <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-lg">B</span>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Bluprinter Color Picker</h2>
                <p class="text-gray-600">Custom implementation with enhanced features</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div>
                <h3 class="text-lg font-semibold mb-4">Product Color Selection</h3>
                <x-advanced-color-picker 
                    :colors="['Black', 'White', 'Red', 'Blue', 'Green', 'Yellow', 'Purple', 'Pink', 'Orange', 'Brown', 'Gray', 'Navy', 'Gold', 'Silver']"
                    selectedColor="Blue"
                />
            </div>
            
            <div>
                <h3 class="text-lg font-semibold mb-4">Interactive Features</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Color Search</label>
                        <input type="text" id="color-search" placeholder="Search colors..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366] focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Color Categories</label>
                        <select id="color-category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366] focus:border-transparent">
                            <option value="all">All Colors</option>
                            <option value="basic">Basic Colors</option>
                            <option value="metallic">Metallic Colors</option>
                            <option value="pastel">Pastel Colors</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Color Format</label>
                        <div class="flex space-x-2">
                            <button class="px-3 py-1 bg-[#005366] text-white rounded text-sm" onclick="showFormat('hex')">HEX</button>
                            <button class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm" onclick="showFormat('rgb')">RGB</button>
                            <button class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm" onclick="showFormat('hsl')">HSL</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Comparison -->
    <div class="bg-white rounded-xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Performance Comparison</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Spectrum.js</h3>
                <ul class="space-y-2 text-sm">
                    <li>✅ Easy to implement</li>
                    <li>✅ jQuery dependency</li>
                    <li>⚠️ 15KB minified</li>
                    <li>✅ Good browser support</li>
                </ul>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Chroma.js</h3>
                <ul class="space-y-2 text-sm">
                    <li>✅ Advanced features</li>
                    <li>✅ No dependencies</li>
                    <li>⚠️ 25KB minified</li>
                    <li>✅ Color manipulation</li>
                </ul>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Custom Implementation</h3>
                <ul class="space-y-2 text-sm">
                    <li>✅ Full control</li>
                    <li>✅ No dependencies</li>
                    <li>✅ Lightweight (~5KB)</li>
                    <li>✅ Tailored features</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Include external libraries -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/spectrum-colorpicker2/dist/spectrum.min.css">
<script src="https://cdn.jsdelivr.net/npm/spectrum-colorpicker2/dist/spectrum.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chroma-js@2.4.2/chroma.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Spectrum.js
    $("#spectrum-basic").spectrum({
        color: "#3498db",
        change: function(color) {
            updateSpectrumInfo(color.toHexString());
        }
    });
    
    $("#spectrum-flat").spectrum({
        color: "#e74c3c",
        flat: true,
        showInput: true
    });
    
    $("#spectrum-advanced").spectrum({
        color: "#2ecc71",
        showInput: true,
        showAlpha: true,
        showButtons: false,
        preferredFormat: "hex",
        change: function(color) {
            updateSpectrumInfo(color.toHexString());
        }
    });
    
    // Initialize Chroma.js demos
    initChromaDemo();
    
    // Initialize custom features
    initCustomFeatures();
});

function updateSpectrumInfo(hex) {
    document.getElementById('spectrum-preview').style.background = hex;
    document.getElementById('spectrum-hex').textContent = hex;
    
    // Convert to RGB
    const rgb = chroma(hex).rgb();
    document.getElementById('spectrum-rgb').textContent = `rgb(${rgb[0]}, ${rgb[1]}, ${rgb[2]})`;
}

function initChromaDemo() {
    // Color scale
    const scale = chroma.scale(['#ff0000', '#ffff00', '#00ff00', '#00ffff', '#0000ff']).mode('lab');
    const scaleDiv = document.getElementById('chroma-scale');
    
    for (let i = 0; i < 10; i++) {
        const color = scale(i / 9);
        const colorDiv = document.createElement('div');
        colorDiv.className = 'inline-block w-full h-full';
        colorDiv.style.background = color.hex();
        scaleDiv.appendChild(colorDiv);
    }
    
    // Color palette
    const palette = chroma.scale(['#ff0000', '#0000ff']).colors(8);
    const paletteDiv = document.getElementById('chroma-palette');
    
    palette.forEach(color => {
        const colorDiv = document.createElement('div');
        colorDiv.className = 'w-8 h-8 rounded border border-gray-300';
        colorDiv.style.background = color;
        paletteDiv.appendChild(colorDiv);
    });
    
    // Update color info
    updateChromaInfo('#2ecc71');
}

function updateChromaInfo(hex) {
    const color = chroma(hex);
    document.getElementById('luminance').textContent = color.luminance().toFixed(2);
    document.getElementById('temperature').textContent = color.temperature() > 5000 ? 'cool' : 'warm';
    document.getElementById('contrast').textContent = color.luminance() > 0.5 ? 'high' : 'low';
}

function initCustomFeatures() {
    // Color search
    document.getElementById('color-search').addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase();
        // Implement search logic here
        console.log('Searching for:', query);
    });
    
    // Color category filter
    document.getElementById('color-category').addEventListener('change', function(e) {
        console.log('Category changed to:', e.target.value);
    });
    
    // Gradient preview
    document.getElementById('gradient-start').addEventListener('input', updateGradient);
    document.getElementById('gradient-end').addEventListener('input', updateGradient);
}

function updateGradient() {
    const start = document.getElementById('gradient-start').value;
    const end = document.getElementById('gradient-end').value;
    const preview = document.getElementById('gradient-preview');
    preview.style.background = `linear-gradient(to right, ${start}, ${end})`;
}

function showFormat(format) {
    // Update button states
    document.querySelectorAll('[onclick^="showFormat"]').forEach(btn => {
        btn.className = 'px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm';
    });
    event.target.className = 'px-3 py-1 bg-[#005366] text-white rounded text-sm';
    
    console.log('Format changed to:', format);
}
</script>
@endsection
