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
            [
                'user_id' => $admin->id,
                'title' => 'Our Intellectual Property Policy',
                'slug' => 'intellectual-property-policy',
                'content' => '<div class="max-w-5xl mx-auto py-8 px-4">
                    <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
                        <!-- Hero Header -->
                        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white px-8 py-12">
                            <h1 class="text-5xl font-bold mb-4">Our Intellectual Property Policy</h1>
                            <p class="text-indigo-100 text-xl mb-2">Protecting copyright and intellectual property rights</p>
                            <p class="text-indigo-200 text-sm">Last updated: ' . now()->format('F d, Y') . '</p>
                        </div>

                        <div class="px-8 py-8">
                            <!-- Introduction -->
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-l-4 border-indigo-500 rounded-r-lg p-6 mb-8">
                                <p class="text-gray-800 leading-relaxed mb-4">
                                    Bluprinter has adopted the following general policy towards the infringement of copyright and other intellectual property in accordance with general <strong>United States intellectual property laws</strong> and the <strong>Digital Millennium Copyright Act (DMCA)</strong>.
                                </p>
                                <p class="text-gray-800 leading-relaxed">
                                    Bluprinter will respond to notices in the form provided below from jurisdictions other than the United States as well.
                                </p>
                            </div>

                            <!-- Contact Information Box -->
                            <div class="bg-gradient-to-r from-green-100 to-emerald-100 border-2 border-green-500 rounded-lg p-6 mb-8">
                                <h2 class="text-2xl font-bold text-green-900 mb-4 flex items-center">
                                    <svg class="w-8 h-8 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    Contact Bluprinter Legal Department
                                </h2>
                                <div class="bg-white rounded-lg p-5">
                                    <p class="text-gray-700 mb-4">
                                        Please contact Bluprinter\'s Legal Department for any and all Notice and Counter Notice of claims of copyright or other intellectual property infringement:
                                    </p>
                                    <div class="space-y-3">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 w-10 h-10 bg-green-500 rounded-full flex items-center justify-center mr-3">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-800">Email (Preferred Method)</h4>
                                                <a href="mailto:legal@bluprinter.com" class="text-blue-600 hover:text-blue-800 font-semibold text-lg underline">legal@bluprinter.com</a>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 w-10 h-10 bg-green-500 rounded-full flex items-center justify-center mr-3">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-800">Mailing Address</h4>
                                                <p class="text-gray-700">
                                                    <strong>Attn: Legal Department</strong><br>
                                                    3rd Floor, 24T3 Thanh Xuan Complex Building<br>
                                                    6 Le Van Thiem Street, Thanh Xuan Trung Ward<br>
                                                    Thanh Xuan District, Hanoi, Vietnam
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                                        <p class="text-gray-800 text-sm">
                                            <strong>Note:</strong> Bluprinter\'s Legal Department is the designated agent to receive notifications of alleged intellectual property infringements on the Website.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Repeat Infringer Warning -->
                            <div class="bg-red-100 border-2 border-red-500 rounded-lg p-6 mb-8">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mr-4">
                                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-red-800 mb-2">Repeat Infringer Policy</h3>
                                        <p class="text-red-900 font-semibold">
                                            Bluprinter will terminate rights of subscribers and account holders in appropriate circumstances if they are determined to be repeat infringers.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- A. Reporting Infringements -->
                            <div class="mb-10">
                                <div class="bg-gradient-to-r from-amber-500 to-orange-500 text-white px-6 py-4 rounded-t-lg">
                                    <h2 class="text-3xl font-bold flex items-center">
                                        <span class="w-12 h-12 bg-white text-amber-600 rounded-full flex items-center justify-center mr-3 text-2xl font-bold">A</span>
                                        Reporting Infringements
                                    </h2>
                                </div>
                                <div class="bg-amber-50 border-2 border-amber-300 border-t-0 rounded-b-lg p-6">
                                    <p class="text-gray-800 leading-relaxed mb-4">
                                        Bluprinter respects the intellectual property of others, and asks our users to do the same. If you believe that your work has been copied in a way that constitutes copyright infringement, or your intellectual property rights have been otherwise violated, please provide <a href="mailto:legal@bluprinter.com" class="text-blue-600 hover:text-blue-800 font-semibold underline">legal@bluprinter.com</a> the following information in writing pursuant to the DMCA:
                                    </p>
                                    
                                    <div class="bg-white rounded-lg p-5 mb-4">
                                        <p class="text-gray-700 font-semibold mb-4">Required Information (see Section 512(c)(3) of the Copyright Act):</p>
                                        
                                        <div class="space-y-4">
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0 w-10 h-10 bg-amber-500 text-white rounded-full flex items-center justify-center mr-3 font-bold">a</div>
                                                <div class="flex-1">
                                                    <p class="text-gray-800">An electronic or physical <strong>signature</strong> of the person authorized to act on behalf of the owner of the copyright or other intellectual property interest</p>
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0 w-10 h-10 bg-amber-500 text-white rounded-full flex items-center justify-center mr-3 font-bold">b</div>
                                                <div class="flex-1">
                                                    <p class="text-gray-800">A <strong>specific description</strong> of the copyrighted work or other intellectual property that you claim to be infringing (if multiple works have been infringed, please provide a list with specific descriptions)</p>
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0 w-10 h-10 bg-amber-500 text-white rounded-full flex items-center justify-center mr-3 font-bold">c</div>
                                                <div class="flex-1">
                                                    <p class="text-gray-800">A <strong>specific description of the location</strong> where the material that you claim to be infringing is located in Bluprinter (sufficient to permit Bluprinter to locate the material)</p>
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0 w-10 h-10 bg-amber-500 text-white rounded-full flex items-center justify-center mr-3 font-bold">d</div>
                                                <div class="flex-1">
                                                    <p class="text-gray-800">Your <strong>address, telephone number and email address</strong></p>
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0 w-10 h-10 bg-amber-500 text-white rounded-full flex items-center justify-center mr-3 font-bold">e</div>
                                                <div class="flex-1">
                                                    <p class="text-gray-800">A <strong>statement</strong> that you have a good faith belief that the disputed use is not authorized by the copyright or intellectual property owner, its agent or the law</p>
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0 w-10 h-10 bg-amber-500 text-white rounded-full flex items-center justify-center mr-3 font-bold">f</div>
                                                <div class="flex-1">
                                                    <p class="text-gray-800">A <strong>statement made under penalty of perjury</strong> that the information in your Notice is accurate and that you are the copyright or intellectual property owner or authorized to act on their behalf</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Process Info -->
                                    <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-lg p-5 mb-4">
                                        <h4 class="font-bold text-blue-900 mb-2 flex items-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            What Happens Next
                                        </h4>
                                        <p class="text-gray-700 text-sm">
                                            Once a proper infringement notification is received by Bluprinter\'s Legal Department, Bluprinter may remove or disable access to the infringing material. When removing or disabling access, Bluprinter will make reasonable attempts to inform the allegedly infringing user and may provide a copy of the notice.
                                        </p>
                                    </div>

                                    <!-- Warning Box -->
                                    <div class="bg-yellow-50 border-2 border-yellow-400 rounded-lg p-4">
                                        <p class="text-yellow-900 text-sm">
                                            <strong>⚠️ Important:</strong> If you fail to comply with all of the aforementioned Notice requirements in writing, your Notice may not be valid and Bluprinter may ignore such incomplete or inaccurate notices without liability. Under Section 512(f) of the Copyright Act, any person who knowingly materially misrepresents that material or activity is infringing may be subject to liability.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- B. Responding To Infringements -->
                            <div class="mb-8">
                                <div class="bg-gradient-to-r from-green-500 to-teal-500 text-white px-6 py-4 rounded-t-lg">
                                    <h2 class="text-3xl font-bold flex items-center">
                                        <span class="w-12 h-12 bg-white text-green-600 rounded-full flex items-center justify-center mr-3 text-2xl font-bold">B</span>
                                        Responding To Infringements
                                    </h2>
                                </div>
                                <div class="bg-green-50 border-2 border-green-300 border-t-0 rounded-b-lg p-6">
                                    <p class="text-gray-800 leading-relaxed mb-4">
                                        If you believe that your work has been removed or disabled by mistake or misidentification, please provide <a href="mailto:legal@bluprinter.com" class="text-blue-600 hover:text-blue-800 font-semibold underline">legal@bluprinter.com</a> with the following information in writing pursuant to the DMCA:
                                    </p>
                                    
                                    <div class="bg-white rounded-lg p-5 mb-4">
                                        <p class="text-gray-700 font-semibold mb-4">Required Information for Counter Notice (see Section 512(g)(3) of the Copyright Act):</p>
                                        
                                        <div class="space-y-4">
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0 w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center mr-3 font-bold">a</div>
                                                <div class="flex-1">
                                                    <p class="text-gray-800">A physical or electronic <strong>signature</strong> of the subscriber of Bluprinter</p>
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0 w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center mr-3 font-bold">b</div>
                                                <div class="flex-1">
                                                    <p class="text-gray-800"><strong>Identification</strong> of the material that has been removed or to which access has been disabled and the location at which the material appeared before removal</p>
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0 w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center mr-3 font-bold">c</div>
                                                <div class="flex-1">
                                                    <p class="text-gray-800">A <strong>statement made under penalty of perjury</strong> that the subscriber has a good faith belief that the material was removed or disabled as a result of mistake or misidentification</p>
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0 w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center mr-3 font-bold">d</div>
                                                <div class="flex-1">
                                                    <p class="text-gray-800">The subscriber\'s <strong>name, address, telephone number</strong>, and a statement that the subscriber consents to the jurisdiction of the Federal District Court (or appropriate judicial district outside the US) and will accept service of process from the complaining party or their agent</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Counter Notice Process -->
                                    <div class="bg-teal-50 border-l-4 border-teal-500 rounded-r-lg p-5">
                                        <h4 class="font-bold text-teal-900 mb-3 flex items-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                            </svg>
                                            Counter Notice Process
                                        </h4>
                                        <p class="text-gray-700 text-sm leading-relaxed">
                                            If a Counter Notice is received by Bluprinter\'s Legal Department, Bluprinter may send a copy to the original complaining party. Unless the copyright or intellectual property owner files an action seeking a court order against you, the removed material may be replaced (or access restored) in approximately <strong class="text-teal-800">10 business days</strong> after receipt of the Counter Notice, at the sole discretion of Bluprinter\'s Legal Department.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Thank You -->
                            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg p-8 text-center text-white">
                                <div class="flex justify-center mb-4">
                                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center">
                                        <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="text-2xl font-bold mb-3">Thank You</h3>
                                <p class="text-lg text-indigo-100 mb-5">Thank you for paying attention to these requirements</p>
                                <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                                    <a href="mailto:legal@bluprinter.com" class="inline-flex items-center px-8 py-3 bg-white text-indigo-600 font-bold rounded-lg shadow-lg hover:shadow-xl transition duration-200">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        Contact Legal Team
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>',
                'excerpt' => 'Our comprehensive intellectual property policy including DMCA procedures, reporting infringements, and counter-notice process',
                'status' => 'published',
                'published_at' => now(),
                'template' => 'default',
                'show_in_menu' => true,
                'menu_title' => 'IP Policy',
                'sort_order' => 3,
                'meta_title' => 'Intellectual Property Policy - Bluprinter DMCA & Copyright Protection',
                'meta_description' => 'Learn about our intellectual property policy, DMCA compliance, how to report copyright infringement, and counter-notice procedures on Bluprinter platform.',
            ],
            [
                'user_id' => $admin->id,
                'title' => 'Returns & Exchanges Policy',
                'slug' => 'returns-exchanges-policy',
                'content' => '<div class="max-w-6xl mx-auto py-8 px-4">
                    <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
                        <!-- Hero Header -->
                        <div class="bg-gradient-to-r from-emerald-600 via-green-600 to-teal-600 text-white px-8 py-12">
                            <h1 class="text-5xl font-bold mb-4">Returns & Exchanges Policy</h1>
                            <p class="text-emerald-100 text-xl mb-2">Your satisfaction is our priority - Easy returns within 30 days</p>
                            <p class="text-emerald-200 text-sm">Last updated: ' . now()->format('F d, Y') . '</p>
                        </div>

                        <div class="px-8 py-8">
                            <!-- Introduction -->
                            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 border-l-4 border-blue-500 rounded-r-lg p-6 mb-8">
                                <p class="text-gray-800 leading-relaxed text-lg">
                                    Bluprinter.com and most sellers on Bluprinter.com offer <strong>returns and exchanges for items within 30 days</strong> from the date of delivery. If you need to return an item, please <a href="mailto:support@bluprinter.com" class="text-blue-600 hover:text-blue-800 font-semibold underline">contact us here</a> to submit your request.
                                </p>
                            </div>

                            <!-- No Restocking Fee -->
                            <div class="bg-gradient-to-r from-green-100 to-emerald-100 border-2 border-green-500 rounded-lg p-6 mb-8">
                                <div class="flex items-center justify-center">
                                    <div class="text-center">
                                        <div class="flex justify-center mb-4">
                                            <div class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center">
                                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <h2 class="text-3xl font-bold text-green-900 mb-2">Restocking Fee</h2>
                                        <p class="text-5xl font-bold text-green-600">NO FEE</p>
                                        <p class="text-gray-700 mt-2">We don\'t charge any restocking fees for returns</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Return Criteria Section -->
                            <div class="mb-10">
                                <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-4 rounded-t-lg mb-0">
                                    <h2 class="text-3xl font-bold">Return Permitted When:</h2>
                                    <p class="text-purple-100 mt-2">Your purchase return is permitted ONLY when the goods delivered fall under the following criteria:</p>
                                </div>

                                <!-- Criteria Cards -->
                                <div class="space-y-6 mt-6">
                                    <!-- Criterion A -->
                                    <div class="bg-gradient-to-br from-red-50 to-rose-50 border-l-4 border-red-500 rounded-r-lg p-6">
                                        <div class="flex items-start mb-4">
                                            <div class="flex-shrink-0 w-14 h-14 bg-red-500 text-white rounded-full flex items-center justify-center mr-4 text-2xl font-bold">A</div>
                                            <div class="flex-1">
                                                <h3 class="text-2xl font-bold text-red-800 mb-3">Wrong/Damaged/Faulty Items</h3>
                                                <p class="text-gray-700 mb-4">We guarantee to assist with cases where customers receive wrong/damaged/faulty items. Includes these cases:</p>
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 ml-0 md:ml-18">
                                            <div class="bg-white rounded-lg p-4 shadow-sm border border-red-200">
                                                <div class="flex items-start">
                                                    <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    <p class="text-gray-800"><strong>Wrong items:</strong> Product doesn\'t match website description, wrong material, wrong size</p>
                                                </div>
                                            </div>
                                            
                                            <div class="bg-white rounded-lg p-4 shadow-sm border border-red-200">
                                                <div class="flex items-start">
                                                    <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    <p class="text-gray-800"><strong>Damaged condition:</strong> Torn, dirty, wet, or hairy fabric received</p>
                                                </div>
                                            </div>
                                            
                                            <div class="bg-white rounded-lg p-4 shadow-sm border border-red-200">
                                                <div class="flex items-start">
                                                    <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    <p class="text-gray-800"><strong>Print defects:</strong> Visible defects with print, blurred, or out of place</p>
                                                </div>
                                            </div>
                                            
                                            <div class="bg-white rounded-lg p-4 shadow-sm border border-red-200">
                                                <div class="flex items-start">
                                                    <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    <p class="text-gray-800"><strong>Damaged prints:</strong> Products with damaged or peeled prints after first wash</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Criterion B -->
                                    <div class="bg-gradient-to-br from-orange-50 to-amber-50 border-l-4 border-orange-500 rounded-r-lg p-6">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 w-14 h-14 bg-orange-500 text-white rounded-full flex items-center justify-center mr-4 text-2xl font-bold">B</div>
                                            <div class="flex-1">
                                                <h3 class="text-2xl font-bold text-orange-800 mb-3">Wrong Size Items</h3>
                                                <div class="bg-white rounded-lg p-4 shadow-sm border border-orange-200">
                                                    <p class="text-gray-800 leading-relaxed">
                                                        We guarantee to assist with cases where customers receive the wrong size product. Specifically, these products have <strong>wrong measurements compared to the product\'s size guide</strong> (A difference of over <strong class="text-orange-600">1.5"</strong> from standard product measurements).
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
                                                <h3 class="text-2xl font-bold text-yellow-800 mb-3">Non-Fitting Items</h3>
                                                <div class="bg-white rounded-lg p-4 shadow-sm border border-yellow-200">
                                                    <p class="text-gray-800 leading-relaxed mb-2">
                                                        We guarantee to assist with cases where customers receive non-fitting items.
                                                    </p>
                                                    <div class="bg-yellow-100 border border-yellow-300 rounded-lg p-3">
                                                        <p class="text-yellow-900 text-sm">
                                                            <strong>⚠️ Please note:</strong> For this case, only <strong>t-shirts and tank tops</strong> are eligible for returns and exchanges.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Criterion D -->
                                    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 border-l-4 border-blue-500 rounded-r-lg p-6">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 w-14 h-14 bg-blue-500 text-white rounded-full flex items-center justify-center mr-4 text-2xl font-bold">D</div>
                                            <div class="flex-1">
                                                <h3 class="text-2xl font-bold text-blue-800 mb-3">Shipping Damage</h3>
                                                <div class="bg-white rounded-lg p-4 shadow-sm border border-blue-200">
                                                    <p class="text-gray-800 leading-relaxed mb-3">
                                                        We guarantee to assist with cases where customers receive wrong/damaged/faulty items due to the shipping process.
                                                    </p>
                                                    <div class="bg-blue-100 border border-blue-300 rounded-lg p-3">
                                                        <p class="text-blue-900 text-sm">
                                                            <strong>💡 Important:</strong> Please double-check the package carefully when receiving your orders. If you find the products are wrong, defective, or damaged, please <strong>return them to the courier immediately</strong> or contact Bluprinter within <strong>30 days of delivery</strong> for quick response and support.
                                                        </p>
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
                                    Products Eligible for Return
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
                                        <p class="text-gray-800 text-center">Products meet the <strong>eligibility criteria</strong> for support listed above</p>
                                    </div>
                                    
                                    <div class="bg-white rounded-lg p-5 shadow-md border border-teal-200">
                                        <div class="flex justify-center mb-3">
                                            <div class="w-12 h-12 bg-teal-500 rounded-full flex items-center justify-center">
                                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <p class="text-gray-800 text-center">Products with <strong>no sign of being used</strong>, have neck label intact and original packaging</p>
                                    </div>
                                    
                                    <div class="bg-white rounded-lg p-5 shadow-md border border-teal-200">
                                        <div class="flex justify-center mb-3">
                                            <div class="w-12 h-12 bg-teal-500 rounded-full flex items-center justify-center">
                                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <p class="text-gray-800 text-center">Return requested <strong>within 30 days</strong> from date of delivery</p>
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
                                        Product Return Process
                                    </h2>
                                </div>
                                <div class="bg-indigo-50 border-2 border-indigo-300 border-t-0 rounded-b-lg p-6">
                                    <p class="text-gray-800 font-semibold mb-6 text-lg">Please make sure your product is eligible for the return policy at Bluprinter.com</p>
                                    
                                    <!-- Step 1 -->
                                    <div class="mb-6">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-500 text-white rounded-full flex items-center justify-center mr-4 text-2xl font-bold shadow-lg">1</div>
                                            <div class="flex-1 bg-white rounded-lg p-5 shadow-md border border-indigo-200">
                                                <h3 class="text-xl font-bold text-indigo-900 mb-3">Contact Us</h3>
                                                <p class="text-gray-700 mb-3">Contact us and clarify the issue with the products.</p>
                                                <div class="bg-indigo-100 border border-indigo-300 rounded-lg p-4">
                                                    <p class="text-indigo-900 mb-2"><strong>How to contact:</strong></p>
                                                    <ul class="space-y-1 text-indigo-800">
                                                        <li>• Send email to <a href="mailto:support@bluprinter.com" class="text-blue-600 hover:text-blue-800 font-semibold underline">support@bluprinter.com</a></li>
                                                        <li>• Or contact us through our contact form</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 2 -->
                                    <div class="mb-6">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 text-white rounded-full flex items-center justify-center mr-4 text-2xl font-bold shadow-lg">2</div>
                                            <div class="flex-1 bg-white rounded-lg p-5 shadow-md border border-purple-200">
                                                <h3 class="text-xl font-bold text-purple-900 mb-3">Provide Required Information</h3>
                                                <p class="text-gray-700 mb-3">When you contact us, please provide the following information:</p>
                                                <div class="space-y-3">
                                                    <div class="flex items-start bg-purple-50 rounded-lg p-3">
                                                        <svg class="w-5 h-5 text-purple-600 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <p class="text-gray-800">Order information to confirm the order</p>
                                                    </div>
                                                    <div class="flex items-start bg-purple-50 rounded-lg p-3">
                                                        <svg class="w-5 h-5 text-purple-600 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <p class="text-gray-800">Photos of the packaging label</p>
                                                    </div>
                                                    <div class="flex items-start bg-purple-50 rounded-lg p-3">
                                                        <svg class="w-5 h-5 text-purple-600 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <p class="text-gray-800">Photos of items clearly showing damaged/wrong/faulty parts different from website description</p>
                                                    </div>
                                                    <div class="flex items-start bg-purple-50 rounded-lg p-3">
                                                        <svg class="w-5 h-5 text-purple-600 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <p class="text-gray-800">Photos showing actual measurements: width, length (if wrong size items)</p>
                                                    </div>
                                                    <div class="flex items-start bg-purple-50 rounded-lg p-3">
                                                        <svg class="w-5 h-5 text-purple-600 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <p class="text-gray-800">Information of the item you want to be resent</p>
                                                    </div>
                                                </div>
                                                <div class="mt-4 bg-pink-100 border border-pink-300 rounded-lg p-3">
                                                    <p class="text-pink-900 text-sm">
                                                        <strong>📸 Note:</strong> For orders with multiple items, please provide photos/videos of products placed side by side on a flat surface.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 3 -->
                                    <div>
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 w-16 h-16 bg-gradient-to-br from-pink-500 to-rose-500 text-white rounded-full flex items-center justify-center mr-4 text-2xl font-bold shadow-lg">3</div>
                                            <div class="flex-1 bg-white rounded-lg p-5 shadow-md border border-pink-200">
                                                <h3 class="text-xl font-bold text-pink-900 mb-3">Verification & Resolution</h3>
                                                <p class="text-gray-700 mb-3">After confirming that your products are eligible for our exchange/return policy:</p>
                                                <div class="bg-green-100 border-2 border-green-400 rounded-lg p-4 mb-3">
                                                    <p class="text-green-900 font-semibold">
                                                        ✓ You will receive a <strong>refund</strong> or a <strong>replacement</strong> resent to your address within <strong class="text-green-700">7 business days</strong>
                                                    </p>
                                                    <p class="text-green-800 mt-2">
                                                        ✓ You <strong>do NOT need to resend the package back</strong>
                                                    </p>
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
                                            <strong class="text-amber-800">⚠️</strong> Products returned <strong>without our verification are ineligible</strong> for support. We appreciate your understanding.
                                        </p>
                                    </div>
                                    <div class="bg-white rounded-lg p-4 border-l-4 border-amber-500">
                                        <p class="text-gray-800">
                                            <strong class="text-amber-800">💱</strong> You only receive a replacement that has the <strong>same or higher price</strong> than the returned item. (Please pay extra cost if exchanged products have a higher price)
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
                                    For all orders shipped <strong>outside the US</strong>
                                </p>
                                <div class="bg-white bg-opacity-20 rounded-lg p-4 inline-block">
                                    <p class="text-2xl font-bold">60 DAYS</p>
                                    <p class="text-blue-100">support window from date of delivery</p>
                                </div>
                                <p class="mt-4 text-sm text-blue-100">We support all defective or unwanted orders within 60 days</p>
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
            ],
            [
                'user_id' => $admin->id,
                'title' => 'Refund Policy',
                'slug' => 'refund-policy',
                'content' => '<div class="max-w-6xl mx-auto py-8 px-4">
                    <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
                        <!-- Hero Header -->
                        <div class="bg-gradient-to-r from-blue-600 via-cyan-600 to-teal-600 text-white px-8 py-12">
                            <h1 class="text-5xl font-bold mb-4">Refund Policy</h1>
                            <p class="text-blue-100 text-xl mb-2">Your satisfaction guaranteed - Full refunds within 30 days</p>
                            <p class="text-blue-200 text-sm">Last updated: ' . now()->format('F d, Y') . '</p>
                        </div>

                        <div class="px-8 py-8">
                            <!-- Introduction -->
                            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 border-l-4 border-blue-500 rounded-r-lg p-6 mb-8">
                                <p class="text-gray-800 leading-relaxed text-lg mb-4">
                                    Bluprinter.com and most sellers on Bluprinter.com offer <strong>refunds for items within 30 days</strong> from the date of delivery. If there are any problems, please <a href="mailto:support@bluprinter.com" class="text-blue-600 hover:text-blue-800 font-semibold underline">contact us here</a> to submit your request.
                                </p>
                                <p class="text-gray-800 leading-relaxed text-lg">
                                    We always guarantee that you are satisfied with the orders you have placed on Bluprinter.com. To guarantee your rights when placing orders, please refer to our refund policy under the following conditions:
                                </p>
                            </div>

                            <!-- Refund Scenarios -->
                            <div class="space-y-8 mb-8">
                                <!-- Scenario 1: Wrong/Damaged/Faulty -->
                                <div class="border-2 border-red-300 rounded-lg overflow-hidden">
                                    <div class="bg-gradient-to-r from-red-500 to-rose-500 text-white px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-14 h-14 bg-white text-red-600 rounded-full flex items-center justify-center mr-4 text-2xl font-bold shadow-lg">1</div>
                                            <h2 class="text-3xl font-bold">Wrong/Damaged/Faulty Items</h2>
                                        </div>
                                    </div>
                                    <div class="bg-red-50 p-6">
                                        <p class="text-gray-800 font-semibold mb-4 text-lg">We guarantee to assist with cases where customers receive wrong/damaged/faulty items. Includes these cases:</p>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                            <div class="bg-white rounded-lg p-4 shadow-sm border-l-4 border-red-500">
                                                <div class="flex items-start">
                                                    <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    <p class="text-gray-800"><strong>Wrong items:</strong> Product doesn\'t match website description, wrong material, wrong size</p>
                                                </div>
                                            </div>
                                            
                                            <div class="bg-white rounded-lg p-4 shadow-sm border-l-4 border-red-500">
                                                <div class="flex items-start">
                                                    <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    <p class="text-gray-800"><strong>Damaged condition:</strong> Torn, dirty, wet, or hairy fabric</p>
                                                </div>
                                            </div>
                                            
                                            <div class="bg-white rounded-lg p-4 shadow-sm border-l-4 border-red-500">
                                                <div class="flex items-start">
                                                    <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    <p class="text-gray-800"><strong>Print defects:</strong> Visible defects, blurred, or out of place</p>
                                                </div>
                                            </div>
                                            
                                            <div class="bg-white rounded-lg p-4 shadow-sm border-l-4 border-red-500">
                                                <div class="flex items-start">
                                                    <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    <p class="text-gray-800"><strong>Damaged prints:</strong> Damaged or peeled prints after first wash</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 mb-6">
                                            <p class="text-yellow-900">
                                                <strong>⏰ Time limit:</strong> Please contact Customer Support within <strong>30 days of delivery</strong>. Orders over 30 days will not be supported.
                                            </p>
                                        </div>

                                        <div class="bg-white rounded-lg p-5 shadow-md border border-red-200">
                                            <h3 class="font-bold text-red-900 mb-3 text-lg">Required Information:</h3>
                                            <div class="space-y-2">
                                                <div class="flex items-start">
                                                    <svg class="w-5 h-5 text-red-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <p class="text-gray-800">Order information to confirm the order</p>
                                                </div>
                                                <div class="flex items-start">
                                                    <svg class="w-5 h-5 text-red-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <p class="text-gray-800">Photos of the packaging label</p>
                                                </div>
                                                <div class="flex items-start">
                                                    <svg class="w-5 h-5 text-red-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <p class="text-gray-800">Photos of the items and size tag/neck label in a frame</p>
                                                </div>
                                                <div class="flex items-start">
                                                    <svg class="w-5 h-5 text-red-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <p class="text-gray-800">Photos clearly showing damaged/wrong/faulty parts different from website description</p>
                                                </div>
                                            </div>
                                            <div class="mt-4 bg-red-100 border border-red-300 rounded-lg p-3">
                                                <p class="text-red-900 text-sm">
                                                    <strong>📸 Note:</strong> For orders with multiple items, provide photos/videos of products placed side by side on a flat surface.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-6 bg-green-100 border-2 border-green-500 rounded-lg p-5">
                                            <p class="text-green-900 font-semibold text-lg">
                                                ✓ After we confirm your product is eligible, you will receive a <strong>refund immediately</strong> to the account you used for payment.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Scenario 2: Wrong Size -->
                                <div class="border-2 border-orange-300 rounded-lg overflow-hidden">
                                    <div class="bg-gradient-to-r from-orange-500 to-amber-500 text-white px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-14 h-14 bg-white text-orange-600 rounded-full flex items-center justify-center mr-4 text-2xl font-bold shadow-lg">2</div>
                                            <h2 class="text-3xl font-bold">Wrong Size from the One Ordered</h2>
                                        </div>
                                    </div>
                                    <div class="bg-orange-50 p-6">
                                        <p class="text-gray-800 font-semibold mb-4 text-lg">
                                            We guarantee to assist with cases where customers receive the wrong size product. Specifically, products with wrong measurements compared to the size guide <strong class="text-orange-600">(A difference of over 1.5")</strong> from standard measurements.
                                        </p>

                                        <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 mb-6">
                                            <p class="text-yellow-900">
                                                <strong>⏰ Time limit:</strong> Please contact Customer Support within <strong>30 days of delivery</strong>. Orders over 30 days will not be supported.
                                            </p>
                                        </div>

                                        <div class="bg-white rounded-lg p-5 shadow-md border border-orange-200">
                                            <h3 class="font-bold text-orange-900 mb-3 text-lg">Required Information:</h3>
                                            <div class="space-y-2">
                                                <div class="flex items-start">
                                                    <svg class="w-5 h-5 text-orange-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <p class="text-gray-800">Order information to confirm the order</p>
                                                </div>
                                                <div class="flex items-start">
                                                    <svg class="w-5 h-5 text-orange-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <p class="text-gray-800">Photos of the items and size tag/neck label in a frame</p>
                                                </div>
                                                <div class="flex items-start">
                                                    <svg class="w-5 h-5 text-orange-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <p class="text-gray-800">Photos clearly showing actual measurements: width, length (Please use measuring tape)</p>
                                                </div>
                                            </div>
                                            <div class="mt-4 bg-orange-100 border border-orange-300 rounded-lg p-3">
                                                <p class="text-orange-900 text-sm">
                                                    <strong>📸 Note:</strong> For orders with multiple items, provide photos/videos of products placed side by side on a flat surface.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-6 bg-green-100 border-2 border-green-500 rounded-lg p-5">
                                            <p class="text-green-900 font-semibold text-lg">
                                                ✓ After we confirm measurements (unused) differ over 1.5" from standard, you will receive a <strong>refund immediately</strong> to the account you used for payment.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Scenario 3: Lost Items -->
                                <div class="border-2 border-purple-300 rounded-lg overflow-hidden">
                                    <div class="bg-gradient-to-r from-purple-500 to-indigo-500 text-white px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-14 h-14 bg-white text-purple-600 rounded-full flex items-center justify-center mr-4 text-2xl font-bold shadow-lg">3</div>
                                            <h2 class="text-3xl font-bold">Lost Items in Shipping</h2>
                                        </div>
                                    </div>
                                    <div class="bg-purple-50 p-6">
                                        <p class="text-gray-800 font-semibold mb-4 text-lg">
                                            We guarantee to assist with cases where your order is lost in the shipping process.
                                        </p>

                                        <div class="bg-blue-100 border-l-4 border-blue-500 p-4 mb-6">
                                            <p class="text-blue-900">
                                                <strong>📧 Important:</strong> As soon as tracking reports show "delivered", we will email you to confirm receipt. Please check your mailbox, security cameras, neighbors, and contact local post office before claiming lost items.
                                            </p>
                                        </div>

                                        <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 mb-6">
                                            <p class="text-yellow-900">
                                                <strong>⏰ Time limit:</strong> Contact us immediately within <strong>30 days of delivery</strong> if you haven\'t received the package. Orders over 30 days will not be supported.
                                            </p>
                                        </div>

                                        <div class="bg-white rounded-lg p-5 shadow-md border border-purple-200">
                                            <h3 class="font-bold text-purple-900 mb-3 text-lg">Required Information:</h3>
                                            <div class="space-y-2">
                                                <div class="flex items-start">
                                                    <svg class="w-5 h-5 text-purple-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <p class="text-gray-800">Order information</p>
                                                </div>
                                                <div class="flex items-start">
                                                    <svg class="w-5 h-5 text-purple-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <p class="text-gray-800">Verified shipping address</p>
                                                </div>
                                                <div class="flex items-start">
                                                    <svg class="w-5 h-5 text-purple-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <p class="text-gray-800">Verification from your local post office that you haven\'t received the package</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-6 bg-green-100 border-2 border-green-500 rounded-lg p-5">
                                            <p class="text-green-900 font-semibold text-lg">
                                                ✓ After sending all necessary information, you will receive a <strong>refund immediately</strong> to the account you used for payment.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Scenario 4: Cancellation -->
                                <div class="border-2 border-pink-300 rounded-lg overflow-hidden">
                                    <div class="bg-gradient-to-r from-pink-500 to-rose-500 text-white px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-14 h-14 bg-white text-pink-600 rounded-full flex items-center justify-center mr-4 text-2xl font-bold shadow-lg">4</div>
                                            <h2 class="text-3xl font-bold">Order Cancellation</h2>
                                        </div>
                                    </div>
                                    <div class="bg-pink-50 p-6">
                                        <div class="bg-white rounded-lg p-6 shadow-md border border-pink-200">
                                            <div class="flex items-start mb-4">
                                                <div class="flex-shrink-0 w-16 h-16 bg-gradient-to-br from-pink-500 to-rose-500 text-white rounded-full flex items-center justify-center mr-4">
                                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <h3 class="text-2xl font-bold text-pink-900 mb-2">4 Hours Window</h3>
                                                    <p class="text-gray-800 leading-relaxed">
                                                        After making your purchase, you have <strong class="text-pink-600">4 hours</strong> to cancel the order. Please submit a cancellation request, and your order will be canceled with a full refund to your card.
                                                    </p>
                                                </div>
                                            </div>
                                            
                                            <div class="bg-red-100 border-2 border-red-400 rounded-lg p-4">
                                                <p class="text-red-900 font-semibold">
                                                    ⚠️ Once 4 hours have passed, Bluprinter <strong>refuses to support</strong> order cancellation or modification requests.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Information Note -->
                            <div class="bg-gradient-to-br from-slate-100 to-gray-100 border-l-4 border-slate-500 rounded-r-lg p-6 mb-8">
                                <h3 class="text-xl font-bold text-slate-800 mb-3 flex items-center">
                                    <svg class="w-6 h-6 mr-2 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Order Information Includes:
                                </h3>
                                <div class="bg-white rounded-lg p-4 space-y-2">
                                    <p class="text-gray-800"><strong>•</strong> Order code</p>
                                    <p class="text-gray-800"><strong>•</strong> Shipping address</p>
                                    <p class="text-gray-800"><strong>•</strong> Recipient\'s information: Full name, Phone number, Email address</p>
                                </div>
                            </div>

                            <!-- International Orders Notice -->
                            <div class="bg-gradient-to-r from-cyan-600 to-blue-600 rounded-lg p-8 text-center text-white mb-8">
                                <div class="flex justify-center mb-4">
                                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center">
                                        <svg class="w-10 h-10 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="text-2xl font-bold mb-3">International Orders</h3>
                                <p class="text-xl text-cyan-100 mb-2">
                                    For all orders shipped <strong>outside the US</strong>
                                </p>
                                <div class="bg-white bg-opacity-20 rounded-lg p-4 inline-block">
                                    <p class="text-3xl font-bold">60 DAYS</p>
                                    <p class="text-cyan-100">support window from date of delivery</p>
                                </div>
                                <p class="mt-4 text-sm text-cyan-100">We support all defective or unwanted orders within 60 days</p>
                            </div>

                            <!-- Contact Footer -->
                            <div class="bg-gradient-to-r from-blue-600 to-teal-600 rounded-lg p-8 text-center text-white">
                                <div class="flex justify-center mb-4">
                                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center">
                                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="text-2xl font-bold mb-3">Questions About Refunds?</h3>
                                <p class="text-lg text-blue-100 mb-5">
                                    If you have any questions regarding our refund policy, please contact our Customer Support Team for quick response and support.
                                </p>
                                <a href="mailto:support@bluprinter.com" class="inline-flex items-center px-8 py-3 bg-white text-blue-600 font-bold rounded-lg shadow-lg hover:shadow-xl transition duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    support@bluprinter.com
                                </a>
                            </div>
                        </div>
                    </div>
                </div>',
                'excerpt' => 'Complete refund policy - 30-day refunds for wrong, damaged, wrong size or lost items. 4-hour cancellation window.',
                'status' => 'published',
                'published_at' => now(),
                'template' => 'default',
                'show_in_menu' => true,
                'menu_title' => 'Refund Policy',
                'sort_order' => 5,
                'meta_title' => 'Refund Policy - Bluprinter Full Refund Guarantee',
                'meta_description' => 'Full refund policy within 30 days for wrong, damaged, or lost items. 4-hour cancellation window. Learn about our refund procedures on Bluprinter.',
            ],
            [
                'user_id' => $admin->id,
                'title' => 'Cancel or Change Order',
                'slug' => 'cancel-change-order',
                'content' => '<div class="max-w-5xl mx-auto py-8 px-4">
                    <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
                        <!-- Hero Header -->
                        <div class="bg-gradient-to-r from-orange-600 via-red-600 to-pink-600 text-white px-8 py-12">
                            <h1 class="text-5xl font-bold mb-4">Cancel or Change Order</h1>
                            <p class="text-orange-100 text-xl mb-2">Need to make changes? You have 4 hours!</p>
                            <p class="text-orange-200 text-sm">Last updated: ' . now()->format('F d, Y') . '</p>
                        </div>

                        <div class="px-8 py-8">
                            <!-- 4 Hours Window - Main Feature -->
                            <div class="bg-gradient-to-br from-amber-100 to-orange-100 border-2 border-orange-400 rounded-lg p-8 mb-8">
                                <div class="flex items-center justify-center mb-6">
                                    <div class="relative">
                                        <div class="w-32 h-32 bg-gradient-to-br from-orange-500 to-red-500 rounded-full flex items-center justify-center shadow-2xl">
                                            <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div class="absolute -top-2 -right-2 w-12 h-12 bg-yellow-400 rounded-full flex items-center justify-center font-bold text-2xl text-orange-900 shadow-lg animate-pulse">
                                            4
                                        </div>
                                    </div>
                                </div>
                                <h2 class="text-4xl font-bold text-center text-orange-900 mb-4">4 Hours Window</h2>
                                <p class="text-center text-gray-800 text-xl leading-relaxed">
                                    You have <strong class="text-orange-600">4 hours</strong> to cancel or change your order after placing it
                                </p>
                            </div>

                            <!-- What Can Be Changed -->
                            <div class="mb-8">
                                <div class="bg-gradient-to-r from-blue-600 to-cyan-600 text-white px-6 py-4 rounded-t-lg">
                                    <h2 class="text-3xl font-bold flex items-center">
                                        <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        What Can Be Changed?
                                    </h2>
                                </div>
                                <div class="bg-blue-50 border-2 border-blue-300 border-t-0 rounded-b-lg p-6">
                                    <p class="text-gray-800 font-semibold mb-4 text-lg">Within 4 hours, you can modify the following:</p>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="bg-white rounded-lg p-5 shadow-md border-l-4 border-blue-500">
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0 w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-bold text-blue-900 text-lg mb-1">Size</h3>
                                                    <p class="text-gray-700 text-sm">Change product size</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-white rounded-lg p-5 shadow-md border-l-4 border-cyan-500">
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0 w-12 h-12 bg-cyan-500 rounded-full flex items-center justify-center mr-3">
                                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-bold text-cyan-900 text-lg mb-1">Color</h3>
                                                    <p class="text-gray-700 text-sm">Change product color</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-white rounded-lg p-5 shadow-md border-l-4 border-green-500">
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0 w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mr-3">
                                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-bold text-green-900 text-lg mb-1">Quantity</h3>
                                                    <p class="text-gray-700 text-sm">Change order quantity</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-white rounded-lg p-5 shadow-md border-l-4 border-purple-500">
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0 w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center mr-3">
                                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-bold text-purple-900 text-lg mb-1">Shipping Address</h3>
                                                    <p class="text-gray-700 text-sm">Update delivery address</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- How to Cancel or Change -->
                            <div class="mb-8">
                                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-4 rounded-t-lg">
                                    <h2 class="text-3xl font-bold flex items-center">
                                        <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        How to Cancel or Change Your Order
                                    </h2>
                                </div>
                                <div class="bg-indigo-50 border-2 border-indigo-300 border-t-0 rounded-b-lg p-6">
                                    <div class="space-y-4">
                                        <div class="flex items-start bg-white rounded-lg p-5 shadow-md">
                                            <div class="flex-shrink-0 w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-500 text-white rounded-full flex items-center justify-center mr-4 text-2xl font-bold shadow-lg">1</div>
                                            <div class="flex-1">
                                                <h3 class="text-xl font-bold text-indigo-900 mb-2">Go to "Contact Us"</h3>
                                                <p class="text-gray-700">Navigate to our Contact Us page or customer support section</p>
                                            </div>
                                        </div>

                                        <div class="flex items-start bg-white rounded-lg p-5 shadow-md">
                                            <div class="flex-shrink-0 w-14 h-14 bg-gradient-to-br from-purple-500 to-pink-500 text-white rounded-full flex items-center justify-center mr-4 text-2xl font-bold shadow-lg">2</div>
                                            <div class="flex-1">
                                                <h3 class="text-xl font-bold text-purple-900 mb-2">Create a Ticket</h3>
                                                <p class="text-gray-700">Submit a support ticket with your order details and requested changes</p>
                                            </div>
                                        </div>

                                        <div class="flex items-start bg-white rounded-lg p-5 shadow-md">
                                            <div class="flex-shrink-0 w-14 h-14 bg-gradient-to-br from-pink-500 to-rose-500 text-white rounded-full flex items-center justify-center mr-4 text-2xl font-bold shadow-lg">3</div>
                                            <div class="flex-1">
                                                <h3 class="text-xl font-bold text-pink-900 mb-2">Reach Customer Support</h3>
                                                <p class="text-gray-700 mb-2">Or contact our customer support team directly:</p>
                                                <a href="mailto:support@bluprinter.com" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-semibold underline">
                                                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                    </svg>
                                                    support@bluprinter.com
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Warning - After 4 Hours -->
                            <div class="bg-red-100 border-2 border-red-500 rounded-lg p-8 mb-8">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-16 h-16 bg-red-500 rounded-full flex items-center justify-center mr-4">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-2xl font-bold text-red-800 mb-3">⚠️ Important: After 4 Hours</h3>
                                        <p class="text-red-900 font-semibold text-lg mb-3">
                                            When it is more than <strong>4 hours after placing an order</strong>, Bluprinter <strong>refuses to support</strong> order cancellation or order modification requests.
                                        </p>
                                        <div class="bg-white rounded-lg p-4 border-l-4 border-red-500">
                                            <p class="text-gray-800">
                                                Once the 4-hour window has passed, your order will enter production and cannot be changed or cancelled.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Delivery Address Responsibility -->
                            <div class="bg-gradient-to-br from-yellow-50 to-amber-50 border-2 border-yellow-400 rounded-lg p-6 mb-8">
                                <h3 class="text-2xl font-bold text-yellow-900 mb-4 flex items-center">
                                    <svg class="w-8 h-8 mr-3 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Delivery Address Responsibility
                                </h3>
                                <div class="bg-white rounded-lg p-5 shadow-md border-l-4 border-yellow-500">
                                    <p class="text-gray-800 leading-relaxed mb-3">
                                        It is the <strong>customer\'s responsibility</strong> to ensure the product delivery address is correct.
                                    </p>
                                    <div class="bg-yellow-100 border border-yellow-300 rounded-lg p-4">
                                        <p class="text-yellow-900 font-semibold">
                                            <strong>⚠️ Please Note:</strong> Bluprinter takes <strong>no responsibility</strong> for any product a customer does not receive because of errors in the delivery address given to us.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Tips -->
                            <div class="bg-gradient-to-br from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-r-lg p-6">
                                <h3 class="text-xl font-bold text-green-900 mb-4 flex items-center">
                                    <svg class="w-7 h-7 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                    </svg>
                                    Quick Tips
                                </h3>
                                <div class="bg-white rounded-lg p-5 space-y-3">
                                    <div class="flex items-start">
                                        <svg class="w-6 h-6 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-gray-800">Double-check your order details before submitting</p>
                                    </div>
                                    <div class="flex items-start">
                                        <svg class="w-6 h-6 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-gray-800">Verify your shipping address is complete and accurate</p>
                                    </div>
                                    <div class="flex items-start">
                                        <svg class="w-6 h-6 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-gray-800">Act quickly if you need to make changes - don\'t wait until the last minute</p>
                                    </div>
                                    <div class="flex items-start">
                                        <svg class="w-6 h-6 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-gray-800">Save your order confirmation email for reference</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>',
                'excerpt' => '4-hour window to cancel or change your order - size, color, quantity, or shipping address. Quick and easy modification process.',
                'status' => 'published',
                'published_at' => now(),
                'template' => 'default',
                'show_in_menu' => true,
                'menu_title' => 'Cancel/Change Order',
                'sort_order' => 6,
                'meta_title' => 'Cancel or Change Order - Bluprinter 4-Hour Window',
                'meta_description' => 'Need to cancel or change your order? You have 4 hours to modify size, color, quantity, or shipping address. Learn how on Bluprinter.',
            ],
            [
                'user_id' => $admin->id,
                'title' => 'About Us',
                'slug' => 'about-us',
                'content' => '<div class="max-w-7xl mx-auto py-8 px-4">
                    <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
                        <!-- Hero Header -->
                        <div class="relative bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 text-white px-8 py-16">
                            <div class="absolute inset-0 bg-black opacity-10"></div>
                            <div class="relative z-10 text-center">
                                <h1 class="text-6xl font-bold mb-4">Welcome to Bluprinter</h1>
                                <p class="text-3xl text-purple-100 mb-6">World of Unique Creations</p>
                                <div class="flex justify-center mb-4">
                                    <div class="w-24 h-1 bg-white rounded"></div>
                                </div>
                                <p class="text-xl text-indigo-100 max-w-4xl mx-auto leading-relaxed">
                                    Discover a global marketplace where creativity thrives
                                </p>
                            </div>
                        </div>

                        <div class="px-8 py-12">
                            <!-- Introduction -->
                            <div class="max-w-5xl mx-auto mb-16">
                                <div class="text-center mb-12">
                                    <p class="text-2xl text-gray-700 leading-relaxed mb-6">
                                        At <strong class="text-indigo-600">Bluprinter</strong>, we bring together passionate makers, inspired collectors, and unique shoppers to celebrate individuality and craftsmanship. Whether you\'re looking for handmade treasures, vintage finds, or one-of-a-kind pieces, Bluprinter makes it simple to connect directly with independent sellers from around the world.
                                    </p>
                                    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg p-8 border-l-4 border-indigo-500">
                                        <p class="text-xl text-gray-800 font-semibold">
                                            No warehouses. No middlemen. <span class="text-indigo-600">Just extraordinary items made and sold with love.</span>
                                        </p>
                                        <p class="text-lg text-gray-700 mt-3">
                                            Start your journey today and find something truly special.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- About Us Section -->
                            <div class="mb-16">
                                <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-8 py-6 rounded-t-lg">
                                    <h2 class="text-4xl font-bold flex items-center justify-center">
                                        <svg class="w-10 h-10 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                        Our Story
                                    </h2>
                                </div>
                                <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-b-lg p-8 border-2 border-purple-200 border-t-0">
                                    <div class="max-w-4xl mx-auto">
                                        <p class="text-lg text-gray-800 leading-relaxed mb-6">
                                            Bluprinter was born out of a vision to bridge the gap between creators and those seeking extraordinary items. We believe that every handmade piece, vintage find, or collectible has a story to tell. That\'s why we\'ve created a platform where independent sellers can share their passions and buyers can discover the magic of unique craftsmanship.
                                        </p>
                                        <p class="text-lg text-gray-800 leading-relaxed mb-6">
                                            At Bluprinter, we celebrate <strong class="text-purple-600">creativity</strong>, <strong class="text-indigo-600">authenticity</strong>, and <strong class="text-blue-600">community</strong>. With no central warehouse, every purchase directly supports talented makers and small businesses across the globe. We\'re more than just a marketplace—we\'re a movement to inspire and empower.
                                        </p>
                                        <div class="bg-white rounded-lg p-6 shadow-lg border-l-4 border-purple-500">
                                            <p class="text-2xl font-bold text-center text-gray-800">
                                                Our mission is simple: <span class="text-purple-600">to make the world a little more creative, one connection at a time.</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Us Section -->
                            <div class="mb-16">
                                <div class="bg-gradient-to-r from-blue-600 to-cyan-600 text-white px-8 py-6 rounded-t-lg">
                                    <h2 class="text-4xl font-bold flex items-center justify-center">
                                        <svg class="w-10 h-10 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        Contact Us
                                    </h2>
                                </div>
                                <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-b-lg p-8 border-2 border-blue-200 border-t-0">
                                    <p class="text-center text-2xl font-semibold text-gray-800 mb-8">We\'d Love to Hear From You!</p>
                                    <p class="text-center text-lg text-gray-700 mb-8 max-w-3xl mx-auto">
                                        Have a question, need assistance, or just want to say hello? We\'re here to help. Reach out to us anytime using the contact options below:
                                    </p>

                                    <!-- Contact Methods -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                                        <div class="bg-white rounded-lg p-6 shadow-lg text-center border-2 border-blue-200 hover:border-blue-400 transition duration-200">
                                            <div class="flex justify-center mb-4">
                                                <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <h3 class="text-xl font-bold text-gray-800 mb-2">Email</h3>
                                            <a href="mailto:admin@bluprinter.com" class="text-blue-600 hover:text-blue-800 font-semibold text-lg underline">admin@bluprinter.com</a>
                                        </div>

                                        <div class="bg-white rounded-lg p-6 shadow-lg text-center border-2 border-green-200 hover:border-green-400 transition duration-200">
                                            <div class="flex justify-center mb-4">
                                                <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <h3 class="text-xl font-bold text-gray-800 mb-2">Phone</h3>
                                            <a href="tel:0767383676" class="text-green-600 hover:text-green-800 font-semibold text-lg">0767 383 676</a>
                                        </div>

                                        <div class="bg-white rounded-lg p-6 shadow-lg text-center border-2 border-purple-200 hover:border-purple-400 transition duration-200">
                                            <div class="flex justify-center mb-4">
                                                <div class="w-16 h-16 bg-purple-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <h3 class="text-xl font-bold text-gray-800 mb-2">Live Chat</h3>
                                            <p class="text-gray-600">Available on our website for real-time assistance</p>
                                        </div>
                                    </div>

                                    <!-- Warehouse Addresses -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="bg-white rounded-lg p-6 shadow-lg border-l-4 border-red-500">
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0 w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mr-4">
                                                    <span class="text-white font-bold text-xl">🇺🇸</span>
                                                </div>
                                                <div>
                                                    <h3 class="text-xl font-bold text-gray-800 mb-2">US Warehouse</h3>
                                                    <p class="text-gray-700">
                                                        1301 E ARAPAHO RD, STE 101<br>
                                                        RICHARDSON, TX 75081<br>
                                                        United States
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-white rounded-lg p-6 shadow-lg border-l-4 border-blue-500">
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0 w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mr-4">
                                                    <span class="text-white font-bold text-xl">🇬🇧</span>
                                                </div>
                                                <div>
                                                    <h3 class="text-xl font-bold text-gray-800 mb-2">UK Warehouse</h3>
                                                    <p class="text-gray-700">
                                                        3 Kincraig Rd<br>
                                                        Blackpool FY2 0FY<br>
                                                        United Kingdom
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-8 bg-blue-100 border-2 border-blue-300 rounded-lg p-6 text-center">
                                        <p class="text-blue-900 font-semibold text-lg">
                                            We\'re committed to making your Bluprinter experience extraordinary. Let\'s connect!
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Legal Information Section -->
                            <div class="mb-8">
                                <div class="bg-gradient-to-r from-slate-700 to-gray-700 text-white px-8 py-6 rounded-t-lg">
                                    <h2 class="text-4xl font-bold flex items-center justify-center">
                                        <svg class="w-10 h-10 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Legal Information
                                    </h2>
                                </div>
                                <div class="bg-gradient-to-br from-slate-50 to-gray-50 rounded-b-lg p-8 border-2 border-slate-300 border-t-0">
                                    <p class="text-center text-xl font-semibold text-gray-800 mb-8">The website is jointly operated by:</p>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Vietnam Company -->
                                        <div class="bg-white rounded-lg p-6 shadow-lg border-2 border-red-200">
                                            <div class="flex items-start mb-3">
                                                <span class="text-3xl mr-3">🇻🇳</span>
                                                <h3 class="text-xl font-bold text-gray-800">Vietnam</h3>
                                            </div>
                                            <div class="bg-red-50 rounded-lg p-4">
                                                <p class="font-bold text-red-900 mb-2">HM FULFILL COMPANY LIMITED</p>
                                                <p class="text-gray-700 text-sm">
                                                    63/9Đ, Ap Chanh 1, Tan Xuan<br>
                                                    Hoc Mon, Ho Chi Minh City<br>
                                                    700000, Vietnam
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Hong Kong Company -->
                                        <div class="bg-white rounded-lg p-6 shadow-lg border-2 border-yellow-200">
                                            <div class="flex items-start mb-3">
                                                <span class="text-3xl mr-3">🇭🇰</span>
                                                <h3 class="text-xl font-bold text-gray-800">Hong Kong</h3>
                                            </div>
                                            <div class="bg-yellow-50 rounded-lg p-4">
                                                <p class="font-bold text-yellow-900 mb-2">BLUE STAR TRADING LIMITED</p>
                                                <p class="text-gray-700 text-sm">
                                                    RM C, 6/F, WORLD TRUST TOWER<br>
                                                    50 STANLEY STREET<br>
                                                    CENTRAL, HONG KONG
                                                </p>
                                            </div>
                                        </div>

                                        <!-- UK Company -->
                                        <div class="bg-white rounded-lg p-6 shadow-lg border-2 border-blue-200">
                                            <div class="flex items-start mb-3">
                                                <span class="text-3xl mr-3">🇬🇧</span>
                                                <h3 class="text-xl font-bold text-gray-800">United Kingdom</h3>
                                            </div>
                                            <div class="bg-blue-50 rounded-lg p-4">
                                                <p class="font-bold text-blue-900 mb-2">Bluprinter LTD</p>
                                                <p class="text-gray-700 text-sm mb-2">
                                                    <strong>Company Number:</strong> 16342615
                                                </p>
                                                <p class="text-gray-700 text-sm">
                                                    71-75 Shelton Street<br>
                                                    Covent Garden, London<br>
                                                    WC2H 9JQ, United Kingdom
                                                </p>
                                            </div>
                                        </div>

                                        <!-- US Company -->
                                        <div class="bg-white rounded-lg p-6 shadow-lg border-2 border-red-200">
                                            <div class="flex items-start mb-3">
                                                <span class="text-3xl mr-3">🇺🇸</span>
                                                <h3 class="text-xl font-bold text-gray-800">United States</h3>
                                            </div>
                                            <div class="bg-red-50 rounded-lg p-4">
                                                <p class="font-bold text-red-900 mb-2">Bluprinter LLC</p>
                                                <p class="text-gray-700 text-sm">
                                                    5900 BALCONES DR STE 100<br>
                                                    AUSTIN, TX 78731<br>
                                                    United States
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Call to Action -->
                            <div class="bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 rounded-lg p-10 text-center text-white">
                                <h3 class="text-3xl font-bold mb-4">Join Our Creative Community</h3>
                                <p class="text-xl text-purple-100 mb-6 max-w-3xl mx-auto">
                                    Start your journey with Bluprinter today and discover a world of unique, handcrafted treasures.
                                </p>
                                <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                                    <a href="/" class="inline-flex items-center px-8 py-4 bg-white text-indigo-600 font-bold rounded-lg shadow-xl hover:shadow-2xl transition duration-200 text-lg">
                                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                        </svg>
                                        Explore Marketplace
                                    </a>
                                    <a href="mailto:admin@bluprinter.com" class="inline-flex items-center px-8 py-4 bg-transparent border-2 border-white text-white font-bold rounded-lg hover:bg-white hover:text-indigo-600 transition duration-200 text-lg">
                                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        Contact Us
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>',
                'excerpt' => 'Learn about Bluprinter - a global marketplace connecting creators and collectors. Discover our story, mission, and contact information.',
                'status' => 'published',
                'published_at' => now(),
                'template' => 'default',
                'show_in_menu' => true,
                'menu_title' => 'About',
                'sort_order' => 7,
                'meta_title' => 'About Us - Bluprinter Creative Marketplace',
                'meta_description' => 'Discover Bluprinter - a global marketplace where creativity thrives. Connect with passionate makers and find unique handcrafted treasures from around the world.',
            ],
            [
                'user_id' => $admin->id,
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'content' => '<div class="max-w-7xl mx-auto py-8 px-4">
                    <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
                        <!-- Hero Header -->
                        <div class="bg-gradient-to-r from-green-600 via-teal-600 to-cyan-600 text-white px-8 py-16">
                            <div class="text-center">
                                <div class="flex justify-center mb-6">
                                    <div class="w-24 h-24 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                                        <svg class="w-14 h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <h1 class="text-6xl font-bold mb-4">Contact Us</h1>
                                <p class="text-2xl text-green-100">We\'re Here to Help!</p>
                                <p class="text-lg text-teal-100 mt-4 max-w-3xl mx-auto">
                                    Get in touch with us through any of the channels below. We\'re available worldwide to support your needs.
                                </p>
                            </div>
                        </div>

                        <div class="px-8 py-12">
                            <!-- Contact Methods -->
                            <div class="mb-16">
                                <div class="text-center mb-10">
                                    <h2 class="text-4xl font-bold text-gray-800 mb-3">Get In Touch</h2>
                                    <div class="flex justify-center">
                                        <div class="w-20 h-1 bg-gradient-to-r from-green-500 to-teal-500 rounded"></div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                                    <!-- Email -->
                                    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-lg p-6 shadow-lg border-2 border-blue-200 hover:border-blue-400 transition duration-200">
                                        <div class="flex justify-center mb-4">
                                            <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center shadow-lg">
                                                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <h3 class="text-xl font-bold text-center text-gray-800 mb-2">Email</h3>
                                        <p class="text-center">
                                            <a href="mailto:admin@bluprinter.com" class="text-blue-600 hover:text-blue-800 font-semibold text-lg underline break-all">admin@bluprinter.com</a>
                                        </p>
                                    </div>

                                    <!-- Phone -->
                                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-6 shadow-lg border-2 border-green-200 hover:border-green-400 transition duration-200">
                                        <div class="flex justify-center mb-4">
                                            <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center shadow-lg">
                                                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <h3 class="text-xl font-bold text-center text-gray-800 mb-2">Call Us</h3>
                                        <p class="text-center">
                                            <a href="tel:+18563782798" class="text-green-600 hover:text-green-800 font-semibold text-lg">+1 856-378-2798</a>
                                        </p>
                                    </div>

                                    <!-- iMessage -->
                                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-6 shadow-lg border-2 border-purple-200 hover:border-purple-400 transition duration-200">
                                        <div class="flex justify-center mb-4">
                                            <div class="w-16 h-16 bg-purple-500 rounded-full flex items-center justify-center shadow-lg">
                                                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <h3 class="text-xl font-bold text-center text-gray-800 mb-2">iMessage</h3>
                                        <p class="text-center text-purple-600 font-semibold text-lg">+1 856-378-2798</p>
                                    </div>

                                    <!-- WhatsApp -->
                                    <div class="bg-gradient-to-br from-green-50 to-lime-50 rounded-lg p-6 shadow-lg border-2 border-green-200 hover:border-green-400 transition duration-200">
                                        <div class="flex justify-center mb-4">
                                            <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center shadow-lg">
                                                <svg class="w-9 h-9 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <h3 class="text-xl font-bold text-center text-gray-800 mb-2">WhatsApp</h3>
                                        <p class="text-center">
                                            <a href="https://wa.me/18563782798" target="_blank" class="text-green-600 hover:text-green-800 font-semibold text-lg">+1 856-378-2798</a>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Business Addresses -->
                            <div class="mb-16">
                                <div class="text-center mb-10">
                                    <h2 class="text-4xl font-bold text-gray-800 mb-3">Our Global Offices</h2>
                                    <div class="flex justify-center">
                                        <div class="w-20 h-1 bg-gradient-to-r from-indigo-500 to-purple-500 rounded"></div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- UK Office -->
                                    <div class="bg-white rounded-lg p-6 shadow-xl border-2 border-blue-200 hover:shadow-2xl transition duration-200">
                                        <div class="flex items-start mb-4">
                                            <div class="flex-shrink-0 w-14 h-14 bg-blue-500 rounded-full flex items-center justify-center mr-4">
                                                <span class="text-2xl">🇬🇧</span>
                                            </div>
                                            <div>
                                                <h3 class="text-2xl font-bold text-gray-800">United Kingdom</h3>
                                                <p class="text-blue-600 text-sm font-semibold">Business Office</p>
                                            </div>
                                        </div>
                                        <div class="bg-blue-50 rounded-lg p-4">
                                            <p class="font-bold text-blue-900 mb-2">Bluprinter LTD</p>
                                            <p class="text-gray-700 mb-2">
                                                <strong>Company Number:</strong> 16342615
                                            </p>
                                            <p class="text-gray-700">
                                                71-75 Shelton Street<br>
                                                Covent Garden, London<br>
                                                WC2H 9JQ, United Kingdom
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Vietnam Office -->
                                    <div class="bg-white rounded-lg p-6 shadow-xl border-2 border-red-200 hover:shadow-2xl transition duration-200">
                                        <div class="flex items-start mb-4">
                                            <div class="flex-shrink-0 w-14 h-14 bg-red-500 rounded-full flex items-center justify-center mr-4">
                                                <span class="text-2xl">🇻🇳</span>
                                            </div>
                                            <div>
                                                <h3 class="text-2xl font-bold text-gray-800">Vietnam</h3>
                                                <p class="text-red-600 text-sm font-semibold">Business Office</p>
                                            </div>
                                        </div>
                                        <div class="bg-red-50 rounded-lg p-4">
                                            <p class="font-bold text-red-900 mb-2">HM FULFILL COMPANY LIMITED</p>
                                            <p class="text-gray-700">
                                                63/9Đ Ap Chanh 1, Tan Xuan<br>
                                                Hoc Mon, Ho Chi Minh City<br>
                                                700000, Vietnam
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Hong Kong Office -->
                                    <div class="bg-white rounded-lg p-6 shadow-xl border-2 border-yellow-200 hover:shadow-2xl transition duration-200">
                                        <div class="flex items-start mb-4">
                                            <div class="flex-shrink-0 w-14 h-14 bg-yellow-500 rounded-full flex items-center justify-center mr-4">
                                                <span class="text-2xl">🇭🇰</span>
                                            </div>
                                            <div>
                                                <h3 class="text-2xl font-bold text-gray-800">Hong Kong</h3>
                                                <p class="text-yellow-600 text-sm font-semibold">Business Office</p>
                                            </div>
                                        </div>
                                        <div class="bg-yellow-50 rounded-lg p-4">
                                            <p class="font-bold text-yellow-900 mb-2">BLUE STAR TRADING LIMITED</p>
                                            <p class="text-gray-700">
                                                RM C, 6/F, WORLD TRUST TOWER<br>
                                                50 STANLEY STREET<br>
                                                CENTRAL, HONG KONG
                                            </p>
                                        </div>
                                    </div>

                                    <!-- US Office -->
                                    <div class="bg-white rounded-lg p-6 shadow-xl border-2 border-indigo-200 hover:shadow-2xl transition duration-200">
                                        <div class="flex items-start mb-4">
                                            <div class="flex-shrink-0 w-14 h-14 bg-indigo-500 rounded-full flex items-center justify-center mr-4">
                                                <span class="text-2xl">🇺🇸</span>
                                            </div>
                                            <div>
                                                <h3 class="text-2xl font-bold text-gray-800">United States</h3>
                                                <p class="text-indigo-600 text-sm font-semibold">Business Office</p>
                                            </div>
                                        </div>
                                        <div class="bg-indigo-50 rounded-lg p-4">
                                            <p class="font-bold text-indigo-900 mb-2">Bluprinter LLC</p>
                                            <p class="text-gray-700">
                                                5900 BALCONES DR STE 100<br>
                                                AUSTIN, TX 78731<br>
                                                United States
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Warehouses -->
                            <div class="mb-8">
                                <div class="text-center mb-10">
                                    <h2 class="text-4xl font-bold text-gray-800 mb-3">Warehouse Locations</h2>
                                    <div class="flex justify-center">
                                        <div class="w-20 h-1 bg-gradient-to-r from-orange-500 to-red-500 rounded"></div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- US Warehouse -->
                                    <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-lg p-6 shadow-xl border-2 border-orange-300">
                                        <div class="flex items-start mb-4">
                                            <div class="flex-shrink-0 w-14 h-14 bg-gradient-to-br from-orange-500 to-red-500 rounded-full flex items-center justify-center mr-4 shadow-lg">
                                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="flex items-center mb-1">
                                                    <span class="text-2xl mr-2">🇺🇸</span>
                                                    <h3 class="text-2xl font-bold text-gray-800">US Warehouse</h3>
                                                </div>
                                                <p class="text-orange-600 text-sm font-semibold">Distribution Center</p>
                                            </div>
                                        </div>
                                        <div class="bg-white rounded-lg p-4 shadow-md">
                                            <p class="text-gray-700 leading-relaxed">
                                                1301 E ARAPAHO RD, STE 101<br>
                                                RICHARDSON, TX 75081<br>
                                                United States
                                            </p>
                                        </div>
                                    </div>

                                    <!-- UK Warehouse -->
                                    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-lg p-6 shadow-xl border-2 border-blue-300">
                                        <div class="flex items-start mb-4">
                                            <div class="flex-shrink-0 w-14 h-14 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-full flex items-center justify-center mr-4 shadow-lg">
                                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="flex items-center mb-1">
                                                    <span class="text-2xl mr-2">🇬🇧</span>
                                                    <h3 class="text-2xl font-bold text-gray-800">UK Warehouse</h3>
                                                </div>
                                                <p class="text-blue-600 text-sm font-semibold">Distribution Center</p>
                                            </div>
                                        </div>
                                        <div class="bg-white rounded-lg p-4 shadow-md">
                                            <p class="text-gray-700 leading-relaxed mb-3">
                                                3 Kincraig Rd<br>
                                                Blackpool FY2 0FY<br>
                                                United Kingdom
                                            </p>
                                            <div class="border-t border-blue-200 pt-3">
                                                <p class="text-gray-700">
                                                    <strong>📞 Phone:</strong> <a href="tel:02045136359" class="text-blue-600 hover:text-blue-800 font-semibold">020 4513 6359</a>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Call to Action -->
                            <div class="bg-gradient-to-r from-green-600 via-teal-600 to-cyan-600 rounded-lg p-10 text-center text-white">
                                <div class="flex justify-center mb-6">
                                    <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="text-3xl font-bold mb-4">Ready to Connect?</h3>
                                <p class="text-xl text-green-100 mb-6 max-w-3xl mx-auto">
                                    We\'re here 24/7 to answer your questions and provide support. Choose your preferred method and get in touch today!
                                </p>
                                <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                                    <a href="mailto:admin@bluprinter.com" class="inline-flex items-center px-8 py-4 bg-white text-teal-600 font-bold rounded-lg shadow-xl hover:shadow-2xl transition duration-200 text-lg">
                                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        Email Us
                                    </a>
                                    <a href="https://wa.me/18563782798" target="_blank" class="inline-flex items-center px-8 py-4 bg-transparent border-2 border-white text-white font-bold rounded-lg hover:bg-white hover:text-teal-600 transition duration-200 text-lg">
                                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"></path>
                                        </svg>
                                        WhatsApp
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>',
                'excerpt' => 'Contact Bluprinter - Email, phone, WhatsApp, iMessage. Global offices in UK, US, Vietnam, Hong Kong with warehouse locations.',
                'status' => 'published',
                'published_at' => now(),
                'template' => 'default',
                'show_in_menu' => true,
                'menu_title' => 'Contact',
                'sort_order' => 8,
                'meta_title' => 'Contact Us - Bluprinter Global Support',
                'meta_description' => 'Contact Bluprinter through email, phone, WhatsApp, or iMessage. Find our global offices in UK, US, Vietnam, Hong Kong and warehouse locations.',
            ],
            [
                'user_id' => $admin->id,
                'title' => 'Frequently Asked Questions (FAQs)',
                'slug' => 'faqs',
                'content' => '<div class="max-w-6xl mx-auto py-8 px-4">
                    <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
                        <!-- Hero Header -->
                        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white px-8 py-16">
                            <div class="text-center">
                                <div class="flex justify-center mb-6">
                                    <div class="w-24 h-24 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                                        <svg class="w-14 h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <h1 class="text-6xl font-bold mb-4">FAQs</h1>
                                <p class="text-2xl text-indigo-100">Frequently Asked Questions</p>
                                <p class="text-lg text-purple-100 mt-4 max-w-3xl mx-auto">
                                    Find answers to common questions about ordering, shipping, returns, and more
                                </p>
                            </div>
                        </div>

                        <div class="px-8 py-12">
                            <!-- FAQ Items -->
                            <div class="space-y-6">
                                <!-- FAQ 1 -->
                                <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-lg border-2 border-blue-200 overflow-hidden">
                                    <div class="bg-gradient-to-r from-blue-500 to-cyan-500 px-6 py-4">
                                        <h3 class="text-2xl font-bold text-white flex items-center">
                                            <span class="w-10 h-10 bg-white text-blue-600 rounded-full flex items-center justify-center mr-3 font-bold">1</span>
                                            How do I place an order?
                                        </h3>
                                    </div>
                                    <div class="p-6">
                                        <p class="text-gray-700 mb-4">You can carry out the following steps to complete your order:</p>
                                        <div class="space-y-3">
                                            <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                                <span class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center mr-3 flex-shrink-0 font-bold">1</span>
                                                <p class="text-gray-800 pt-1">Choose your style on the product page</p>
                                            </div>
                                            <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                                <span class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center mr-3 flex-shrink-0 font-bold">2</span>
                                                <p class="text-gray-800 pt-1">Adjust the quantity of product</p>
                                            </div>
                                            <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                                <span class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center mr-3 flex-shrink-0 font-bold">3</span>
                                                <p class="text-gray-800 pt-1">Click the "Add To Cart" button</p>
                                            </div>
                                            <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                                <span class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center mr-3 flex-shrink-0 font-bold">4</span>
                                                <p class="text-gray-800 pt-1">Process payment and apply a discount code (if you have) to complete purchasing</p>
                                            </div>
                                            <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                                <span class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center mr-3 flex-shrink-0 font-bold">5</span>
                                                <p class="text-gray-800 pt-1">Receive your confirmation email/message when your order is successful</p>
                                            </div>
                                        </div>
                                        <div class="mt-4 p-4 bg-blue-100 rounded-lg">
                                            <p class="text-blue-900">If you need any further assistance, please contact us via email: <a href="mailto:support@bluprinter.com" class="font-semibold underline">support@bluprinter.com</a></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- FAQ 2 -->
                                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg border-2 border-green-200 overflow-hidden">
                                    <div class="bg-gradient-to-r from-green-500 to-emerald-500 px-6 py-4">
                                        <h3 class="text-2xl font-bold text-white flex items-center">
                                            <span class="w-10 h-10 bg-white text-green-600 rounded-full flex items-center justify-center mr-3 font-bold">2</span>
                                            Where does your order ship from?
                                        </h3>
                                    </div>
                                    <div class="p-6">
                                        <p class="text-gray-800 leading-relaxed mb-3">
                                            Orders are shipped with <strong>USPS</strong>, <strong>FedEx</strong>, or <strong>Canada Post</strong>. Most orders placed within the US will be shipped from the facilities in the US.
                                        </p>
                                        <p class="text-gray-800 leading-relaxed">
                                            For international orders, in Canada, Australia, Europe, and more to ensure you can get your order shipped from the facilities within your country (or the nearest facilities). Fulfillers strive to ensure you get your order as soon as possible in the highest quality.
                                        </p>
                                    </div>
                                </div>

                                <!-- FAQ 3 -->
                                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg border-2 border-purple-200 overflow-hidden">
                                    <div class="bg-gradient-to-r from-purple-500 to-pink-500 px-6 py-4">
                                        <h3 class="text-2xl font-bold text-white flex items-center">
                                            <span class="w-10 h-10 bg-white text-purple-600 rounded-full flex items-center justify-center mr-3 font-bold">3</span>
                                            What is the shipping cost?
                                        </h3>
                                    </div>
                                    <div class="p-6">
                                        <p class="text-gray-800 leading-relaxed">
                                            Shipping times and costs can be varied based on the items you put on your virtual shopping bag. You can see the estimated shipping fees and times at the checkout.
                                        </p>
                                    </div>
                                </div>

                                <!-- FAQ 4 -->
                                <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-lg border-2 border-orange-200 overflow-hidden">
                                    <div class="bg-gradient-to-r from-orange-500 to-red-500 px-6 py-4">
                                        <h3 class="text-2xl font-bold text-white flex items-center">
                                            <span class="w-10 h-10 bg-white text-orange-600 rounded-full flex items-center justify-center mr-3 font-bold">4</span>
                                            How long will it take to ship my order?
                                        </h3>
                                    </div>
                                    <div class="p-6">
                                        <p class="text-gray-800 leading-relaxed">
                                            Please check shipping times and cost information at checkout or contact our support team for specific shipping estimates.
                                        </p>
                                    </div>
                                </div>

                                <!-- FAQ 5 -->
                                <div class="bg-gradient-to-br from-teal-50 to-cyan-50 rounded-lg border-2 border-teal-200 overflow-hidden">
                                    <div class="bg-gradient-to-r from-teal-500 to-cyan-500 px-6 py-4">
                                        <h3 class="text-2xl font-bold text-white flex items-center">
                                            <span class="w-10 h-10 bg-white text-teal-600 rounded-full flex items-center justify-center mr-3 font-bold">5</span>
                                            What is the status of my order?
                                        </h3>
                                    </div>
                                    <div class="p-6">
                                        <p class="text-gray-800 leading-relaxed">
                                            You can keep track of your order through your account dashboard. Log in to see real-time updates on your order status.
                                        </p>
                                    </div>
                                </div>

                                <!-- FAQ 6 -->
                                <div class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-lg border-2 border-yellow-200 overflow-hidden">
                                    <div class="bg-gradient-to-r from-yellow-500 to-amber-500 px-6 py-4">
                                        <h3 class="text-2xl font-bold text-white flex items-center">
                                            <span class="w-10 h-10 bg-white text-yellow-600 rounded-full flex items-center justify-center mr-3 font-bold">6</span>
                                            My orders are past the estimated delivery time
                                        </h3>
                                    </div>
                                    <div class="p-6">
                                        <p class="text-gray-800 leading-relaxed mb-3">
                                            Orders typically ship within <strong>1-5 days</strong> once you have submitted your order. Once your order has been shipped you can expect it to arrive within <strong>2-15 days</strong>. International orders may take an additional <strong>1-2 weeks</strong>.
                                        </p>
                                        <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 rounded">
                                            <p class="text-yellow-900">
                                                If your order has not arrived within the times stated above, please <a href="mailto:support@bluprinter.com" class="font-semibold underline">contact customer service</a>.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- FAQ 7 -->
                                <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-lg border-2 border-indigo-200 overflow-hidden">
                                    <div class="bg-gradient-to-r from-indigo-500 to-blue-500 px-6 py-4">
                                        <h3 class="text-2xl font-bold text-white flex items-center">
                                            <span class="w-10 h-10 bg-white text-indigo-600 rounded-full flex items-center justify-center mr-3 font-bold">7</span>
                                            Why is my tracking information not working?
                                        </h3>
                                    </div>
                                    <div class="p-6">
                                        <p class="text-gray-800 leading-relaxed mb-3">
                                            Please note that tracking information updates once the order ships and has been picked up and scanned by the postal courier.
                                        </p>
                                        <div class="bg-indigo-100 border-l-4 border-indigo-500 p-4 rounded">
                                            <p class="text-indigo-900">
                                                If you placed your order over <strong>21 days ago</strong> and your tracking information is still not available, please <a href="mailto:support@bluprinter.com" class="font-semibold underline">contact customer support</a>. Be sure to have your order number and email that was used to make the purchase.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- FAQ 8 -->
                                <div class="bg-gradient-to-br from-pink-50 to-rose-50 rounded-lg border-2 border-pink-200 overflow-hidden">
                                    <div class="bg-gradient-to-r from-pink-500 to-rose-500 px-6 py-4">
                                        <h3 class="text-2xl font-bold text-white flex items-center">
                                            <span class="w-10 h-10 bg-white text-pink-600 rounded-full flex items-center justify-center mr-3 font-bold">8</span>
                                            Changes to order
                                        </h3>
                                    </div>
                                    <div class="p-6">
                                        <p class="text-gray-800 leading-relaxed">
                                            Order changes have to be made within <strong class="text-pink-600">4 hours</strong> of first placing the order. If your order is eligible, you can <a href="mailto:support@bluprinter.com" class="text-blue-600 hover:text-blue-800 font-semibold underline">request changes here</a>.
                                        </p>
                                    </div>
                                </div>

                                <!-- FAQ 9 -->
                                <div class="bg-gradient-to-br from-red-50 to-orange-50 rounded-lg border-2 border-red-200 overflow-hidden">
                                    <div class="bg-gradient-to-r from-red-500 to-orange-500 px-6 py-4">
                                        <h3 class="text-2xl font-bold text-white flex items-center">
                                            <span class="w-10 h-10 bg-white text-red-600 rounded-full flex items-center justify-center mr-3 font-bold">9</span>
                                            Order cancellation
                                        </h3>
                                    </div>
                                    <div class="p-6">
                                        <p class="text-gray-800 leading-relaxed">
                                            Order cancellations must be made within <strong class="text-red-600">4 hours</strong> after the order has been placed. If your order qualifies, you can <a href="mailto:support@bluprinter.com" class="text-blue-600 hover:text-blue-800 font-semibold underline">request cancellation here</a>.
                                        </p>
                                    </div>
                                </div>

                                <!-- FAQ 10 -->
                                <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-lg border-2 border-emerald-200 overflow-hidden">
                                    <div class="bg-gradient-to-r from-emerald-500 to-teal-500 px-6 py-4">
                                        <h3 class="text-2xl font-bold text-white flex items-center">
                                            <span class="w-10 h-10 bg-white text-emerald-600 rounded-full flex items-center justify-center mr-3 font-bold">10</span>
                                            Refund or Exchange
                                        </h3>
                                    </div>
                                    <div class="p-6">
                                        <p class="text-gray-800 leading-relaxed">
                                            If your item is missing, materially defective, or incorrect, please <a href="mailto:support@bluprinter.com" class="text-blue-600 hover:text-blue-800 font-semibold underline">contact us here</a>.
                                        </p>
                                    </div>
                                </div>

                                <!-- FAQ 11 -->
                                <div class="bg-gradient-to-br from-violet-50 to-purple-50 rounded-lg border-2 border-violet-200 overflow-hidden">
                                    <div class="bg-gradient-to-r from-violet-500 to-purple-500 px-6 py-4">
                                        <h3 class="text-2xl font-bold text-white flex items-center">
                                            <span class="w-10 h-10 bg-white text-violet-600 rounded-full flex items-center justify-center mr-3 font-bold">11</span>
                                            Didn\'t Receive Confirmation Email
                                        </h3>
                                    </div>
                                    <div class="p-6">
                                        <p class="text-gray-800 leading-relaxed mb-3">
                                            When an order is placed, email is sent to you with your receipt. This email (the confirmation email) also contains your order details.
                                        </p>
                                        <p class="text-gray-700 mb-3 font-semibold">If you did not receive your confirmation email, please follow these steps:</p>
                                        <div class="space-y-2">
                                            <div class="flex items-start bg-white rounded-lg p-3 shadow-sm">
                                                <svg class="w-5 h-5 text-violet-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                                <p class="text-gray-800">Check your spam folder and other email accounts, especially if you checked out with PayPal</p>
                                            </div>
                                            <div class="flex items-start bg-white rounded-lg p-3 shadow-sm">
                                                <svg class="w-5 h-5 text-violet-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                                <p class="text-gray-800">If these don\'t work, please <a href="mailto:support@bluprinter.com" class="text-blue-600 hover:text-blue-800 font-semibold underline">contact us</a></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- FAQ 12 -->
                                <div class="bg-gradient-to-br from-cyan-50 to-blue-50 rounded-lg border-2 border-cyan-200 overflow-hidden">
                                    <div class="bg-gradient-to-r from-cyan-500 to-blue-500 px-6 py-4">
                                        <h3 class="text-2xl font-bold text-white flex items-center">
                                            <span class="w-10 h-10 bg-white text-cyan-600 rounded-full flex items-center justify-center mr-3 font-bold">12</span>
                                            Size Guide
                                        </h3>
                                    </div>
                                    <div class="p-6">
                                        <p class="text-gray-800 leading-relaxed">
                                            Please check the sizing guide for all of the sizing information on different brands and products on each product page.
                                        </p>
                                    </div>
                                </div>

                                <!-- FAQ 13 -->
                                <div class="bg-gradient-to-br from-lime-50 to-green-50 rounded-lg border-2 border-lime-200 overflow-hidden">
                                    <div class="bg-gradient-to-r from-lime-500 to-green-500 px-6 py-4">
                                        <h3 class="text-2xl font-bold text-white flex items-center">
                                            <span class="w-10 h-10 bg-white text-lime-600 rounded-full flex items-center justify-center mr-3 font-bold">13</span>
                                            Will I be charged VAT taxes?
                                        </h3>
                                    </div>
                                    <div class="p-6">
                                        <p class="text-gray-800 leading-relaxed mb-3">
                                            Items shipping internationally from the US are shipped <strong>DDU (Delivered Duty Unpaid)</strong> and we do not collect VAT (Value Added Taxes). All taxes, duties, and customs fees are the responsibility of the recipient of the package.
                                        </p>
                                        <p class="text-gray-800 leading-relaxed mb-3">
                                            Depending on the receiving country, your package may incur local customs or VAT charges. We recommend contacting your local customs office for more information regarding your country\'s customs policies.
                                        </p>
                                        <div class="bg-lime-100 rounded-lg p-4">
                                            <p class="text-lime-900">
                                                <strong>Note:</strong> We do not charge any other taxes on the orders.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- FAQ 14 -->
                                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg border-2 border-blue-200 overflow-hidden">
                                    <div class="bg-gradient-to-r from-blue-500 to-indigo-500 px-6 py-4">
                                        <h3 class="text-2xl font-bold text-white flex items-center">
                                            <span class="w-10 h-10 bg-white text-blue-600 rounded-full flex items-center justify-center mr-3 font-bold">14</span>
                                            How secure is my personal information?
                                        </h3>
                                    </div>
                                    <div class="p-6">
                                        <p class="text-gray-800 leading-relaxed mb-3">
                                            Bluprinter Store adheres to the <strong>highest industry standards</strong> to protect your personal information when you checkout and purchase from our online store.
                                        </p>
                                        <p class="text-gray-800 leading-relaxed mb-3">
                                            Your credit card information is encrypted during transmission using <strong>secure socket layer (SSL) technology</strong>, which is widely used on the Internet for processing payments. Your credit card information is only used to complete the requested transaction and is not subsequently stored.
                                        </p>
                                        <div class="bg-blue-100 border-l-4 border-blue-500 p-4 rounded">
                                            <p class="text-blue-900">
                                                If you need any further assistance, please contact us via email: <a href="mailto:support@bluprinter.com" class="font-semibold underline">support@bluprinter.com</a>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- FAQ 15 -->
                                <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-lg border-2 border-purple-200 overflow-hidden">
                                    <div class="bg-gradient-to-r from-purple-500 to-indigo-500 px-6 py-4">
                                        <h3 class="text-2xl font-bold text-white flex items-center">
                                            <span class="w-10 h-10 bg-white text-purple-600 rounded-full flex items-center justify-center mr-3 font-bold">15</span>
                                            How do I contact customer support?
                                        </h3>
                                    </div>
                                    <div class="p-6">
                                        <p class="text-gray-800 leading-relaxed mb-4">
                                            We are glad to answer any questions that you may have. Please contact customer support:
                                        </p>
                                        <div class="flex flex-col sm:flex-row gap-3">
                                            <a href="mailto:support@bluprinter.com" class="inline-flex items-center justify-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg shadow-md transition duration-200">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                </svg>
                                                support@bluprinter.com
                                            </a>
                                            <a href="/contact-us" class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md transition duration-200">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Contact Page
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Call to Action Footer -->
                            <div class="mt-12 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-lg p-10 text-center text-white">
                                <div class="flex justify-center mb-6">
                                    <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="text-3xl font-bold mb-4">Still Have Questions?</h3>
                                <p class="text-xl text-indigo-100 mb-6 max-w-3xl mx-auto">
                                    Our customer support team is here to help you 24/7. Don\'t hesitate to reach out!
                                </p>
                                <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                                    <a href="mailto:support@bluprinter.com" class="inline-flex items-center px-8 py-4 bg-white text-purple-600 font-bold rounded-lg shadow-xl hover:shadow-2xl transition duration-200 text-lg">
                                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        Email Support
                                    </a>
                                    <a href="/contact-us" class="inline-flex items-center px-8 py-4 bg-transparent border-2 border-white text-white font-bold rounded-lg hover:bg-white hover:text-purple-600 transition duration-200 text-lg">
                                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Contact Us
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>',
                'excerpt' => 'Find answers to frequently asked questions about ordering, shipping, returns, refunds, tracking, and more on Bluprinter.',
                'status' => 'published',
                'published_at' => now(),
                'template' => 'default',
                'show_in_menu' => true,
                'menu_title' => 'FAQs',
                'sort_order' => 9,
                'meta_title' => 'FAQs - Frequently Asked Questions | Bluprinter Help Center',
                'meta_description' => 'Get answers to common questions about ordering, shipping, returns, tracking, payment, and more. Complete FAQ guide for Bluprinter customers.',
            ],
            [
                'user_id' => $admin->id,
                'title' => 'Local Support',
                'slug' => 'local-support',
                'content' => '<div class="max-w-5xl mx-auto py-8 px-4">
                    <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
                        <!-- Hero Header -->
                        <div class="bg-gradient-to-r from-red-600 via-yellow-500 to-red-600 text-white px-8 py-16">
                            <div class="text-center">
                                <div class="flex justify-center mb-6">
                                    <div class="w-24 h-24 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                                        <span class="text-6xl">🇻🇳</span>
                                    </div>
                                </div>
                                <h1 class="text-6xl font-bold mb-4">Local Support</h1>
                                <p class="text-2xl text-red-100">Hỗ Trợ Địa Phương - Vietnam</p>
                                <p class="text-lg text-yellow-100 mt-4 max-w-3xl mx-auto">
                                    Connect with our local team in Ho Chi Minh City for personalized assistance
                                </p>
                            </div>
                        </div>

                        <div class="px-8 py-12">
                            <!-- Introduction -->
                            <div class="bg-gradient-to-br from-red-50 to-yellow-50 border-l-4 border-red-500 rounded-r-lg p-6 mb-10 text-center">
                                <h2 class="text-3xl font-bold text-gray-800 mb-3">Contact Us - Liên Hệ</h2>
                                <p class="text-gray-700 text-lg">
                                    Reach out to our Vietnam support team for local assistance
                                </p>
                            </div>

                            <!-- Contact Methods Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                                <!-- Email -->
                                <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-lg p-8 shadow-xl border-2 border-blue-300 hover:shadow-2xl transition duration-200">
                                    <div class="flex flex-col items-center text-center">
                                        <div class="w-20 h-20 bg-blue-500 rounded-full flex items-center justify-center mb-4 shadow-lg">
                                            <svg class="w-11 h-11 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-2xl font-bold text-gray-800 mb-3">Email Support</h3>
                                        <a href="mailto:support@bluprinter.com" class="text-blue-600 hover:text-blue-800 font-bold text-xl underline break-all">support@bluprinter.com</a>
                                        <p class="text-gray-600 text-sm mt-2">Response within 24 hours</p>
                                    </div>
                                </div>

                                <!-- Phone -->
                                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-8 shadow-xl border-2 border-green-300 hover:shadow-2xl transition duration-200">
                                    <div class="flex flex-col items-center text-center">
                                        <div class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center mb-4 shadow-lg">
                                            <svg class="w-11 h-11 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-2xl font-bold text-gray-800 mb-3">Call Us - Gọi Điện</h3>
                                        <a href="tel:+18563782798" class="text-green-600 hover:text-green-800 font-bold text-2xl">+1 856-378-2798</a>
                                        <p class="text-gray-600 text-sm mt-2">Available during business hours</p>
                                    </div>
                                </div>

                                <!-- iMessage -->
                                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-8 shadow-xl border-2 border-purple-300 hover:shadow-2xl transition duration-200">
                                    <div class="flex flex-col items-center text-center">
                                        <div class="w-20 h-20 bg-purple-500 rounded-full flex items-center justify-center mb-4 shadow-lg">
                                            <svg class="w-11 h-11 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-2xl font-bold text-gray-800 mb-3">iMessage</h3>
                                        <p class="text-purple-600 font-bold text-2xl">+1 856-378-2798</p>
                                        <p class="text-gray-600 text-sm mt-2">For iOS users</p>
                                    </div>
                                </div>

                                <!-- WhatsApp -->
                                <div class="bg-gradient-to-br from-green-50 to-lime-50 rounded-lg p-8 shadow-xl border-2 border-green-300 hover:shadow-2xl transition duration-200">
                                    <div class="flex flex-col items-center text-center">
                                        <div class="w-20 h-20 bg-green-600 rounded-full flex items-center justify-center mb-4 shadow-lg">
                                            <svg class="w-11 h-11 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-2xl font-bold text-gray-800 mb-3">WhatsApp</h3>
                                        <a href="https://wa.me/18563782798" target="_blank" class="text-green-600 hover:text-green-800 font-bold text-2xl">+1 856-378-2798</a>
                                        <p class="text-gray-600 text-sm mt-2">Quick messaging support</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Office Address -->
                            <div class="mb-8">
                                <div class="bg-gradient-to-r from-red-600 to-orange-600 text-white px-6 py-4 rounded-t-lg">
                                    <h2 class="text-3xl font-bold flex items-center justify-center">
                                        <svg class="w-9 h-9 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                        Office Address - Địa Chỉ Văn Phòng
                                    </h2>
                                </div>
                                <div class="bg-gradient-to-br from-red-50 to-orange-50 border-2 border-red-300 border-t-0 rounded-b-lg p-8">
                                    <div class="bg-white rounded-lg p-8 shadow-xl border-l-4 border-red-500">
                                        <div class="flex items-start mb-6">
                                            <div class="flex-shrink-0 w-16 h-16 bg-red-500 rounded-full flex items-center justify-center mr-4 shadow-lg">
                                                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <h3 class="text-2xl font-bold text-gray-800 mb-1 flex items-center">
                                                    <span class="text-3xl mr-2">🇻🇳</span>
                                                    Vietnam Local Office
                                                </h3>
                                                <p class="text-red-600 font-semibold">Ho Chi Minh City</p>
                                            </div>
                                        </div>
                                        
                                        <div class="bg-gradient-to-r from-red-50 to-yellow-50 rounded-lg p-6 border border-red-200">
                                            <div class="text-center md:text-left">
                                                <p class="text-xl text-gray-800 leading-relaxed mb-2">
                                                    <strong class="text-red-700">📍 Address:</strong>
                                                </p>
                                                <p class="text-lg text-gray-700 leading-relaxed">
                                                    24 Thanh Xuan 14 Street<br>
                                                    Thanh Xuan Ward, District 12<br>
                                                    Ho Chi Minh City, Vietnam
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Access Links -->
                            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 border-2 border-indigo-300 rounded-lg p-8 mb-8">
                                <h3 class="text-2xl font-bold text-indigo-900 mb-6 text-center flex items-center justify-center">
                                    <svg class="w-8 h-8 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    Quick Access - Truy Cập Nhanh
                                </h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <a href="mailto:support@bluprinter.com" class="flex items-center bg-white hover:bg-blue-50 rounded-lg p-5 shadow-md border-l-4 border-blue-500 transition duration-200">
                                        <div class="flex-shrink-0 w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mr-4">
                                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-800 text-lg">Send Email</h4>
                                            <p class="text-gray-600 text-sm">Gửi email cho chúng tôi</p>
                                        </div>
                                    </a>

                                    <a href="tel:+18563782798" class="flex items-center bg-white hover:bg-green-50 rounded-lg p-5 shadow-md border-l-4 border-green-500 transition duration-200">
                                        <div class="flex-shrink-0 w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mr-4">
                                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-800 text-lg">Call Now</h4>
                                            <p class="text-gray-600 text-sm">Gọi ngay</p>
                                        </div>
                                    </a>

                                    <a href="sms:+18563782798" class="flex items-center bg-white hover:bg-purple-50 rounded-lg p-5 shadow-md border-l-4 border-purple-500 transition duration-200">
                                        <div class="flex-shrink-0 w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center mr-4">
                                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-800 text-lg">iMessage</h4>
                                            <p class="text-gray-600 text-sm">Nhắn tin qua iMessage</p>
                                        </div>
                                    </a>

                                    <a href="https://wa.me/18563782798" target="_blank" class="flex items-center bg-white hover:bg-green-50 rounded-lg p-5 shadow-md border-l-4 border-green-600 transition duration-200">
                                        <div class="flex-shrink-0 w-12 h-12 bg-green-600 rounded-full flex items-center justify-center mr-4">
                                            <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-800 text-lg">WhatsApp</h4>
                                            <p class="text-gray-600 text-sm">Chat qua WhatsApp</p>
                                        </div>
                                    </a>
                                </div>
                            </div>

                            <!-- Why Choose Local Support -->
                            <div class="bg-gradient-to-br from-yellow-50 to-amber-50 border-l-4 border-yellow-500 rounded-r-lg p-6 mb-8">
                                <h3 class="text-2xl font-bold text-yellow-900 mb-4 flex items-center">
                                    <svg class="w-8 h-8 mr-3 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Why Choose Local Support?
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="bg-white rounded-lg p-4 shadow-sm">
                                        <div class="flex items-start">
                                            <svg class="w-6 h-6 text-yellow-500 mr-2 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <p class="text-gray-800"><strong>Vietnamese Language Support</strong> - Hỗ trợ tiếng Việt</p>
                                        </div>
                                    </div>
                                    <div class="bg-white rounded-lg p-4 shadow-sm">
                                        <div class="flex items-start">
                                            <svg class="w-6 h-6 text-yellow-500 mr-2 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <p class="text-gray-800"><strong>Local Business Hours</strong> - Giờ làm việc địa phương</p>
                                        </div>
                                    </div>
                                    <div class="bg-white rounded-lg p-4 shadow-sm">
                                        <div class="flex items-start">
                                            <svg class="w-6 h-6 text-yellow-500 mr-2 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <p class="text-gray-800"><strong>Faster Response Time</strong> - Phản hồi nhanh hơn</p>
                                        </div>
                                    </div>
                                    <div class="bg-white rounded-lg p-4 shadow-sm">
                                        <div class="flex items-start">
                                            <svg class="w-6 h-6 text-yellow-500 mr-2 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <p class="text-gray-800"><strong>Local Understanding</strong> - Hiểu văn hóa địa phương</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Call to Action Footer -->
                            <div class="bg-gradient-to-r from-red-600 via-yellow-500 to-red-600 rounded-lg p-10 text-center text-white">
                                <div class="flex justify-center mb-6">
                                    <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="text-3xl font-bold mb-4">Sẵn Sàng Hỗ Trợ Bạn!</h3>
                                <p class="text-xl text-yellow-100 mb-6 max-w-3xl mx-auto">
                                    Our local team in Vietnam is ready to assist you in your preferred language. Contact us today!
                                </p>
                                <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                                    <a href="mailto:support@bluprinter.com" class="inline-flex items-center px-8 py-4 bg-white text-red-600 font-bold rounded-lg shadow-xl hover:shadow-2xl transition duration-200 text-lg">
                                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        Email Us
                                    </a>
                                    <a href="https://wa.me/18563782798" target="_blank" class="inline-flex items-center px-8 py-4 bg-transparent border-2 border-white text-white font-bold rounded-lg hover:bg-white hover:text-red-600 transition duration-200 text-lg">
                                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"></path>
                                        </svg>
                                        WhatsApp
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>',
                'excerpt' => 'Local support in Vietnam - Contact our Ho Chi Minh City office via email, phone, iMessage, or WhatsApp for Vietnamese language assistance.',
                'status' => 'published',
                'published_at' => now(),
                'template' => 'default',
                'show_in_menu' => true,
                'menu_title' => 'Local Support',
                'sort_order' => 10,
                'meta_title' => 'Local Support Vietnam - Bluprinter Hỗ Trợ Địa Phương',
                'meta_description' => 'Contact Bluprinter local support team in Ho Chi Minh City, Vietnam. Vietnamese language support via email, phone, iMessage, and WhatsApp.',
            ],
            [
                'user_id' => $admin->id,
                'title' => 'Free Return',
                'slug' => 'free-return',
                'content' => '<div class="max-w-6xl mx-auto py-8 px-4">
                    <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
                        <!-- Hero Header -->
                        <div class="relative bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 text-white px-8 py-16">
                            <div class="absolute inset-0 bg-black opacity-10"></div>
                            <div class="relative z-10 text-center">
                                <div class="flex justify-center mb-6">
                                    <div class="w-32 h-32 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm shadow-2xl">
                                        <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                        </svg>
                                    </div>
                                </div>
                                <h1 class="text-6xl font-bold mb-4">Free Return</h1>
                                <p class="text-3xl text-green-100 mb-6">Returns & Exchanges Policy</p>
                                <div class="flex justify-center mb-4">
                                    <div class="w-24 h-1 bg-white rounded"></div>
                                </div>
                                <p class="text-xl text-emerald-100 max-w-4xl mx-auto leading-relaxed">
                                    Your satisfaction is our priority - Simple and straightforward returns within 30 days
                                </p>
                            </div>
                        </div>

                        <div class="px-8 py-12">
                            <!-- Introduction -->
                            <div class="bg-gradient-to-br from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-r-lg p-8 mb-10">
                                <p class="text-xl text-gray-800 leading-relaxed mb-4">
                                    At <strong class="text-green-600">Bluprinter</strong>, we strive to ensure your satisfaction with every purchase. If you need to return or exchange an item, our policy makes the process simple and straightforward.
                                </p>
                                <div class="bg-white rounded-lg p-6 shadow-md">
                                    <p class="text-gray-800 leading-relaxed">
                                        If you need assistance, please contact us at <a href="mailto:support@bluprinter.com" class="text-blue-600 hover:text-blue-800 font-semibold underline">support@bluprinter.com</a>
                                    </p>
                                </div>
                            </div>

                            <!-- No Restocking Fee -->
                            <div class="bg-gradient-to-r from-green-100 to-emerald-100 border-2 border-green-500 rounded-lg p-8 mb-10">
                                <div class="flex items-center justify-center">
                                    <div class="text-center">
                                        <div class="flex justify-center mb-4">
                                            <div class="w-24 h-24 bg-green-500 rounded-full flex items-center justify-center shadow-xl">
                                                <svg class="w-14 h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <h2 class="text-4xl font-bold text-green-900 mb-3">Restocking Fee</h2>
                                        <p class="text-6xl font-bold text-green-600 mb-2">NO FEE</p>
                                        <p class="text-gray-700 text-lg">We do not charge any restocking fees for returns or exchanges</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Eligible Reasons Section -->
                            <div class="mb-10">
                                <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-5 rounded-t-lg">
                                    <h2 class="text-4xl font-bold text-center">Eligible Reasons for Return</h2>
                                    <p class="text-center text-purple-100 mt-2 text-lg">Returns and exchanges are only permitted under the following conditions:</p>
                                </div>

                                <div class="space-y-6 mt-6">
                                    <!-- Reason A -->
                                    <div class="bg-gradient-to-br from-red-50 to-rose-50 border-2 border-red-300 rounded-lg overflow-hidden">
                                        <div class="bg-gradient-to-r from-red-500 to-rose-500 px-6 py-4">
                                            <div class="flex items-center">
                                                <span class="w-12 h-12 bg-white text-red-600 rounded-full flex items-center justify-center mr-3 text-2xl font-bold shadow-lg">A</span>
                                                <h3 class="text-2xl font-bold text-white">Wrong, Damaged, or Faulty Items</h3>
                                            </div>
                                        </div>
                                        <div class="p-6">
                                            <p class="text-gray-800 font-semibold mb-4 text-lg">We will fully support returns or exchanges for items that:</p>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div class="bg-white rounded-lg p-4 shadow-sm border-l-4 border-red-500">
                                                    <div class="flex items-start">
                                                        <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                        <p class="text-gray-800">Do not match the product description (wrong item, incorrect material, wrong size)</p>
                                                    </div>
                                                </div>
                                                <div class="bg-white rounded-lg p-4 shadow-sm border-l-4 border-red-500">
                                                    <div class="flex items-start">
                                                        <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                        <p class="text-gray-800">Are received torn, dirty, wet, or with defective/hairy fabric</p>
                                                    </div>
                                                </div>
                                                <div class="bg-white rounded-lg p-4 shadow-sm border-l-4 border-red-500">
                                                    <div class="flex items-start">
                                                        <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                        <p class="text-gray-800">Have visible print defects, such as blurring or incorrect placement</p>
                                                    </div>
                                                </div>
                                                <div class="bg-white rounded-lg p-4 shadow-sm border-l-4 border-red-500">
                                                    <div class="flex items-start">
                                                        <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                        <p class="text-gray-800">Show damage, such as peeling prints, after the first wash</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reason B -->
                                    <div class="bg-gradient-to-br from-orange-50 to-amber-50 border-2 border-orange-300 rounded-lg overflow-hidden">
                                        <div class="bg-gradient-to-r from-orange-500 to-amber-500 px-6 py-4">
                                            <div class="flex items-center">
                                                <span class="w-12 h-12 bg-white text-orange-600 rounded-full flex items-center justify-center mr-3 text-2xl font-bold shadow-lg">B</span>
                                                <h3 class="text-2xl font-bold text-white">Incorrect Size</h3>
                                            </div>
                                        </div>
                                        <div class="p-6">
                                            <div class="bg-white rounded-lg p-5 shadow-md border border-orange-200">
                                                <p class="text-gray-800 leading-relaxed">
                                                    If the product you received does not match the size guide <strong class="text-orange-600">(a discrepancy of more than 1.5")</strong> from standard measurements, we will assist you with the return or exchange.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reason C -->
                                    <div class="bg-gradient-to-br from-yellow-50 to-amber-50 border-2 border-yellow-300 rounded-lg overflow-hidden">
                                        <div class="bg-gradient-to-r from-yellow-500 to-amber-500 px-6 py-4">
                                            <div class="flex items-center">
                                                <span class="w-12 h-12 bg-white text-yellow-600 rounded-full flex items-center justify-center mr-3 text-2xl font-bold shadow-lg">C</span>
                                                <h3 class="text-2xl font-bold text-white">Non-Fitting Items</h3>
                                            </div>
                                        </div>
                                        <div class="p-6">
                                            <div class="bg-white rounded-lg p-5 shadow-md border border-yellow-200">
                                                <p class="text-gray-800 leading-relaxed mb-2">
                                                    Returns or exchanges are accepted for non-fitting items.
                                                </p>
                                                <div class="bg-yellow-100 border border-yellow-300 rounded-lg p-3">
                                                    <p class="text-yellow-900 font-semibold">
                                                        <strong>⚠️ Important:</strong> For <strong>t-shirts and tank tops only</strong>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reason D -->
                                    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 border-2 border-blue-300 rounded-lg overflow-hidden">
                                        <div class="bg-gradient-to-r from-blue-500 to-cyan-500 px-6 py-4">
                                            <div class="flex items-center">
                                                <span class="w-12 h-12 bg-white text-blue-600 rounded-full flex items-center justify-center mr-3 text-2xl font-bold shadow-lg">D</span>
                                                <h3 class="text-2xl font-bold text-white">Damage Caused During Shipping</h3>
                                            </div>
                                        </div>
                                        <div class="p-6">
                                            <p class="text-gray-800 leading-relaxed mb-4">
                                                If your package arrives damaged or contains the wrong items due to shipping issues, please:
                                            </p>
                                            <div class="space-y-3">
                                                <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                                    <svg class="w-6 h-6 text-blue-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <p class="text-gray-800">Check the package carefully upon delivery</p>
                                                </div>
                                                <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                                    <svg class="w-6 h-6 text-blue-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <p class="text-gray-800">Immediately return defective items to the courier, or</p>
                                                </div>
                                                <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                                    <svg class="w-6 h-6 text-blue-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <p class="text-gray-800">Contact us within <strong>30 days</strong> for prompt support</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Eligibility Criteria -->
                            <div class="mb-10">
                                <div class="bg-gradient-to-r from-teal-600 to-cyan-600 text-white px-6 py-5 rounded-t-lg">
                                    <h2 class="text-4xl font-bold text-center flex items-center justify-center">
                                        <svg class="w-9 h-9 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Eligibility for Return or Exchange
                                    </h2>
                                </div>
                                <div class="bg-teal-50 border-2 border-teal-300 border-t-0 rounded-b-lg p-8">
                                    <p class="text-gray-800 font-semibold mb-6 text-lg text-center">To qualify for a return or exchange, the following conditions must be met:</p>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                        <div class="bg-white rounded-lg p-6 shadow-lg border-2 border-teal-200">
                                            <div class="flex justify-center mb-4">
                                                <div class="w-14 h-14 bg-teal-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <p class="text-gray-800 text-center">The product falls under one of the <strong>eligible reasons</strong> listed above</p>
                                        </div>
                                        
                                        <div class="bg-white rounded-lg p-6 shadow-lg border-2 border-teal-200">
                                            <div class="flex justify-center mb-4">
                                                <div class="w-14 h-14 bg-teal-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <p class="text-gray-800 text-center">The item shows <strong>no signs of use</strong>, retains neck label, and is in original packaging</p>
                                        </div>
                                        
                                        <div class="bg-white rounded-lg p-6 shadow-lg border-2 border-teal-200">
                                            <div class="flex justify-center mb-4">
                                                <div class="w-14 h-14 bg-teal-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <p class="text-gray-800 text-center">Request submitted within <strong>30 days</strong> from delivery date</p>
                                        </div>
                                    </div>

                                    <div class="mt-6 bg-blue-100 border-l-4 border-blue-500 rounded-r-lg p-5">
                                        <p class="text-blue-900 font-semibold text-lg">
                                            🌍 For orders shipped outside the US: Defective or unwanted items are supported within <strong>60 days</strong> of delivery
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Return Process -->
                            <div class="mb-10">
                                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-5 rounded-t-lg">
                                    <h2 class="text-4xl font-bold text-center flex items-center justify-center">
                                        <svg class="w-9 h-9 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                        </svg>
                                        Return Process
                                    </h2>
                                    <p class="text-center text-indigo-100 mt-2 text-lg">Follow these simple steps for a smooth return experience</p>
                                </div>
                                <div class="bg-indigo-50 border-2 border-indigo-300 border-t-0 rounded-b-lg p-8">
                                    <div class="space-y-6">
                                        <!-- Step 1 -->
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-500 text-white rounded-full flex items-center justify-center mr-5 text-3xl font-bold shadow-xl">1</div>
                                            <div class="flex-1 bg-white rounded-lg p-6 shadow-lg border-2 border-indigo-200">
                                                <h3 class="text-2xl font-bold text-indigo-900 mb-3">Verify Eligibility</h3>
                                                <p class="text-gray-800 leading-relaxed">
                                                    Before initiating a return, ensure your item meets the eligibility criteria outlined above.
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Step 2 -->
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 w-20 h-20 bg-gradient-to-br from-purple-500 to-pink-500 text-white rounded-full flex items-center justify-center mr-5 text-3xl font-bold shadow-xl">2</div>
                                            <div class="flex-1 bg-white rounded-lg p-6 shadow-lg border-2 border-purple-200">
                                                <h3 class="text-2xl font-bold text-purple-900 mb-3">Contact Us</h3>
                                                <p class="text-gray-800 mb-4">Reach out via email at <a href="mailto:support@bluprinter.com" class="text-blue-600 hover:text-blue-800 font-semibold underline">support@bluprinter.com</a></p>
                                                <p class="text-gray-700 font-semibold mb-3">Provide the following information:</p>
                                                <div class="space-y-2">
                                                    <div class="flex items-start bg-purple-50 rounded-lg p-3">
                                                        <svg class="w-5 h-5 text-purple-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        <p class="text-gray-800"><strong>Order Details:</strong> Include your order number and relevant information</p>
                                                    </div>
                                                    <div class="flex items-start bg-purple-50 rounded-lg p-3">
                                                        <svg class="w-5 h-5 text-purple-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        <p class="text-gray-800"><strong>Photos of Packaging Label:</strong> Clearly show the shipping label</p>
                                                    </div>
                                                    <div class="flex items-start bg-purple-50 rounded-lg p-3">
                                                        <svg class="w-5 h-5 text-purple-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        <p class="text-gray-800"><strong>Photos of the Issue:</strong> Highlight damaged, faulty, or incorrect aspects</p>
                                                    </div>
                                                    <div class="flex items-start bg-purple-50 rounded-lg p-3">
                                                        <svg class="w-5 h-5 text-purple-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        <p class="text-gray-800">For wrong-sized items: Include photos of actual measurements (width and length)</p>
                                                    </div>
                                                    <div class="flex items-start bg-purple-50 rounded-lg p-3">
                                                        <svg class="w-5 h-5 text-purple-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        <p class="text-gray-800"><strong>Replacement Details:</strong> Specify the item you wish to receive</p>
                                                    </div>
                                                </div>
                                                <div class="mt-4 bg-pink-100 border border-pink-300 rounded-lg p-3">
                                                    <p class="text-pink-900 text-sm">
                                                        <strong>📸 Note:</strong> For multiple items, provide photos/videos of all items placed side by side on a flat surface
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Step 3 -->
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 w-20 h-20 bg-gradient-to-br from-pink-500 to-rose-500 text-white rounded-full flex items-center justify-center mr-5 text-3xl font-bold shadow-xl">3</div>
                                            <div class="flex-1 bg-white rounded-lg p-6 shadow-lg border-2 border-pink-200">
                                                <h3 class="text-2xl font-bold text-pink-900 mb-3">Verification & Resolution</h3>
                                                <p class="text-gray-800 mb-4 leading-relaxed">
                                                    Once we verify your claim, we will issue a refund or resend the replacement to your address within <strong class="text-pink-600">7 business days</strong>.
                                                </p>
                                                <div class="bg-green-100 border-2 border-green-400 rounded-lg p-5">
                                                    <div class="space-y-3">
                                                        <p class="text-green-900 font-semibold text-lg flex items-center">
                                                            <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                            You do NOT need to return the original package unless instructed
                                                        </p>
                                                        <p class="text-green-900 font-semibold text-lg flex items-center">
                                                            <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                            Replacements will be of equal or higher value
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Important Notes -->
                            <div class="bg-gradient-to-br from-amber-100 to-orange-100 border-2 border-amber-500 rounded-lg p-8 mb-10">
                                <h3 class="text-3xl font-bold text-amber-900 mb-6 flex items-center justify-center">
                                    <svg class="w-9 h-9 mr-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    Important Notes
                                </h3>
                                <div class="space-y-4">
                                    <div class="bg-white rounded-lg p-5 shadow-md border-l-4 border-amber-500">
                                        <p class="text-gray-800 leading-relaxed">
                                            <strong class="text-amber-800">⚠️</strong> If the replacement costs more, you will need to pay the difference
                                        </p>
                                    </div>
                                    <div class="bg-white rounded-lg p-5 shadow-md border-l-4 border-red-500">
                                        <p class="text-gray-800 leading-relaxed">
                                            <strong class="text-red-800">⚠️</strong> Products returned <strong>without verification are ineligible</strong> for support
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Call to Action Footer -->
                            <div class="bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 rounded-lg p-10 text-center text-white">
                                <div class="flex justify-center mb-6">
                                    <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="text-3xl font-bold mb-4">Need Help with a Return?</h3>
                                <p class="text-xl text-green-100 mb-6 max-w-3xl mx-auto">
                                    If you have any questions or need assistance with your return or exchange, our support team is here to help!
                                </p>
                                <a href="mailto:support@bluprinter.com" class="inline-flex items-center px-10 py-4 bg-white text-green-600 font-bold rounded-lg shadow-xl hover:shadow-2xl transition duration-200 text-xl">
                                    <svg class="w-7 h-7 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    Contact Support Team
                                </a>
                            </div>
                        </div>
                    </div>
                </div>',
                'excerpt' => 'Free returns within 30 days - No restocking fee. Easy return process for wrong, damaged, incorrect size, or non-fitting items.',
                'status' => 'published',
                'published_at' => now(),
                'template' => 'default',
                'show_in_menu' => true,
                'menu_title' => 'Free Return',
                'sort_order' => 11,
                'meta_title' => 'Free Return - Bluprinter Easy Returns & Exchanges',
                'meta_description' => 'Free returns and exchanges within 30 days. No restocking fee. Simple return process for wrong, damaged, or non-fitting items on Bluprinter.',
            ],
            [
                'user_id' => $admin->id,
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '<div class="max-w-6xl mx-auto py-8 px-4">
                    <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
                        <!-- Hero Header -->
                        <div class="relative bg-gradient-to-r from-blue-700 via-indigo-700 to-purple-700 text-white px-8 py-16">
                            <div class="absolute inset-0 bg-black opacity-10"></div>
                            <div class="relative z-10 text-center">
                                <div class="flex justify-center mb-6">
                                    <div class="w-32 h-32 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm shadow-2xl">
                                        <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <h1 class="text-6xl font-bold mb-4">Privacy Policy</h1>
                                <p class="text-2xl text-blue-100">Your Privacy is Our Priority</p>
                                <p class="text-lg text-indigo-100 mt-4 max-w-4xl mx-auto">
                                    Last updated: ' . now()->format('F d, Y') . '
                                </p>
                            </div>
                        </div>

                        <div class="px-8 py-12">
                            <!-- Introduction -->
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-l-4 border-blue-500 rounded-r-lg p-8 mb-10">
                                <p class="text-xl text-gray-800 leading-relaxed mb-6">
                                    At <strong class="text-blue-600">Bluprinter</strong>, accessible at <strong>www.bluprinter.com</strong>, we are committed to protecting your personal information and your right to privacy. If you have any questions or concerns about this privacy policy or our practices regarding your personal data, please contact our external data protection officer:
                                </p>
                                <div class="bg-white rounded-lg p-6 shadow-lg grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-gray-800"><strong>📍 Address:</strong></p>
                                        <p class="text-gray-700">24 Thanh Xuan, Thanh Xuan Street<br>12 District, Ho Chi Minh City, Vietnam</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-800"><strong>📧 Email:</strong></p>
                                        <a href="mailto:support@bluprinter.com" class="text-blue-600 hover:text-blue-800 font-semibold underline text-lg">support@bluprinter.com</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 1: Information We Collect -->
                            <div class="mb-10">
                                <div class="bg-gradient-to-r from-cyan-600 to-blue-600 text-white px-6 py-5 rounded-t-lg">
                                    <h2 class="text-4xl font-bold flex items-center">
                                        <span class="w-12 h-12 bg-white text-cyan-600 rounded-full flex items-center justify-center mr-3 text-2xl font-bold">1</span>
                                        Information We Collect
                                    </h2>
                                </div>
                                <div class="bg-cyan-50 border-2 border-cyan-300 border-t-0 rounded-b-lg p-6">
                                    <p class="text-gray-800 mb-6 text-lg">When you use Bluprinter, we may collect the following types of information:</p>
                                    
                                    <div class="space-y-4">
                                        <div class="bg-white rounded-lg p-5 shadow-md border-l-4 border-blue-500">
                                            <h4 class="font-bold text-blue-900 text-lg mb-2">1.1. Personal Identifiable Information (PII)</h4>
                                            <ul class="space-y-2 text-gray-700">
                                                <li>• <strong>Account Information:</strong> Name, email, phone number, password</li>
                                                <li>• <strong>Order Information:</strong> Billing and shipping address, order details</li>
                                                <li>• <strong>Communication Information:</strong> Name, email, message content</li>
                                            </ul>
                                        </div>
                                        
                                        <div class="bg-white rounded-lg p-5 shadow-md border-l-4 border-green-500">
                                            <h4 class="font-bold text-green-900 text-lg mb-2">1.2. Payment Information</h4>
                                            <p class="text-gray-700">Credit/debit card details handled securely by third-party payment providers (not stored on our servers)</p>
                                        </div>
                                        
                                        <div class="bg-white rounded-lg p-5 shadow-md border-l-4 border-purple-500">
                                            <h4 class="font-bold text-purple-900 text-lg mb-2">1.3. Automatically Collected Information</h4>
                                            <ul class="space-y-2 text-gray-700">
                                                <li>• Device information (type, OS, browser)</li>
                                                <li>• Usage information (pages visited, time spent)</li>
                                                <li>• IP Address for location-based content</li>
                                                <li>• Cookies and tracking data</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: How We Use Your Information -->
                            <div class="mb-10">
                                <div class="bg-gradient-to-r from-green-600 to-emerald-600 text-white px-6 py-5 rounded-t-lg">
                                    <h2 class="text-4xl font-bold flex items-center">
                                        <span class="w-12 h-12 bg-white text-green-600 rounded-full flex items-center justify-center mr-3 text-2xl font-bold">2</span>
                                        How We Use Your Information
                                    </h2>
                                </div>
                                <div class="bg-green-50 border-2 border-green-300 border-t-0 rounded-b-lg p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="bg-white rounded-lg p-4 shadow-sm border-l-4 border-green-500">
                                            <h4 class="font-bold text-green-800 mb-2">✓ Provide Services</h4>
                                            <p class="text-gray-700 text-sm">Process orders, manage accounts, handle communications</p>
                                        </div>
                                        <div class="bg-white rounded-lg p-4 shadow-sm border-l-4 border-blue-500">
                                            <h4 class="font-bold text-blue-800 mb-2">✓ Improve Experience</h4>
                                            <p class="text-gray-700 text-sm">Personalize content, optimize functionality</p>
                                        </div>
                                        <div class="bg-white rounded-lg p-4 shadow-sm border-l-4 border-purple-500">
                                            <h4 class="font-bold text-purple-800 mb-2">✓ Process Payments</h4>
                                            <p class="text-gray-700 text-sm">Secure transactions, prevent fraud</p>
                                        </div>
                                        <div class="bg-white rounded-lg p-4 shadow-sm border-l-4 border-cyan-500">
                                            <h4 class="font-bold text-cyan-800 mb-2">✓ Communicate</h4>
                                            <p class="text-gray-700 text-sm">Updates, support, marketing (if opted in)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 3: Sharing Your Information -->
                            <div class="mb-10">
                                <div class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-5 rounded-t-lg">
                                    <h2 class="text-4xl font-bold flex items-center">
                                        <span class="w-12 h-12 bg-white text-purple-600 rounded-full flex items-center justify-center mr-3 text-2xl font-bold">3</span>
                                        Sharing Your Information
                                    </h2>
                                </div>
                                <div class="bg-purple-50 border-2 border-purple-300 border-t-0 rounded-b-lg p-6">
                                    <div class="bg-red-100 border-l-4 border-red-500 p-5 rounded-lg mb-6">
                                        <p class="text-red-900 font-semibold text-lg">
                                            <strong>Important:</strong> We do NOT sell, rent, or trade your personal information to third parties.
                                        </p>
                                    </div>
                                    <p class="text-gray-800 mb-4 font-semibold">Your information may be shared with:</p>
                                    <div class="space-y-3">
                                        <div class="bg-white rounded-lg p-4 shadow-sm">
                                            <p class="text-gray-800"><strong class="text-purple-600">Service Providers:</strong> Payment processors, shipping companies, analytics partners</p>
                                        </div>
                                        <div class="bg-white rounded-lg p-4 shadow-sm">
                                            <p class="text-gray-800"><strong class="text-purple-600">Legal Requirements:</strong> When required by law or to protect rights</p>
                                        </div>
                                        <div class="bg-white rounded-lg p-4 shadow-sm">
                                            <p class="text-gray-800"><strong class="text-purple-600">Business Transfers:</strong> In case of merger, acquisition, or sale</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 4: Data Retention -->
                            <div class="mb-10">
                                <div class="bg-gradient-to-r from-orange-600 to-red-600 text-white px-6 py-5 rounded-t-lg">
                                    <h2 class="text-4xl font-bold flex items-center">
                                        <span class="w-12 h-12 bg-white text-orange-600 rounded-full flex items-center justify-center mr-3 text-2xl font-bold">4</span>
                                        Data Retention
                                    </h2>
                                </div>
                                <div class="bg-orange-50 border-2 border-orange-300 border-t-0 rounded-b-lg p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="bg-white rounded-lg p-5 shadow-md">
                                            <h4 class="font-bold text-orange-800 mb-2">Account Data</h4>
                                            <p class="text-gray-700 text-sm">Retained while account is active</p>
                                        </div>
                                        <div class="bg-white rounded-lg p-5 shadow-md">
                                            <h4 class="font-bold text-orange-800 mb-2">Order Data</h4>
                                            <p class="text-gray-700 text-sm">Minimum 7 years for legal compliance</p>
                                        </div>
                                        <div class="bg-white rounded-lg p-5 shadow-md">
                                            <h4 class="font-bold text-orange-800 mb-2">Communication Data</h4>
                                            <p class="text-gray-700 text-sm">As needed for support and quality assurance</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 5: Your Rights -->
                            <div class="mb-10">
                                <div class="bg-gradient-to-r from-indigo-600 to-blue-600 text-white px-6 py-5 rounded-t-lg">
                                    <h2 class="text-4xl font-bold flex items-center">
                                        <span class="w-12 h-12 bg-white text-indigo-600 rounded-full flex items-center justify-center mr-3 text-2xl font-bold">5</span>
                                        Your Rights
                                    </h2>
                                </div>
                                <div class="bg-indigo-50 border-2 border-indigo-300 border-t-0 rounded-b-lg p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="bg-white rounded-lg p-4 shadow-md border-l-4 border-indigo-500">
                                            <h4 class="font-bold text-indigo-800 mb-2">Right to Access</h4>
                                            <p class="text-gray-700 text-sm">Request access to your personal data</p>
                                        </div>
                                        <div class="bg-white rounded-lg p-4 shadow-md border-l-4 border-blue-500">
                                            <h4 class="font-bold text-blue-800 mb-2">Right to Rectification</h4>
                                            <p class="text-gray-700 text-sm">Correct inaccurate or incomplete data</p>
                                        </div>
                                        <div class="bg-white rounded-lg p-4 shadow-md border-l-4 border-purple-500">
                                            <h4 class="font-bold text-purple-800 mb-2">Right to Deletion</h4>
                                            <p class="text-gray-700 text-sm">Request removal of your data</p>
                                        </div>
                                        <div class="bg-white rounded-lg p-4 shadow-md border-l-4 border-cyan-500">
                                            <h4 class="font-bold text-cyan-800 mb-2">Right to Portability</h4>
                                            <p class="text-gray-700 text-sm">Receive data in machine-readable format</p>
                                        </div>
                                        <div class="bg-white rounded-lg p-4 shadow-md border-l-4 border-green-500">
                                            <h4 class="font-bold text-green-800 mb-2">Right to Object</h4>
                                            <p class="text-gray-700 text-sm">Object to processing for marketing</p>
                                        </div>
                                        <div class="bg-white rounded-lg p-4 shadow-md border-l-4 border-pink-500">
                                            <h4 class="font-bold text-pink-800 mb-2">Right to Withdraw Consent</h4>
                                            <p class="text-gray-700 text-sm">Withdraw consent at any time</p>
                                        </div>
                                    </div>
                                    <div class="mt-6 bg-blue-100 border-l-4 border-blue-500 p-5 rounded-r-lg">
                                        <p class="text-blue-900 font-semibold">We aim to respond to all legitimate requests within <strong>30 days</strong></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 6: Security -->
                            <div class="mb-10">
                                <div class="bg-gradient-to-r from-red-600 to-rose-600 text-white px-6 py-5 rounded-t-lg">
                                    <h2 class="text-4xl font-bold flex items-center">
                                        <span class="w-12 h-12 bg-white text-red-600 rounded-full flex items-center justify-center mr-3 text-2xl font-bold">6</span>
                                        Security of Your Information
                                    </h2>
                                </div>
                                <div class="bg-red-50 border-2 border-red-300 border-t-0 rounded-b-lg p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="bg-white rounded-lg p-5 shadow-lg border-2 border-blue-200">
                                            <div class="flex justify-center mb-3">
                                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <h4 class="font-bold text-gray-800 text-center mb-2">Technical Measures</h4>
                                            <p class="text-gray-700 text-sm text-center">SSL encryption, secure servers, firewalls</p>
                                        </div>
                                        
                                        <div class="bg-white rounded-lg p-5 shadow-lg border-2 border-green-200">
                                            <div class="flex justify-center mb-3">
                                                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <h4 class="font-bold text-gray-800 text-center mb-2">Administrative Measures</h4>
                                            <p class="text-gray-700 text-sm text-center">Access control, employee training, audits</p>
                                        </div>
                                        
                                        <div class="bg-white rounded-lg p-5 shadow-lg border-2 border-purple-200">
                                            <div class="flex justify-center mb-3">
                                                <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <h4 class="font-bold text-gray-800 text-center mb-2">Physical Measures</h4>
                                            <p class="text-gray-700 text-sm text-center">Secure facilities with access control</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 7: Cookies -->
                            <div class="mb-10">
                                <div class="bg-gradient-to-r from-yellow-600 to-amber-600 text-white px-6 py-5 rounded-t-lg">
                                    <h2 class="text-4xl font-bold flex items-center">
                                        <span class="w-12 h-12 bg-white text-yellow-600 rounded-full flex items-center justify-center mr-3 text-2xl font-bold">7</span>
                                        Cookies and Tracking Technologies
                                    </h2>
                                </div>
                                <div class="bg-yellow-50 border-2 border-yellow-300 border-t-0 rounded-b-lg p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="bg-white rounded-lg p-4 shadow-md border-l-4 border-blue-500">
                                            <h4 class="font-bold text-blue-800 mb-2">Essential Cookies</h4>
                                            <p class="text-gray-700 text-sm">Required for website functionality, secure login, order processing</p>
                                        </div>
                                        <div class="bg-white rounded-lg p-4 shadow-md border-l-4 border-green-500">
                                            <h4 class="font-bold text-green-800 mb-2">Performance Cookies</h4>
                                            <p class="text-gray-700 text-sm">Analyze visitor interactions and improve performance</p>
                                        </div>
                                        <div class="bg-white rounded-lg p-4 shadow-md border-l-4 border-purple-500">
                                            <h4 class="font-bold text-purple-800 mb-2">Functionality Cookies</h4>
                                            <p class="text-gray-700 text-sm">Remember preferences like language and cart items</p>
                                        </div>
                                        <div class="bg-white rounded-lg p-4 shadow-md border-l-4 border-orange-500">
                                            <h4 class="font-bold text-orange-800 mb-2">Advertising Cookies</h4>
                                            <p class="text-gray-700 text-sm">Deliver relevant ads based on browsing activity</p>
                                        </div>
                                    </div>
                                    <div class="mt-6 bg-yellow-100 border border-yellow-300 rounded-lg p-4">
                                        <p class="text-yellow-900 text-sm">You can manage cookies through your browser settings. Disabling cookies may affect website functionality.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 8: Updates -->
                            <div class="mb-10">
                                <div class="bg-gradient-to-r from-teal-600 to-cyan-600 text-white px-6 py-5 rounded-t-lg">
                                    <h2 class="text-4xl font-bold flex items-center">
                                        <span class="w-12 h-12 bg-white text-teal-600 rounded-full flex items-center justify-center mr-3 text-2xl font-bold">8</span>
                                        Updates to This Privacy Policy
                                    </h2>
                                </div>
                                <div class="bg-teal-50 border-2 border-teal-300 border-t-0 rounded-b-lg p-6">
                                    <div class="space-y-4">
                                        <div class="bg-white rounded-lg p-5 shadow-md">
                                            <h4 class="font-bold text-teal-800 mb-2">SMS Abandoned Cart</h4>
                                            <p class="text-gray-700">We may send SMS reminders about items in your cart if you\'ve opted in. You can unsubscribe anytime.</p>
                                        </div>
                                        <div class="bg-white rounded-lg p-5 shadow-md">
                                            <h4 class="font-bold text-teal-800 mb-2">Location-Based Services</h4>
                                            <p class="text-gray-700">We may collect location data to recommend nearby services and provide accurate shipping information.</p>
                                        </div>
                                        <div class="bg-white rounded-lg p-5 shadow-md">
                                            <h4 class="font-bold text-teal-800 mb-2">Notification of Changes</h4>
                                            <p class="text-gray-700">We\'ll notify you of updates via website posting or email to active account holders.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Section -->
                            <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-purple-700 rounded-lg p-10 text-center text-white">
                                <div class="flex justify-center mb-6">
                                    <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="text-3xl font-bold mb-4">Questions About Privacy?</h3>
                                <p class="text-xl text-blue-100 mb-6 max-w-3xl mx-auto">
                                    If you have questions about this Privacy Policy, please contact us:
                                </p>
                                <div class="bg-white bg-opacity-10 rounded-lg p-6 mb-6 max-w-3xl mx-auto">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
                                        <div>
                                            <p class="text-white font-semibold mb-2">📧 Email:</p>
                                            <a href="mailto:support@bluprinter.com" class="text-blue-200 hover:text-white underline text-lg">support@bluprinter.com</a>
                                        </div>
                                        <div>
                                            <p class="text-white font-semibold mb-2">📍 Address:</p>
                                            <p class="text-blue-100 text-sm">63/9Đ Ap Chanh, Tan Xuan<br>Hoc Mon, Ho Chi Minh City<br>700000, Vietnam</p>
                                        </div>
                                    </div>
                                </div>
                                <a href="mailto:support@bluprinter.com" class="inline-flex items-center px-10 py-4 bg-white text-indigo-600 font-bold rounded-lg shadow-xl hover:shadow-2xl transition duration-200 text-xl">
                                    <svg class="w-7 h-7 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    Contact Us
                                </a>
                            </div>
                        </div>
                    </div>
                </div>',
                'excerpt' => 'Complete privacy policy - Learn how we collect, use, and protect your personal information. Your rights, data security, cookies, and more.',
                'status' => 'published',
                'published_at' => now(),
                'template' => 'default',
                'show_in_menu' => true,
                'menu_title' => 'Privacy Policy',
                'sort_order' => 12,
                'meta_title' => 'Privacy Policy - Bluprinter Data Protection & Security',
                'meta_description' => 'Read our complete privacy policy. Learn how Bluprinter collects, uses, and protects your personal information. Your rights, data security, and cookie policy.',
            ],
            [
                'user_id' => $admin->id,
                'title' => 'Shipping & Delivery',
                'slug' => 'shipping-delivery',
                'content' => '<div class="max-w-7xl mx-auto py-8 px-4">
                    <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
                        <!-- Hero Header -->
                        <div class="relative bg-gradient-to-r from-blue-600 via-cyan-600 to-teal-600 text-white px-8 py-16">
                            <div class="absolute inset-0 bg-black opacity-10"></div>
                            <div class="relative z-10 text-center">
                                <div class="flex justify-center mb-6">
                                    <div class="w-32 h-32 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm shadow-2xl">
                                        <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                        </svg>
                                    </div>
                                </div>
                                <h1 class="text-6xl font-bold mb-4">Shipping & Delivery</h1>
                                <p class="text-2xl text-blue-100">Fast, Reliable Worldwide Shipping</p>
                                <p class="text-lg text-cyan-100 mt-4">Updated: Jan 3, 2025</p>
                            </div>
                        </div>

                        <div class="px-8 py-12">
                            <!-- Processing Notice -->
                            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 border-l-4 border-blue-500 rounded-r-lg p-8 mb-10">
                                <p class="text-xl text-gray-800 leading-relaxed text-center">
                                    Your product will enter the <strong class="text-blue-600">processing stage</strong> as soon as your order is placed.
                                </p>
                            </div>

                            <!-- Timeline Factors -->
                            <div class="mb-12">
                                <h2 class="text-4xl font-bold text-gray-800 text-center mb-8">Delivery Timeline</h2>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-8 shadow-xl border-2 border-purple-300">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center mr-4 shadow-lg">
                                                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="text-2xl font-bold text-purple-900 mb-3">Processing Time</h3>
                                                <p class="text-gray-800 leading-relaxed">
                                                    After your payment is confirmed, your order will enter the processing stage and usually takes <strong class="text-purple-600">1 - 7 days</strong> depending on the product you purchase.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-8 shadow-xl border-2 border-green-300">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-500 rounded-full flex items-center justify-center mr-4 shadow-lg">
                                                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="text-2xl font-bold text-green-900 mb-3">Shipping Time</h3>
                                                <p class="text-gray-800 leading-relaxed">
                                                    Once the processing is complete, your order will be shipped and will take a few more days to reach your address.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Delivery Times Table -->
                            <div class="mb-12">
                                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-5 rounded-t-lg">
                                    <h2 class="text-3xl font-bold text-center">Delivery Times by Product</h2>
                                </div>
                                <div class="bg-white border-2 border-indigo-300 border-t-0 rounded-b-lg overflow-x-auto">
                                    <table class="w-full">
                                        <thead class="bg-indigo-100">
                                            <tr>
                                                <th class="px-4 py-4 text-left font-bold text-gray-800 border-b-2 border-indigo-300">Product</th>
                                                <th class="px-4 py-4 text-center font-bold text-gray-800 border-b-2 border-indigo-300" colspan="2">Standard Delivery</th>
                                                <th class="px-4 py-4 text-center font-bold text-gray-800 border-b-2 border-indigo-300" colspan="2">Premium Delivery</th>
                                                <th class="px-4 py-4 text-center font-bold text-gray-800 border-b-2 border-indigo-300" colspan="2">Express Delivery</th>
                                            </tr>
                                            <tr class="bg-indigo-50">
                                                <th class="px-4 py-2 text-left text-sm text-gray-600"></th>
                                                <th class="px-4 py-2 text-center text-sm text-gray-600 border-l border-indigo-200">Handling</th>
                                                <th class="px-4 py-2 text-center text-sm text-gray-600">Transit</th>
                                                <th class="px-4 py-2 text-center text-sm text-gray-600 border-l border-indigo-200">Handling</th>
                                                <th class="px-4 py-2 text-center text-sm text-gray-600">Transit</th>
                                                <th class="px-4 py-2 text-center text-sm text-gray-600 border-l border-indigo-200">Handling</th>
                                                <th class="px-4 py-2 text-center text-sm text-gray-600">Transit</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <tr class="hover:bg-blue-50 transition">
                                                <td class="px-4 py-4 font-semibold text-gray-800">2D Apparel</td>
                                                <td class="px-4 py-4 text-center border-l border-gray-200">1-5</td>
                                                <td class="px-4 py-4 text-center">2-7</td>
                                                <td class="px-4 py-4 text-center border-l border-gray-200">1-5</td>
                                                <td class="px-4 py-4 text-center">2-5</td>
                                                <td class="px-4 py-4 text-center border-l border-gray-200">1-4</td>
                                                <td class="px-4 py-4 text-center">2-3</td>
                                            </tr>
                                            <tr class="hover:bg-blue-50 transition bg-gray-50">
                                                <td class="px-4 py-4 font-semibold text-gray-800">Mugs</td>
                                                <td class="px-4 py-4 text-center border-l border-gray-200">1-5</td>
                                                <td class="px-4 py-4 text-center">2-9</td>
                                                <td class="px-4 py-4 text-center border-l border-gray-200">1-5</td>
                                                <td class="px-4 py-4 text-center">2-7</td>
                                                <td class="px-4 py-4 text-center border-l border-gray-200">1-4</td>
                                                <td class="px-4 py-4 text-center">2-5</td>
                                            </tr>
                                            <tr class="hover:bg-blue-50 transition">
                                                <td class="px-4 py-4 font-semibold text-gray-800">3D Hoodies</td>
                                                <td class="px-4 py-4 text-center border-l border-gray-200">1-5</td>
                                                <td class="px-4 py-4 text-center">4-12</td>
                                                <td class="px-4 py-4 text-center border-l border-gray-200">1-5</td>
                                                <td class="px-4 py-4 text-center">4-7</td>
                                                <td class="px-4 py-4 text-center border-l border-gray-200">1-4</td>
                                                <td class="px-4 py-4 text-center">2-5</td>
                                            </tr>
                                            <tr class="hover:bg-blue-50 transition bg-gray-50">
                                                <td class="px-4 py-4 font-semibold text-gray-800">Pillows</td>
                                                <td class="px-4 py-4 text-center border-l border-gray-200">1-5</td>
                                                <td class="px-4 py-4 text-center">4-12</td>
                                                <td class="px-4 py-4 text-center border-l border-gray-200">1-5</td>
                                                <td class="px-4 py-4 text-center">4-7</td>
                                                <td class="px-4 py-4 text-center border-l border-gray-200">1-4</td>
                                                <td class="px-4 py-4 text-center">2-5</td>
                                            </tr>
                                            <tr class="hover:bg-blue-50 transition">
                                                <td class="px-4 py-4 font-semibold text-gray-800">Hats</td>
                                                <td class="px-4 py-4 text-center border-l border-gray-200">1-5</td>
                                                <td class="px-4 py-4 text-center">4-12</td>
                                                <td class="px-4 py-4 text-center border-l border-gray-200">1-5</td>
                                                <td class="px-4 py-4 text-center">4-7</td>
                                                <td class="px-4 py-4 text-center border-l border-gray-200">1-4</td>
                                                <td class="px-4 py-4 text-center">2-5</td>
                                            </tr>
                                            <tr class="hover:bg-blue-50 transition bg-gray-50">
                                                <td class="px-4 py-4 font-semibold text-gray-800">Fleece Blankets</td>
                                                <td class="px-4 py-4 text-center border-l border-gray-200">1-5</td>
                                                <td class="px-4 py-4 text-center">4-12</td>
                                                <td class="px-4 py-4 text-center text-gray-400 border-l border-gray-200" colspan="2">Not Available</td>
                                                <td class="px-4 py-4 text-center text-gray-400 border-l border-gray-200" colspan="2">Not Available</td>
                                            </tr>
                                            <tr class="hover:bg-blue-50 transition">
                                                <td class="px-4 py-4 font-semibold text-gray-800">Wooden</td>
                                                <td class="px-4 py-4 text-center border-l border-gray-200">1-5</td>
                                                <td class="px-4 py-4 text-center">4-12</td>
                                                <td class="px-4 py-4 text-center border-l border-gray-200">1-5</td>
                                                <td class="px-4 py-4 text-center">4-7</td>
                                                <td class="px-4 py-4 text-center border-l border-gray-200">1-4</td>
                                                <td class="px-4 py-4 text-center">2-5</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="p-4 bg-blue-50">
                                        <p class="text-blue-900 text-sm">
                                            <strong>📧 Note:</strong> Once your order has been processed, you will receive an email notification with your tracking details.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Important Notes -->
                            <div class="mb-12">
                                <div class="bg-gradient-to-r from-yellow-600 to-amber-600 text-white px-6 py-4 rounded-t-lg">
                                    <h2 class="text-3xl font-bold flex items-center">
                                        <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        Important Shipping Notes
                                    </h2>
                                </div>
                                <div class="bg-yellow-50 border-2 border-yellow-300 border-t-0 rounded-b-lg p-6">
                                    <div class="space-y-4">
                                        <div class="bg-white rounded-lg p-5 shadow-md border-l-4 border-yellow-500">
                                            <p class="text-gray-800"><strong class="text-yellow-600">⚠️</strong> Shipping to Alaska, Hawaii, Puerto Rico can take additional <strong>7-12 business days</strong></p>
                                        </div>
                                        <div class="bg-white rounded-lg p-5 shadow-md border-l-4 border-blue-500">
                                            <p class="text-gray-800"><strong class="text-blue-600">ℹ️</strong> Shipping times are approximate and may vary due to customs, weather, or courier issues</p>
                                        </div>
                                        <div class="bg-white rounded-lg p-5 shadow-md border-l-4 border-purple-500">
                                            <p class="text-gray-800"><strong class="text-purple-600">📦</strong> Single destination shipping only. For multiple locations, order separately</p>
                                        </div>
                                        <div class="bg-white rounded-lg p-5 shadow-md border-l-4 border-red-500">
                                            <p class="text-gray-800"><strong class="text-red-600">🏢</strong> PO boxes and Military APO/FPO available (US only). APO delivery: <strong>40-45 days</strong></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Shipping Costs -->
                            <div class="mb-12">
                                <div class="bg-gradient-to-r from-green-600 to-teal-600 text-white px-6 py-5 rounded-t-lg">
                                    <h2 class="text-3xl font-bold text-center">Shipping Costs (Within USA)</h2>
                                    <p class="text-center text-green-100 mt-2">Handling Fee: <strong>7%</strong> of order value</p>
                                </div>
                                <div class="bg-white border-2 border-green-300 border-t-0 rounded-b-lg overflow-x-auto">
                                    <table class="w-full">
                                        <thead class="bg-green-100">
                                            <tr>
                                                <th class="px-4 py-4 text-left font-bold text-gray-800 border-b-2 border-green-300">Product</th>
                                                <th class="px-4 py-4 text-center font-bold text-gray-800 border-b-2 border-green-300">Amount</th>
                                                <th class="px-4 py-4 text-center font-bold text-gray-800 border-b-2 border-green-300">Standard<br><span class="text-xs font-normal">(3-12 days)</span></th>
                                                <th class="px-4 py-4 text-center font-bold text-gray-800 border-b-2 border-green-300">Premium<br><span class="text-xs font-normal">(3-10 days)</span></th>
                                                <th class="px-4 py-4 text-center font-bold text-gray-800 border-b-2 border-green-300">Express<br><span class="text-xs font-normal">(3-7 days)</span></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <tr class="hover:bg-green-50 transition">
                                                <td class="px-4 py-3 font-semibold text-gray-800" rowspan="2">T-Shirts</td>
                                                <td class="px-4 py-3 text-center text-gray-700">1st item</td>
                                                <td class="px-4 py-3 text-center text-green-700 font-bold">$4.90</td>
                                                <td class="px-4 py-3 text-center text-blue-700 font-bold">$12.99</td>
                                                <td class="px-4 py-3 text-center text-purple-700 font-bold">$28.99</td>
                                            </tr>
                                            <tr class="hover:bg-green-50 transition bg-gray-50">
                                                <td class="px-4 py-3 text-center text-gray-600 text-sm">Adding item</td>
                                                <td class="px-4 py-3 text-center text-gray-700">$1</td>
                                                <td class="px-4 py-3 text-center text-gray-700">$2</td>
                                                <td class="px-4 py-3 text-center text-gray-700">$2</td>
                                            </tr>
                                            <tr class="hover:bg-green-50 transition">
                                                <td class="px-4 py-3 font-semibold text-gray-800" rowspan="2">Tank tops</td>
                                                <td class="px-4 py-3 text-center text-gray-700">1st item</td>
                                                <td class="px-4 py-3 text-center text-green-700 font-bold">$5.99</td>
                                                <td class="px-4 py-3 text-center text-blue-700 font-bold">$12.99</td>
                                                <td class="px-4 py-3 text-center text-purple-700 font-bold">$28.99</td>
                                            </tr>
                                            <tr class="hover:bg-green-50 transition bg-gray-50">
                                                <td class="px-4 py-3 text-center text-gray-600 text-sm">Adding item</td>
                                                <td class="px-4 py-3 text-center text-gray-700">$1</td>
                                                <td class="px-4 py-3 text-center text-gray-700">$2</td>
                                                <td class="px-4 py-3 text-center text-gray-700">$2</td>
                                            </tr>
                                            <tr class="hover:bg-green-50 transition">
                                                <td class="px-4 py-3 font-semibold text-gray-800" rowspan="2">Long sleeves</td>
                                                <td class="px-4 py-3 text-center text-gray-700">1st item</td>
                                                <td class="px-4 py-3 text-center text-green-700 font-bold">$6.99</td>
                                                <td class="px-4 py-3 text-center text-blue-700 font-bold">$13.99</td>
                                                <td class="px-4 py-3 text-center text-purple-700 font-bold">$28.99</td>
                                            </tr>
                                            <tr class="hover:bg-green-50 transition bg-gray-50">
                                                <td class="px-4 py-3 text-center text-gray-600 text-sm">Adding item</td>
                                                <td class="px-4 py-3 text-center text-gray-700">$1</td>
                                                <td class="px-4 py-3 text-center text-gray-700">$2</td>
                                                <td class="px-4 py-3 text-center text-gray-700">$2</td>
                                            </tr>
                                            <tr class="hover:bg-green-50 transition">
                                                <td class="px-4 py-3 font-semibold text-gray-800" rowspan="2">Hoodies</td>
                                                <td class="px-4 py-3 text-center text-gray-700">1st item</td>
                                                <td class="px-4 py-3 text-center text-green-700 font-bold">$9.99</td>
                                                <td class="px-4 py-3 text-center text-blue-700 font-bold">$13.99</td>
                                                <td class="px-4 py-3 text-center text-purple-700 font-bold">$28.99</td>
                                            </tr>
                                            <tr class="hover:bg-green-50 transition bg-gray-50">
                                                <td class="px-4 py-3 text-center text-gray-600 text-sm">Adding item</td>
                                                <td class="px-4 py-3 text-center text-gray-700">$1</td>
                                                <td class="px-4 py-3 text-center text-gray-700">$2</td>
                                                <td class="px-4 py-3 text-center text-gray-700">$2</td>
                                            </tr>
                                            <tr class="hover:bg-green-50 transition">
                                                <td class="px-4 py-3 font-semibold text-gray-800" rowspan="2">Mugs</td>
                                                <td class="px-4 py-3 text-center text-gray-700">1st item</td>
                                                <td class="px-4 py-3 text-center text-green-700 font-bold">$6.99</td>
                                                <td class="px-4 py-3 text-center text-blue-700 font-bold">$10.99</td>
                                                <td class="px-4 py-3 text-center text-gray-400">-</td>
                                            </tr>
                                            <tr class="hover:bg-green-50 transition bg-gray-50">
                                                <td class="px-4 py-3 text-center text-gray-600 text-sm">Adding item</td>
                                                <td class="px-4 py-3 text-center text-gray-700">$1</td>
                                                <td class="px-4 py-3 text-center text-gray-700">$3</td>
                                                <td class="px-4 py-3 text-center text-gray-400">-</td>
                                            </tr>
                                            <tr class="hover:bg-green-50 transition">
                                                <td class="px-4 py-3 font-semibold text-gray-800" rowspan="2">Hats</td>
                                                <td class="px-4 py-3 text-center text-gray-700">1st item</td>
                                                <td class="px-4 py-3 text-center text-green-700 font-bold">$6.99</td>
                                                <td class="px-4 py-3 text-center text-blue-700 font-bold">$10.99</td>
                                                <td class="px-4 py-3 text-center text-gray-400">-</td>
                                            </tr>
                                            <tr class="hover:bg-green-50 transition bg-gray-50">
                                                <td class="px-4 py-3 text-center text-gray-600 text-sm">Adding item</td>
                                                <td class="px-4 py-3 text-center text-gray-700">$1</td>
                                                <td class="px-4 py-3 text-center text-gray-700">$2</td>
                                                <td class="px-4 py-3 text-center text-gray-400">-</td>
                                            </tr>
                                            <tr class="hover:bg-green-50 transition">
                                                <td class="px-4 py-3 font-semibold text-gray-800" rowspan="2">Stickers</td>
                                                <td class="px-4 py-3 text-center text-gray-700">1st item</td>
                                                <td class="px-4 py-3 text-center text-green-700 font-bold">$5.99</td>
                                                <td class="px-4 py-3 text-center text-blue-700 font-bold">$8.99</td>
                                                <td class="px-4 py-3 text-center text-purple-700 font-bold">$28.99</td>
                                            </tr>
                                            <tr class="hover:bg-green-50 transition bg-gray-50">
                                                <td class="px-4 py-3 text-center text-gray-600 text-sm">Adding item</td>
                                                <td class="px-4 py-3 text-center text-gray-700">$0</td>
                                                <td class="px-4 py-3 text-center text-gray-700">$1</td>
                                                <td class="px-4 py-3 text-center text-gray-700">$2</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Order Tracking -->
                            <div class="mb-12">
                                <div class="bg-gradient-to-br from-purple-50 to-pink-50 border-2 border-purple-300 rounded-lg p-8">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center mr-4 shadow-lg">
                                            <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="text-3xl font-bold text-purple-900 mb-3">Order Tracking</h3>
                                            <p class="text-gray-800 leading-relaxed text-lg">
                                                Once your order has been shipped, you will receive a <strong>tracking number via email</strong>. You can use this number to monitor your shipment\'s progress through our tracking portal or the courier\'s website.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Info Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
                                <!-- Customs -->
                                <div class="bg-gradient-to-br from-orange-50 to-red-50 border-2 border-orange-300 rounded-lg p-6">
                                    <div class="flex items-start mb-4">
                                        <div class="flex-shrink-0 w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center mr-3">
                                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-2xl font-bold text-orange-900 mb-2">Customs, Duties & Taxes</h3>
                                            <p class="text-gray-800 leading-relaxed">
                                                Orders shipped outside USA may be subject to customs duties, taxes, and fees. <strong class="text-orange-600">These charges are the customer\'s responsibility</strong> and vary by country.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Failed Deliveries -->
                                <div class="bg-gradient-to-br from-red-50 to-rose-50 border-2 border-red-300 rounded-lg p-6">
                                    <div class="flex items-start mb-4">
                                        <div class="flex-shrink-0 w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mr-3">
                                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-2xl font-bold text-red-900 mb-2">Failed Deliveries</h3>
                                            <p class="text-gray-800 leading-relaxed">
                                                Bluprinter is <strong class="text-red-600">not responsible</strong> for packages delayed, lost, or returned due to incorrect addresses. Additional fees may apply to resend.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Section -->
                            <div class="bg-gradient-to-r from-blue-600 via-cyan-600 to-teal-600 rounded-lg p-10 text-center text-white">
                                <div class="flex justify-center mb-6">
                                    <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="text-3xl font-bold mb-4">Questions About Shipping?</h3>
                                <p class="text-xl text-cyan-100 mb-6 max-w-3xl mx-auto">
                                    If your order hasn\'t arrived or you have concerns about your shipment, we\'re here to help!
                                </p>
                                <a href="mailto:support@bluprinter.com" class="inline-flex items-center px-10 py-4 bg-white text-teal-600 font-bold rounded-lg shadow-xl hover:shadow-2xl transition duration-200 text-xl">
                                    <svg class="w-7 h-7 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    Contact Customer Service
                                </a>
                                <p class="mt-6 text-blue-100 text-lg">
                                    At Bluprinter, customer satisfaction is our priority. Thank you for choosing us!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>',
                'excerpt' => 'Complete shipping and delivery information - Processing times, delivery times by product, shipping costs, tracking, customs, and more.',
                'status' => 'published',
                'published_at' => now(),
                'template' => 'default',
                'show_in_menu' => true,
                'menu_title' => 'Shipping & Delivery',
                'sort_order' => 13,
                'meta_title' => 'Shipping & Delivery - Bluprinter Worldwide Shipping Information',
                'meta_description' => 'Learn about Bluprinter shipping and delivery. Processing times, delivery times by product, shipping costs within USA, order tracking, and customs information.',
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
