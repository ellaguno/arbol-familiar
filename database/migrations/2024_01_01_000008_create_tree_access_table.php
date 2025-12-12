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
        Schema::create('tree_access', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('owner_id')->comment('Dueño del árbol');
            $table->unsignedBigInteger('accessor_id')->comment('Usuario con acceso');

            $table->enum('access_level', ['view_basic', 'view_full', 'edit'])->default('view_basic');
            $table->boolean('include_documents')->default(false)->comment('Puede ver documentos e imagenes');

            $table->timestamp('granted_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();

            // Unique constraint
            $table->unique(['owner_id', 'accessor_id'], 'unique_tree_access');
            $table->index('accessor_id');

            // Foreign keys
            $table->foreign('owner_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('accessor_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tree_access');
    }
};
