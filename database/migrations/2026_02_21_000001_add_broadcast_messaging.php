<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Agregar 'broadcast' al enum type
        DB::statement("ALTER TABLE messages MODIFY COLUMN type ENUM('invitation','consent_request','relationship_found','general','system','person_claim','person_merge','family_edit_request','broadcast')");

        // 2. Hacer recipient_id nullable
        // Drop foreign key y indices que incluyen recipient_id
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['recipient_id']);
            $table->dropIndex('idx_messages_unread');
            $table->dropIndex('idx_messages_action');
            $table->dropIndex('messages_recipient_id_index');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('recipient_id')->nullable()->change();
            $table->string('broadcast_scope', 20)->nullable()->after('type');
        });

        // Re-crear FK e indices
        Schema::table('messages', function (Blueprint $table) {
            $table->foreign('recipient_id')->references('id')->on('users')->nullOnDelete();
            $table->index('recipient_id', 'messages_recipient_id_index');
            $table->index(['recipient_id', 'read_at'], 'idx_messages_unread');
            $table->index(['recipient_id', 'action_required', 'action_status'], 'idx_messages_action');
        });

        // 3. Crear tabla message_recipients
        Schema::create('message_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table->foreign('message_id')->references('id')->on('messages')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->unique(['message_id', 'user_id']);
            $table->index(['user_id', 'deleted_at', 'read_at'], 'idx_mr_user_inbox');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_recipients');

        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('broadcast_scope');
        });

        // Revertir recipient_id a NOT NULL (eliminar broadcasts primero)
        DB::table('messages')->whereNull('recipient_id')->delete();

        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['recipient_id']);
            $table->dropIndex('idx_messages_unread');
            $table->dropIndex('idx_messages_action');
            $table->dropIndex('messages_recipient_id_index');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('recipient_id')->nullable(false)->change();
            $table->foreign('recipient_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index('recipient_id', 'messages_recipient_id_index');
            $table->index(['recipient_id', 'read_at'], 'idx_messages_unread');
            $table->index(['recipient_id', 'action_required', 'action_status'], 'idx_messages_action');
        });

        DB::statement("ALTER TABLE messages MODIFY COLUMN type ENUM('invitation','consent_request','relationship_found','general','system','person_claim','person_merge','family_edit_request')");
    }
};
