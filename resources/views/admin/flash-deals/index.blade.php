@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Flash Deal</h1>
            <p class="text-gray-600 max-w-2xl text-sm leading-relaxed">
                Giảm giá theo khung giờ cho sản phẩm. Có thể <strong>chọn SP thủ công</strong>,
                hoặc để hệ thống tự chọn theo <strong>quy tắc / template / shop opt-in</strong>.
            </p>
        </div>
        <form method="POST" action="{{ route('admin.flash-deals.rotate') }}">
            @csrf
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-[#005366] text-white rounded-xl font-semibold hover:bg-[#003d4d] transition"
                    title="Chạy ngay quy trình: hết hạn deal cũ → áp template → rule → auto-enroll">
                Chạy rotation ngay
            </button>
        </form>
    </div>

    {{-- How price works --}}
    <div class="mb-8 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-950 space-y-2">
        <p class="font-bold text-amber-900">Giá flash hoạt động thế nào?</p>
        <ol class="list-decimal list-inside space-y-1 text-amber-900/90">
            <li><strong>Bật deal:</strong> lưu giá hiện tại vào <code class="bg-amber-100 px-1 rounded">original_price</code>, rồi ghi giá sale vào cột <code class="bg-amber-100 px-1 rounded">products.price</code>.</li>
            <li><strong>Khách thấy:</strong> catalog, trang SP, giỏ hàng đều lấy <code class="bg-amber-100 px-1 rounded">products.price</code> → đã là giá giảm.</li>
            <li><strong>Hết hạn / bấm Gỡ:</strong> trả <code class="bg-amber-100 px-1 rounded">products.price</code> về <code class="bg-amber-100 px-1 rounded">original_price</code>.</li>
        </ol>
        <p class="text-xs text-amber-800/80">
            Cron: <code class="bg-amber-100 px-1 rounded">flash-deals:rotate</code> 00:05 mỗi ngày ·
            <code class="bg-amber-100 px-1 rounded">flash-deals:expire</code> mỗi giờ.
            Item đã nằm trong giỏ <em>không</em> tự đổi giá khi deal hết.
        </p>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Manual create --}}
    <form method="POST" action="{{ route('admin.flash-deals.manual.store') }}" class="bg-white shadow-md rounded-2xl p-6 mb-8 space-y-4" id="fd-manual-form">
        @csrf
        <div>
            <h2 class="text-lg font-bold text-gray-900">0. Tạo flash sale thủ công</h2>
            <p class="text-sm text-gray-500 mt-1">
                Chọn sản phẩm → chọn loại thời lượng (khung giờ / ngày / tuần) → đặt % giảm.
                Có thể tạo nhiều campaign khác nhau (vd: 2 tiếng cho nhóm A, 1 ngày cho nhóm B).
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Lọc category</label>
                <select id="fd-filter-category" class="w-full rounded-xl border-gray-300 text-sm">
                    <option value="">Tất cả</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Lọc shop</label>
                <select id="fd-filter-shop" class="w-full rounded-xl border-gray-300 text-sm">
                    <option value="">Tất cả</option>
                    @foreach ($shops as $shop)
                        <option value="{{ $shop->id }}">{{ $shop->shop_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tìm sản phẩm</label>
                <input type="search" id="fd-product-search" placeholder="Tên, SKU hoặc ID…"
                       class="w-full rounded-xl border-gray-300 text-sm">
            </div>
        </div>

        <div>
            <div id="fd-product-list" class="max-h-72 overflow-y-auto border border-gray-200 rounded-xl divide-y divide-gray-100">
                @forelse ($products as $product)
                    @php
                        $thumb = $product->adminThumbnailUrl();
                        $catId = $product->template->category_id ?? '';
                        $catName = $product->template->category->name ?? '';
                    @endphp
                    <label class="fd-product-row flex items-center gap-3 px-3 py-2.5 hover:bg-gray-50 cursor-pointer text-sm"
                           data-search="{{ strtolower($product->id . ' ' . $product->name . ' ' . ($product->sku ?? '') . ' ' . ($product->shop?->shop_name ?? '') . ' ' . $catName) }}"
                           data-category-id="{{ $catId }}"
                           data-shop-id="{{ $product->shop_id ?? '' }}">
                        <input type="checkbox" name="product_ids[]" value="{{ $product->id }}"
                               class="rounded border-gray-300 text-[#005366] focus:ring-[#005366] shrink-0">
                        @if ($thumb)
                            <img src="{{ $thumb }}" alt="" class="w-12 h-12 rounded-lg object-cover border border-gray-200 shrink-0 bg-gray-50">
                        @else
                            <div class="w-12 h-12 rounded-lg bg-gray-100 border border-gray-200 shrink-0 flex items-center justify-center text-gray-400 text-xs">N/A</div>
                        @endif
                        <span class="min-w-0 flex-1">
                            <span class="font-medium text-gray-900 block truncate">#{{ $product->id }} — {{ $product->name }}</span>
                            <span class="block text-xs text-gray-500">
                                {{ $product->sku ? 'SKU: '.$product->sku.' · ' : '' }}
                                Giá: {{ number_format((float) $product->price, 2) }}
                                @if($catName) · {{ $catName }} @endif
                                @if($product->shop) · {{ $product->shop->shop_name }} @endif
                            </span>
                        </span>
                    </label>
                @empty
                    <p class="px-3 py-4 text-sm text-gray-500">Không có sản phẩm hiển thị được.</p>
                @endforelse
            </div>
            <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                <span>Hiển thị tối đa 400 SP mới nhất · Đang hiện: <span id="fd-visible-count" class="font-semibold text-gray-700">0</span></span>
                <span>Đã chọn: <span id="fd-selected-count" class="font-semibold text-gray-700">0</span></span>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Loại thời lượng</label>
            <div class="flex flex-wrap gap-2" id="fd-duration-presets">
                @foreach ([
                    '2h' => '2 giờ',
                    '6h' => '6 giờ',
                    '12h' => '12 giờ',
                    '1d' => '1 ngày',
                    '7d' => '1 tuần',
                    'custom' => 'Tùy chọn',
                ] as $value => $label)
                    <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-gray-200 bg-gray-50 text-sm cursor-pointer hover:border-[#005366] has-[:checked]:border-[#005366] has-[:checked]:bg-[#005366]/10 has-[:checked]:text-[#005366] has-[:checked]:font-semibold">
                        <input type="radio" name="duration" value="{{ $value }}" class="sr-only"
                               {{ old('duration', '1d') === $value ? 'checked' : '' }}>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            <p class="text-xs text-gray-400 mt-1">Khung giờ = campaign ngắn; ngày/tuần = campaign dài hơn. Có thể tạo nhiều đợt với thời lượng khác nhau.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">% giảm *</label>
                <input type="number" name="discount_percent" value="{{ old('discount_percent', $settings['default_discount_percent']) }}"
                       min="1" max="90" required class="w-full rounded-xl border-gray-300">
                <p class="text-xs text-gray-400 mt-1">Phải ≥ “% giảm tối thiểu” ở cấu hình chung.</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Bắt đầu</label>
                <input type="datetime-local" name="starts_at" id="fd-starts-at" value="{{ old('starts_at') }}"
                       class="w-full rounded-xl border-gray-300">
                <p class="text-xs text-gray-400 mt-1">Để trống = bắt đầu ngay.</p>
            </div>
            <div id="fd-ends-wrap">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kết thúc <span id="fd-ends-required" class="text-red-500 hidden">*</span></label>
                <input type="datetime-local" name="ends_at" id="fd-ends-at"
                       value="{{ old('ends_at', now()->addDay()->format('Y-m-d\TH:i')) }}"
                       class="w-full rounded-xl border-gray-300">
                <p class="text-xs text-gray-400 mt-1" id="fd-ends-hint">Chỉ cần khi chọn “Tùy chọn”.</p>
            </div>
        </div>

        <button type="submit" class="px-4 py-2 bg-[#e2150c] text-white rounded-xl font-semibold hover:bg-[#c0120a]">
            Tạo flash sale cho SP đã chọn
        </button>
    </form>

    {{-- Global settings --}}
    <form method="POST" action="{{ route('admin.flash-deals.settings') }}" class="bg-white shadow-md rounded-2xl p-6 mb-8 space-y-4">
        @csrf
        <div>
            <h2 class="text-lg font-bold text-gray-900">Cấu hình chung</h2>
            <p class="text-sm text-gray-500 mt-1">Áp dụng cho rotation tự động và điều kiện nhận deal.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="flex items-center gap-2 md:col-span-3">
                <input type="checkbox" name="enabled" value="1" id="fd_enabled" class="rounded" {{ $settings['enabled'] ? 'checked' : '' }}>
                <label for="fd_enabled" class="text-sm font-medium text-gray-700">Bật Flash Deal (tắt = không rotate / ẩn section homepage)</label>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Số SP homepage</label>
                <input type="number" name="product_limit" value="{{ old('product_limit', $settings['product_limit']) }}" min="4" max="24"
                       class="w-full rounded-xl border-gray-300">
                <p class="text-xs text-gray-400 mt-1">Tối đa SP trong carousel “Today's Big Deals”.</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">% giảm tối thiểu</label>
                <input type="number" name="min_discount_percent" value="{{ old('min_discount_percent', $settings['min_discount_percent']) }}" min="1" max="90"
                       class="w-full rounded-xl border-gray-300">
                <p class="text-xs text-gray-400 mt-1">Deal dưới ngưỡng này sẽ bị bỏ qua / không hiện.</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">% giảm mặc định (auto)</label>
                <input type="number" name="default_discount_percent" value="{{ old('default_discount_percent', $settings['default_discount_percent']) }}" min="1" max="90"
                       class="w-full rounded-xl border-gray-300">
                <p class="text-xs text-gray-400 mt-1">Dùng khi auto-enrollment (shop bật opt-in).</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Auto-enroll limit</label>
                <input type="number" name="auto_enroll_product_limit" value="{{ old('auto_enroll_product_limit', $settings['auto_enroll_product_limit']) }}" min="1" max="50"
                       class="w-full rounded-xl border-gray-300">
                <p class="text-xs text-gray-400 mt-1">Số SP tối đa mỗi lần rotation từ shop opt-in.</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Ngày bán chạy (auto)</label>
                <input type="number" name="auto_enroll_recent_days" value="{{ old('auto_enroll_recent_days', $settings['auto_enroll_recent_days']) }}" min="1" max="90"
                       class="w-full rounded-xl border-gray-300">
                <p class="text-xs text-gray-400 mt-1">Cửa sổ ngày để tính SP “trending”.</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Bắt đầu ngày (auto)</label>
                <input type="time" name="default_start_time" value="{{ old('default_start_time', $settings['default_start_time']) }}"
                       class="w-full rounded-xl border-gray-300">
                <p class="text-xs text-gray-400 mt-1">Khung giờ mặc định cho rule/auto (không golden hour).</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kết thúc ngày (auto)</label>
                <input type="time" name="default_end_time" value="{{ old('default_end_time', $settings['default_end_time']) }}"
                       class="w-full rounded-xl border-gray-300">
            </div>
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700">Lưu cấu hình</button>
    </form>

    {{-- Active deals --}}
    <div class="bg-white shadow-md rounded-2xl p-6 mb-8">
        <h2 class="text-lg font-bold text-gray-900 mb-1">Deal đang active ({{ $activeDeals->count() }})</h2>
        <p class="text-sm text-gray-500 mb-4">
            Nguồn: <code class="text-xs bg-gray-100 px-1 rounded">manual</code> /
            <code class="text-xs bg-gray-100 px-1 rounded">rule</code> /
            <code class="text-xs bg-gray-100 px-1 rounded">template</code> /
            <code class="text-xs bg-gray-100 px-1 rounded">auto_enrollment</code>.
            Cột Giá = sale / gốc. <strong>Gỡ</strong> = tắt deal + trả giá cũ.
        </p>
        @if ($activeDeals->isEmpty())
            <p class="text-gray-500 text-sm">Chưa có deal. Tạo thủ công phía trên, thêm rule/template, hoặc chạy rotation.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2 pr-4">Sản phẩm</th>
                            <th class="py-2 pr-4">Giá sale / gốc</th>
                            <th class="py-2 pr-4">%</th>
                            <th class="py-2 pr-4">Loại</th>
                            <th class="py-2 pr-4">Nguồn</th>
                            <th class="py-2 pr-4">Bắt đầu → Kết thúc</th>
                            <th class="py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($activeDeals as $deal)
                            @php
                                $hours = max(0.1, $deal->starts_at->diffInMinutes($deal->ends_at) / 60);
                                $typeLabel = $hours <= 12 ? 'Khung giờ' : ($hours <= 36 ? 'Theo ngày' : 'Theo tuần');
                                $typeClass = $hours <= 12 ? 'bg-orange-100 text-orange-800' : ($hours <= 36 ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800');
                                $thumb = $deal->product?->adminThumbnailUrl();
                            @endphp
                            <tr class="border-b border-gray-100">
                                <td class="py-2 pr-4">
                                    <div class="flex items-center gap-2 min-w-0">
                                        @if ($thumb)
                                            <img src="{{ $thumb }}" alt="" class="w-10 h-10 rounded-lg object-cover border border-gray-200 shrink-0">
                                        @endif
                                        <span class="font-medium truncate">{{ $deal->product?->name ?? '—' }}</span>
                                    </div>
                                </td>
                                <td class="py-2 pr-4">{{ number_format($deal->sale_price, 2) }} / {{ number_format($deal->original_price, 2) }}</td>
                                <td class="py-2 pr-4">-{{ $deal->discount_percent }}%</td>
                                <td class="py-2 pr-4">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $typeClass }}">{{ $typeLabel }}</span>
                                    <span class="block text-[10px] text-gray-400 mt-0.5">{{ number_format($hours, 1) }}h</span>
                                </td>
                                <td class="py-2 pr-4">{{ $deal->source }}</td>
                                <td class="py-2 pr-4 text-xs text-gray-600">
                                    {{ $deal->starts_at->format('d/m H:i') }} → {{ $deal->ends_at->format('d/m H:i') }}
                                </td>
                                <td class="py-2">
                                    <form method="POST" action="{{ route('admin.flash-deals.destroy', $deal) }}" class="inline"
                                          onsubmit="return confirm('Gỡ deal và trả giá cũ cho sản phẩm này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Gỡ</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Rules --}}
        <div class="bg-white shadow-md rounded-2xl p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-1">1. Quy tắc (tự chọn SP)</h2>
            <p class="text-sm text-gray-500 mb-4">
                Khi rotation: hệ thống tự tìm SP khớp điều kiện rồi giảm giá.
                <strong>Priority nhỏ hơn = ưu tiên hơn</strong>.
                Giờ vàng (vd <code class="text-xs bg-gray-100 px-1 rounded">12,20</code>) = chỉ chạy quanh giờ đó.
            </p>

            @forelse ($rules as $rule)
                <div class="flex items-start justify-between py-2 border-b border-gray-100 text-sm">
                    <div>
                        <span class="font-semibold">{{ $rule->name }}</span>
                        <span class="text-gray-500">({{ \App\Models\FlashDealRule::ruleTypes()[$rule->rule_type] ?? $rule->rule_type }})</span>
                        <br>
                        <span class="text-gray-500">-{{ $rule->discount_percent }}%, max {{ $rule->max_products }} SP · ưu tiên {{ $rule->priority }}</span>
                        @if ($rule->golden_hours)
                            <span class="text-gray-400"> · giờ vàng: {{ implode(', ', $rule->golden_hours) }}h</span>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('admin.flash-deals.rules.destroy', $rule) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 text-xs">Xóa</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-gray-400 mb-2">Chưa có quy tắc.</p>
            @endforelse

            <form method="POST" action="{{ route('admin.flash-deals.rules.store') }}" class="mt-4 space-y-3 border-t pt-4">
                @csrf
                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Thêm quy tắc</p>
                <input type="text" name="name" placeholder="Tên quy tắc (vd: Xả tồn cuối tuần)" required class="w-full rounded-lg border-gray-300 text-sm">
                <select name="rule_type" class="w-full rounded-lg border-gray-300 text-sm" required>
                    @foreach (\App\Models\FlashDealRule::ruleTypes() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 -mt-1">
                    Tồn lâu = SP lâu không cập nhật · Margin cao = chênh base vs giá bán · Bán chạy = order gần đây.
                </p>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-[10px] text-gray-500 font-medium">% giảm giá</label>
                        <input type="number" name="discount_percent" value="20" min="1" max="90" placeholder="vd: 20" class="w-full rounded-lg border-gray-300 text-sm">
                        <p class="text-[10px] text-gray-400 mt-0.5">SP khớp rule sẽ giảm bấy nhiêu %</p>
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-500 font-medium">Tối đa bao nhiêu SP</label>
                        <input type="number" name="max_products" value="5" min="1" max="50" placeholder="vd: 5" class="w-full rounded-lg border-gray-300 text-sm">
                        <p class="text-[10px] text-gray-400 mt-0.5">Mỗi lần rotation, rule này chọn tối đa N SP</p>
                    </div>
                </div>
                <input type="text" name="golden_hours" placeholder="Giờ vàng (tuỳ chọn): 12,20 — để trống nếu chạy cả ngày" class="w-full rounded-lg border-gray-300 text-sm">
                <p class="text-xs text-gray-400 -mt-1">Để trống trừ khi muốn chỉ flash quanh giờ cụ thể. Điền <code class="bg-gray-100 px-1 rounded">0</code> = khung 0h–2h (dễ bỏ lỡ cả ngày).</p>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <input type="number" name="min_days_stale" value="7" placeholder="Ngày tồn" class="w-full rounded-lg border-gray-300 text-sm">
                        <p class="text-[10px] text-gray-400 mt-0.5">Cho rule tồn lâu (mặc định 7). Quá cao → 0 SP khớp.</p>
                    </div>
                    <div>
                        <input type="number" name="min_margin_percent" placeholder="Margin %" class="w-full rounded-lg border-gray-300 text-sm">
                        <p class="text-[10px] text-gray-400 mt-0.5">Cho rule margin</p>
                    </div>
                    <div>
                        <input type="number" name="recent_days" placeholder="Ngày trending" class="w-full rounded-lg border-gray-300 text-sm">
                        <p class="text-[10px] text-gray-400 mt-0.5">Cho rule bán chạy</p>
                    </div>
                </div>
                <div>
                    <label class="text-[10px] text-gray-500 font-medium">Độ ưu tiên (1 = cao nhất)</label>
                    <input type="number" name="priority" value="10" min="1" max="100" placeholder="10" class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <button type="submit" class="px-3 py-2 bg-gray-800 text-white rounded-lg text-sm font-semibold">Thêm quy tắc</button>
            </form>
        </div>

        {{-- Templates --}}
        <div class="bg-white shadow-md rounded-2xl p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-1">2. Template lặp lại (nhiều campaign)</h2>
            <p class="text-sm text-gray-500 mb-4">
                Tạo nhiều template với khung giờ khác nhau — ví dụ <strong>10:00–12:00 (2 giờ)</strong> và
                <strong>00:00–23:59 (cả ngày)</strong>. Rotation sẽ áp từng template đúng lịch.
            </p>

            @forelse ($templates as $template)
                @php
                    $badgeClass = match ($template->recurrence) {
                        'hourly' => 'bg-orange-100 text-orange-800',
                        'weekly' => 'bg-purple-100 text-purple-800',
                        default => 'bg-blue-100 text-blue-800',
                    };
                @endphp
                <div class="flex items-start justify-between py-2 border-b border-gray-100 text-sm">
                    <div>
                        <span class="font-semibold">{{ $template->name }}</span>
                        <span class="inline-flex ml-1 px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $badgeClass }}">{{ $template->recurrenceLabel() }}</span>
                        <br>
                        <span class="text-gray-500">
                            -{{ $template->discount_percent }}% · {{ substr((string) $template->start_time, 0, 5) }}–{{ substr((string) $template->end_time, 0, 5) }}
                            · max {{ $template->max_products }} SP
                            @if ($template->category) · {{ $template->category->name }} @endif
                            @if ($template->recurrence === 'weekly' && $template->days_of_week)
                                · thứ: {{ implode(',', $template->days_of_week) }}
                            @endif
                        </span>
                    </div>
                    <form method="POST" action="{{ route('admin.flash-deals.templates.destroy', $template) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 text-xs">Xóa</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-gray-400 mb-2">Chưa có template.</p>
            @endforelse

            <form method="POST" action="{{ route('admin.flash-deals.templates.store') }}" class="mt-4 space-y-3 border-t pt-4">
                @csrf
                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Thêm template / campaign</p>
                <input type="text" name="name" placeholder="Tên (vd: Flash 2h trưa / Deal cả ngày)" required class="w-full rounded-lg border-gray-300 text-sm">
                <select name="category_id" class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="">Tất cả category</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <div class="grid grid-cols-2 gap-2">
                    <input type="number" name="discount_percent" value="20" min="1" max="90" class="rounded-lg border-gray-300 text-sm" title="% giảm">
                    <input type="number" name="max_products" value="5" min="1" max="50" class="rounded-lg border-gray-300 text-sm" title="Max SP">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-[10px] text-gray-400">Giờ bắt đầu</label>
                        <input type="time" name="start_time" value="10:00" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-400">Giờ kết thúc</label>
                        <input type="time" name="end_time" value="12:00" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                </div>
                <select name="recurrence" id="fd-template-recurrence" class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="hourly">Theo khung giờ — mỗi ngày trong khoảng giờ trên (vd 2 tiếng)</option>
                    <option value="daily">Theo ngày — mỗi ngày trong khoảng giờ</option>
                    <option value="weekly">Theo tuần — chỉ các thứ đã chọn</option>
                </select>
                <p class="text-xs text-gray-400 -mt-1">Muốn vừa flash 2h vừa deal 1 ngày: tạo <em>hai</em> template với khung giờ khác nhau.</p>
                <div class="flex flex-wrap gap-2 text-xs" id="fd-template-days">
                    @foreach (['0'=>'CN','1'=>'T2','2'=>'T3','3'=>'T4','4'=>'T5','5'=>'T6','6'=>'T7'] as $d => $label)
                        <label class="inline-flex items-center gap-1">
                            <input type="checkbox" name="days_of_week[]" value="{{ $d }}"> {{ $label }}
                        </label>
                    @endforeach
                </div>
                <button type="submit" class="px-3 py-2 bg-gray-800 text-white rounded-lg text-sm font-semibold">Thêm template</button>
            </form>
        </div>
    </div>

    <div class="mt-8 bg-blue-50 border border-blue-100 rounded-2xl p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-2">3. Auto-enrollment (seller)</h2>
        <p class="text-sm text-gray-600 leading-relaxed">
            Seller bật trong <strong>Sửa shop</strong>: “Tham gia Flash Deal tự động” và đặt % giảm tối đa chấp nhận.
            Khi rotation, platform tự chọn SP bán chạy + tồn lâu từ các shop đó (tối đa 2 SP/category).
            Không cần chọn SP tay ở đây.
        </p>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const search = document.getElementById('fd-product-search');
    const list = document.getElementById('fd-product-list');
    const countEl = document.getElementById('fd-selected-count');
    const visibleEl = document.getElementById('fd-visible-count');
    const catFilter = document.getElementById('fd-filter-category');
    const shopFilter = document.getElementById('fd-filter-shop');
    const endsAt = document.getElementById('fd-ends-at');
    const endsRequired = document.getElementById('fd-ends-required');
    const endsHint = document.getElementById('fd-ends-hint');
    const recurrence = document.getElementById('fd-template-recurrence');
    const daysWrap = document.getElementById('fd-template-days');

    function refreshCount() {
        if (!list) return;
        if (countEl) {
            countEl.textContent = String(list.querySelectorAll('input[name="product_ids[]"]:checked').length);
        }
        if (visibleEl) {
            visibleEl.textContent = String(
                Array.from(list.querySelectorAll('.fd-product-row')).filter(function (row) {
                    return row.style.display !== 'none';
                }).length
            );
        }
    }

    function filterProducts() {
        if (!list) return;
        const q = (search && search.value ? search.value : '').trim().toLowerCase();
        const cat = catFilter ? catFilter.value : '';
        const shop = shopFilter ? shopFilter.value : '';

        list.querySelectorAll('.fd-product-row').forEach(function (row) {
            const hay = row.getAttribute('data-search') || '';
            const rowCat = row.getAttribute('data-category-id') || '';
            const rowShop = row.getAttribute('data-shop-id') || '';
            let ok = true;
            if (q && hay.indexOf(q) === -1) ok = false;
            if (cat && rowCat !== cat) ok = false;
            if (shop && rowShop !== shop) ok = false;
            row.style.display = ok ? '' : 'none';
        });
        refreshCount();
    }

    function syncDurationUi() {
        const selected = document.querySelector('input[name="duration"]:checked');
        const isCustom = !selected || selected.value === 'custom';
        if (endsAt) {
            endsAt.required = isCustom;
            endsAt.disabled = !isCustom;
            endsAt.classList.toggle('opacity-50', !isCustom);
        }
        if (endsRequired) endsRequired.classList.toggle('hidden', !isCustom);
        if (endsHint) {
            endsHint.textContent = isCustom
                ? 'Bắt buộc khi chọn “Tùy chọn”.'
                : 'Tự tính từ thời lượng đã chọn (bắt đầu + ' + (selected ? selected.value : '') + ').';
        }
    }

    if (search) search.addEventListener('input', filterProducts);
    if (catFilter) catFilter.addEventListener('change', filterProducts);
    if (shopFilter) shopFilter.addEventListener('change', filterProducts);
    if (list) list.addEventListener('change', refreshCount);
    document.querySelectorAll('input[name="duration"]').forEach(function (el) {
        el.addEventListener('change', syncDurationUi);
    });

    if (recurrence && daysWrap) {
        const syncDays = function () {
            daysWrap.style.opacity = recurrence.value === 'weekly' ? '1' : '0.45';
            daysWrap.querySelectorAll('input').forEach(function (cb) {
                cb.disabled = recurrence.value !== 'weekly';
            });
        };
        recurrence.addEventListener('change', syncDays);
        syncDays();
    }

    filterProducts();
    syncDurationUi();
})();
</script>
@endpush
@endsection
