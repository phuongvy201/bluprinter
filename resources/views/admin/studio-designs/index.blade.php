@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ auth()->user()->hasRole('admin') ? 'Studio designs' : 'My designs' }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ auth()->user()->hasRole('admin') ? 'Library graphics for Create Your Own. Designs go live immediately — no approval step. Customers only see a low-resolution preview so the original file cannot be saved for reuse.' : 'Upload designs to the Create Your Own library. They go live immediately. Customers only see a low-resolution preview of your original file.' }}</p>
        </div>
        <a href="{{ route('admin.studio-designs.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">
            Add design
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 rounded-lg bg-green-50 text-green-800 border border-green-200">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @forelse($designs as $design)
            <div class="bg-white shadow rounded-2xl overflow-hidden">
                <div class="aspect-square bg-gray-100 flex items-center justify-center p-3">
                    <img src="{{ $design->previewUrl() }}" alt="{{ $design->name }}" class="max-h-full max-w-full object-contain" draggable="false">
                </div>
                <div class="p-3">
                    <div class="text-sm font-semibold text-gray-900 truncate">{{ $design->name }}</div>
                    <div class="text-xs text-gray-500">{{ $design->tag ?: 'Untagged' }} · ${{ number_format((float) $design->price, 2) }}</div>
                    <div class="mt-2 flex items-center justify-between">
                        @if($design->is_active)
                            <span class="text-xs font-semibold text-green-700">Active</span>
                        @else
                            <span class="text-xs font-semibold text-gray-500">Hidden</span>
                        @endif
                        <div class="flex gap-2">
                            <a href="{{ route('admin.studio-designs.edit', $design) }}" class="text-xs text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.studio-designs.destroy', $design) }}" method="POST" onsubmit="return confirm('Delete this design?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-600 hover:underline">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center text-gray-500 py-12">No designs yet.</div>
        @endforelse
    </div>

    @if($designs->hasPages())
        <div class="mt-6">{{ $designs->links() }}</div>
    @endif
</div>
@endsection
