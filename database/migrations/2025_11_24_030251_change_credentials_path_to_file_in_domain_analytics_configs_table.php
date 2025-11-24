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
        Schema::table('domain_analytics_configs', function (Blueprint $table) {
            $table->renameColumn('credentials_path', 'credentials_file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domain_analytics_configs', function (Blueprint $table) {
            $table->renameColumn('credentials_file', 'credentials_path');
        });
    }
};
