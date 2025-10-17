<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = \App\Models\User::role('admin')->first();

        if (!$admin) {
            $admin = \App\Models\User::first();
        }

        $pages = [
            [
                'user_id' => $admin->id,
                'title' => 'About Us',
                'slug' => 'about-us',
                'content' => '<h2>Welcome to Bluprinter</h2>
                    <p>Bluprinter is a global online marketplace where people come together to make, sell, buy, and collect unique items. We connect creative entrepreneurs with thoughtful consumers.</p>
                    <h3>Our Mission</h3>
                    <p>To keep commerce human by helping people discover and buy from real people.</p>
                    <h3>Our Values</h3>
                    <ul>
                        <li>Support creative entrepreneurs</li>
                        <li>Foster community and connection</li>
                        <li>Champion sustainability and quality</li>
                        <li>Celebrate uniqueness and diversity</li>
                    </ul>',
                'excerpt' => 'Learn about Bluprinter - where creativity meets commerce',
                'status' => 'published',
                'published_at' => now(),
                'template' => 'default',
                'show_in_menu' => true,
                'menu_title' => 'About',
                'sort_order' => 1,
                'meta_title' => 'About Bluprinter - Creative Marketplace',
                'meta_description' => 'Discover the story behind Bluprinter, your creative marketplace for unique, customizable products.',
            ],
            [
                'user_id' => $admin->id,
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'content' => '<h2>Get in Touch</h2>
                    <p>We\'d love to hear from you! Whether you have a question about products, pricing, or anything else, our team is ready to answer all your questions.</p>
                    <h3>Email</h3>
                    <p>info@bluprinter.com</p>
                    <h3>Phone</h3>
                    <p>+84 123 456 789</p>
                    <h3>Office Hours</h3>
                    <p>Monday - Friday: 9:00 AM - 6:00 PM (GMT+7)<br>
                    Saturday: 9:00 AM - 12:00 PM<br>
                    Sunday: Closed</p>',
                'excerpt' => 'Have questions? We\'re here to help!',
                'status' => 'published',
                'published_at' => now(),
                'template' => 'default',
                'show_in_menu' => true,
                'menu_title' => 'Contact',
                'sort_order' => 2,
                'meta_title' => 'Contact Bluprinter - Get in Touch',
                'meta_description' => 'Contact the Bluprinter team. We\'re here to help with your questions.',
            ],
            [
                'user_id' => $admin->id,
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '<h2>Privacy Policy</h2>
                    <p>Last updated: ' . now()->format('F d, Y') . '</p>
                    <h3>Information We Collect</h3>
                    <p>We collect information you provide directly to us, such as when you create an account, make a purchase, or contact us.</p>
                    <h3>How We Use Your Information</h3>
                    <p>We use the information we collect to provide, maintain, and improve our services, process transactions, and communicate with you.</p>
                    <h3>Information Sharing</h3>
                    <p>We do not sell your personal information. We may share your information with service providers who assist us in operating our platform.</p>
                    <h3>Your Rights</h3>
                    <p>You have the right to access, update, or delete your personal information at any time.</p>',
                'excerpt' => 'How we collect, use, and protect your information',
                'status' => 'published',
                'published_at' => now(),
                'template' => 'default',
                'show_in_menu' => true,
                'sort_order' => 3,
                'meta_title' => 'Privacy Policy - Bluprinter',
                'meta_description' => 'Read our privacy policy to understand how we protect your data.',
            ],
            [
                'user_id' => $admin->id,
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'content' => '<h2>Terms of Service</h2>
                    <p>Last updated: ' . now()->format('F d, Y') . '</p>
                    <h3>Acceptance of Terms</h3>
                    <p>By accessing and using Bluprinter, you accept and agree to be bound by these Terms of Service.</p>
                    <h3>User Accounts</h3>
                    <p>You are responsible for maintaining the confidentiality of your account and password.</p>
                    <h3>Seller Responsibilities</h3>
                    <p>Sellers must accurately describe their products and fulfill orders in a timely manner.</p>
                    <h3>Prohibited Activities</h3>
                    <p>Users may not engage in fraudulent activities, harassment, or violate intellectual property rights.</p>',
                'excerpt' => 'Terms and conditions for using Bluprinter',
                'status' => 'published',
                'published_at' => now(),
                'template' => 'default',
                'show_in_menu' => true,
                'sort_order' => 4,
                'meta_title' => 'Terms of Service - Bluprinter',
                'meta_description' => 'Read our terms of service before using the platform.',
            ],
            [
                'user_id' => $admin->id,
                'title' => 'Shipping & Returns',
                'slug' => 'shipping-returns',
                'content' => '<h2>Shipping Information</h2>
                    <h3>Shipping Methods</h3>
                    <p>We offer standard and express shipping worldwide.</p>
                    <h3>Free Shipping</h3>
                    <p>Orders over $50 qualify for free standard shipping.</p>
                    <h2>Return Policy</h2>
                    <h3>30-Day Returns</h3>
                    <p>We accept returns within 30 days of delivery for most items.</p>
                    <h3>Return Process</h3>
                    <p>Contact the seller through your order page to initiate a return.</p>',
                'excerpt' => 'Shipping methods and return policy',
                'status' => 'published',
                'published_at' => now(),
                'template' => 'default',
                'show_in_menu' => false,
                'sort_order' => 5,
                'meta_title' => 'Shipping & Returns - Bluprinter',
            ],
            [
                'user_id' => $admin->id,
                'title' => 'FAQ',
                'slug' => 'faq',
                'content' => '<h2>Frequently Asked Questions</h2>
                    <h3>How do I place an order?</h3>
                    <p>Browse products, add to cart, and proceed to checkout. Follow the prompts to complete your purchase.</p>
                    <h3>Can I customize products?</h3>
                    <p>Yes! Many products offer customization options. Look for the customization section on product pages.</p>
                    <h3>How do I contact support?</h3>
                    <p>You can reach our support team via email at support@bluprinter.com or through our contact page.</p>
                    <h3>What payment methods do you accept?</h3>
                    <p>We accept major credit cards, PayPal, and other secure payment methods.</p>',
                'excerpt' => 'Common questions and answers',
                'status' => 'published',
                'published_at' => now(),
                'template' => 'default',
                'show_in_menu' => true,
                'menu_title' => 'Help',
                'sort_order' => 6,
                'meta_title' => 'FAQ - Bluprinter',
            ],
            [
                'user_id' => $admin->id,
                'title' => 'How to Order',
                'slug' => 'how-to-order',
                'content' => '<h2>How to Place an Order</h2>
                    <h3>Step 1: Browse Products</h3>
                    <p>Explore our marketplace and find products you love. Use filters to narrow down your search.</p>
                    <h3>Step 2: Customize (Optional)</h3>
                    <p>Many products can be personalized with your own text, images, or designs.</p>
                    <h3>Step 3: Add to Cart</h3>
                    <p>Select your size, color, and quantity, then click "Add to Cart".</p>
                    <h3>Step 4: Checkout</h3>
                    <p>Review your order, enter shipping details, and choose payment method.</p>
                    <h3>Step 5: Confirmation</h3>
                    <p>You\'ll receive an email confirmation with your order details and tracking information.</p>',
                'excerpt' => 'Step-by-step guide to placing your first order',
                'status' => 'published',
                'published_at' => now(),
                'template' => 'default',
                'show_in_menu' => false,
                'sort_order' => 7,
                'meta_title' => 'How to Order - Bluprinter Guide',
                'meta_description' => 'Learn how to place your first order on Bluprinter with our step-by-step guide.',
            ],
            [
                'user_id' => $admin->id,
                'title' => 'Size Guide',
                'slug' => 'size-guide',
                'content' => '<h2>Product Size Guide</h2>
                    <h3>T-Shirts</h3>
                    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                        <tr style="background: #f5f5f5;">
                            <th style="border: 1px solid #ddd; padding: 8px;">Size</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">Chest (inches)</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">Length (inches)</th>
                        </tr>
                        <tr><td style="border: 1px solid #ddd; padding: 8px;">S</td><td style="border: 1px solid #ddd; padding: 8px;">34-36</td><td style="border: 1px solid #ddd; padding: 8px;">28</td></tr>
                        <tr><td style="border: 1px solid #ddd; padding: 8px;">M</td><td style="border: 1px solid #ddd; padding: 8px;">38-40</td><td style="border: 1px solid #ddd; padding: 8px;">29</td></tr>
                        <tr><td style="border: 1px solid #ddd; padding: 8px;">L</td><td style="border: 1px solid #ddd; padding: 8px;">42-44</td><td style="border: 1px solid #ddd; padding: 8px;">30</td></tr>
                        <tr><td style="border: 1px solid #ddd; padding: 8px;">XL</td><td style="border: 1px solid #ddd; padding: 8px;">46-48</td><td style="border: 1px solid #ddd; padding: 8px;">31</td></tr>
                    </table>
                    <h3>Hoodies & Sweatshirts</h3>
                    <p>Hoodies typically run one size larger than t-shirts. Check individual product pages for specific measurements.</p>
                    <h3>Mugs</h3>
                    <p>Standard mugs: 11 oz capacity. Large mugs: 15 oz capacity.</p>',
                'excerpt' => 'Find the perfect size for your order',
                'status' => 'published',
                'published_at' => now(),
                'template' => 'default',
                'show_in_menu' => false,
                'sort_order' => 8,
                'meta_title' => 'Size Guide - Bluprinter',
                'meta_description' => 'Use our size guide to find the perfect fit for t-shirts, hoodies, and other products.',
            ],
            [
                'user_id' => $admin->id,
                'title' => 'Customization Guide',
                'slug' => 'customization-guide',
                'content' => '<h2>Product Customization Guide</h2>
                    <h3>Text Customization</h3>
                    <p>Add your own text to products with our easy-to-use text editor:</p>
                    <ul>
                        <li>Choose from hundreds of fonts</li>
                        <li>Adjust size, color, and position</li>
                        <li>Add effects like shadows and outlines</li>
                    </ul>
                    <h3>Image Upload</h3>
                    <p>Upload your own images and artwork:</p>
                    <ul>
                        <li>Supported formats: PNG, JPG, GIF</li>
                        <li>Recommended size: 300 DPI or higher</li>
                        <li>Maximum file size: 25MB</li>
                    </ul>
                    <h3>Design Templates</h3>
                    <p>Browse our library of pre-made designs:</p>
                    <ul>
                        <li>Professional graphics</li>
                        <li>Seasonal designs</li>
                        <li>Popular quotes and sayings</li>
                        <li>Business logos and branding</li>
                    </ul>
                    <h3>Tips for Best Results</h3>
                    <ul>
                        <li>Use high-resolution images</li>
                        <li>Keep text readable and not too small</li>
                        <li>Consider product placement and margins</li>
                        <li>Preview your design before ordering</li>
                    </ul>',
                'excerpt' => 'Learn how to customize products with text and images',
                'status' => 'published',
                'published_at' => now(),
                'template' => 'default',
                'show_in_menu' => false,
                'sort_order' => 10,
                'meta_title' => 'Customization Guide - Bluprinter',
                'meta_description' => 'Master product customization with our comprehensive guide to text, images, and design.',
            ],
            [
                'user_id' => $admin->id,
                'title' => 'Support Center',
                'slug' => 'support-center',
                'content' => '<h2>Customer Support</h2>
                    <h3>Contact Methods</h3>
                    <p><strong>Email:</strong> support@bluprinter.com<br>
                    <strong>Phone:</strong> +1 (555) 123-4567<br>
                    <strong>Live Chat:</strong> Available 24/7 on our website</p>
                    <h3>Support Hours</h3>
                    <p>Monday - Friday: 9:00 AM - 9:00 PM EST<br>
                    Saturday - Sunday: 10:00 AM - 6:00 PM EST</p>
                    <h3>Common Issues</h3>
                    <ul>
                        <li><a href="/faq">Frequently Asked Questions</a></li>
                        <li><a href="/how-to-order">Ordering Help</a></li>
                        <li><a href="/shipping-returns">Shipping & Returns</a></li>
                        <li><a href="/size-guide">Size Guide</a></li>
                    </ul>
                    <h3>Report a Problem</h3>
                    <p>If you encounter any issues with your order or account, please contact us immediately. We\'re here to help!</p>',
                'excerpt' => 'Get help from our customer support team',
                'status' => 'published',
                'published_at' => now(),
                'template' => 'default',
                'show_in_menu' => false,
                'sort_order' => 11,
                'meta_title' => 'Support Center - Bluprinter',
                'meta_description' => 'Get help with your orders, account, and any questions you may have.',
            ],
        ];

        foreach ($pages as $page) {
            \App\Models\Page::updateOrCreate(
                ['slug' => $page['slug']],
                $page
            );
        }
    }
}
