@extends('layouts.admin')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Header Layout</h1>
        <p class="text-gray-600">
            Tùy chỉnh toàn bộ nội dung header: thanh thông báo (slide), menu, Creator Studio, User menu.
            Dòng chữ trên cùng (primary) luôn hiển thị <strong>đậm</strong>.
        </p>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.header.update') }}" class="space-y-8">
        @csrf
        @method('PUT')

        {{-- Brand / top bar --}}
        <div class="bg-white shadow-md rounded-2xl p-6 space-y-5">
            <h2 class="text-lg font-bold text-gray-900">Thanh trên & thương hiệu</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">Tagline</label>
                    <input type="text" name="tagline" value="{{ old('tagline', $settings['tagline']) }}"
                           class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">Số danh mục hiển thị</label>
                    <input type="number" min="4" max="30" name="categories_limit" value="{{ old('categories_limit', $settings['categories_limit']) }}"
                           class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">App label</label>
                    <input type="text" name="app_label" value="{{ old('app_label', $settings['app_label']) }}"
                           class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">App URL</label>
                    <input type="text" name="app_url" value="{{ old('app_url', $settings['app_url']) }}"
                           class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">Promo label</label>
                    <input type="text" name="promo_label" value="{{ old('promo_label', $settings['promo_label']) }}"
                           class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">Promo URL</label>
                    <input type="text" name="promo_url" value="{{ old('promo_url', $settings['promo_url']) }}"
                           class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-1">Search placeholders (mỗi dòng một câu)</label>
                <textarea name="search_placeholders" rows="4"
                          class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50">{{ old('search_placeholders', implode("\n", $settings['search_placeholders'] ?? [])) }}</textarea>
            </div>
        </div>

        {{-- Slides --}}
        <div class="bg-white shadow-md rounded-2xl p-6 space-y-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Announcement slides</h2>
                <p class="text-sm text-gray-500">Primary = chữ đậm bên trái. Secondary = phần sau dấu | (xoay slide).</p>
            </div>
            @php $slides = old('announcement_slides', $settings['announcement_slides']); @endphp
            @for ($i = 0; $i < 6; $i++)
                @php $slide = $slides[$i] ?? ['primary' => '', 'secondary' => '', 'show_stars' => false]; @endphp
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 p-4 rounded-xl bg-gray-50 border border-gray-100">
                    <div class="md:col-span-5">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Primary (đậm) #{{ $i + 1 }}</label>
                        <input type="text" name="announcement_slides[{{ $i }}][primary]" value="{{ $slide['primary'] ?? '' }}"
                               class="w-full rounded-lg border-gray-300 text-sm" placeholder="3,500,000+ Happy Customers">
                    </div>
                    <div class="md:col-span-5">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Secondary</label>
                        <input type="text" name="announcement_slides[{{ $i }}][secondary]" value="{{ $slide['secondary'] ?? '' }}"
                               class="w-full rounded-lg border-gray-300 text-sm" placeholder="Since 2021">
                    </div>
                    <div class="md:col-span-2 flex items-end pb-1">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="announcement_slides[{{ $i }}][show_stars]" value="1"
                                   @checked(!empty($slide['show_stars'])) class="rounded border-gray-300 text-blue-600">
                            Stars
                        </label>
                    </div>
                </div>
            @endfor
        </div>

        {{-- Nav links --}}
        <div class="bg-white shadow-md rounded-2xl p-6 space-y-4">
            <h2 class="text-lg font-bold text-gray-900">Sub navigation</h2>
            @php $navLinks = old('nav_links', $settings['nav_links']); @endphp
            @foreach ($navLinks as $i => $link)
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <div class="md:col-span-4">
                        <input type="text" name="nav_links[{{ $i }}][label]" value="{{ $link['label'] ?? '' }}"
                               class="w-full rounded-lg border-gray-300 text-sm" placeholder="Label">
                    </div>
                    <div class="md:col-span-6">
                        <input type="text" name="nav_links[{{ $i }}][url]" value="{{ $link['url'] ?? '' }}"
                               class="w-full rounded-lg border-gray-300 text-sm" placeholder="/products">
                    </div>
                    <div class="md:col-span-2 flex items-center">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="nav_links[{{ $i }}][accent]" value="1"
                                   @checked(!empty($link['accent'])) class="rounded border-gray-300 text-orange-500">
                            Accent
                        </label>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- User menu --}}
        <div class="bg-white shadow-md rounded-2xl p-6 space-y-4">
            <h2 class="text-lg font-bold text-gray-900">User menu</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Guest title (đậm)</label>
                    <input type="text" name="user_menu[guest_title]" value="{{ old('user_menu.guest_title', $settings['user_menu']['guest_title']) }}"
                           class="w-full rounded-xl border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Guest subtitle</label>
                    <input type="text" name="user_menu[guest_subtitle]" value="{{ old('user_menu.guest_subtitle', $settings['user_menu']['guest_subtitle']) }}"
                           class="w-full rounded-xl border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Auth title (đậm)</label>
                    <input type="text" name="user_menu[auth_title]" value="{{ old('user_menu.auth_title', $settings['user_menu']['auth_title']) }}"
                           class="w-full rounded-xl border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Auth subtitle</label>
                    <input type="text" name="user_menu[auth_subtitle]" value="{{ old('user_menu.auth_subtitle', $settings['user_menu']['auth_subtitle']) }}"
                           class="w-full rounded-xl border-gray-300">
                </div>
            </div>
        </div>

        {{-- Creator Studio --}}
        <div class="bg-white shadow-md rounded-2xl p-6 space-y-4">
            <h2 class="text-lg font-bold text-gray-900">Creator Studio (Gene AI)</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Title (đậm)</label>
                    <input type="text" name="creator_studio[title]" value="{{ old('creator_studio.title', $settings['creator_studio']['title']) }}"
                           class="w-full rounded-xl border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Subtitle</label>
                    <input type="text" name="creator_studio[subtitle]" value="{{ old('creator_studio.subtitle', $settings['creator_studio']['subtitle']) }}"
                           class="w-full rounded-xl border-gray-300">
                </div>
            </div>
            @php $cards = old('creator_studio.cards', $settings['creator_studio']['cards']); @endphp
            @foreach ($cards as $i => $card)
                <div class="p-4 rounded-xl border border-gray-100 bg-gray-50 space-y-3">
                    <p class="text-sm font-bold text-gray-800">Card #{{ $i + 1 }}</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <input type="text" name="creator_studio[cards][{{ $i }}][title]" value="{{ $card['title'] ?? '' }}"
                               class="w-full rounded-lg border-gray-300 text-sm" placeholder="Title">
                        <input type="text" name="creator_studio[cards][{{ $i }}][description]" value="{{ $card['description'] ?? '' }}"
                               class="w-full rounded-lg border-gray-300 text-sm" placeholder="Description">
                        <input type="text" name="creator_studio[cards][{{ $i }}][url]" value="{{ $card['url'] ?? '' }}"
                               class="w-full rounded-lg border-gray-300 text-sm" placeholder="/products">
                        <input type="text" name="creator_studio[cards][{{ $i }}][bg]" value="{{ $card['bg'] ?? '' }}"
                               class="w-full rounded-lg border-gray-300 text-sm" placeholder="#e8f7fb">
                        <input type="text" name="creator_studio[cards][{{ $i }}][image]" value="{{ $card['image'] ?? '' }}"
                               class="w-full rounded-lg border-gray-300 text-sm md:col-span-2" placeholder="Image URL (optional)">
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-3 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">
                Lưu cấu hình header
            </button>
        </div>
    </form>
</div>
@endsection
