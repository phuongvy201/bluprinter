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

        $sellerToken = ApiToken::generateToken(
            'Seller API Full Access',
            'Seller role token for Product + Blog API (Swagger /api-docs.html). Set default_shop_id to your shop.',
            ['seller']
        );

        $this->command->info('API Token created:');
        $this->command->info('Name: ' . $aiToken->name);
        $this->command->info('Token: ' . $aiToken->token);
        $this->command->info('Permissions: ' . implode(', ', $aiToken->permissions));
        $this->command->info('');
        $this->command->info('Seller API Token created:');
        $this->command->info('Name: ' . $sellerToken->name);
        $this->command->info('Token: ' . $sellerToken->token);
        $this->command->info('Permissions: seller (Product + Blog, scoped to default_shop_id)');
        $this->command->info('Remember: set default_shop_id on this token to the seller shop id.');
        $this->command->info('');
        $this->command->info('Use header: X-API-Token: ' . $sellerToken->token);
        $this->command->info('Docs: /api-docs.html');
    }
}
