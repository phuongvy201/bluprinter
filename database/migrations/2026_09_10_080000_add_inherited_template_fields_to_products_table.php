<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'allow_customization')) {
                $table->boolean('allow_customization')->nullable()->after('description');
            }
            if (! Schema::hasColumn('products', 'customizations')) {
                $table->json('customizations')->nullable()->after('allow_customization');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $columns = array_values(array_filter([
                Schema::hasColumn('products', 'allow_customization') ? 'allow_customization' : null,
                Schema::hasColumn('products', 'customizations') ? 'customizations' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
