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
        Schema::create('persons', function (Blueprint $table) {
            $table->id();
            $table->string('gedcom_id', 50)->nullable()->comment('ID para compatibilidad GEDCOM (@I1@, etc)');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Si la persona tiene cuenta en el sistema');

            // Identificacion
            $table->string('first_name', 100);
            $table->string('patronymic', 100)->comment('Apellido paterno');
            $table->string('matronymic', 100)->nullable()->comment('Apellido materno');
            $table->string('nickname', 100)->nullable();
            $table->enum('gender', ['M', 'F', 'U'])->default('U')->comment('M=Masculino, F=Femenino, U=Desconocido');

            // Nacimiento
            $table->date('birth_date')->nullable();
            $table->boolean('birth_date_approx')->default(false)->comment('Fecha aproximada');
            $table->string('birth_place')->nullable();
            $table->string('birth_country', 100)->nullable();

            // Defuncion
            $table->date('death_date')->nullable();
            $table->boolean('death_date_approx')->default(false);
            $table->string('death_place')->nullable();
            $table->string('death_country', 100)->nullable();

            // Estado actual
            $table->boolean('is_living')->default(true);
            $table->boolean('is_minor')->default(false)->comment('Menor de 18 anos');

            // Residencia actual
            $table->string('residence_place')->nullable();
            $table->string('residence_country', 100)->nullable();

            // Informacion adicional
            $table->string('occupation')->nullable();
            $table->string('email')->nullable()->comment('Email de contacto');
            $table->string('phone', 50)->nullable();

            // Origen / herencia etnica
            $table->boolean('has_ethnic_heritage')->default(false);
            $table->enum('heritage_region', ['region_1', 'region_2', 'region_3', 'region_4', 'other', 'unknown'])->nullable();
            $table->string('origin_town')->nullable()->comment('Poblacion de origen');
            $table->string('migration_decade', 20)->nullable()->comment('Decada de migracion');
            $table->string('migration_destination', 100)->nullable()->comment('Primer pais de destino');

            // Multimedia
            $table->string('photo_path', 500)->nullable();

            // Privacidad y consentimiento
            $table->enum('privacy_level', ['private', 'family', 'community', 'public'])->default('family');
            $table->enum('consent_status', ['pending', 'approved', 'denied', 'not_required'])->default('not_required');
            $table->timestamp('consent_requested_at')->nullable();
            $table->timestamp('consent_responded_at')->nullable();

            // Auditoria
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Indices
            $table->index('user_id');
            $table->index(['first_name', 'patronymic'], 'idx_persons_names');
            $table->index(['has_ethnic_heritage', 'heritage_region'], 'idx_persons_heritage');
            $table->index('is_living');
            $table->index('created_by');
            $table->fullText(['first_name', 'patronymic', 'matronymic'], 'idx_persons_fulltext');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persons');
    }
};
