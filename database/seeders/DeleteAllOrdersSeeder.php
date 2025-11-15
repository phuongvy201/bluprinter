<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteAllOrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder will delete all orders and related order items from the database.
     * WARNING: This action is irreversible!
     */
    public function run(): void
    {
        // Start transaction for safety
        DB::beginTransaction();

        try {
            // Get count before deletion for logging
            $ordersCount = Order::count();
            $orderItemsCount = OrderItem::count();

            if ($ordersCount === 0) {
                $this->command->info('No orders found in database. Nothing to delete.');
                DB::rollBack();
                return;
            }

            $this->command->warn("⚠️  WARNING: About to delete {$ordersCount} orders and {$orderItemsCount} order items!");

            // Confirm deletion (skip in non-interactive mode)
            if ($this->command->confirm('Are you sure you want to delete all orders?', true)) {
                // Delete order items first (due to foreign key constraints)
                $deletedItems = OrderItem::query()->delete();
                $this->command->info("✓ Deleted {$deletedItems} order items");

                // Delete all orders
                $deletedOrders = Order::query()->delete();
                $this->command->info("✓ Deleted {$deletedOrders} orders");

                // Commit transaction
                DB::commit();

                $this->command->info("✅ Successfully deleted all orders and order items!");

                Log::info('All orders deleted via seeder', [
                    'deleted_orders' => $deletedOrders,
                    'deleted_items' => $deletedItems
                ]);
            } else {
                DB::rollBack();
                $this->command->info('Operation cancelled.');
            }
        } catch (\Exception $e) {
            // Rollback on error
            DB::rollBack();

            $this->command->error('❌ Error deleting orders: ' . $e->getMessage());

            Log::error('Error deleting orders via seeder', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}
