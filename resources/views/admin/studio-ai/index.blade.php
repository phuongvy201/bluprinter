@extends('layouts.admin')

@section('title', 'Studio AI generations')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Studio AI generations</h1>
            <p class="text-sm text-gray-500 mt-1">Every design generated on Create Your Own. Add a specific variation to the library from its thumbnail.</p>
        </div>
        <a href="{{ route('admin.studio-ai.settings') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50">
            AI settings
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 rounded-lg bg-green-50 text-green-800 border border-green-200">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 rounded-lg bg-red-50 text-red-800 border border-red-200">{{ session('error') }}</div>
    @endif

    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label for="search" class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" id="search" value="{{ request('search') }}"
                   class="w-full rounded-lg border-gray-300 text-sm" placeholder="Prompt, email, name">
        </div>
        <div>
            <label for="hidden" class="block text-xs font-medium text-gray-500 mb-1">Visibility</label>
            <select name="hidden" id="hidden" class="w-full rounded-lg border-gray-300 text-sm">
                <option value="">All</option>
                <option value="0" @selected(request('hidden') === '0')>Visible to customer</option>
                <option value="1" @selected(request('hidden') === '1')>Hidden from customer</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="px-4 py-2 bg-gray-900 text-white text-sm rounded-lg hover:bg-gray-800">Filter</button>
            <a href="{{ route('admin.studio-ai.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Reset</a>
        </div>
    </form>

    <div class="space-y-4">
        @forelse($generations as $generation)
            @php $images = $generation->images(); @endphp
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-5 flex flex-col lg:flex-row gap-4">
                    <div class="flex gap-3 overflow-x-auto shrink-0">
                        @foreach($images as $index => $url)
                            <div class="w-28 shrink-0">
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="block w-28 h-28 rounded-xl bg-gray-100 overflow-hidden border border-gray-200">
                                    <img src="{{ $url }}" alt="Design {{ $index + 1 }}" class="w-full h-full object-contain">
                                </a>
                                <form action="{{ route('admin.studio-ai.promote', $generation) }}" method="POST" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="image_index" value="{{ $index }}">
                                    <button type="submit" class="w-full text-xs font-semibold text-gray-800 border border-gray-300 rounded-lg py-1.5 hover:bg-gray-50">
                                        Add to library
                                    </button>
                                </form>
                            </div>
                        @endforeach
                        @if($images === [])
                            <div class="w-28 h-28 rounded-xl bg-gray-100 text-xs text-gray-400 flex items-center justify-center">No image</div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $generation->prompt }}</p>
                        <p class="mt-2 text-xs text-gray-500">
                            {{ $generation->created_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}
                            · {{ $generation->image_model ?: 'model unset' }}
                            ·
                            @if($generation->user)
                                {{ $generation->user->name }} ({{ $generation->user->email }})
                            @else
                                Guest
                            @endif
                            @if($generation->hidden_from_customer)
                                · <span class="font-semibold text-amber-700">Hidden from customer</span>
                            @endif
                        </p>
                        <div class="mt-3 flex flex-wrap gap-3">
                            <form action="{{ route('admin.studio-ai.hide', $generation) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-blue-600 hover:underline">
                                    {{ $generation->hidden_from_customer ? 'Show to customer' : 'Hide from customer' }}
                                </button>
                            </form>
                            <form action="{{ route('admin.studio-ai.destroy', $generation) }}" method="POST" onsubmit="return confirm('Delete this generation from history?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 py-16 bg-white rounded-2xl border border-gray-200">No generations yet.</div>
        @endforelse
    </div>

    @if($generations->hasPages())
        <div class="mt-6">{{ $generations->links() }}</div>
    @endif
</div>
@endsection
