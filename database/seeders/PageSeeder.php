<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Page;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
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

        // Xóa tất cả pages cũ trước khi seed
        Page::truncate();

        $this->command->info('Bắt đầu tạo các trang...');

        // Mảng chứa các trang sẽ được tạo
        $pages = [
            [
                'user_id' => $admin->id,
                'title' => 'DMCA & Intellectual Property Policy',
                'slug' => 'dmca-policy',
                'content' => '<div class="max-w-5xl mx-auto py-8 px-4">
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white px-8 py-10">
                            <h1 class="text-4xl font-bold mb-3">DMCA & Intellectual Property Policy</h1>
                            <p class="text-blue-100 text-lg">Protecting intellectual property rights on Bluprinter</p>
                            <p class="text-blue-200 text-sm mt-2">Last updated: ' . now()->format('F d, Y') . '</p>
                        </div>

                        <div class="px-8 py-8">
                            <!-- Main Policy Section -->
                            <div class="mb-10">
                                <div class="flex items-start mb-4">
                                    <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                    </div>
                        <div>
                                        <h2 class="text-2xl font-bold text-gray-800 mb-3">Intellectual Property Complaint Policy</h2>
                                        <p class="text-gray-700 leading-relaxed mb-4">
                                            Bluprinter.com provides users with a platform to sell their own merchandise. User contractually agree to all terms prior to use of Bluprinter.com services. Bluprinter.com contractually prohibit users from using its services to sell merchandise that infringes upon third party intellectual property rights (such as copyright, trademark, trade dress, and right of publicity).
                                        </p>
                                        <p class="text-gray-700 leading-relaxed">
                                            It is Bluprinter.com policy to block and remove any content that it believes in good faith to infringe the intellectual property rights of third parties following receipt of a compliant notice; and to terminate service for repeated infringement.
                                        </p>
                            </div>
                        </div>
                    </div>

                            <!-- How to Report Section -->
                            <div class="mb-10 bg-amber-50 border-l-4 border-amber-500 rounded-r-lg p-6">
                                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                                    <svg class="w-6 h-6 text-amber-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    How to Report Infringement
                                </h3>
                                <p class="text-gray-700 mb-4">
                                    If you believe that your intellectual property rights have been infringed upon by a Bluprinter.com user, please notify Bluprinter.com at <a href="mailto:legal@bluprinter.com" class="text-blue-600 hover:text-blue-800 font-semibold underline">legal@bluprinter.com</a>
                                </p>
                                <p class="text-gray-800 font-semibold mb-3">You must include within your notification the following information:</p>
                                
                                <div class="space-y-3">
                                    <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                        <span class="flex-shrink-0 w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold mr-3">1</span>
                                        <p class="text-gray-700 pt-1">A physical or electronic signature of a person authorized to act on behalf of the owner of the intellectual property that you allege is being infringed</p>
                        </div>
                        
                                    <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                        <span class="flex-shrink-0 w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold mr-3">2</span>
                                        <p class="text-gray-700 pt-1">The URL to the Bluprinter.com campaign(s) used in connection with the sale of the allegedly infringing merchandise</p>
                        </div>
                        
                                    <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                        <span class="flex-shrink-0 w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold mr-3">3</span>
                                        <p class="text-gray-700 pt-1">Identification of the copyright, trademark, or other rights that allegedly have been infringed, including proof of ownership (such as copies of existing trademark or copyright registrations)</p>
                        </div>
                        
                                    <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                        <span class="flex-shrink-0 w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold mr-3">4</span>
                                        <p class="text-gray-700 pt-1">Your full name, address, telephone number(s), and email address(es)</p>
                    </div>

                                    <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                        <span class="flex-shrink-0 w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold mr-3">5</span>
                                        <p class="text-gray-700 pt-1">A statement that you have a good-faith belief that use of the material in the URL submitted is unauthorized by the rights owner, or its licensee, and such use amounts to infringement under federal or state law</p>
                        </div>
                        
                                    <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                        <span class="flex-shrink-0 w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold mr-3">6</span>
                                        <p class="text-gray-700 pt-1">A statement, under penalty of perjury, that the information in the notification is complete and accurate and that you are authorized to act on behalf of the owner of the intellectual property or other right that is allegedly infringed</p>
                                    </div>
                        </div>
                    </div>

                            <!-- Counter-Notice Policy -->
                            <div class="mb-10 bg-green-50 border-l-4 border-green-500 rounded-r-lg p-6">
                                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                                    <svg class="w-6 h-6 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Counter-Notice Policy
                                </h3>
                                <div class="bg-white rounded-lg p-5 mb-4">
                                    <p class="text-gray-700 leading-relaxed mb-3">
                                        If you believe that a claim of intellectual property infringement was filed by mistake or misidentification you may file a counter-notice. 
                                    </p>
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-3">
                                        <p class="text-red-800 text-sm">
                                            <strong>⚠️ Warning:</strong> If you materially misrepresent in your counter-notice that your design is not infringing upon the intellectual property, you may be liable for damages to the intellectual property owner (including costs and attorney\'s fees). Therefore, if you are unsure whether or not the material infringes on the intellectual property, please contact an attorney before filing the counter-notice.
                                        </p>
                                    </div>
                                    <p class="text-gray-700 mb-3">
                                        The counter-notice should be submitted to <a href="mailto:legal@bluprinter.com" class="text-blue-600 hover:text-blue-800 font-semibold underline">legal@bluprinter.com</a> and must include:
                                    </p>
                                </div>

                                <div class="space-y-3">
                                    <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                        <span class="flex-shrink-0 w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center font-bold mr-3 text-sm">①</span>
                                        <p class="text-gray-700 pt-1">Your physical or electronic signature</p>
                    </div>

                                    <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                        <span class="flex-shrink-0 w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center font-bold mr-3 text-sm">②</span>
                                        <p class="text-gray-700 pt-1">Your full name, address, telephone number(s), and email address(es)</p>
                                    </div>
                                    
                                    <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                        <span class="flex-shrink-0 w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center font-bold mr-3 text-sm">③</span>
                                        <p class="text-gray-700 pt-1">Identification of the material and its location before it was removed, either by URL to the Bluprinter.com campaign(s) used in connection with the sale of the allegedly infringing merchandise or Bluprinter.com campaign number</p>
                    </div>

                                    <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                        <span class="flex-shrink-0 w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center font-bold mr-3 text-sm">④</span>
                                        <p class="text-gray-700 pt-1">A statement under penalty of perjury that the claim of intellectual property infringement that led to the removal or blockage of access to material was filed by mistake or misidentification</p>
                                    </div>
                                    
                                    <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                        <span class="flex-shrink-0 w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center font-bold mr-3 text-sm">⑤</span>
                                        <p class="text-gray-700 pt-1">Your consent to the jurisdiction of a federal court in the district where you live (if you are in the U.S.), or your consent to the jurisdiction of a federal court in the district where your service provider is located (if you are not in the U.S.)</p>
                                    </div>
                                    
                                    <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                        <span class="flex-shrink-0 w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center font-bold mr-3 text-sm">⑥</span>
                                        <p class="text-gray-700 pt-1">Your consent to accept service of process from the party who submitted the takedown notice or an agent of that party</p>
                                    </div>
                                </div>

                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                                    <p class="text-blue-900 text-sm leading-relaxed">
                                        <strong>📋 Process:</strong> If you submit a counter-notice, a copy may be sent to the complaining party. Unless the intellectual property owner files an action seeking a court order against you, the removed material may be replaced or access to it restored in <strong>10 to 14 business days</strong> after receipt of the counter-notice.
                                    </p>
                                </div>
                            </div>

                            <!-- Repeat Infringement Policy -->
                            <div class="mb-8 bg-red-50 border-l-4 border-red-500 rounded-r-lg p-6">
                                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                                    <svg class="w-6 h-6 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    Repeat Intellectual Property Complaint Policy
                                </h3>
                                <div class="bg-white rounded-lg p-5 space-y-3">
                                    <p class="text-gray-700 leading-relaxed">
                                        If Bluprinter.com receives repeated notices that you have posted others\' intellectual property without permission, <strong class="text-red-600">Bluprinter.com may terminate your account</strong>. Bluprinter.com has a system for keeping track of repeat violators of intellectual property rights of others, and determining when to suspend or terminate your account.
                                    </p>
                                    <div class="bg-red-100 border border-red-300 rounded-lg p-4">
                                        <p class="text-red-800 font-semibold">
                                            ⚠️ Bluprinter.com reserves the right to terminate accounts that act against the spirit of the Terms of Service, regardless of how many strikes are involved.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Section -->
                            <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-6 border border-gray-200">
                                <div class="text-center">
                                    <h3 class="text-lg font-bold text-gray-800 mb-3">Need Assistance?</h3>
                                    <p class="text-gray-700 mb-4">If you require further assistance, please contact us:</p>
                                    <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                                        <a href="mailto:legal@bluprinter.com" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition duration-200">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            legal@bluprinter.com
                                        </a>
                                        <a href="mailto:support@bluprinter.com" class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition duration-200">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                            </svg>
                                            support@bluprinter.com
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>',
                'excerpt' => 'DMCA policy and intellectual property complaint procedures for Bluprinter platform',
                'status' => 'published',
                'published_at' => now(),
                'template' => 'default',
                'show_in_menu' => true,
                'menu_title' => 'DMCA',
                'sort_order' => 1,
                'meta_title' => 'DMCA & Intellectual Property Policy - Bluprinter',
                'meta_description' => 'Learn about our DMCA policy, how to report intellectual property infringement, counter-notice procedures, and repeat infringement policy.',
            ],
            [
                'user_id' => $admin->id,
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'content' => '<div class="max-w-6xl mx-auto py-8 px-4">
                    <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
                        <!-- Hero Header -->
                        <div class="bg-gradient-to-r from-purple-600 via-blue-600 to-indigo-600 text-white px-8 py-12">
                            <h1 class="text-5xl font-bold mb-4">Terms of Service</h1>
                            <p class="text-purple-100 text-xl mb-2">Please read carefully before using the services offered by Bluprinter.com</p>
                            <p class="text-purple-200 text-sm">Last updated: ' . now()->format('F d, Y') . '</p>
                        </div>

                        <div class="px-8 py-8">
                            <!-- Introduction -->
                            <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-lg p-6 mb-8">
                                <p class="text-gray-800 leading-relaxed mb-3">
                                    Bluprinter.com provides users with an automated Internet-based service to design and sell t-shirts and other products. By using Bluprinter.com and its services in any capacity, you have agreed to the terms and conditions of the Terms of Service ("Agreement") and agree to use the site and service solely as provided in this Agreement.
                                </p>
                            </div>

                            <!-- User Agreement Warning -->
                            <div class="bg-red-100 border-2 border-red-500 rounded-lg p-6 mb-8">
                                <h2 class="text-2xl font-bold text-red-800 mb-3 flex items-center">
                                    <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    USER AGREEMENT
                                </h2>
                                <p class="text-red-900 font-semibold text-lg">
                                    By violating this User Agreement in any capacity, you are subject to an immediate removal of your campaign(s), possible forfeit of profit(s), and potential suspension or termination of your account.
                                </p>
                                <p class="text-gray-800 mt-4 leading-relaxed">
                                    Bluprinter.com provides its website and related services to you ("Seller" or "you") subject to this User Agreement (the "Agreement"), the Intellectual Property Complaint Policy, the Counter-Notice Policy, the Repeat Intellectual Property Complaint Policy, the Refund Policy, and the Privacy Policy.
                                </p>
                            </div>

                            <!-- Key Policies Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                                <!-- Delivery -->
                                <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-300 rounded-lg p-6">
                                    <div class="flex items-center mb-3">
                                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center mr-3">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-bold text-green-800">Delivery Procedure</h3>
                                    </div>
                                    <p class="text-gray-700 text-sm">Any time quoted for delivery is an estimate only. No delay in shipment or delivery of any merchandise relieves Seller of their obligations under this Agreement.</p>
                                </div>

                                <!-- Print Variance -->
                                <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-300 rounded-lg p-6">
                                    <div class="flex items-center mb-3">
                                        <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center mr-3">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-bold text-purple-800">Print Variance</h3>
                                    </div>
                                    <p class="text-gray-700 text-sm">Product production is generated from artwork uploaded by Seller. Print size and exact location may vary based on product size. Exact print size, location, and colors are not guaranteed.</p>
                                </div>

                                <!-- Price & Payment -->
                                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-300 rounded-lg p-6">
                                    <div class="flex items-center mb-3">
                                        <div class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center mr-3">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-bold text-yellow-800">Price & Payment</h3>
                                    </div>
                                    <p class="text-gray-700 text-sm">Seller determines the price of merchandise; Bluprinter.com processes customer payments; the base price is cost of goods sold; Bluprinter remits Seller any amount in excess ("Seller Profits").</p>
                                </div>
                            </div>

                            <!-- Campaign Creation Obligations -->
                            <div class="bg-gradient-to-r from-indigo-50 to-blue-50 border-2 border-indigo-300 rounded-lg p-8 mb-8">
                                <h2 class="text-2xl font-bold text-indigo-900 mb-5 flex items-center">
                                    <svg class="w-8 h-8 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    BY CREATING A CAMPAIGN ON BLUPRINTER:
                                </h2>
                                
                                <div class="space-y-4">
                                    <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                        <div class="flex-shrink-0 w-8 h-8 bg-indigo-500 text-white rounded-full flex items-center justify-center mr-3 mt-1">✓</div>
                                        <p class="text-gray-800">You agree to accept and abide by Bluprinter.com\'s Terms of Service in their entirety.</p>
                                    </div>
                                    
                                    <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                        <div class="flex-shrink-0 w-8 h-8 bg-indigo-500 text-white rounded-full flex items-center justify-center mr-3 mt-1">✓</div>
                                        <p class="text-gray-800">You agree that you are the owner, or licensee, of all rights associated with any created or uploaded artwork or text, including trademarks and copyrights.</p>
                                    </div>
                                    
                                    <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                        <div class="flex-shrink-0 w-8 h-8 bg-indigo-500 text-white rounded-full flex items-center justify-center mr-3 mt-1">✓</div>
                                        <p class="text-gray-800">You agree that the description and title of the campaign do not infringe upon the rights of any third party.</p>
                                    </div>
                                    
                                    <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                        <div class="flex-shrink-0 w-8 h-8 bg-indigo-500 text-white rounded-full flex items-center justify-center mr-3 mt-1">✓</div>
                                        <p class="text-gray-800">You understand and agree that Bluprinter.com reserves the right to remove any content that may be considered to promote hate, violence, racial intolerance, or the financial exploitation of a crime.</p>
                                    </div>
                                    
                                    <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                        <div class="flex-shrink-0 w-8 h-8 bg-indigo-500 text-white rounded-full flex items-center justify-center mr-3 mt-1">✓</div>
                                        <p class="text-gray-800">You agree to defend, indemnify, and hold Bluprinter.com harmless from and against any and all claims, damages, costs, and expenses, including attorneys\' fees.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Intellectual Property Rights -->
                            <div class="bg-gradient-to-br from-pink-50 to-rose-50 border-l-4 border-pink-500 rounded-r-lg p-6 mb-8">
                                <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                                    <svg class="w-7 h-7 mr-3 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    Intellectual Property Rights and License
                                </h2>
                                <div class="bg-white rounded-lg p-5 space-y-3">
                                    <p class="text-gray-700 leading-relaxed">
                                        By submitting listings to Bluprinter, you grant Bluprinter a <strong>non-exclusive, worldwide, royalty-free, sublicensable and transferable license</strong> to use, reproduce, distribute, prepare derivative works of and display the content of such listings in connection with Bluprinter\'s services.
                                    </p>
                                    <p class="text-gray-700 leading-relaxed">
                                        All intellectual property rights in this website and the Bluprinter service are owned by or licensed to Bluprinter. You may not use, adapt, reproduce, store, distribute, print, display, perform, publish or create derivative works from any part of this website without Bluprinter\'s written permission.
                                    </p>
                                </div>
                            </div>

                            <!-- Mobile Terms -->
                            <div class="bg-gradient-to-br from-cyan-50 to-blue-50 border-2 border-cyan-400 rounded-lg p-6 mb-8">
                                <h2 class="text-2xl font-bold text-cyan-900 mb-4 flex items-center">
                                    <svg class="w-7 h-7 mr-3 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    Mobile Terms of Service
                                </h2>
                                <div class="bg-white rounded-lg p-5 space-y-3">
                                    <p class="text-gray-700 leading-relaxed">
                                        By consenting to HM FULFILL\'s SMS/text messaging service, you agree to receive recurring SMS/text messages through your wireless provider to the mobile number you provided, even if your mobile number is registered on any Do Not Call list.
                                    </p>
                                    <div class="bg-cyan-100 border border-cyan-300 rounded-lg p-4">
                                        <p class="text-cyan-900"><strong>Opt-out:</strong> Text <strong>STOP</strong> to <strong>+18555255940</strong> or click the unsubscribe link</p>
                                        <p class="text-cyan-900 mt-2"><strong>Support:</strong> Text <strong>HELP</strong> to <strong>+18555255940</strong> or email <a href="mailto:admin@bluprinter.com" class="text-blue-600 hover:underline font-semibold">admin@bluprinter.com</a></p>
                                    </div>
                                    <p class="text-gray-600 text-sm">Message frequency varies. Message and data rates may apply. You are responsible for all charges from your wireless provider.</p>
                                </div>
                            </div>

                            <!-- Disclaimer & Liability -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                <!-- Disclaimer -->
                                <div class="bg-orange-50 border-2 border-orange-400 rounded-lg p-6">
                                    <h3 class="text-xl font-bold text-orange-800 mb-3 flex items-center">
                                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        Disclaimer of Warranties
                                    </h3>
                                    <p class="text-gray-700 text-sm leading-relaxed uppercase">
                                        Your use of the Bluprinter service is at your sole risk. The service is provided on an "AS IS" and "AS AVAILABLE" basis. Bluprinter expressly disclaims all warranties of any kind.
                                    </p>
                                </div>

                                <!-- Liability -->
                                <div class="bg-red-50 border-2 border-red-400 rounded-lg p-6">
                                    <h3 class="text-xl font-bold text-red-800 mb-3 flex items-center">
                                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                        </svg>
                                        Limitation of Liability
                                    </h3>
                                    <p class="text-gray-700 text-sm leading-relaxed">
                                        Bluprinter will not be liable for any indirect, incidental, special, or consequential damages. <strong>Total liability will not exceed the amount paid in the last 6 months, or $100, whichever is greater.</strong>
                                    </p>
                                </div>
                            </div>

                            <!-- Buyer Terms -->
                            <div class="bg-gradient-to-r from-teal-50 to-emerald-50 border-l-4 border-teal-500 rounded-r-lg p-6 mb-8">
                                <h2 class="text-2xl font-bold text-teal-900 mb-5 flex items-center">
                                    <svg class="w-8 h-8 mr-3 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                    </svg>
                                    Buyer Payments, Returns, Refunds & Cancellation
                                </h2>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="bg-white rounded-lg p-4 shadow-sm">
                                        <h4 class="font-bold text-teal-800 mb-2 flex items-center">
                                            <span class="w-2 h-2 bg-teal-500 rounded-full mr-2"></span>
                                            Payment Methods
                                        </h4>
                                        <p class="text-gray-700 text-sm">Bluprinter accepts VISA, MASTER, AMERICAN EXPRESS and PayPal. Buyers charged at time of order placement.</p>
                                    </div>
                                    
                                    <div class="bg-white rounded-lg p-4 shadow-sm">
                                        <h4 class="font-bold text-teal-800 mb-2 flex items-center">
                                            <span class="w-2 h-2 bg-teal-500 rounded-full mr-2"></span>
                                            Shipping Time
                                        </h4>
                                        <p class="text-gray-700 text-sm">Customers can expect to receive products <strong>14-21 business days</strong> after payment. This is an estimate and may vary.</p>
                                    </div>
                                    
                                    <div class="bg-white rounded-lg p-4 shadow-sm">
                                        <h4 class="font-bold text-teal-800 mb-2 flex items-center">
                                            <span class="w-2 h-2 bg-teal-500 rounded-full mr-2"></span>
                                            International Shipping
                                        </h4>
                                        <p class="text-gray-700 text-sm">Certain countries do not provide international tracking. Bluprinter is not responsible for lost or stolen shipments.</p>
                                    </div>
                                    
                                    <div class="bg-white rounded-lg p-4 shadow-sm">
                                        <h4 class="font-bold text-teal-800 mb-2 flex items-center">
                                            <span class="w-2 h-2 bg-teal-500 rounded-full mr-2"></span>
                                            Returns & Refunds
                                        </h4>
                                        <p class="text-gray-700 text-sm">Email within <strong>30 days</strong> for domestic orders. <strong>60 days</strong> for international orders shipped outside the US.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- API Policy -->
                            <div class="bg-gradient-to-br from-violet-50 to-purple-50 border-2 border-violet-400 rounded-lg p-6 mb-8">
                                <h2 class="text-2xl font-bold text-violet-900 mb-4 flex items-center">
                                    <svg class="w-8 h-8 mr-3 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    API (Shopify, WooCommerce, etc.) Policy
                                </h2>
                                <div class="bg-white rounded-lg p-5 space-y-3">
                                    <p class="text-gray-700 leading-relaxed">
                                        Bluprinter.com integrates all information, tools, and services through 3rd Party Platforms (i.e. Shopify) to benefit the Seller. By accessing or using any part of the Bluprinter.com application, the Seller agrees to be bound by this Agreement.
                                    </p>
                                    <div class="bg-violet-100 border border-violet-300 rounded-lg p-4">
                                        <p class="text-violet-900 text-sm">
                                            <strong>Important:</strong> Bluprinter.com does not handle and is not responsible for any Seller services including payment processing, returns, refunds, or exchanges. Bluprinter.com is not responsible for Seller\'s customer service.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Miscellaneous Provisions -->
                            <div class="bg-gradient-to-r from-slate-100 to-gray-100 border-l-4 border-slate-500 rounded-r-lg p-6 mb-8">
                                <h2 class="text-2xl font-bold text-slate-800 mb-5">Miscellaneous Provisions</h2>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="bg-white rounded-lg p-4 border border-slate-200">
                                        <h4 class="font-bold text-slate-700 mb-2">Governing Law</h4>
                                        <p class="text-gray-600 text-sm">These Terms shall be governed by the laws of <strong>Hong Kong</strong>.</p>
                                    </div>
                                    
                                    <div class="bg-white rounded-lg p-4 border border-slate-200">
                                        <h4 class="font-bold text-slate-700 mb-2">Assignment</h4>
                                        <p class="text-gray-600 text-sm">Seller may not assign rights without written consent. Bluprinter may assign at its discretion.</p>
                                    </div>
                                    
                                    <div class="bg-white rounded-lg p-4 border border-slate-200">
                                        <h4 class="font-bold text-slate-700 mb-2">Waiver of Jury Trial</h4>
                                        <p class="text-gray-600 text-sm">Each party waives any right to a trial by jury for legal actions arising from this Agreement.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Security Warning -->
                            <div class="bg-gradient-to-r from-amber-100 to-orange-100 border-2 border-amber-500 rounded-lg p-6 mb-8">
                                <h3 class="text-xl font-bold text-amber-900 mb-3 flex items-center">
                                    <svg class="w-7 h-7 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    Exploiting System Vulnerabilities
                                </h3>
                                <p class="text-gray-800 mb-3">
                                    Bluprinter.com preserves the right to deal with individuals and organizations that intentionally exploit system vulnerabilities. Those who exploit vulnerabilities are obligated to recover the damage caused including:
                                </p>
                                <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
                                    <li>Loss of money and other benefits</li>
                                    <li>System interruption</li>
                                    <li>Damage caused by DDoS and other forms of attack</li>
                                </ul>
                            </div>

                            <!-- Contact Footer -->
                            <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-8 text-center text-white">
                                <h3 class="text-2xl font-bold mb-3">Questions About These Terms?</h3>
                                <p class="mb-5 text-blue-100">If you have any questions about these Terms of Service, please contact us:</p>
                                <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                                    <a href="mailto:admin@bluprinter.com" class="inline-flex items-center px-8 py-3 bg-white text-blue-600 font-bold rounded-lg shadow-lg hover:shadow-xl transition duration-200">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        admin@bluprinter.com
                                    </a>
                                    <a href="mailto:legal@bluprinter.com" class="inline-flex items-center px-8 py-3 bg-white text-purple-600 font-bold rounded-lg shadow-lg hover:shadow-xl transition duration-200">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        legal@bluprinter.com
                                    </a>
                                </div>
                                <p class="mt-6 text-sm text-blue-100">By using Bluprinter.com, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service.</p>
                            </div>
                        </div>
                    </div>
                </div>',
                'excerpt' => 'Complete Terms of Service for Bluprinter - User Agreement, Payment Terms, IP Rights, Mobile Terms, API Policy, and Legal Information',
                'status' => 'published',
                'published_at' => now(),
                'template' => 'default',
                'show_in_menu' => true,
                'menu_title' => 'Terms of Service',
                'sort_order' => 2,
                'meta_title' => 'Terms of Service - Bluprinter Legal Agreement',
                'meta_description' => 'Read our complete Terms of Service including User Agreement, Intellectual Property Policy, Payment Terms, Mobile Terms, API Policy, and all legal information for using Bluprinter platform.',
            ],
        ];

        // Tạo các pages
        foreach ($pages as $page) {
            Page::create($page);
            $this->command->info("✓ Đã tạo trang: {$page['title']}");
        }

        $this->command->info('Hoàn tất! Đã tạo ' . count($pages) . ' trang.');
    }
}
