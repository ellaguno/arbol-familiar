<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('chat_groups')) {
            Schema::create('chat_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->tinyInteger('max_participants')->unsigned()->default(20);
                $table->enum('status', ['active', 'archived'])->default('active');
                $table->timestamps();

                $table->index(['status', 'updated_at']);
            });
        }

        if (!Schema::hasTable('chat_group_participants')) {
            Schema::create('chat_group_participants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('group_id')->constrained('chat_groups')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->enum('role', ['admin', 'member'])->default('member');
                $table->timestamp('joined_at')->useCurrent();
                $table->timestamp('left_at')->nullable();

                $table->index(['group_id', 'left_at']);
                $table->index(['user_id', 'left_at']);
            });
        }

        if (!Schema::hasTable('chat_group_read_status')) {
            Schema::create('chat_group_read_status', function (Blueprint $table) {
                $table->id();
                $table->foreignId('group_id')->constrained('chat_groups')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedBigInteger('last_read_message_id')->nullable();
                $table->timestamp('last_read_at')->nullable();

                $table->unique(['group_id', 'user_id']);

                $table->foreign('last_read_message_id')
                    ->references('id')
                    ->on('chat_messages')
                    ->nullOnDelete();
            });
        }

        // Agregar soporte grupal a chat_messages
        if (!Schema::hasColumn('chat_messages', 'chat_group_id')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->foreignId('chat_group_id')
                    ->nullable()
                    ->after('recipient_id')
                    ->constrained('chat_groups')
                    ->cascadeOnDelete();

                $table->index('chat_group_id');
            });

            // Hacer recipient_id nullable para mensajes grupales
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->unsignedBigInteger('recipient_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('chat_messages', 'chat_group_id')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->dropForeign(['chat_group_id']);
                $table->dropIndex(['chat_group_id']);
                $table->dropColumn('chat_group_id');
            });
        }

        Schema::dropIfExists('chat_group_read_status');
        Schema::dropIfExists('chat_group_participants');
        Schema::dropIfExists('chat_groups');
    }
};
