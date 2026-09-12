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
    <form method="POST" action="{{ route('admin.flash-deals.manual.store') }}" class="bg-white shadow-md rounded-2xl p-6 mb-8 space-y-4">
        @csrf
        <div>
            <h2 class="text-lg font-bold text-gray-900">0. Tạo flash sale thủ công</h2>
            <p class="text-sm text-gray-500 mt-1">
                Chọn sản phẩm cụ thể → đặt % giảm và thời gian kết thúc.
                Nguồn deal sẽ là <code class="text-xs bg-gray-100 px-1 rounded">manual</code>.
            </p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Tìm sản phẩm</label>
            <input type="search" id="fd-product-search" placeholder="Gõ tên, SKU hoặc ID…"
                   class="w-full rounded-xl border-gray-300 text-sm mb-2">
            <div id="fd-product-list" class="max-h-56 overflow-y-auto border border-gray-200 rounded-xl divide-y divide-gray-100">
                @forelse ($products as $product)
                    <label class="fd-product-row flex items-start gap-3 px-3 py-2 hover:bg-gray-50 cursor-pointer text-sm"
                           data-search="{{ strtolower($product->id . ' ' . $product->name . ' ' . ($product->sku ?? '') . ' ' . ($product->shop?->shop_name ?? '')) }}">
                        <input type="checkbox" name="product_ids[]" value="{{ $product->id }}"
                               class="mt-1 rounded border-gray-300 text-[#005366] focus:ring-[#005366]">
                        <span class="min-w-0">
                            <span class="font-medium text-gray-900">#{{ $product->id }} — {{ $product->name }}</span>
                            <span class="block text-xs text-gray-500">
                                {{ $product->sku ? 'SKU: '.$product->sku.' · ' : '' }}
                                Giá hiện tại: {{ number_format((float) $product->price, 2) }}
                                @if($product->shop) · {{ $product->shop->shop_name }} @endif
                            </span>
                        </span>
                    </label>
                @empty
                    <p class="px-3 py-4 text-sm text-gray-500">Không có sản phẩm hiển thị được.</p>
                @endforelse
            </div>
            <p class="mt-1 text-xs text-gray-400">Hiển thị tối đa 400 SP mới nhất. Đã chọn: <span id="fd-selected-count" class="font-semibold text-gray-700">0</span></p>
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
                <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}"
                       class="w-full rounded-xl border-gray-300">
                <p class="text-xs text-gray-400 mt-1">Để trống = bắt đầu ngay.</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kết thúc *</label>
                <input type="datetime-local" name="ends_at"
                       value="{{ old('ends_at', now()->addDay()->format('Y-m-d\TH:i')) }}"
                       required class="w-full rounded-xl border-gray-300">
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
                            <th class="py-2 pr-4">Nguồn</th>
                            <th class="py-2 pr-4">Kết thúc</th>
                            <th class="py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($activeDeals as $deal)
                            <tr class="border-b border-gray-100">
                                <td class="py-2 pr-4 font-medium">{{ $deal->product?->name ?? '—' }}</td>
                                <td class="py-2 pr-4">{{ number_format($deal->sale_price, 2) }} / {{ number_format($deal->original_price, 2) }}</td>
                                <td class="py-2 pr-4">-{{ $deal->discount_percent }}%</td>
                                <td class="py-2 pr-4">{{ $deal->source }}</td>
                                <td class="py-2 pr-4">{{ $deal->ends_at->format('d/m H:i') }}</td>
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
            <h2 class="text-lg font-bold text-gray-900 mb-1">2. Template lặp lại</h2>
            <p class="text-sm text-gray-500 mb-4">
                Lịch cố định (daily/weekly) + khung giờ. Mỗi lần rotation đúng ngày → random SP trong category (nếu chọn).
            </p>

            @forelse ($templates as $template)
                <div class="flex items-start justify-between py-2 border-b border-gray-100 text-sm">
                    <div>
                        <span class="font-semibold">{{ $template->name }}</span>
                        <br>
                        <span class="text-gray-500">
                            -{{ $template->discount_percent }}% · {{ substr($template->start_time, 0, 5) }}-{{ substr($template->end_time, 0, 5) }}
                            · {{ $template->recurrence }}
                            @if ($template->category) · {{ $template->category->name }} @endif
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
                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Thêm template</p>
                <input type="text" name="name" placeholder="Tên template (vd: Flash trưa T-Shirt)" required class="w-full rounded-lg border-gray-300 text-sm">
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
                        <input type="time" name="end_time" value="14:00" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                </div>
                <select name="recurrence" class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="daily">Hàng ngày</option>
                    <option value="weekly">Hàng tuần (chọn thứ bên dưới)</option>
                </select>
                <div class="flex flex-wrap gap-2 text-xs">
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
    if (!search || !list) return;

    function refreshCount() {
        if (!countEl) return;
        countEl.textContent = String(list.querySelectorAll('input[name="product_ids[]"]:checked').length);
    }

    search.addEventListener('input', function () {
        const q = search.value.trim().toLowerCase();
        list.querySelectorAll('.fd-product-row').forEach(function (row) {
            const hay = row.getAttribute('data-search') || '';
            row.style.display = !q || hay.indexOf(q) !== -1 ? '' : 'none';
        });
    });

    list.addEventListener('change', refreshCount);
    refreshCount();
})();
</script>
@endpush
@endsection
