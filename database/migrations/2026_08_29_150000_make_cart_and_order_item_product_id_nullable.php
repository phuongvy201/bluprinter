<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropForeignIfExists('carts', 'carts_product_id_foreign');
        DB::statement('ALTER TABLE carts MODIFY product_id BIGINT UNSIGNED NULL');

        if (! $this->foreignExists('carts', 'carts_product_id_foreign')) {
            Schema::table('carts', function ($table) {
                $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            });
        }

        $this->dropForeignIfExists('order_items', 'order_items_product_id_foreign');
        DB::statement('ALTER TABLE order_items MODIFY product_id BIGINT UNSIGNED NULL');

        if (! $this->foreignExists('order_items', 'order_items_product_id_foreign')) {
            Schema::table('order_items', function ($table) {
                $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Keep columns nullable; restoring NOT NULL would fail on existing custom rows.
    }

    protected function dropForeignIfExists(string $table, string $constraint): void
    {
        $exists = DB::selectOne(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ?',
            [$table, $constraint, 'FOREIGN KEY']
        );

        if ($exists) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraint}`");
        }
    }

    protected function foreignExists(string $table, string $constraint): bool
    {
        return (bool) DB::selectOne(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ?',
            [$table, $constraint, 'FOREIGN KEY']
        );
    }
};
