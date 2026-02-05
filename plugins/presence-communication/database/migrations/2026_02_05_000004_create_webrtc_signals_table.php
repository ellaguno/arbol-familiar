<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('webrtc_signals')) {
            Schema::create('webrtc_signals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('caller_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('callee_id')->constrained('users')->cascadeOnDelete();
                $table->enum('type', [
                    'call-request',
                    'call-accept',
                    'call-reject',
                    'call-end',
                    'offer',
                    'answer',
                    'ice-candidate',
                ]);
                $table->enum('media_type', ['voice', 'video'])->default('video');
                $table->text('payload')->nullable();
                $table->boolean('consumed')->default(false);
                $table->timestamps();

                $table->index(['callee_id', 'consumed', 'created_at']);
                $table->index(['caller_id', 'consumed', 'created_at']);
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('webrtc_signals');
    }
};
