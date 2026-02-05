<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('webrtc_signals') && !Schema::hasColumn('webrtc_signals', 'sent_by')) {
            Schema::table('webrtc_signals', function (Blueprint $table) {
                $table->foreignId('sent_by')->nullable()->after('callee_id')->constrained('users')->cascadeOnDelete();
                $table->index(['sent_by', 'consumed']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('webrtc_signals') && Schema::hasColumn('webrtc_signals', 'sent_by')) {
            Schema::table('webrtc_signals', function (Blueprint $table) {
                $table->dropForeign(['sent_by']);
                $table->dropColumn('sent_by');
            });
        }
    }
};
