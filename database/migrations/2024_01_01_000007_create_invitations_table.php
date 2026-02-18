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
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('inviter_id')->comment('Usuario que invita');
            $table->unsignedBigInteger('person_id')->comment('Persona a la que se invita');

            $table->string('email');
            $table->string('token', 100)->unique();

            $table->enum('status', ['pending', 'sent', 'accepted', 'declined', 'expired'])->default('pending');

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Indices
            $table->index('email');
            $table->index('status');

            // Foreign keys
            $table->foreign('inviter_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('person_id')->references('id')->on('persons')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
