@extends('layouts.admin')

@section('title', 'Xóa sản phẩm khỏi Google Merchant Center')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Xóa sản phẩm khỏi Google Merchant Center</h1>
                <p class="text-gray-600">
                    Nhập Offer ID của sản phẩm cần xóa, chọn domain và thị trường tương ứng.
                </p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                ← Quay lại
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white shadow-md rounded-2xl overflow-hidden">
        <form id="deleteGMCForm" class="p-6 space-y-6">
            @csrf

            <div>
                <label for="offer_id" class="block text-sm font-semibold text-gray-900 mb-1">
                    Offer ID (Product ID) <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="offer_id"
                    id="offer_id"
                    value="{{ old('offer_id') }}"
                    placeholder="VD: PRD-LGSPLRMF hoặc SKU12345"
                    required
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('offer_id') border-red-500 @enderror"
                >
                @error('offer_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">
                    Nhập Offer ID của sản phẩm trong Google Merchant Center (thường là SKU hoặc PRD-{id})
                </p>
            </div>

            <div>
                <label for="domain" class="block text-sm font-semibold text-gray-900 mb-1">
                    Domain <span class="text-red-500">*</span>
                </label>
                <select
                    name="domain"
                    id="domain"
                    required
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('domain') border-red-500 @enderror"
                    onchange="updateTargetCountryOptions()"
                >
                    <option value="">-- Chọn domain --</option>
                    @foreach($domains as $domain)
                        <option value="{{ $domain }}" {{ old('domain') == $domain ? 'selected' : '' }}>
                            {{ $domain }}
                        </option>
                    @endforeach
                </select>
                @error('domain')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">
                    Chọn domain tương ứng với cấu hình GMC
                </p>
            </div>

            <div>
                <label for="target_country" class="block text-sm font-semibold text-gray-900 mb-1">
                    Thị trường <span class="text-red-500">*</span>
                </label>
                <select
                    name="target_country"
                    id="target_country"
                    required
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('target_country') border-red-500 @enderror"
                >
                    <option value="">-- Chọn domain trước --</option>
                </select>
                @error('target_country')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">
                    Chọn thị trường (target country) tương ứng với domain đã chọn
                </p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                    Hủy
                </a>
                <button
                    type="submit"
                    id="submitBtn"
                    class="px-5 py-2.5 rounded-xl bg-red-600 text-white font-semibold hover:bg-red-700 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                >
                    <span id="submitText">Xóa sản phẩm</span>
                    <span id="submitLoading" class="hidden">
                        <svg class="inline-block w-5 h-5 animate-spin mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Đang xóa...
                    </span>
                </button>
            </div>
        </form>

        <!-- Result Message -->
        <div id="resultMessage" class="hidden px-6 pb-6">
            <div id="successMessage" class="hidden rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                <p class="font-semibold">Xóa thành công!</p>
                <p id="successText" class="mt-1 text-sm"></p>
            </div>
            <div id="errorMessage" class="hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                <p class="font-semibold">Xóa thất bại!</p>
                <p id="errorText" class="mt-1 text-sm"></p>
            </div>
        </div>
    </div>
</div>

<script>
const gmcConfigsByDomain = @json($gmcConfigsByDomain);
const countryLabels = @json($countryLabels);

function updateTargetCountryOptions() {
    const domainSelect = document.getElementById('domain');
    const countrySelect = document.getElementById('target_country');
    const selectedDomain = domainSelect.value;

    // Clear existing options
    countrySelect.innerHTML = '<option value="">-- Chọn thị trường --</option>';

    if (!selectedDomain || !gmcConfigsByDomain[selectedDomain]) {
        return;
    }

    // Add options for selected domain
    const configs = gmcConfigsByDomain[selectedDomain];
    configs.forEach(config => {
        const countryCode = config.target_country;
        const label = countryLabels[countryCode] || `${countryCode}`;
        const option = document.createElement('option');
        option.value = countryCode;
        option.textContent = `${label} - ${config.name}`;
        countrySelect.appendChild(option);
    });
}

// Initialize on page load if domain is pre-selected
document.addEventListener('DOMContentLoaded', function() {
    const domainSelect = document.getElementById('domain');
    if (domainSelect.value) {
        updateTargetCountryOptions();
    }
});

// Handle form submission
document.getElementById('deleteGMCForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const offerId = document.getElementById('offer_id').value;
    const domain = document.getElementById('domain').value;
    const targetCountry = document.getElementById('target_country').value;

    if (!offerId || !domain || !targetCountry) {
        alert('Vui lòng điền đầy đủ thông tin!');
        return;
    }

    // Confirm deletion
    if (!confirm(`Bạn có chắc chắn muốn xóa sản phẩm "${offerId}" khỏi Google Merchant Center?\n\nDomain: ${domain}\nThị trường: ${targetCountry}\n\nLưu ý: Hành động này không thể hoàn tác.`)) {
        return;
    }

    // Show loading state
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitLoading = document.getElementById('submitLoading');
    const resultMessage = document.getElementById('resultMessage');
    const successMessage = document.getElementById('successMessage');
    const errorMessage = document.getElementById('errorMessage');

    submitBtn.disabled = true;
    submitText.classList.add('hidden');
    submitLoading.classList.remove('hidden');
    resultMessage.classList.add('hidden');
    successMessage.classList.add('hidden');
    errorMessage.classList.add('hidden');

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        
        const response = await fetch('{{ route("api.gmc.delete-product") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                offer_id: offerId,
                domain: domain,
                target_country: targetCountry
            })
        });

        const data = await response.json();

        resultMessage.classList.remove('hidden');

        if (response.ok && data.success) {
            successMessage.classList.remove('hidden');
            document.getElementById('successText').textContent = data.message || 'Sản phẩm đã được xóa thành công khỏi Google Merchant Center.';
            
            // Reset form
            document.getElementById('deleteGMCForm').reset();
            document.getElementById('target_country').innerHTML = '<option value="">-- Chọn domain trước --</option>';
        } else {
            errorMessage.classList.remove('hidden');
            document.getElementById('errorText').textContent = data.message || data.error || 'Có lỗi xảy ra khi xóa sản phẩm.';
        }
    } catch (error) {
        console.error('Delete error:', error);
        resultMessage.classList.remove('hidden');
        errorMessage.classList.remove('hidden');
        document.getElementById('errorText').textContent = 'Có lỗi xảy ra khi kết nối đến server. Vui lòng thử lại.';
    } finally {
        submitBtn.disabled = false;
        submitText.classList.remove('hidden');
        submitLoading.classList.add('hidden');
    }
});
</script>
@endsection

