<?php

namespace Plugin\PresenceCommunication\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Plugin\PresenceCommunication\Events\ChatMessageSent;
use Plugin\PresenceCommunication\Models\ChatMessage;

class ChatController extends Controller
{
    /**
     * Vista principal del chat (pagina completa).
     */
    public function index()
    {
        return view('presence-communication::chat-window');
    }

    /**
     * Obtener conversaciones recientes del usuario.
     */
    public function conversations(): JsonResponse
    {
        $userId = Auth::id();

        // Obtener los usuarios con los que se ha conversado
        $conversations = ChatMessage::where('sender_id', $userId)
            ->orWhere('recipient_id', $userId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($msg) use ($userId) {
                return $msg->sender_id === $userId ? $msg->recipient_id : $msg->sender_id;
            })
            ->unique()
            ->values();

        $result = [];
        foreach ($conversations as $otherUserId) {
            $otherUser = User::with('person')->find($otherUserId);
            if (!$otherUser) {
                continue;
            }

            $otherPerson = $otherUser->person;
            $photoUrl = ($otherPerson && $otherPerson->photo_path)
                ? Storage::url($otherPerson->photo_path)
                : null;

            $lastMessage = ChatMessage::conversation($userId, $otherUserId)
                ->orderByDesc('created_at')
                ->first();

            $unreadCount = ChatMessage::where('sender_id', $otherUserId)
                ->where('recipient_id', $userId)
                ->unread()
                ->count();

            $result[] = [
                'user_id' => $otherUser->id,
                'name' => $otherPerson ? $otherPerson->full_name : $otherUser->email,
                'photo' => $photoUrl,
                'sex' => $otherPerson->sex ?? null,
                'last_message' => $lastMessage ? [
                    'message' => \Illuminate\Support\Str::limit($lastMessage->message, 50),
                    'created_at' => $lastMessage->created_at->toISOString(),
                    'is_mine' => $lastMessage->sender_id === $userId,
                ] : null,
                'unread_count' => $unreadCount,
            ];
        }

        return response()->json(['conversations' => $result]);
    }

    /**
     * Obtener mensajes de una conversacion.
     */
    public function messages(int $userId): JsonResponse
    {
        $currentUserId = Auth::id();

        $messages = ChatMessage::conversation($currentUserId, $userId)
            ->with('sender.person')
            ->orderBy('created_at')
            ->limit(100)
            ->get()
            ->map(function ($msg) use ($currentUserId) {
                $senderPerson = $msg->sender->person ?? null;
                $senderPhoto = ($senderPerson && $senderPerson->photo_path)
                    ? Storage::url($senderPerson->photo_path)
                    : null;

                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'recipient_id' => $msg->recipient_id,
                    'message' => $msg->message,
                    'is_mine' => $msg->sender_id === $currentUserId,
                    'read_at' => $msg->read_at?->toISOString(),
                    'created_at' => $msg->created_at->toISOString(),
                    'sender_name' => $senderPerson ? $senderPerson->full_name : ($msg->sender->email ?? ''),
                    'sender_photo' => $senderPhoto,
                ];
            });

        // Marcar como leidos los mensajes recibidos
        ChatMessage::where('sender_id', $userId)
            ->where('recipient_id', $currentUserId)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['messages' => $messages]);
    }

    /**
     * Enviar un mensaje.
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'recipient_id' => 'required|integer|exists:users,id',
            'message' => 'required|string|max:2000',
        ]);

        $senderId = Auth::id();
        $recipientId = (int) $request->input('recipient_id');

        // No permitir enviarse mensajes a si mismo
        if ($senderId === $recipientId) {
            return response()->json(['error' => __('No puedes enviarte mensajes a ti mismo')], 422);
        }

        $chatMessage = ChatMessage::create([
            'sender_id' => $senderId,
            'recipient_id' => $recipientId,
            'message' => $request->input('message'),
        ]);

        $chatMessage->load('sender.person');

        // Broadcast del evento
        try {
            event(new ChatMessageSent($chatMessage));
        } catch (\Throwable $e) {
            // No interrumpir si broadcasting no esta configurado
        }

        $senderPerson = $chatMessage->sender->person ?? null;

        return response()->json([
            'message' => [
                'id' => $chatMessage->id,
                'sender_id' => $chatMessage->sender_id,
                'recipient_id' => $chatMessage->recipient_id,
                'message' => $chatMessage->message,
                'is_mine' => true,
                'read_at' => null,
                'created_at' => $chatMessage->created_at->toISOString(),
                'sender_name' => $senderPerson ? $senderPerson->full_name : ($chatMessage->sender->email ?? ''),
            ],
        ]);
    }

    /**
     * Marcar mensajes como leidos.
     */
    public function markRead(int $userId): JsonResponse
    {
        $currentUserId = Auth::id();

        ChatMessage::where('sender_id', $userId)
            ->where('recipient_id', $currentUserId)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['status' => 'ok']);
    }

    /**
     * Obtener conteo de mensajes no leidos.
     */
    public function unreadCount(): JsonResponse
    {
        $count = ChatMessage::where('recipient_id', Auth::id())
            ->unread()
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Obtener mensajes no leidos recientes (para popup flotante).
     */
    public function unreadMessages(): JsonResponse
    {
        $userId = Auth::id();

        $messages = ChatMessage::where('recipient_id', $userId)
            ->unread()
            ->with('sender.person')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($msg) {
                $senderPerson = $msg->sender->person ?? null;
                $senderPhoto = ($senderPerson && $senderPerson->photo_path)
                    ? Storage::url($senderPerson->photo_path)
                    : null;

                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'message' => \Illuminate\Support\Str::limit($msg->message, 80),
                    'sender_name' => $senderPerson ? $senderPerson->full_name : ($msg->sender->email ?? ''),
                    'sender_photo' => $senderPhoto,
                    'created_at' => $msg->created_at->toISOString(),
                ];
            });

        return response()->json(['messages' => $messages]);
    }
}
