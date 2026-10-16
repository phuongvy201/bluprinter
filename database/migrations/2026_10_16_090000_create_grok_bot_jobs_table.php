<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grok_bot_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('request_type', 20);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedSmallInteger('dpi')->nullable();
            $table->text('instruction')->nullable();
            $table->string('outcome', 20);
            $table->text('coordinator_message')->nullable();
            $table->string('coordinator_model', 80)->nullable();
            $table->string('print_model', 80)->nullable();
            $table->string('source_path')->nullable();
            $table->string('print_path')->nullable();
            $table->json('layers')->nullable();
            $table->json('checklist')->nullable();
            $table->text('redo_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grok_bot_jobs');
    }
};
