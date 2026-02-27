<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de autorizaciones de chat entre usuarios no-familia.
     * user_a_id siempre es el menor ID, user_b_id el mayor,
     * para garantizar unicidad bidireccional.
     */
    public function up(): void
    {
        Schema::create('chat_authorizations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_a_id');
            $table->unsignedBigInteger('user_b_id');
            $table->unsignedBigInteger('message_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_a_id', 'user_b_id']);
            $table->foreign('user_a_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('user_b_id')->references('id')->on('users')->cascadeOnDelete();
        });

        // Retrocompatibilidad: autorizar todos los pares con conversaciones existentes
        if (Schema::hasTable('chat_messages')) {
            $existingPairs = DB::table('chat_messages')
                ->selectRaw('LEAST(sender_id, recipient_id) as user_a, GREATEST(sender_id, recipient_id) as user_b')
                ->distinct()
                ->get();

            foreach ($existingPairs as $pair) {
                DB::table('chat_authorizations')->insertOrIgnore([
                    'user_a_id' => $pair->user_a,
                    'user_b_id' => $pair->user_b,
                    'created_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_authorizations');
    }
};
