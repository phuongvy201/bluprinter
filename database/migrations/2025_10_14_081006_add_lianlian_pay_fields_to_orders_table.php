<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_transaction_id')->nullable()->after('payment_id');
            $table->timestamp('paid_at')->nullable()->after('payment_transaction_id');
            $table->decimal('refund_amount', 10, 2)->nullable()->after('paid_at');
            $table->text('refund_reason')->nullable()->after('refund_amount');
            $table->enum('refund_status', ['pending', 'processing', 'completed', 'failed'])->nullable()->after('refund_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_transaction_id',
                'paid_at',
                'refund_amount',
                'refund_reason',
                'refund_status'
            ]);
        });
    }
};
