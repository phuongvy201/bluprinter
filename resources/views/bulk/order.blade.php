@extends('layouts.app')

@section('content')
<div class="bg-gray-50 py-10">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    @if(session('success'))
      <div class="mb-6 p-4 rounded-lg bg-green-50 text-green-800 border border-green-200">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="mb-6 p-4 rounded-lg bg-red-50 text-red-800 border border-red-200">{{ session('error') }}</div>
    @endif
    @if($errors->any())
      <div class="mb-6 p-4 rounded-lg bg-red-50 text-red-800 border border-red-200">
        <ul class="list-disc ml-5 space-y-1">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="bg-white rounded-2xl shadow p-6">
      <div class="text-center mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Need a Quote? Then Fill in the Request Form Below.</h1>
        <p class="text-gray-500 mt-2">We'll do special batches on request. Your contact person will get in touch.</p>
      </div>

      <form action="{{ route('bulk.order.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Left: Your Products -->
          <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Your Products</h2>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity of products required <span class="text-red-500">*</span></label>
                <input type="number" name="quantity" min="1" value="{{ old('quantity') }}" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Quantity of products required" required>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Products requested <span class="text-red-500">*</span></label>
                <input type="text" name="products" value="{{ old('products') }}" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Product (type, color, etc.)" required>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Upload design</label>
                <div class="flex">
                  <input type="file" name="design" class="flex-1 border border-gray-300 rounded-l-md px-3 py-2 text-sm" accept="image/*,application/pdf,image/vnd.adobe.photoshop">
                  <span class="inline-flex items-center px-4 py-2 bg-gray-100 border border-l-0 border-gray-300 rounded-r-md text-gray-600 text-sm">Upload file</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">Max 5 MB. Supported files: jpg, png, gif, pdf, psd.</p>
              </div>
            </div>
          </div>

          <!-- Right: Contact Information -->
          <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Your Contact Information</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Your Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter Your Name" required>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Your Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter Your Email" required>
              </div>
            </div>
            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Company/Organization</label>
              <input type="text" name="company" value="{{ old('company') }}" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Company/Organization">
            </div>
            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Number we can reach you at</label>
              <input type="text" name="phone" value="{{ old('phone') }}" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Phone number">
            </div>
            <p class="text-xs text-gray-500 mt-2">* Mandatory Fields</p>
          </div>
        </div>

        <div class="mt-8 flex justify-center">
          <button type="submit" class="px-6 py-3 rounded-full bg-indigo-700 hover:bg-indigo-800 text-white font-semibold">Get free Quote</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
