<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ListUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all users with their roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::with('roles')->get();

        $this->info('Users created:');
        $this->newLine();

        foreach ($users as $user) {
            $roles = $user->roles->pluck('name')->join(', ');
            $this->line("• {$user->name} ({$user->email}) - Roles: {$roles}");
        }

        $this->newLine();
        $this->info("Total users: {$users->count()}");
    }
}
