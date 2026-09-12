@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Homepage Content</h1>
            <p class="text-gray-600">Chỉnh sửa banner, tiêu đề section, màu nền, hình ảnh và nội dung hiển thị trên trang chủ.</p>
        </div>
        <a href="{{ url('/') }}" target="_blank" class="inline-flex items-center px-4 py-2 rounded-xl bg-gray-100 text-gray-800 text-sm font-semibold hover:bg-gray-200">
            Xem trang chủ
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.home.update') }}" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        @include('admin.settings.partials.home-form-fields', ['settings' => $settings, 'compact' => false])

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-3 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700">
                Save Homepage
            </button>
        </div>
    </form>
</div>
@endsection
