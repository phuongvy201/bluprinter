<!-- Custom File Upload Component -->
<div id="custom-file-upload-section" class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
    <h4 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
        </svg>
        Upload Custom Files (Optional)
    </h4>
    
    <!-- File Upload Area -->
    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors cursor-pointer" 
         id="custom-file-upload-area"
         onclick="document.getElementById('custom-files-input').click()">
        <input type="file" 
               id="custom-files-input" 
               multiple 
               accept="image/*,video/*,.pdf,.doc,.docx,.txt"
               class="hidden">
        
        <div id="upload-area-content">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            <p class="text-sm text-gray-600 mb-2">Click to upload custom files</p>
            <p class="text-xs text-gray-500">Images, Videos, Documents (Max 10MB each, up to 5 files)</p>
        </div>
        
        <div id="upload-progress" class="hidden">
            <div class="flex items-center justify-center mb-2">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                <span class="ml-2 text-sm text-blue-600">Uploading...</span>
            </div>
        </div>
    </div>
    
    <!-- Uploaded Files Preview -->
    <div id="uploaded-files-preview" class="mt-4 hidden">
        <h5 class="text-sm font-semibold text-gray-700 mb-2">Uploaded Files:</h5>
        <div id="uploaded-files-list" class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <!-- Files will be added here -->
        </div>
    </div>
    
    <!-- Upload Info -->
    <div class="mt-3 text-xs text-gray-500">
        <p>Supported formats: JPG, PNG, GIF, WebP, SVG, MP4, AVI, MOV, WMV, PDF, DOC, DOCX, TXT</p>
        <p>Files will be automatically deleted after 24 hours if not used in an order</p>
    </div>
</div>

<script>
// Custom File Upload JavaScript
let uploadedFiles = [];
let isUploading = false;

document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('custom-files-input');
    const uploadArea = document.getElementById('custom-file-upload-area');
    
    if (fileInput && uploadArea) {
        // File input change handler
        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                handleFileUpload(e.target.files);
            }
        });
        
        // Drag and drop handlers
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.classList.add('border-blue-400', 'bg-blue-50');
        });
        
        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('border-blue-400', 'bg-blue-50');
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('border-blue-400', 'bg-blue-50');
            
            if (e.dataTransfer.files.length > 0) {
                handleFileUpload(e.dataTransfer.files);
            }
        });
    }
});

function handleFileUpload(files) {
    if (isUploading) {
        return;
    }
    
    const fileArray = Array.from(files);
    
    // Validate files
    const validation = validateFiles(fileArray);
    if (!validation.isValid) {
        showFileUploadError(validation.message);
        return;
    }
    
    // Show upload progress
    showUploadProgress();
    
    // Upload files
    uploadFiles(fileArray);
}

function validateFiles(files) {
    const maxFiles = 5;
    const maxSize = 10 * 1024 * 1024; // 10MB
    const allowedTypes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
        'video/mp4', 'video/avi', 'video/mov', 'video/wmv',
        'application/pdf', 'application/msword', 
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain'
    ];
    
    if (files.length > maxFiles) {
        return {
            isValid: false,
            message: `Maximum ${maxFiles} files allowed`
        };
    }
    
    for (let file of files) {
        if (file.size > maxSize) {
            return {
                isValid: false,
                message: `File "${file.name}" is too large. Maximum size is 10MB`
            };
        }
        
        if (!allowedTypes.includes(file.type)) {
            return {
                isValid: false,
                message: `File "${file.name}" type not supported`
            };
        }
    }
    
    return { isValid: true };
}

function showUploadProgress() {
    isUploading = true;
    document.getElementById('upload-area-content').classList.add('hidden');
    document.getElementById('upload-progress').classList.remove('hidden');
}

function hideUploadProgress() {
    isUploading = false;
    document.getElementById('upload-area-content').classList.remove('hidden');
    document.getElementById('upload-progress').classList.add('hidden');
}

function uploadFiles(files) {
    const formData = new FormData();
    formData.append('product_id', '{{ $product->id }}');
    
    files.forEach(file => {
        formData.append('files[]', file);
    });
    
    fetch('/api/custom-files/upload', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideUploadProgress();
        
        if (data.success) {
            // Add uploaded files to the list
            data.data.files.forEach(file => {
                uploadedFiles.push(file);
            });
            
            // Update preview
            updateFilesPreview();
            
            // Show success message
            showFileUploadSuccess(`Successfully uploaded ${data.data.uploaded_count} file(s)`);
        } else {
            showFileUploadError(data.message || 'Upload failed');
        }
    })
    .catch(error => {
        hideUploadProgress();
        console.error('Upload error:', error);
        showFileUploadError('Upload failed. Please try again.');
    });
}

function updateFilesPreview() {
    const previewContainer = document.getElementById('uploaded-files-preview');
    const filesList = document.getElementById('uploaded-files-list');
    
    if (uploadedFiles.length === 0) {
        previewContainer.classList.add('hidden');
        return;
    }
    
    previewContainer.classList.remove('hidden');
    filesList.innerHTML = '';
    
    uploadedFiles.forEach((file, index) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'bg-white rounded-lg border border-gray-200 p-3 shadow-sm';
        
        const isImage = file.mime_type.startsWith('image/');
        const isVideo = file.mime_type.startsWith('video/');
        
        fileItem.innerHTML = `
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    ${isImage ? `
                        <img src="${file.file_url}" alt="${file.original_name}" class="w-12 h-12 object-cover rounded">
                    ` : isVideo ? `
                        <div class="w-12 h-12 bg-purple-100 rounded flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"></path>
                            </svg>
                        </div>
                    ` : `
                        <div class="w-12 h-12 bg-gray-100 rounded flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    `}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate" title="${file.original_name}">${file.original_name}</p>
                    <p class="text-xs text-gray-500">${file.file_size}</p>
                </div>
                <button onclick="removeUploadedFile(${index})" class="text-red-600 hover:text-red-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        filesList.appendChild(fileItem);
    });
}

function removeUploadedFile(index) {
    const file = uploadedFiles[index];
    
    // Delete from server
    fetch(`/api/custom-files/${file.id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove from local array
            uploadedFiles.splice(index, 1);
            updateFilesPreview();
        } else {
            showFileUploadError('Failed to delete file');
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        showFileUploadError('Failed to delete file');
    });
}

function showFileUploadSuccess(message) {
    // You can use SweetAlert2 or any other notification library
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    } else {
        alert(message);
    }
}

function showFileUploadError(message) {
    // You can use SweetAlert2 or any other notification library
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000
        });
    } else {
        alert(message);
    }
}

// Function to get uploaded files (for use in addToCart)
function getUploadedFiles() {
    return uploadedFiles;
}

// Function to clear uploaded files
function clearUploadedFiles() {
    uploadedFiles = [];
    updateFilesPreview();
}
</script>



