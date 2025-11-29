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
        Schema::create('domain_currency_configs', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique()->comment('Domain name (e.g., bluprinter.com, bluprinter.vn)');
            $table->string('currency', 3)->comment('Currency code: USD, VND, GBP, EUR, etc.');
            $table->decimal('currency_rate', 10, 6)->default(1.0)->comment('Exchange rate from USD (e.g., 1.0 for USD, 25000 for VND)');
            $table->boolean('is_active')->default(true)->comment('Whether this currency config is active');
            $table->text('notes')->nullable()->comment('Optional notes about this configuration');
            $table->timestamps();

            $table->index('domain');
            $table->index('is_active');
            $table->index('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domain_currency_configs');
    }
};
