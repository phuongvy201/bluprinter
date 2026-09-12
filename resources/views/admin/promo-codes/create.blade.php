@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Create Promo Code</h1>
    <form action="{{ route('admin.promo-codes.store') }}" method="POST" class="bg-white shadow rounded-2xl p-6 space-y-6">
        @csrf
        @include('admin.promo-codes.partials.form-fields')
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.promo-codes.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700">Cancel</a>
            <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700">Create</button>
        </div>
    </form>
</div>
@endsection
