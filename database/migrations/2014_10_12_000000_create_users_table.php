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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password');
            $table->unsignedBigInteger('person_id')->nullable()->comment('Referencia a la persona que representa este usuario');
            $table->boolean('is_admin')->default(false);

            // Configuracion
            $table->enum('language', ['es', 'en'])->default('es');
            $table->string('theme_preference', 10)->default('default');
            $table->enum('privacy_level', ['direct_family', 'extended_family', 'selected_users', 'community'])->default('direct_family');

            // Verificacion
            $table->timestamp('email_verified_at')->nullable();
            $table->string('confirmation_code', 10)->nullable();
            $table->boolean('first_login_completed')->default(false);

            // Seguridad
            $table->rememberToken();
            $table->timestamp('last_login_at')->nullable();
            $table->unsignedTinyInteger('login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();

            $table->timestamps();

            // Indices
            $table->index('person_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
