<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shipping_rates', function (Blueprint $table) {
            $table->json('domains')->nullable()->after('domain');
        });

        // Backfill existing single-domain values into the new JSON column
        DB::table('shipping_rates')
            ->whereNotNull('domain')
            ->update(['domains' => DB::raw("JSON_ARRAY(domain)")]);
    }

    public function down(): void
    {
        Schema::table('shipping_rates', function (Blueprint $table) {
            $table->dropColumn('domains');
        });
    }
};

