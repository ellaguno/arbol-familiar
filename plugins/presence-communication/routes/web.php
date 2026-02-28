<?php

use Illuminate\Support\Facades\Route;
use Plugin\PresenceCommunication\Controllers\ChatController;
use Plugin\PresenceCommunication\Controllers\PresenceController;
use Plugin\PresenceCommunication\Controllers\WebRTCController;

Route::middleware(['web', 'auth', 'verified'])->group(function () {
    // Presencia (bucket propio: presence)
    Route::post('/presence/heartbeat', [PresenceController::class, 'heartbeat'])->name('presence.heartbeat')->middleware('throttle:10,1,presence');
    Route::get('/presence/online', [PresenceController::class, 'online'])->name('presence.online')->middleware('throttle:10,1,presence');
    Route::post('/presence/offline', [PresenceController::class, 'offline'])->name('presence.offline')->middleware('throttle:10,1,presence');

    // Chat (bucket propio: chat)
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/conversations', [ChatController::class, 'conversations'])->name('chat.conversations')->middleware('throttle:30,1,chat');
    Route::get('/chat/messages/{userId}', [ChatController::class, 'messages'])->name('chat.messages')->middleware('throttle:30,1,chat');
    Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send')->middleware('throttle:20,1,chat-send');
    Route::post('/chat/mark-read/{userId}', [ChatController::class, 'markRead'])->name('chat.mark-read')->middleware('throttle:30,1,chat');
    Route::get('/chat/unread-count', [ChatController::class, 'unreadCount'])->name('chat.unread-count')->middleware('throttle:30,1,chat');
    Route::get('/chat/unread-messages', [ChatController::class, 'unreadMessages'])->name('chat.unread-messages')->middleware('throttle:30,1,chat');
    Route::get('/chat/auth-status/{userId}', [ChatController::class, 'checkAuthStatus'])->name('chat.auth-status')->middleware('throttle:30,1,chat');

    // Chat grupal (bucket propio: chat / chat-send)
    Route::post('/chat/group/create', [ChatController::class, 'createGroup'])->name('chat.group.create')->middleware('throttle:10,1,chat');
    Route::get('/chat/group/{groupId}/messages', [ChatController::class, 'groupMessages'])->name('chat.group.messages')->middleware('throttle:30,1,chat');
    Route::post('/chat/group/{groupId}/send', [ChatController::class, 'sendGroupMessage'])->name('chat.group.send')->middleware('throttle:20,1,chat-send');
    Route::post('/chat/group/{groupId}/mark-read', [ChatController::class, 'markGroupRead'])->name('chat.group.mark-read')->middleware('throttle:30,1,chat');
    Route::post('/chat/group/{groupId}/add-participant', [ChatController::class, 'addGroupParticipant'])->name('chat.group.add-participant')->middleware('throttle:10,1,chat');
    Route::post('/chat/group/{groupId}/leave', [ChatController::class, 'leaveGroup'])->name('chat.group.leave')->middleware('throttle:10,1,chat');
    Route::get('/chat/group/{groupId}/info', [ChatController::class, 'groupInfo'])->name('chat.group.info')->middleware('throttle:30,1,chat');

    // WebRTC Calls (bucket propio: webrtc)
    Route::post('/call/initiate', [WebRTCController::class, 'initiateCall'])->name('call.initiate')->middleware('throttle:10,1,webrtc');
    Route::post('/call/respond', [WebRTCController::class, 'respondCall'])->name('call.respond')->middleware('throttle:10,1,webrtc');
    Route::post('/call/signal', [WebRTCController::class, 'signal'])->name('call.signal')->middleware('throttle:120,1,webrtc-signal');
    Route::get('/call/poll', [WebRTCController::class, 'pollSignals'])->name('call.poll')->middleware('throttle:30,1,webrtc-poll');
    Route::post('/call/end', [WebRTCController::class, 'endCall'])->name('call.end')->middleware('throttle:10,1,webrtc');

    // Group calling (rooms)
    Route::post('/call/room/add', [WebRTCController::class, 'addParticipant'])->name('call.room.add')->middleware('throttle:10,1,webrtc');
    Route::post('/call/room/respond', [WebRTCController::class, 'respondRoomInvite'])->name('call.room.respond')->middleware('throttle:10,1,webrtc');
    Route::get('/call/room/info', [WebRTCController::class, 'getRoomInfo'])->name('call.room.info')->middleware('throttle:30,1,webrtc');
});
