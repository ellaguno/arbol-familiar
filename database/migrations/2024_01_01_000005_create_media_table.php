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
        Schema::create('media', function (Blueprint $table) {
            $table->id();

            // Polimorfismo
            $table->string('mediable_type', 100)->comment('App\\Models\\Person o App\\Models\\User');
            $table->unsignedBigInteger('mediable_id');

            // Tipo de media
            $table->enum('type', ['image', 'document', 'link']);

            // Metadatos
            $table->string('title');
            $table->text('description')->nullable();

            // Para archivos
            $table->string('file_path', 500)->nullable();
            $table->string('file_name')->nullable();
            $table->unsignedInteger('file_size')->nullable()->comment('Tamano en bytes');
            $table->string('mime_type', 100)->nullable();

            // Para enlaces externos
            $table->string('external_url', 1000)->nullable();

            // Organizacion
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false)->comment('Foto principal del perfil');

            // Auditoria
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_at')->useCurrent();

            // Indices
            $table->index(['mediable_type', 'mediable_id'], 'idx_media_mediable');
            $table->index('type');
            $table->index('created_by');

            // Foreign key
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
