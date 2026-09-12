@extends('layouts.admin')

@section('title', 'Import Reviews')

@section('content')
<div class="space-y-6 max-w-4xl">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Import Reviews</h1>
            <p class="mt-1 text-sm text-gray-600">Upload CSV or Excel file to bulk create reviews.</p>
        </div>
        <a href="{{ route('admin.reviews.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Back to reviews</a>
    </div>

    @if (session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <form method="POST" action="{{ route('admin.reviews.import.process') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Import file</label>
                    <input type="file" name="file" id="file" accept=".xlsx,.xls,.csv" required
                           class="w-full text-sm text-gray-600">
                    <p class="text-xs text-gray-500 mt-1">Supported: .csv, .xlsx, .xls (max 10MB)</p>
                </div>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    Import Reviews
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Template</h2>
            <p class="text-sm text-gray-600">Download sample CSV with required columns.</p>
            <a href="{{ route('admin.reviews.import.template') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">
                Download template
            </a>
            <div class="text-xs text-gray-500 space-y-1">
                <p><strong>product_id</strong> or <strong>product_slug</strong> (one required)</p>
                <p><strong>customer_name</strong>, <strong>rating</strong> (1-5)</p>
                <p><strong>review_text</strong>, <strong>is_verified_purchase</strong>, <strong>is_approved</strong></p>
                <p><strong>review_date</strong>, <strong>image_1</strong> … <strong>image_5</strong> (URLs)</p>
            </div>
        </div>
    </div>
</div>
@endsection
