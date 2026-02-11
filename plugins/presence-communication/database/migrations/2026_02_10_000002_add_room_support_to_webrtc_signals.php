<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add room_id and target_id columns
        if (!Schema::hasColumn('webrtc_signals', 'room_id')) {
            Schema::table('webrtc_signals', function (Blueprint $table) {
                $table->foreignId('room_id')->nullable()->after('id')->constrained('webrtc_rooms')->cascadeOnDelete();
                $table->foreignId('target_id')->nullable()->after('sent_by')->constrained('users')->cascadeOnDelete();
                $table->index(['room_id', 'consumed', 'created_at']);
                $table->index(['target_id', 'consumed', 'created_at']);
            });
        }

        // Convert enum 'type' to varchar to support new signal types
        // Only run if the column is still an enum (check by trying to detect column type)
        if (Schema::hasColumn('webrtc_signals', 'type') && !Schema::hasColumn('webrtc_signals', 'type_new')) {
            Schema::table('webrtc_signals', function (Blueprint $table) {
                $table->string('type_new', 20)->after('type');
            });

            DB::table('webrtc_signals')->update(['type_new' => DB::raw('`type`')]);

            Schema::table('webrtc_signals', function (Blueprint $table) {
                $table->dropColumn('type');
            });

            Schema::table('webrtc_signals', function (Blueprint $table) {
                $table->renameColumn('type_new', 'type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('webrtc_signals', 'target_id')) {
            Schema::table('webrtc_signals', function (Blueprint $table) {
                $table->dropForeign(['target_id']);
                $table->dropColumn('target_id');
            });
        }

        if (Schema::hasColumn('webrtc_signals', 'room_id')) {
            Schema::table('webrtc_signals', function (Blueprint $table) {
                $table->dropForeign(['room_id']);
                $table->dropColumn('room_id');
            });
        }
    }
};
