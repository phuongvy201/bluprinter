<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('shops', 'flash_deal_auto_enroll')) {
            Schema::table('shops', function (Blueprint $table) {
                $table->boolean('flash_deal_auto_enroll')->default(false)->after('shop_status');
            });
        }

        if (! Schema::hasColumn('shops', 'flash_deal_max_discount_percent')) {
            Schema::table('shops', function (Blueprint $table) {
                $table->unsignedTinyInteger('flash_deal_max_discount_percent')->nullable()->after('flash_deal_auto_enroll');
            });
        }

        if (! Schema::hasColumn('products', 'flash_deal_auto_enroll')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('flash_deal_auto_enroll')->default(false)->after('status');
            });
        }

        if (! Schema::hasColumn('products', 'flash_deal_min_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('flash_deal_min_price', 10, 2)->nullable()->after('flash_deal_auto_enroll');
            });
        }

        if (! Schema::hasTable('flash_deal_rules')) {
            Schema::create('flash_deal_rules', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('rule_type'); // stale_inventory, high_margin, trending
                $table->json('config')->nullable();
                $table->unsignedTinyInteger('discount_percent')->default(20);
                $table->unsignedSmallInteger('max_products')->default(5);
                $table->unsignedSmallInteger('priority')->default(10);
                $table->json('golden_hours')->nullable(); // e.g. [12, 20]
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('flash_deal_templates')) {
            Schema::create('flash_deal_templates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name');
                $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
                $table->unsignedTinyInteger('discount_percent')->default(20);
                $table->time('start_time')->default('10:00:00');
                $table->time('end_time')->default('14:00:00');
                $table->string('recurrence')->default('daily'); // daily, weekly
                $table->json('days_of_week')->nullable(); // 0=Sun .. 6=Sat for weekly
                $table->unsignedSmallInteger('max_products')->default(5);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('flash_deals')) {
            Schema::create('flash_deals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('sale_price', 10, 2);
                $table->decimal('original_price', 10, 2);
                $table->timestamp('starts_at');
                $table->timestamp('ends_at');
                $table->string('source')->default('manual'); // rule, template, auto_enrollment, manual
                $table->unsignedBigInteger('source_id')->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamp('expired_at')->nullable();
                $table->timestamps();

                $table->index(['is_active', 'starts_at', 'ends_at']);
                $table->index(['product_id', 'is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('flash_deals');
        Schema::dropIfExists('flash_deal_templates');
        Schema::dropIfExists('flash_deal_rules');

        if (Schema::hasColumn('products', 'flash_deal_auto_enroll')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn(['flash_deal_auto_enroll', 'flash_deal_min_price']);
            });
        }

        if (Schema::hasColumn('shops', 'flash_deal_auto_enroll')) {
            Schema::table('shops', function (Blueprint $table) {
                $table->dropColumn(['flash_deal_auto_enroll', 'flash_deal_max_discount_percent']);
            });
        }
    }
};
