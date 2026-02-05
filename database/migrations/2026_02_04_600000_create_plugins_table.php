<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('version', 20)->default('0.0.0');
            $table->string('author')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['enabled', 'disabled', 'error'])->default('disabled');
            $table->boolean('installed')->default(false);
            $table->json('settings')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamp('enabled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plugins');
    }
};
