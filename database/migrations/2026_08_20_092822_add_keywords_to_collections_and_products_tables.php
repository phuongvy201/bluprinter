<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            if (!Schema::hasColumn('collections', 'keywords')) {
                $table->json('keywords')->nullable()->after('auto_rules');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'keywords')) {
                $table->json('keywords')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            if (Schema::hasColumn('collections', 'keywords')) {
                $table->dropColumn('keywords');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'keywords')) {
                $table->dropColumn('keywords');
            }
        });
    }
};
