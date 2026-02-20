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
                $table->index(['room_id', 'consumed', 'created_at']);
            });
        }

        if (!Schema::hasColumn('webrtc_signals', 'target_id')) {
            Schema::table('webrtc_signals', function (Blueprint $table) {
                $table->foreignId('target_id')->nullable()->after('sent_by')->constrained('users')->cascadeOnDelete();
                $table->index(['target_id', 'consumed', 'created_at']);
            });
        }

        // Convert enum 'type' to varchar to support new signal types
        // Handle all possible states from partial migration runs:

        $hasType = Schema::hasColumn('webrtc_signals', 'type');
        $hasTypeNew = Schema::hasColumn('webrtc_signals', 'type_new');

        if ($hasType && !$hasTypeNew) {
            // Normal case: type exists as enum, needs conversion
            // Check if it's already a varchar (idempotent re-run)
            $colType = DB::selectOne("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'webrtc_signals' AND COLUMN_NAME = 'type'");
            if ($colType && str_starts_with($colType->COLUMN_TYPE, 'enum')) {
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
            // If already varchar, nothing to do
        } elseif (!$hasType && $hasTypeNew) {
            // Partial failure: type was dropped but type_new wasn't renamed
            Schema::table('webrtc_signals', function (Blueprint $table) {
                $table->renameColumn('type_new', 'type');
            });
        } elseif (!$hasType && !$hasTypeNew) {
            // Column missing entirely: recreate as varchar
            Schema::table('webrtc_signals', function (Blueprint $table) {
                $table->string('type', 20)->after('callee_id');
            });
        }
        // If both exist somehow, drop type_new (data already in type)
        if (Schema::hasColumn('webrtc_signals', 'type') && Schema::hasColumn('webrtc_signals', 'type_new')) {
            Schema::table('webrtc_signals', function (Blueprint $table) {
                $table->dropColumn('type_new');
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
