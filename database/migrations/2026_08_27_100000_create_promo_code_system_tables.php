<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['percent', 'fixed'])->default('percent');
            $table->decimal('value', 10, 2);
            $table->decimal('min_order_amount', 10, 2)->default(0);
            $table->decimal('max_discount_amount', 10, 2)->nullable();
            $table->enum('audience', ['welcome', 'thank_you', 'win_back', 'vip', 'public'])->default('public');
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->unsignedTinyInteger('per_customer_limit')->default(1);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('show_on_promo_page')->default(false);
            $table->boolean('is_auto_generated')->default(false);
            $table->string('assigned_email')->nullable()->index();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['audience', 'is_active']);
            $table->index(['show_on_promo_page', 'is_active']);
        });

        Schema::create('promo_code_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_code_id')->constrained('promo_codes')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email')->index();
            $table->decimal('discount_amount', 10, 2);
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('promo_code_id')->nullable()->after('tip_amount')->constrained('promo_codes')->nullOnDelete();
            $table->string('promo_code')->nullable()->after('promo_code_id');
            $table->enum('discount_type', ['none', 'promo', 'volume'])->default('none')->after('promo_code');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('discount_type');
            $table->unsignedTinyInteger('volume_discount_percent')->nullable()->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promo_code_id');
            $table->dropColumn(['promo_code', 'discount_type', 'discount_amount', 'volume_discount_percent']);
        });

        Schema::dropIfExists('promo_code_usages');
        Schema::dropIfExists('promo_codes');
    }
};
