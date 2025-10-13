<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@bluprinter.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        // Tạo Seller
        $seller = User::firstOrCreate(
            ['email' => 'seller@bluprinter.com'],
            [
                'name' => 'Seller User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $seller->assignRole('seller');

        // Tạo Customer
        $customer = User::firstOrCreate(
            ['email' => 'customer@bluprinter.com'],
            [
                'name' => 'Customer User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $customer->assignRole('customer');

        // Tạo thêm một số customers mẫu
        for ($i = 1; $i <= 3; $i++) {
            $testCustomer = User::firstOrCreate(
                ['email' => "customer{$i}@bluprinter.com"],
                [
                    'name' => "Customer {$i}",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $testCustomer->assignRole('customer');
        }
    }
}
