<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ApiToken;

class ApiTokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo API token cho AI integration
        $aiToken = ApiToken::generateToken(
            'AI Product Generator',
            'Token for AI to automatically create products',
            ['product:create', 'product:read']
        );

        $this->command->info('API Token created:');
        $this->command->info('Name: ' . $aiToken->name);
        $this->command->info('Token: ' . $aiToken->token);
        $this->command->info('Permissions: ' . implode(', ', $aiToken->permissions));
        $this->command->info('');
        $this->command->info('Use this token in your API requests:');
        $this->command->info('Header: X-API-Token: ' . $aiToken->token);
        $this->command->info('Or parameter: api_token=' . $aiToken->token);
    }
}
