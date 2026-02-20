<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->string('attachment_path')->nullable()->after('message');
            $table->string('attachment_type', 50)->nullable()->after('attachment_path');
        });

        DB::statement('ALTER TABLE chat_messages MODIFY message TEXT NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE chat_messages SET message = '' WHERE message IS NULL");
        DB::statement('ALTER TABLE chat_messages MODIFY message TEXT NOT NULL');

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn(['attachment_path', 'attachment_type']);
        });
    }
};
