<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateSeller extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seller:create {name} {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new seller account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->argument('password');

        // Kiểm tra email đã tồn tại chưa
        if (User::where('email', $email)->exists()) {
            $this->error("User with email {$email} already exists!");
            return 1;
        }

        // Kiểm tra role seller có tồn tại không
        $sellerRole = Role::where('name', 'seller')->first();
        if (!$sellerRole) {
            $this->error("Seller role does not exist!");
            $this->info("Available roles: " . Role::all()->pluck('name')->join(', '));
            return 1;
        }

        // Tạo user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        // Gán role seller
        $user->assignRole('seller');

        $this->info("Seller created successfully!");
        $this->line("Name: {$user->name}");
        $this->line("Email: {$user->email}");
        $this->line("Role: seller");
        $this->line("Permissions: " . $user->getAllPermissions()->pluck('name')->join(', '));

        return 0;
    }
}
