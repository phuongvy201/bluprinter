<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S3 Direct Upload Test - Bluprinter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .upload-zone {
            border: 2px dashed #cbd5e0;
            transition: all 0.3s ease;
        }
        .upload-zone.dragover {
            border-color: #3182ce;
            background-color: #ebf8ff;
        }
        .upload-zone.has-files {
            border-color: #38a169;
            background-color: #f0fff4;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">S3 Direct Upload Test</h1>
            <p class="text-gray-600">Test optimized file upload directly to AWS S3</p>
        </div>

        <!-- Upload Form -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Upload Files</h2>
            
            <!-- File Input -->
            <div class="upload-zone rounded-lg p-8 text-center mb-6" id="upload-zone">
                <div class="upload-content">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <p class="text-lg text-gray-600 mb-2">Drag & drop files here or click to select</p>
                    <p class="text-sm text-gray-500 mb-4">Images (JPEG, PNG, WebP) and Videos (MP4, AVI, MOV, WebM)</p>
                    <p class="text-xs text-gray-400">Max 100MB per file, up to 10 files</p>
                    <input type="file" id="file-input" multiple accept="image/*,video/*" class="hidden">
                </div>
            </div>

            <!-- Selected Files -->
            <div id="selected-files" class="mb-6 hidden">
                <h3 class="text-lg font-medium mb-3">Selected Files</h3>
                <div id="files-list" class="space-y-2"></div>
            </div>

            <!-- Upload Controls -->
            <div class="flex gap-4">
                <button id="upload-btn" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    Upload to S3
                </button>
                <button id="clear-btn" class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                    Clear All
                </button>
            </div>
        </div>

        <!-- Upload Progress -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Upload Progress</h2>
            <div id="upload-progress" class="space-y-4">
                <p class="text-gray-500 text-center">No uploads in progress</p>
            </div>
        </div>

        <!-- Results -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Upload Results</h2>
            <div id="upload-results" class="space-y-4">
                <p class="text-gray-500 text-center">No uploads completed yet</p>
            </div>
        </div>
    </div>

    <!-- Include S3 Upload Library -->
    <script src="/js/s3-upload.js"></script>
    
    <script>
        // Configuration
        const API_TOKEN = 'bluprinter_slEAoWPIU6wRFIsoyOQ3orpV4wGeRiRM6ykzwGndzXFaBMMCYm0n0jijNpIk'; // S3 Upload Test Token
        const BASE_URL = window.location.origin;

        // Initialize components
        const uploadZone = document.getElementById('upload-zone');
        const fileInput = document.getElementById('file-input');
        const selectedFilesDiv = document.getElementById('selected-files');
        const filesList = document.getElementById('files-list');
        const uploadBtn = document.getElementById('upload-btn');
        const clearBtn = document.getElementById('clear-btn');
        const uploadProgressDiv = document.getElementById('upload-progress');
        const uploadResultsDiv = document.getElementById('upload-results');

        let selectedFiles = [];
        let progressUI = new UploadProgressUI('upload-progress');

        // Initialize S3 Upload
        const s3Upload = new S3DirectUpload({
            apiToken: API_TOKEN,
            baseUrl: BASE_URL,
            maxFileSize: 100 * 1024 * 1024, // 100MB
            maxFiles: 10,
            enableCompression: true,
            onProgress: (data) => {
                console.log('Upload progress:', data);
                // Progress is handled by UploadProgressUI
            },
            onComplete: (results) => {
                console.log('Upload completed:', results);
                displayResults(results);
                uploadBtn.disabled = false;
                uploadBtn.textContent = 'Upload to S3';
            },
            onError: (error) => {
                console.error('Upload error:', error);
                alert('Upload failed: ' + error.message);
                uploadBtn.disabled = false;
                uploadBtn.textContent = 'Upload to S3';
            }
        });

        // File selection handlers
        uploadZone.addEventListener('click', () => fileInput.click());
        uploadZone.addEventListener('dragover', handleDragOver);
        uploadZone.addEventListener('dragleave', handleDragLeave);
        uploadZone.addEventListener('drop', handleDrop);
        fileInput.addEventListener('change', handleFileSelect);

        // Button handlers
        uploadBtn.addEventListener('click', handleUpload);
        clearBtn.addEventListener('click', handleClear);

        function handleDragOver(e) {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        }

        function handleDragLeave(e) {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
        }

        function handleDrop(e) {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            const files = Array.from(e.dataTransfer.files);
            addFiles(files);
        }

        function handleFileSelect(e) {
            const files = Array.from(e.target.files);
            addFiles(files);
        }

        function addFiles(files) {
            selectedFiles = [...selectedFiles, ...files];
            displaySelectedFiles();
            uploadZone.classList.add('has-files');
        }

        function displaySelectedFiles() {
            if (selectedFiles.length === 0) {
                selectedFilesDiv.classList.add('hidden');
                return;
            }

            selectedFilesDiv.classList.remove('hidden');
            filesList.innerHTML = '';

            selectedFiles.forEach((file, index) => {
                const fileDiv = document.createElement('div');
                fileDiv.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg';
                fileDiv.innerHTML = `
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">${file.name}</div>
                            <div class="text-sm text-gray-500">${formatFileSize(file.size)} • ${file.type}</div>
                        </div>
                    </div>
                    <button onclick="removeFile(${index})" class="text-red-500 hover:text-red-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                `;
                filesList.appendChild(fileDiv);
            });
        }

        function removeFile(index) {
            selectedFiles.splice(index, 1);
            displaySelectedFiles();
            if (selectedFiles.length === 0) {
                uploadZone.classList.remove('has-files');
            }
        }

        async function handleUpload() {
            if (selectedFiles.length === 0) {
                alert('Please select files to upload');
                return;
            }

            uploadBtn.disabled = true;
            uploadBtn.textContent = 'Uploading...';
            uploadProgressDiv.innerHTML = '';

            try {
                // Add files to progress UI
                const uploadIds = selectedFiles.map((file, index) => {
                    return progressUI.addUpload(file.name, file.size);
                });

                // Start upload
                const results = await s3Upload.uploadFiles(selectedFiles);

                // Mark all as successful
                uploadIds.forEach(id => progressUI.setSuccess(id));

            } catch (error) {
                console.error('Upload failed:', error);
                alert('Upload failed: ' + error.message);
            }
        }

        function handleClear() {
            selectedFiles = [];
            displaySelectedFiles();
            uploadZone.classList.remove('has-files');
            uploadProgressDiv.innerHTML = '<p class="text-gray-500 text-center">No uploads in progress</p>';
            uploadResultsDiv.innerHTML = '<p class="text-gray-500 text-center">No uploads completed yet</p>';
        }

        function displayResults(results) {
            uploadResultsDiv.innerHTML = '';

            if (results.length === 0) {
                uploadResultsDiv.innerHTML = '<p class="text-gray-500 text-center">No uploads completed yet</p>';
                return;
            }

            results.forEach((result, index) => {
                const resultDiv = document.createElement('div');
                resultDiv.className = 'p-4 bg-green-50 border border-green-200 rounded-lg';
                resultDiv.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">${result.original_name}</div>
                                <div class="text-sm text-gray-500">${formatFileSize(result.size)} • ${result.type}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-green-600 font-medium">Uploaded</div>
                            <a href="${result.public_url}" target="_blank" class="text-sm text-blue-600 hover:underline">View File</a>
                        </div>
                    </div>
                `;
                uploadResultsDiv.appendChild(resultDiv);
            });
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    </script>
</body>
</html>
