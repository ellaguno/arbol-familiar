<?php

use Illuminate\Support\Facades\Route;
use Plugin\PresenceCommunication\Controllers\ChatController;
use Plugin\PresenceCommunication\Controllers\PresenceController;
use Plugin\PresenceCommunication\Controllers\WebRTCController;

Route::middleware(['web', 'auth', 'verified'])->group(function () {
    // Presencia
    Route::post('/presence/heartbeat', [PresenceController::class, 'heartbeat'])->name('presence.heartbeat')->middleware('throttle:30,1');
    Route::get('/presence/online', [PresenceController::class, 'online'])->name('presence.online')->middleware('throttle:60,1');
    Route::post('/presence/offline', [PresenceController::class, 'offline'])->name('presence.offline')->middleware('throttle:10,1');

    // Chat
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/conversations', [ChatController::class, 'conversations'])->name('chat.conversations')->middleware('throttle:60,1');
    Route::get('/chat/messages/{userId}', [ChatController::class, 'messages'])->name('chat.messages')->middleware('throttle:60,1');
    Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send')->middleware('throttle:30,1');
    Route::post('/chat/mark-read/{userId}', [ChatController::class, 'markRead'])->name('chat.mark-read')->middleware('throttle:60,1');
    Route::get('/chat/unread-count', [ChatController::class, 'unreadCount'])->name('chat.unread-count')->middleware('throttle:60,1');
    Route::get('/chat/unread-messages', [ChatController::class, 'unreadMessages'])->name('chat.unread-messages')->middleware('throttle:60,1');

    // WebRTC Calls
    Route::post('/call/initiate', [WebRTCController::class, 'initiateCall'])->name('call.initiate')->middleware('throttle:10,1');
    Route::post('/call/respond', [WebRTCController::class, 'respondCall'])->name('call.respond')->middleware('throttle:10,1');
    Route::post('/call/signal', [WebRTCController::class, 'signal'])->name('call.signal')->middleware('throttle:120,1');
    Route::get('/call/poll', [WebRTCController::class, 'pollSignals'])->name('call.poll')->middleware('throttle:30,1');
    Route::post('/call/end', [WebRTCController::class, 'endCall'])->name('call.end')->middleware('throttle:10,1');

    // Group calling (rooms)
    Route::post('/call/room/add', [WebRTCController::class, 'addParticipant'])->name('call.room.add')->middleware('throttle:10,1');
    Route::post('/call/room/respond', [WebRTCController::class, 'respondRoomInvite'])->name('call.room.respond')->middleware('throttle:10,1');
    Route::get('/call/room/info', [WebRTCController::class, 'getRoomInfo'])->name('call.room.info')->middleware('throttle:30,1');
});
