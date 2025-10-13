<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Zoom Effect</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8 text-center">Zoom Effect Test</h1>
        
        <div class="bg-white p-8 rounded-xl shadow-lg">
            <h2 class="text-xl font-semibold mb-4">Di chuyển chuột vào ảnh để zoom</h2>
            
            <!-- Image Container -->
            <div class="aspect-square bg-white rounded-xl shadow-lg overflow-hidden relative" id="image-container">
                <img src="https://images.unsplash.com/photo-1523381210434-271e8be1f52b?w=800" 
                     alt="Test Image" 
                     id="main-image"
                     class="w-full h-full object-cover">
                
                <!-- Zoom Overlay -->
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center pointer-events-none">
                    <div class="opacity-0 group-hover:opacity-100 transition-all duration-300">
                        <div class="bg-white bg-opacity-90 rounded-full p-3 shadow-lg">
                            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 text-center text-gray-600">
                <p>✨ Hiệu ứng zoom 2x khi di chuyển chuột</p>
                <p>🔍 Click để xem ảnh lớn (demo)</p>
            </div>
        </div>
    </div>

    <style>
    #main-image {
        transition: transform 0.3s ease-out;
        cursor: zoom-in;
    }
    
    #image-container:hover {
        cursor: zoom-in;
    }
    </style>

    <script>
    // Initialize zoom effect
    const mainImage = document.getElementById('main-image');
    const imageContainer = document.getElementById('image-container');
    
    if (mainImage && imageContainer) {
        // Add zoom effect on mouse move
        imageContainer.addEventListener('mousemove', function(e) {
            const rect = imageContainer.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            // Calculate percentage position
            const xPercent = (x / rect.width) * 100;
            const yPercent = (y / rect.height) * 100;
            
            // Apply transform origin and scale
            mainImage.style.transformOrigin = `${xPercent}% ${yPercent}%`;
            mainImage.style.transform = 'scale(2)';
        });
        
        // Reset transform on mouse leave
        imageContainer.addEventListener('mouseleave', function() {
            mainImage.style.transformOrigin = 'center center';
            mainImage.style.transform = 'scale(1)';
        });
        
        // Click handler (demo)
        imageContainer.addEventListener('click', function() {
            alert('Clicked! This would open the gallery modal.');
        });
    }
    </script>
</body>
</html>

