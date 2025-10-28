/**
 * S3 Direct Upload with Progress Bar and Image Compression
 * Optimized for Bluprinter product creation
 */

class S3DirectUpload {
    constructor(options = {}) {
        this.apiToken = options.apiToken;
        this.baseUrl = options.baseUrl || window.location.origin;
        this.maxFileSize = options.maxFileSize || 100 * 1024 * 1024; // 100MB
        this.allowedTypes = options.allowedTypes || [
            'image/jpeg', 'image/jpg', 'image/png', 'image/webp',
            'video/mp4', 'video/avi', 'video/mov', 'video/webm'
        ];
        this.maxFiles = options.maxFiles || 10;
        this.onProgress = options.onProgress || (() => {});
        this.onComplete = options.onComplete || (() => {});
        this.onError = options.onError || (() => {});
        this.enableCompression = options.enableCompression !== false;
    }

    /**
     * Upload files directly to S3
     */
    async uploadFiles(files, productId = null) {
        try {
            // Validate files
            this.validateFiles(files);

            // Compress images if enabled
            const processedFiles = await this.processFiles(files);

        // Get presigned URLs
        const presignedData = await this.getPresignedUrls(processedFiles, productId);
        
        // Debug: Log the full response
        console.log('Presigned data response:', presignedData);
        
        // Extract presigned URLs from response
        const presignedUrls = presignedData.data?.presigned_urls || presignedData.presigned_urls || [];
        console.log('Extracted presigned URLs:', presignedUrls);

        // Upload files to S3
        const uploadResults = await this.uploadToS3(processedFiles, presignedUrls);

            // Confirm upload completion
            if (productId) {
                await this.confirmUpload(productId, uploadResults);
            }

            this.onComplete(uploadResults);
            return uploadResults;

        } catch (error) {
            console.error('Upload failed:', error);
            this.onError(error);
            throw error;
        }
    }

    /**
     * Validate files before upload
     */
    validateFiles(files) {
        if (!files || files.length === 0) {
            throw new Error('No files selected');
        }

        if (files.length > this.maxFiles) {
            throw new Error(`Maximum ${this.maxFiles} files allowed`);
        }

        for (let i = 0; i < files.length; i++) {
            const file = files[i];

            // Check file size
            if (file.size > this.maxFileSize) {
                throw new Error(`File "${file.name}" is too large. Maximum size: ${this.formatFileSize(this.maxFileSize)}`);
            }

            // Check file type
            if (!this.allowedTypes.includes(file.type)) {
                throw new Error(`File "${file.name}" has unsupported type. Allowed types: ${this.allowedTypes.join(', ')}`);
            }
        }
    }

    /**
     * Process files (compress images if needed)
     */
    async processFiles(files) {
        const processedFiles = [];

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            let processedFile = file;

            // Compress images if enabled and file is image
            if (this.enableCompression && file.type.startsWith('image/')) {
                try {
                    processedFile = await this.compressImage(file);
                } catch (error) {
                    console.warn('Image compression failed, using original:', error);
                    processedFile = file;
                }
            }

            processedFiles.push({
                name: processedFile.name,
                type: processedFile.type,
                size: processedFile.size,
                file: processedFile
            });
        }

        return processedFiles;
    }

    /**
     * Compress image using Canvas API
     */
    async compressImage(file, quality = 0.8, maxWidth = 1920, maxHeight = 1080) {
        return new Promise((resolve, reject) => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();

            img.onload = () => {
                // Calculate new dimensions
                let { width, height } = img;
                
                if (width > maxWidth || height > maxHeight) {
                    const ratio = Math.min(maxWidth / width, maxHeight / height);
                    width *= ratio;
                    height *= ratio;
                }

                // Set canvas dimensions
                canvas.width = width;
                canvas.height = height;

                // Draw and compress
                ctx.drawImage(img, 0, 0, width, height);
                
                canvas.toBlob((blob) => {
                    if (blob) {
                        const compressedFile = new File([blob], file.name, {
                            type: file.type,
                            lastModified: Date.now()
                        });
                        resolve(compressedFile);
                    } else {
                        reject(new Error('Image compression failed'));
                    }
                }, file.type, quality);
            };

            img.onerror = () => reject(new Error('Failed to load image'));
            img.src = URL.createObjectURL(file);
        });
    }

    /**
     * Get presigned URLs from backend
     */
    async getPresignedUrls(files, productId) {
        const response = await fetch(`${this.baseUrl}/api/upload/presigned-urls`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-API-Token': this.apiToken
            },
            body: JSON.stringify({
                files: files.map(f => ({
                    name: f.name,
                    type: f.type,
                    size: f.size
                })),
                product_id: productId
            })
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Failed to get presigned URLs');
        }

        return await response.json();
    }

    /**
     * Upload files to S3 using presigned URLs
     */
    async uploadToS3(files, presignedUrls) {
        // Debug: Log presigned URLs structure
        console.log('Presigned URLs:', presignedUrls);
        console.log('Files count:', files.length);
        console.log('Presigned URLs count:', presignedUrls.length);
        
        // Validate presigned URLs
        if (!presignedUrls || presignedUrls.length === 0) {
            throw new Error('No presigned URLs received');
        }
        
        if (presignedUrls.length !== files.length) {
            throw new Error(`Mismatch: ${files.length} files but ${presignedUrls.length} presigned URLs`);
        }

        const uploadPromises = files.map(async (file, index) => {
            const presignedUrl = presignedUrls[index];
            
            // Validate presigned URL
            if (!presignedUrl) {
                throw new Error(`No presigned URL for file ${index}: ${file.name}`);
            }
            
            if (!presignedUrl.presigned_url) {
                throw new Error(`Invalid presigned URL structure for file ${index}: ${file.name}`);
            }
            
            return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();

                // Track upload progress
                xhr.upload.addEventListener('progress', (event) => {
                    if (event.lengthComputable) {
                        const progress = (event.loaded / event.total) * 100;
                        this.onProgress({
                            fileIndex: index,
                            fileName: file.name,
                            progress: Math.round(progress),
                            loaded: event.loaded,
                            total: event.total
                        });
                    }
                });

                xhr.addEventListener('load', () => {
                    console.log(`Upload response for ${file.name}:`, {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText
                    });
                    
                    if (xhr.status === 200 || xhr.status === 204) {
                        resolve({
                            index: index,
                            filename: presignedUrl.filename,
                            original_name: presignedUrl.original_name,
                            public_url: presignedUrl.public_url,
                            key: presignedUrl.key,
                            type: presignedUrl.type,
                            size: file.size
                        });
                    } else {
                        const errorMsg = `Upload failed for ${file.name}: ${xhr.status} ${xhr.statusText}`;
                        console.error(errorMsg, xhr.responseText);
                        reject(new Error(errorMsg));
                    }
                });

                xhr.addEventListener('error', (event) => {
                    const errorMsg = `Upload failed for ${file.name}: Network error`;
                    console.error(errorMsg, event);
                    reject(new Error(errorMsg));
                });

                xhr.addEventListener('abort', () => {
                    const errorMsg = `Upload aborted for ${file.name}`;
                    console.error(errorMsg);
                    reject(new Error(errorMsg));
                });

                // Debug: Log upload details
                console.log(`Starting upload for ${file.name}:`, {
                    presignedUrl: presignedUrl.presigned_url,
                    contentType: file.type,
                    fileSize: file.size
                });
                
                // Start upload
                xhr.open('PUT', presignedUrl.presigned_url);
                xhr.setRequestHeader('Content-Type', file.type);
                xhr.send(file.file);
            });
        });

        return await Promise.all(uploadPromises);
    }

    /**
     * Confirm upload completion
     */
    async confirmUpload(productId, uploadResults) {
        const response = await fetch(`${this.baseUrl}/api/upload/confirm`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-API-Token': this.apiToken
            },
            body: JSON.stringify({
                product_id: productId,
                uploaded_files: uploadResults.map(result => ({
                    key: result.key,
                    public_url: result.public_url,
                    type: result.type
                }))
            })
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Failed to confirm upload');
        }

        return await response.json();
    }

    /**
     * Format file size for display
     */
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
}

/**
 * Upload Progress UI Component
 */
class UploadProgressUI {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.uploads = new Map();
    }

    addUpload(fileName, fileSize) {
        const uploadId = Date.now() + Math.random();
        const uploadElement = this.createUploadElement(uploadId, fileName, fileSize);
        this.container.appendChild(uploadElement);
        this.uploads.set(uploadId, uploadElement);
        return uploadId;
    }

    createUploadElement(uploadId, fileName, fileSize) {
        const div = document.createElement('div');
        div.className = 'upload-item mb-4 p-4 border border-gray-200 rounded-lg';
        div.innerHTML = `
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center">
                    <div class="upload-icon mr-3">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="font-medium text-gray-900">${fileName}</div>
                        <div class="text-sm text-gray-500">${this.formatFileSize(fileSize)}</div>
                    </div>
                </div>
                <div class="upload-status">
                    <span class="text-sm text-gray-500">Preparing...</span>
                </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="upload-progress bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <div class="upload-error hidden mt-2 text-sm text-red-600"></div>
        `;
        return div;
    }

    updateProgress(uploadId, progress, loaded, total) {
        const uploadElement = this.uploads.get(uploadId);
        if (!uploadElement) return;

        const progressBar = uploadElement.querySelector('.upload-progress');
        const status = uploadElement.querySelector('.upload-status');
        
        progressBar.style.width = `${progress}%`;
        status.textContent = `${progress}% (${this.formatFileSize(loaded)} / ${this.formatFileSize(total)})`;
    }

    setSuccess(uploadId) {
        const uploadElement = this.uploads.get(uploadId);
        if (!uploadElement) return;

        const status = uploadElement.querySelector('.upload-status');
        const progressBar = uploadElement.querySelector('.upload-progress');
        
        status.textContent = 'Completed';
        status.className = 'upload-status text-sm text-green-600';
        progressBar.className = 'upload-progress bg-green-600 h-2 rounded-full transition-all duration-300';
        progressBar.style.width = '100%';
    }

    setError(uploadId, errorMessage) {
        const uploadElement = this.uploads.get(uploadId);
        if (!uploadElement) return;

        const status = uploadElement.querySelector('.upload-status');
        const errorDiv = uploadElement.querySelector('.upload-error');
        const progressBar = uploadElement.querySelector('.upload-progress');
        
        status.textContent = 'Failed';
        status.className = 'upload-status text-sm text-red-600';
        errorDiv.textContent = errorMessage;
        errorDiv.classList.remove('hidden');
        progressBar.className = 'upload-progress bg-red-600 h-2 rounded-full transition-all duration-300';
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
}

// Export for use in other scripts
window.S3DirectUpload = S3DirectUpload;
window.UploadProgressUI = UploadProgressUI;
