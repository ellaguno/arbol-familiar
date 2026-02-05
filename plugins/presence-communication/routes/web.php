<?php

use Illuminate\Support\Facades\Route;
use Plugin\PresenceCommunication\Controllers\ChatController;
use Plugin\PresenceCommunication\Controllers\PresenceController;
use Plugin\PresenceCommunication\Controllers\WebRTCController;

Route::middleware(['web', 'auth', 'verified'])->group(function () {
    // Presencia
    Route::post('/presence/heartbeat', [PresenceController::class, 'heartbeat'])->name('presence.heartbeat');
    Route::get('/presence/online', [PresenceController::class, 'online'])->name('presence.online');
    Route::post('/presence/offline', [PresenceController::class, 'offline'])->name('presence.offline');

    // Chat
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/conversations', [ChatController::class, 'conversations'])->name('chat.conversations');
    Route::get('/chat/messages/{userId}', [ChatController::class, 'messages'])->name('chat.messages');
    Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');
    Route::post('/chat/mark-read/{userId}', [ChatController::class, 'markRead'])->name('chat.mark-read');
    Route::get('/chat/unread-count', [ChatController::class, 'unreadCount'])->name('chat.unread-count');
    Route::get('/chat/unread-messages', [ChatController::class, 'unreadMessages'])->name('chat.unread-messages');

    // WebRTC Calls
    Route::post('/call/initiate', [WebRTCController::class, 'initiateCall'])->name('call.initiate');
    Route::post('/call/respond', [WebRTCController::class, 'respondCall'])->name('call.respond');
    Route::post('/call/signal', [WebRTCController::class, 'signal'])->name('call.signal');
    Route::get('/call/poll', [WebRTCController::class, 'pollSignals'])->name('call.poll');
    Route::post('/call/end', [WebRTCController::class, 'endCall'])->name('call.end');
});
