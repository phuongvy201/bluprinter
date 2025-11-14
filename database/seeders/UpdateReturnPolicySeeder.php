<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Page;

class UpdateReturnPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder này chỉ cập nhật page Returns & Exchanges Policy
     * Xóa page cũ (nếu có) và tạo lại với nội dung mới
     */
    public function run(): void
    {
        // Lấy user admin
        $admin = User::role('admin')->first();

        if (!$admin) {
            $admin = User::first();
        }

        if (!$admin) {
            $this->command->error('Không tìm thấy user nào trong database. Vui lòng tạo user trước.');
            return;
        }

        $this->command->info('Đang cập nhật page Returns & Exchanges Policy...');

        // Xóa page cũ nếu tồn tại
        $oldPage = Page::where('slug', 'returns-exchanges-policy')->first();
        if ($oldPage) {
            $oldPage->delete();
            $this->command->info('✓ Đã xóa page cũ');
        }

        // Tạo page mới
        Page::create([
            'user_id' => $admin->id,
            'title' => 'Returns & Exchanges Policy',
            'slug' => 'returns-exchanges-policy',
            'content' => '<div class="max-w-6xl mx-auto py-8 px-4">
                    <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
                        <!-- Hero Header -->
                        <div class="px-8 py-12" style="background: linear-gradient(to right, #059669, #10b981, #14b8a6); background-color: #10b981;">
                            <h1 class="text-5xl font-bold mb-4 text-white">Returns & Exchanges Policy – Bluprinter</h1>
                            <p class="text-white text-xl mb-2">Your satisfaction is our priority - Easy returns within 30 days</p>
                            <p class="text-gray-100 text-sm">Last updated: ' . now()->format('F d, Y') . '</p>
                        </div>

                        <div class="px-8 py-8">
                            <!-- Introduction -->
                            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 border-l-4 border-blue-500 rounded-r-lg p-6 mb-8">
                                <p class="text-gray-800 leading-relaxed text-lg">
                                    At <strong>Bluprinter</strong>, we offer returns and exchanges within <strong>30 days</strong> from the date you receive your order. If you need to return or exchange an item, please contact our customer support team to submit your request.
                                </p>
                            </div>

                            <!-- Policy Details Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                <!-- Restocking Fee -->
                                <div class="bg-gradient-to-r from-green-100 to-emerald-100 border-2 border-green-500 rounded-lg p-6">
                                    <div class="flex items-center justify-center">
                                        <div class="text-center">
                                            <div class="flex justify-center mb-4">
                                                <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <h3 class="text-xl font-bold text-green-900 mb-2">Restocking Fee</h3>
                                            <p class="text-4xl font-bold text-green-600 mb-2">NO FEE</p>
                                            <p class="text-gray-700 text-sm">Bluprinter does not charge any restocking fees.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Return Method -->
                                <div class="bg-gradient-to-r from-blue-100 to-cyan-100 border-2 border-blue-500 rounded-lg p-6">
                                    <div class="flex items-center justify-center">
                                        <div class="text-center">
                                            <div class="flex justify-center mb-4">
                                                <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <h3 class="text-xl font-bold text-blue-900 mb-2">Return Method</h3>
                                            <p class="text-2xl font-bold text-blue-600 mb-2">NO RETURN REQUIRED</p>
                                            <p class="text-gray-700 text-sm">You do not need to send the item back to us.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Currency -->
                                <div class="bg-gradient-to-r from-purple-100 to-pink-100 border-2 border-purple-500 rounded-lg p-6">
                                    <div class="flex items-center justify-center">
                                        <div class="text-center">
                                            <div class="flex justify-center mb-4">
                                                <div class="w-16 h-16 bg-purple-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <h3 class="text-xl font-bold text-purple-900 mb-2">Currency</h3>
                                            <p class="text-2xl font-bold text-purple-600 mb-2">USD</p>
                                            <p class="text-gray-700 text-sm">All refunds are processed in US Dollars (USD).</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Refund Processing Time -->
                                <div class="bg-gradient-to-r from-orange-100 to-amber-100 border-2 border-orange-500 rounded-lg p-6">
                                    <div class="flex items-center justify-center">
                                        <div class="text-center">
                                            <div class="flex justify-center mb-4">
                                                <div class="w-16 h-16 bg-orange-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <h3 class="text-xl font-bold text-orange-900 mb-2">Refund Processing Time</h3>
                                            <p class="text-2xl font-bold text-orange-600 mb-2">7-14 BUSINESS DAYS</p>
                                            <p class="text-gray-700 text-sm">Refunds are processed within 7-14 business days after approval.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Return Label Information -->
                            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 border-l-4 border-indigo-500 rounded-r-lg p-6 mb-8">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center mr-4">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-xl font-bold text-indigo-900 mb-3">Return Label</h3>
                                        <p class="text-gray-800 leading-relaxed mb-3">
                                            <strong>No return label needed.</strong> Since you do not need to send items back, there is no requirement for a return shipping label. Simply contact our customer support team with your order information and photos of the issue, and we will process your return or exchange request.
                                        </p>
                                        <div class="bg-indigo-100 border border-indigo-300 rounded-lg p-3">
                                            <p class="text-indigo-900 text-sm">
                                                <strong>📦 Note:</strong> When contacting us, please include a photo of your shipping label (from the original package) to help us locate your order quickly.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Return Criteria Section -->
                            <div class="mb-10">
                                <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-4 rounded-t-lg mb-0">
                                    <h2 class="text-3xl font-bold">2. Conditions Eligible for Returns & Exchanges</h2>
                                    <p class="text-purple-100 mt-2">You may request a return or exchange only if your item falls under one of the following categories:</p>
                                </div>

                                <!-- Criteria Cards -->
                                <div class="space-y-6 mt-6">
                                    <!-- Criterion A -->
                                    <div class="bg-gradient-to-br from-red-50 to-rose-50 border-l-4 border-red-500 rounded-r-lg p-6">
                                        <div class="flex items-start mb-4">
                                            <div class="flex-shrink-0 w-14 h-14 bg-red-500 text-white rounded-full flex items-center justify-center mr-4 text-2xl font-bold">A</div>
                                            <div class="flex-1">
                                                <h3 class="text-2xl font-bold text-red-800 mb-3">a. Wrong / Damaged / Defective Products</h3>
                                                <p class="text-gray-700 mb-4">Bluprinter will fully support cases where:</p>
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 ml-0 md:ml-18">
                                            <div class="bg-white rounded-lg p-4 shadow-sm border border-red-200">
                                                <div class="flex items-start">
                                                    <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    <p class="text-gray-800">The product does not match the description on the website: <strong>wrong item, wrong material, wrong size</strong>.</p>
                                                </div>
                                            </div>
                                            
                                            <div class="bg-white rounded-lg p-4 shadow-sm border border-red-200">
                                                <div class="flex items-start">
                                                    <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    <p class="text-gray-800">The product arrives <strong>torn, dirty, wet, or covered with lint/hair</strong>.</p>
                                                </div>
                                            </div>
                                            
                                            <div class="bg-white rounded-lg p-4 shadow-sm border border-red-200">
                                                <div class="flex items-start">
                                                    <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    <p class="text-gray-800">The print has visible defects: <strong>blurred, misaligned, or incorrect placement</strong>.</p>
                                                </div>
                                            </div>
                                            
                                            <div class="bg-white rounded-lg p-4 shadow-sm border border-red-200">
                                                <div class="flex items-start">
                                                    <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    <p class="text-gray-800">Print damage occurs after the first wash (<strong>peeling, fading, etc.</strong>).</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Criterion B -->
                                    <div class="bg-gradient-to-br from-orange-50 to-amber-50 border-l-4 border-orange-500 rounded-r-lg p-6">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 w-14 h-14 bg-orange-500 text-white rounded-full flex items-center justify-center mr-4 text-2xl font-bold">B</div>
                                            <div class="flex-1">
                                                <h3 class="text-2xl font-bold text-orange-800 mb-3">b. Incorrect Size</h3>
                                                <div class="bg-white rounded-lg p-4 shadow-sm border border-orange-200">
                                                    <p class="text-gray-800 leading-relaxed">
                                                        Applicable when the item received differs from the size chart by <strong class="text-orange-600">over 1.5 inches</strong> in measurement.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Criterion C -->
                                    <div class="bg-gradient-to-br from-yellow-50 to-amber-50 border-l-4 border-yellow-500 rounded-r-lg p-6">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 w-14 h-14 bg-yellow-500 text-white rounded-full flex items-center justify-center mr-4 text-2xl font-bold">C</div>
                                            <div class="flex-1">
                                                <h3 class="text-2xl font-bold text-yellow-800 mb-3">c. Non-fitting Items</h3>
                                                <div class="bg-white rounded-lg p-4 shadow-sm border border-yellow-200">
                                                    <p class="text-gray-800 leading-relaxed mb-2">
                                                        Only <strong>t-shirts and tank tops</strong> are eligible for returns/exchanges due to fitting issues.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Criterion D -->
                                    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 border-l-4 border-blue-500 rounded-r-lg p-6">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 w-14 h-14 bg-blue-500 text-white rounded-full flex items-center justify-center mr-4 text-2xl font-bold">D</div>
                                            <div class="flex-1">
                                                <h3 class="text-2xl font-bold text-blue-800 mb-3">d. Shipping-related Damage or Errors</h3>
                                                <div class="bg-white rounded-lg p-4 shadow-sm border border-blue-200">
                                                    <p class="text-gray-800 leading-relaxed mb-3">
                                                        Bluprinter supports cases where the order is wrong, damaged, or faulty due to the shipping process.
                                                    </p>
                                                    <div class="bg-blue-100 border border-blue-300 rounded-lg p-3">
                                                        <p class="text-blue-900 text-sm mb-2">
                                                            <strong>💡 Please check your package carefully upon delivery.</strong> If you find any issues:
                                                        </p>
                                                        <ul class="list-disc list-inside text-blue-900 space-y-1">
                                                            <li>Refuse the package immediately, or</li>
                                                            <li>Contact Bluprinter within <strong>30 days of delivery</strong> for assistance.</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Eligible Products Section -->
                            <div class="bg-gradient-to-r from-teal-600 to-cyan-600 text-white px-6 py-4 rounded-t-lg mb-0">
                                <h2 class="text-3xl font-bold flex items-center">
                                    <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    3. Products Eligible for Return
                                </h2>
                            </div>
                            <div class="bg-teal-50 border-2 border-teal-300 border-t-0 rounded-b-lg p-6 mb-10">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="bg-white rounded-lg p-5 shadow-md border border-teal-200">
                                        <div class="flex justify-center mb-3">
                                            <div class="w-12 h-12 bg-teal-500 rounded-full flex items-center justify-center">
                                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <p class="text-gray-800 text-center">Items that meet the <strong>eligibility criteria</strong> listed above.</p>
                                    </div>
                                    
                                    <div class="bg-white rounded-lg p-5 shadow-md border border-teal-200">
                                        <div class="flex justify-center mb-3">
                                            <div class="w-12 h-12 bg-teal-500 rounded-full flex items-center justify-center">
                                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <p class="text-gray-800 text-center">Items that show <strong>no signs of use</strong>, have the <strong>neck label intact</strong>, and remain in the <strong>original packaging</strong>.</p>
                                    </div>
                                    
                                    <div class="bg-white rounded-lg p-5 shadow-md border border-teal-200">
                                        <div class="flex justify-center mb-3">
                                            <div class="w-12 h-12 bg-teal-500 rounded-full flex items-center justify-center">
                                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <p class="text-gray-800 text-center">Return/exchange requests submitted <strong>within 30 days</strong> from delivery.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Return Process Section -->
                            <div class="mb-8">
                                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-4 rounded-t-lg">
                                    <h2 class="text-3xl font-bold flex items-center">
                                        <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                        </svg>
                                        4. Return & Exchange Process
                                    </h2>
                                </div>
                                <div class="bg-indigo-50 border-2 border-indigo-300 border-t-0 rounded-b-lg p-6">
                                    <!-- Step 1 -->
                                    <div class="mb-8">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <div class="w-12 h-12 text-white rounded-full flex items-center justify-center text-xl font-bold shadow-md mr-4" style="background: linear-gradient(135deg, #6366f1, #8b5cf6); background-color: #6366f1;">1</div>
                                            </div>
                                            <div class="flex-1 bg-white rounded-lg p-6 shadow-sm border border-indigo-200">
                                                <h3 class="text-xl font-bold text-indigo-900 mb-2">Contact Bluprinter</h3>
                                                <p class="text-gray-700 mb-4">When contacting us, please provide:</p>
                                                <div class="space-y-2.5">
                                                    <div class="flex items-start">
                                                        <svg class="w-5 h-5 text-indigo-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <p class="text-gray-800">Order information.</p>
                                                    </div>
                                                    <div class="flex items-start">
                                                        <svg class="w-5 h-5 text-indigo-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <p class="text-gray-800">Photo of the shipping label.</p>
                                                    </div>
                                                    <div class="flex items-start">
                                                        <svg class="w-5 h-5 text-indigo-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <p class="text-gray-800">Photos clearly showing the damaged/wrong/defective area.</p>
                                                    </div>
                                                    <div class="flex items-start">
                                                        <svg class="w-5 h-5 text-indigo-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <p class="text-gray-800">Photos showing accurate width & length measurements (if the size is incorrect).</p>
                                                    </div>
                                                    <div class="flex items-start">
                                                        <svg class="w-5 h-5 text-indigo-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <p class="text-gray-800">Information about the item you want to receive as a replacement.</p>
                                                    </div>
                                                </div>
                                                <div class="mt-4 bg-blue-50 border-l-3 border-blue-400 rounded-r-lg p-3">
                                                    <p class="text-blue-900 text-sm">
                                                        <strong>📸 Note:</strong> For orders with multiple items, please provide photos or videos of all items laid flat side by side.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 2 -->
                                    <div>
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <div class="w-12 h-12 text-white rounded-full flex items-center justify-center text-xl font-bold shadow-md mr-4" style="background: linear-gradient(135deg, #a855f7, #ec4899); background-color: #a855f7;">2</div>
                                            </div>
                                            <div class="flex-1 bg-white rounded-lg p-6 shadow-sm border border-purple-200">
                                                <h3 class="text-xl font-bold text-purple-900 mb-2">Verification</h3>
                                                <p class="text-gray-700 mb-4">After confirming that your item qualifies for return or exchange, Bluprinter will:</p>
                                                <div class="bg-green-50 border-l-3 border-green-500 rounded-r-lg p-4">
                                                    <div class="space-y-2.5">
                                                        <p class="text-green-900 font-medium">
                                                            ✓ Issue a <strong>refund</strong> (processed within 7-14 business days in USD), or
                                                        </p>
                                                        <p class="text-green-900 font-medium">
                                                            ✓ Send a <strong>replacement</strong> within <strong class="text-green-700">7 business days</strong>.
                                                        </p>
                                                        <p class="text-green-900 font-medium pt-2 border-t border-green-200">
                                                            ✓ You <strong>do not need to send the item back</strong> (no return label required).
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Important Notes -->
                            <div class="bg-gradient-to-br from-amber-100 to-orange-100 border-2 border-amber-500 rounded-lg p-6 mb-8">
                                <h3 class="text-2xl font-bold text-amber-900 mb-4 flex items-center">
                                    <svg class="w-8 h-8 mr-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    Important Notes
                                </h3>
                                <div class="space-y-3">
                                    <div class="bg-white rounded-lg p-4 border-l-4 border-amber-500">
                                        <p class="text-gray-800">
                                            <strong class="text-amber-800">⚠️</strong> Items returned <strong>without Bluprinter\'s prior verification will not be supported</strong>.
                                        </p>
                                    </div>
                                    <div class="bg-white rounded-lg p-4 border-l-4 border-amber-500">
                                        <p class="text-gray-800">
                                            <strong class="text-amber-800">💱</strong> Replacements will be issued for items of <strong>equal or greater value</strong> (price differences may apply).
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- International Orders Notice -->
                            <div class="bg-gradient-to-r from-blue-600 to-cyan-600 rounded-lg p-8 text-center text-white">
                                <div class="flex justify-center mb-4">
                                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center">
                                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="text-2xl font-bold mb-3">International Orders</h3>
                                <p class="text-xl text-blue-100 mb-2">
                                    For <strong>international orders</strong>
                                </p>
                                <div class="bg-white bg-opacity-20 rounded-lg p-4 inline-block">
                                    <p class="text-2xl font-bold">60 DAYS</p>
                                    <p class="text-blue-100">support window from date of delivery</p>
                                </div>
                                <p class="mt-4 text-sm text-blue-100">For international orders, defective or unwanted items are supported within 60 days of delivery.</p>
                            </div>
                        </div>
                    </div>
                </div>',
            'excerpt' => 'Complete returns and exchanges policy - 30-day returns, no restocking fee, easy process for wrong, damaged or non-fitting items',
            'status' => 'published',
            'published_at' => now(),
            'template' => 'default',
            'show_in_menu' => true,
            'menu_title' => 'Returns & Exchanges',
            'sort_order' => 4,
            'meta_title' => 'Returns & Exchanges Policy - Bluprinter Easy Returns',
            'meta_description' => 'Easy returns and exchanges within 30 days. No restocking fee. Learn about our return policy for wrong, damaged, or non-fitting items on Bluprinter.',
        ]);

        $this->command->info('✓ Đã tạo page Returns & Exchanges Policy mới');
        $this->command->info('Hoàn tất! Page đã được cập nhật.');
    }
}
