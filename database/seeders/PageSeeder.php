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
                    <h3>How do I become a seller?</h3>
                    <p>Register for an account, complete your profile, and apply for seller status in your dashboard.</p>
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
        ];

        foreach ($pages as $page) {
            \App\Models\Page::create($page);
        }
    }
}
