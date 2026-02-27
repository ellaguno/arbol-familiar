<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Agrega tipo 'chat_request' al enum type de messages.
     * Usado para solicitudes de chat entre usuarios no-familia.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE messages MODIFY COLUMN type ENUM(
            'invitation','consent_request','relationship_found','general','system',
            'person_claim','person_merge','family_edit_request','broadcast',
            'relationship_claim','chat_request'
        )");
    }

    public function down(): void
    {
        DB::table('messages')->where('type', 'chat_request')->update(['type' => 'general']);

        DB::statement("ALTER TABLE messages MODIFY COLUMN type ENUM(
            'invitation','consent_request','relationship_found','general','system',
            'person_claim','person_merge','family_edit_request','broadcast',
            'relationship_claim'
        )");
    }
};
