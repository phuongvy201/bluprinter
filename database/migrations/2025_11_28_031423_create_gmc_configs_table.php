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
        Schema::create('gmc_configs', function (Blueprint $table) {
            $table->id();

            // Domain và thị trường
            $table->string('domain')->comment('Domain của website (vd: bluprinter.com)');
            $table->string('target_country', 2)->comment('Mã quốc gia: US, GB, VN, etc.');

            // Tên config để dễ nhận biết
            $table->string('name')->comment('Tên config (VD: US Store, UK Store)');

            // GMC credentials
            $table->string('merchant_id')->comment('Google Merchant Center Merchant ID');
            $table->string('data_source_id')->default('PRODUCT_FEED_API')->comment('Data Source ID');
            $table->string('credentials_path')->comment('Đường dẫn đến file credentials JSON');

            // Market settings
            $table->string('currency', 3)->comment('Currency code: USD, GBP, VND, etc.');
            $table->string('content_language', 5)->default('en')->comment('Language code: en, vi, etc.');

            // Status
            $table->boolean('is_active')->default(true)->comment('Config có đang hoạt động không');

            // Unique constraint: mỗi domain chỉ có 1 config cho mỗi target_country
            $table->unique(['domain', 'target_country'], 'domain_country_unique');

            $table->timestamps();

            // Indexes
            $table->index('domain');
            $table->index('target_country');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gmc_configs');
    }
};
