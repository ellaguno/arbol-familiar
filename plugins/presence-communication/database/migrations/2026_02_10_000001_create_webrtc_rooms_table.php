<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('webrtc_rooms')) {
            Schema::create('webrtc_rooms', function (Blueprint $table) {
                $table->id();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->enum('media_type', ['voice', 'video'])->default('video');
                $table->enum('status', ['active', 'ended'])->default('active');
                $table->tinyInteger('max_participants')->unsigned()->default(4);
                $table->timestamps();

                $table->index(['status', 'created_at']);
            });
        }

        if (!Schema::hasTable('webrtc_room_participants')) {
            Schema::create('webrtc_room_participants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('room_id')->constrained('webrtc_rooms')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('joined_at')->useCurrent();
                $table->timestamp('left_at')->nullable();

                $table->index(['room_id', 'left_at']);
                $table->index(['user_id', 'left_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('webrtc_room_participants');
        Schema::dropIfExists('webrtc_rooms');
    }
};
