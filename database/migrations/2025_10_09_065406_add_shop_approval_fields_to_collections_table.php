<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->after('user_id')->constrained()->onDelete('cascade');
            $table->boolean('admin_approved')->default(false)->after('featured');
            $table->text('admin_notes')->nullable()->after('admin_approved');

            // Indexes
            $table->index(['shop_id', 'admin_approved']);
            $table->index('admin_approved');
        });
    }

    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->dropForeign(['shop_id']);
            $table->dropIndex(['shop_id', 'admin_approved']);
            $table->dropIndex(['admin_approved']);
            $table->dropColumn(['shop_id', 'admin_approved', 'admin_notes']);
        });
    }
};
