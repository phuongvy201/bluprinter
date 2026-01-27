@extends('layouts.admin')

@section('title', 'Import Products')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Import Products</h1>
            <p class="mt-1 text-sm text-gray-600">Upload Excel or CSV file to create products in bulk</p>
        </div>
        <a href="{{ route('admin.products.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Products
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Upload Section -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Upload Import File
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Supports: Excel (.xlsx, .xls) and CSV (.csv) files</p>
                </div>
                
                <form method="POST" action="{{ route('admin.products.import.process') }}" enctype="multipart/form-data" id="import-form">
                    @csrf
                    <div class="p-6">
                        <!-- File Upload Area -->
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-12 text-center hover:border-blue-400 transition-colors bg-gradient-to-br from-gray-50 to-blue-50" 
                             id="drop-zone"
                             ondrop="handleDrop(event)" 
                             ondragover="handleDragOver(event)"
                             ondragleave="handleDragLeave(event)">
                            <input type="file" 
                                   id="file-input" 
                                   name="file" 
                                   accept=".xlsx,.xls,.csv"
                                   class="hidden"
                                   onchange="handleFileSelect(this.files[0])"
                                   required>
                            
                            <div id="upload-prompt">
                                <svg class="w-20 h-20 text-gray-400 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-xl font-semibold text-gray-700 mb-2">Drop your file here</p>
                                <p class="text-sm text-gray-500 mb-4">or</p>
                                <button type="button" 
                                        onclick="document.getElementById('file-input').click()"
                                        class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                    </svg>
                                    Choose File
                                </button>
                                <p class="text-xs text-gray-400 mt-4">Max file size: 10MB</p>
                            </div>
                            
                            <!-- File Selected Info -->
                            <div id="file-info" class="hidden">
                                <div class="flex items-center justify-center space-x-4">
                                    <div class="p-4 bg-green-100 rounded-xl">
                                        <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="text-left">
                                        <p class="text-lg font-bold text-gray-900" id="file-name"></p>
                                        <p class="text-sm text-gray-500" id="file-size"></p>
                                        <button type="button" 
                                                onclick="clearFile()"
                                                class="text-xs text-red-600 hover:text-red-700 mt-2">
                                            Remove file
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @error('file')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        
                        <!-- Progress Bar (Hidden by default) -->
                        <div id="progress-container" class="mt-6 hidden">
                            <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-semibold text-gray-900">Import Progress</h4>
                                    <span id="progress-status" class="text-xs font-medium text-gray-600">Processing...</span>
                                </div>
                                
                                <!-- Progress Bar -->
                                <div class="w-full bg-gray-200 rounded-full h-4 mb-3 overflow-hidden">
                                    <div id="progress-bar" 
                                         class="bg-gradient-to-r from-green-500 to-green-600 h-4 rounded-full transition-all duration-300 ease-out"
                                         style="width: 0%">
                                    </div>
                                </div>
                                
                                <!-- Progress Info -->
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center space-x-4">
                                        <span class="text-gray-700">
                                            <span id="progress-text">0/0</span> items processed
                                        </span>
                                        <span class="text-green-600 font-semibold">
                                            <span id="success-count">0</span> successful
                                        </span>
                                        <span id="error-count-container" class="text-red-600 font-semibold hidden">
                                            <span id="error-count">0</span> errors
                                        </span>
                                    </div>
                                    <span id="progress-percentage" class="text-gray-600 font-semibold">0%</span>
                                </div>
                                
                                <!-- Error Messages (if any) -->
                                <div id="error-messages" class="mt-4 hidden">
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 max-h-32 overflow-y-auto">
                                        <p class="text-xs font-semibold text-red-800 mb-2">Errors:</p>
                                        <ul id="error-list" class="text-xs text-red-700 space-y-1"></ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="mt-6 flex justify-end">
                            <button type="submit" 
                                    id="submit-btn"
                                    disabled
                                    class="px-8 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-all shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                <span id="submit-text">Import Products</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Instructions Sidebar -->
        <div class="space-y-6">
            <!-- Download Template -->
            <div class="bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-teal-50">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Download Template
                    </h3>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-600 mb-4">Download the Excel template with sample data to see the required format</p>
                    <a href="{{ route('admin.products.import.template') }}" 
                       class="inline-flex items-center w-full justify-center px-6 py-3 bg-gradient-to-r from-green-600 to-teal-600 text-white font-semibold rounded-xl hover:from-green-700 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-all shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Download CSV Template
                    </a>
                </div>
            </div>
            
            <!-- Instructions -->
            <div class="bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-pink-50">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        File Format
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4 text-sm">
                        <div>
                            <p class="font-semibold text-gray-900 mb-2">Required Columns:</p>
                            <ul class="space-y-2 text-gray-600">
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span><strong>template_id</strong> - Template ID number</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span><strong>product_name</strong> - Product name</span>
                                </li>
                            </ul>
                        </div>
                        
                        <div>
                            <p class="font-semibold text-gray-900 mb-2">Optional Columns:</p>
                            <ul class="space-y-2 text-gray-600">
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span><strong>price</strong> - Price to add to template base_price</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span><strong>description</strong> - Custom description (replaces template description)</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span><strong>quantity</strong> - Stock quantity</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span><strong>status</strong> - active, draft, or inactive</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-purple-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span><strong>image_1 to image_8</strong> - Image URLs (max 8 images)</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-red-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"></path>
                                    </svg>
                                    <span><strong>video_url</strong> - Video URL (max 1 video)</span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-xs font-semibold text-yellow-800 mb-1">💡 Notes about price and description:</p>
                            <ul class="text-xs text-yellow-700 space-y-1 ml-4 list-disc">
                                <li><strong>price</strong>: Final price = template base_price + price in file</li>
                                <li><strong>description</strong>: If empty, will use description from template</li>
                                <li><strong>media</strong>: If no media URLs, will use media from template</li>
                            </ul>
                        </div>
                        
                        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-start">
                                <svg class="w-4 h-4 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <div>
                                    <p class="text-xs font-semibold text-blue-800 mb-1">☁️ Automatic AWS S3 Upload</p>
                                    <p class="text-xs text-blue-700">Images and videos from URLs will be automatically downloaded and uploaded to AWS S3. You only need to provide public URLs of media.</p>
                                </div>
                            </div>
                        </div>
                        
                        @if(!auth()->user()->hasRole('admin'))
                        <div class="mt-4 p-3 bg-purple-50 border border-purple-200 rounded-lg">
                            <div class="flex items-start">
                                <svg class="w-4 h-4 text-purple-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                </svg>
                                <div>
                                    <p class="text-xs font-semibold text-purple-800 mb-1">🔒 Template Usage Rights</p>
                                    <p class="text-xs text-purple-700">You can only use your own templates for import. Cannot use templates from others.</p>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Available Templates -->
        <div class="space-y-6">
            <div class="bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-yellow-50 to-orange-50">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        @if(auth()->user()->hasRole('admin'))
                            All Templates
                        @else
                            Your Templates
                        @endif
                    </h3>
                    <p class="text-xs text-gray-600 mt-1">Use this Template ID in import file</p>
                </div>
                <div class="p-4 max-h-96 overflow-y-auto">
                    @if($templates->count() > 0)
                        <div class="space-y-2">
                            @foreach($templates as $template)
                            <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-bold bg-gradient-to-r from-blue-500 to-indigo-600 text-white">
                                            #{{ $template->id }}
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ Str::limit($template->name, 20) }}</p>
                                            <p class="text-xs text-gray-500">{{ $template->category->name }}</p>
                                            @if(auth()->user()->hasRole('admin') && $template->user)
                                                <p class="text-xs text-blue-600 mt-0.5">
                                                    <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    {{ $template->user->name }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-sm text-gray-500 mb-2">
                                @if(auth()->user()->hasRole('admin'))
                                    No templates in system
                                @else
                                    You have no templates
                                @endif
                            </p>
                            @if(!auth()->user()->hasRole('admin'))
                            <a href="{{ route('admin.product-templates.create') }}" class="text-xs text-blue-600 hover:text-blue-700 mt-2 inline-block">
                                Create template first →
                            </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Quick Tips -->
            <div class="bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg rounded-xl p-6">
                <h4 class="text-lg font-bold mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    Quick Tips
                </h4>
                <ul class="space-y-2 text-sm text-blue-50">
                    <li class="flex items-start">
                        <span class="mr-2">💡</span>
                        <span>Download template first to see correct format</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">📋</span>
                        <span>Use Template ID from the list on the right</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">💰</span>
                        <span>Import price = add to template base_price</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">📝</span>
                        <span>Empty description = use from template</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">🖼️</span>
                        <span>Can add up to 8 images + 1 video (public URLs)</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">☁️</span>
                        <span>Media will automatically upload to AWS S3</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">✨</span>
                        <span>Variants will automatically create from template</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">⚡</span>
                        <span>Maximum file size: 10MB</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
let progressInterval = null;
let currentProgressKey = null;

function handleDragOver(event) {
    event.preventDefault();
    event.currentTarget.classList.add('border-blue-500', 'bg-blue-100');
}

function handleDragLeave(event) {
    event.preventDefault();
    event.currentTarget.classList.remove('border-blue-500', 'bg-blue-100');
}

function handleDrop(event) {
    event.preventDefault();
    event.currentTarget.classList.remove('border-blue-500', 'bg-blue-100');
    
    const files = event.dataTransfer.files;
    if (files.length > 0) {
        const file = files[0];
        document.getElementById('file-input').files = files;
        handleFileSelect(file);
    }
}

function handleFileSelect(file) {
    if (!file) return;
    
    // Show file info
    document.getElementById('upload-prompt').classList.add('hidden');
    document.getElementById('file-info').classList.remove('hidden');
    document.getElementById('file-name').textContent = file.name;
    document.getElementById('file-size').textContent = formatFileSize(file.size);
    
    // Enable submit button
    document.getElementById('submit-btn').disabled = false;
}

function clearFile() {
    document.getElementById('file-input').value = '';
    document.getElementById('upload-prompt').classList.remove('hidden');
    document.getElementById('file-info').classList.add('hidden');
    document.getElementById('submit-btn').disabled = true;
    
    // Reset progress
    stopProgressTracking();
    hideProgress();
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Form submission with AJAX
document.getElementById('import-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    
    // Disable submit button
    submitBtn.disabled = true;
    submitText.textContent = 'Importing...';
    
    // Show progress container
    showProgress();
    
    // Submit form via AJAX
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
        }
    })
    .then(async response => {
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            // Response is not JSON, likely an HTML error page
            const text = await response.text();
            console.error('Non-JSON response received:', text.substring(0, 500));
            throw new Error('Server returned HTML instead of JSON. This usually means there was a server error. Check the console for details.');
        }
        
        // Check if response is ok
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ error: 'Unknown error occurred' }));
            throw new Error(errorData.error || `Server error: ${response.status} ${response.statusText}`);
        }
        
        return response.json();
    })
    .then(data => {
        if (data.success) {
            currentProgressKey = data.progress_key;
            
            if (data.completed) {
                // Import completed immediately
                updateProgressDisplay({
                    processed: data.success_count,
                    total: data.success_count,
                    success: data.success_count,
                    errors: data.error_count,
                    percentage: 100,
                    status: 'completed'
                });
                
                if (data.error_count > 0 && data.errors) {
                    showErrors(data.errors);
                }
                
                setTimeout(() => {
                    window.location.href = '{{ route("admin.products.index") }}?imported=' + data.success_count;
                }, 2000);
            } else {
                // Start polling for progress
                startProgressTracking(data.progress_key);
            }
        } else {
            showError(data.error || 'Import failed');
            submitBtn.disabled = false;
            submitText.textContent = 'Import Products';
        }
    })
    .catch(error => {
        console.error('Import error:', error);
        showError('Import failed: ' + error.message);
        submitBtn.disabled = false;
        submitText.textContent = 'Import Products';
    });
});

function showProgress() {
    document.getElementById('progress-container').classList.remove('hidden');
    document.getElementById('progress-bar').style.width = '0%';
    document.getElementById('progress-text').textContent = '0/0';
    document.getElementById('success-count').textContent = '0';
    document.getElementById('error-count').textContent = '0';
    document.getElementById('progress-percentage').textContent = '0%';
    document.getElementById('progress-status').textContent = 'Processing...';
    document.getElementById('error-count-container').classList.add('hidden');
    document.getElementById('error-messages').classList.add('hidden');
}

function hideProgress() {
    document.getElementById('progress-container').classList.add('hidden');
}

function startProgressTracking(progressKey) {
    currentProgressKey = progressKey;
    
    console.log('Starting progress tracking for key:', progressKey);
    
    // Poll every 1 second (increased from 500ms to reduce server load)
    progressInterval = setInterval(() => {
        // Add timestamp to prevent caching
        const url = '{{ route("admin.products.import.progress") }}?progress_key=' + encodeURIComponent(progressKey) + '&_=' + Date.now();
        
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Cache-Control': 'no-cache',
                'Pragma': 'no-cache'
            },
            cache: 'no-store'
        })
        .then(async response => {
            // Check content type first
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response received:', text.substring(0, 200));
                throw new Error('Server returned non-JSON response');
            }
            
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            // Check if data has error property
            if (data.error) {
                console.error('Progress error:', data.error);
                stopProgressTracking();
                showError(data.error);
                return;
            }
            
            // Check if data is valid
            if (!data || typeof data !== 'object') {
                console.error('Invalid progress data:', data);
                return;
            }
            
            // Debug logging (only occasionally to avoid console spam)
            if (Math.random() < 0.1) {
                console.log('Progress fetched:', {
                    processed: data.processed,
                    total: data.total,
                    success: data.success,
                    errors: data.errors,
                    percentage: data.percentage,
                    status: data.status
                });
            }
            
            updateProgressDisplay(data);
            
            if (data.status === 'completed' || data.status === 'failed') {
                stopProgressTracking();
                
                if (data.status === 'completed') {
                    console.log('Import completed! Success:', data.success);
                    // Show completion message
                    document.getElementById('progress-status').textContent = 'Completed!';
                    document.getElementById('progress-status').classList.remove('text-gray-600');
                    document.getElementById('progress-status').classList.add('text-green-600');
                    
                    setTimeout(() => {
                        window.location.href = '{{ route("admin.products.index") }}?imported=' + (data.success || 0);
                    }, 2000);
                } else if (data.status === 'failed') {
                    showError(data.error || 'Import failed');
                    document.getElementById('submit-btn').disabled = false;
                    document.getElementById('submit-text').textContent = 'Import Products';
                }
            }
        })
        .catch(error => {
            console.error('Progress polling error:', error);
            // Don't stop polling on error, just log it
        });
    }, 1000); // Poll every 1 second
}

function stopProgressTracking() {
    if (progressInterval) {
        clearInterval(progressInterval);
        progressInterval = null;
    }
}

function updateProgressDisplay(data) {
    const percentage = Math.min(100, Math.max(0, data.percentage || 0));
    const processed = data.processed || 0;
    const total = data.total || 0;
    const success = data.success || 0;
    const errors = data.errors || 0;
    
    // Update progress bar
    document.getElementById('progress-bar').style.width = percentage + '%';
    document.getElementById('progress-percentage').textContent = percentage.toFixed(1) + '%';
    
    // Update text - show "X/Y" or "X/?" if total is unknown
    if (total > 0) {
        document.getElementById('progress-text').textContent = processed + '/' + total;
    } else if (processed > 0) {
        document.getElementById('progress-text').textContent = processed + '/?';
    } else {
        document.getElementById('progress-text').textContent = '0/0';
    }
    
    document.getElementById('success-count').textContent = success;
    
    // Debug logging (only occasionally to avoid console spam)
    if (Math.random() < 0.1) {
        console.log('Progress update:', {
            processed: processed,
            total: total,
            success: success,
            errors: errors,
            percentage: percentage,
            status: data.status
        });
    }
    
    // Update status
    if (data.status === 'completed') {
        document.getElementById('progress-status').textContent = 'Completed!';
        document.getElementById('progress-status').classList.remove('text-gray-600');
        document.getElementById('progress-status').classList.add('text-green-600');
    } else if (data.status === 'failed') {
        document.getElementById('progress-status').textContent = 'Failed';
        document.getElementById('progress-status').classList.remove('text-gray-600');
        document.getElementById('progress-status').classList.add('text-red-600');
    } else {
        document.getElementById('progress-status').textContent = 'Processing...';
        document.getElementById('progress-status').classList.remove('text-green-600', 'text-red-600');
        document.getElementById('progress-status').classList.add('text-gray-600');
    }
    
    // Show error count if there are errors
    if (errors > 0) {
        document.getElementById('error-count').textContent = errors;
        document.getElementById('error-count-container').classList.remove('hidden');
    } else {
        document.getElementById('error-count-container').classList.add('hidden');
    }
}

function showErrors(errors) {
    if (!errors || errors.length === 0) return;
    
    const errorList = document.getElementById('error-list');
    errorList.innerHTML = '';
    
    errors.slice(0, 10).forEach(error => {
        const li = document.createElement('li');
        li.textContent = error;
        errorList.appendChild(li);
    });
    
    if (errors.length > 10) {
        const li = document.createElement('li');
        li.textContent = '... and ' + (errors.length - 10) + ' more errors';
        li.classList.add('font-semibold');
        errorList.appendChild(li);
    }
    
    document.getElementById('error-messages').classList.remove('hidden');
}

function showError(message) {
    document.getElementById('progress-status').textContent = 'Error: ' + message;
    document.getElementById('progress-status').classList.remove('text-gray-600');
    document.getElementById('progress-status').classList.add('text-red-600');
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    stopProgressTracking();
});
</script>
@endsection


