<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('studio_designs', function (Blueprint $table) {
            $table->string('preview_url', 500)->nullable()->after('image_url');
        });
    }

    public function down(): void
    {
        Schema::table('studio_designs', function (Blueprint $table) {
            $table->dropColumn('preview_url');
        });
    }
};
