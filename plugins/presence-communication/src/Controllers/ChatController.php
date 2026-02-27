<?php

namespace Plugin\PresenceCommunication\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Plugin\PresenceCommunication\Events\ChatMessageSent;
use Plugin\PresenceCommunication\Models\ChatAuthorization;
use Plugin\PresenceCommunication\Models\ChatMessage;
use Plugin\PresenceCommunication\Traits\ChecksFamilyRelation;

class ChatController extends Controller
{
    use ChecksFamilyRelation;

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
                'name' => $otherPerson ? $otherPerson->full_name : (__('Usuario') . ' #' . $otherUser->id),
                'photo' => $photoUrl,
                'sex' => $otherPerson->sex ?? null,
                'last_message' => $lastMessage ? [
                    'message' => $lastMessage->message
                        ? Str::limit($lastMessage->message, 50)
                        : ($lastMessage->hasAttachment() ? __('[Imagen]') : ''),
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
                    'attachment_url' => $msg->attachment_url,
                    'attachment_type' => $msg->attachment_type,
                    'is_mine' => $msg->sender_id === $currentUserId,
                    'read_at' => $msg->read_at?->toISOString(),
                    'created_at' => $msg->created_at->toISOString(),
                    'sender_name' => $senderPerson ? $senderPerson->full_name : (__('Usuario') . ' #' . ($msg->sender->id ?? '')),
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
     * Verificar estado de autorizacion de chat con otro usuario.
     * Retorna: 'family', 'authorized', 'pending', 'none'
     */
    public function checkAuthStatus(int $userId): JsonResponse
    {
        $currentUser = Auth::user();

        // Admins pueden chatear con todos
        if ($currentUser->isAdmin()) {
            return response()->json(['status' => 'authorized']);
        }

        $otherUser = User::with('person')->find($userId);
        if (!$otherUser) {
            return response()->json(['status' => 'none']);
        }

        // Verificar relacion familiar
        $currentPerson = $currentUser->person;
        $otherPerson = $otherUser->person;
        if ($this->isFamilyOf($currentPerson, $otherPerson)) {
            return response()->json(['status' => 'family']);
        }

        // Verificar autorizacion existente
        if (ChatAuthorization::isAuthorized($currentUser->id, $userId)) {
            return response()->json(['status' => 'authorized']);
        }

        // Verificar solicitud pendiente (en cualquier direccion)
        $pending = Message::where('type', 'chat_request')
            ->where('action_status', 'pending')
            ->where(function ($q) use ($currentUser, $userId) {
                $q->where(function ($q2) use ($currentUser, $userId) {
                    $q2->where('sender_id', $currentUser->id)->where('recipient_id', $userId);
                })->orWhere(function ($q2) use ($currentUser, $userId) {
                    $q2->where('sender_id', $userId)->where('recipient_id', $currentUser->id);
                });
            })
            ->exists();

        if ($pending) {
            return response()->json(['status' => 'pending']);
        }

        return response()->json(['status' => 'none']);
    }

    /**
     * Enviar un mensaje.
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'recipient_id' => 'required|integer|exists:users,id',
            'message' => 'nullable|string|max:2000',
            'attachment' => 'nullable|file|image|mimes:jpg,jpeg,png,gif,webp|max:3072',
        ], [
            'attachment.image' => __('El archivo debe ser una imagen.'),
            'attachment.mimes' => __('Formato permitido: JPG, PNG, GIF o WebP.'),
            'attachment.max' => __('La imagen no debe superar 3 MB.'),
        ]);

        // Al menos mensaje o adjunto requerido
        if (!$request->filled('message') && !$request->hasFile('attachment')) {
            return response()->json(['error' => __('Escribe un mensaje o adjunta una imagen.')], 422);
        }

        $senderId = Auth::id();
        $recipientId = (int) $request->input('recipient_id');

        // No permitir enviarse mensajes a si mismo
        if ($senderId === $recipientId) {
            return response()->json(['error' => __('No puedes enviarte mensajes a ti mismo')], 422);
        }

        // --- Gate de autorizacion ---
        $currentUser = Auth::user();
        $recipientUser = User::with('person')->find($recipientId);
        $needsAuth = true;

        // Admins bypass
        if ($currentUser->isAdmin()) {
            $needsAuth = false;
        }

        // Familia bypass
        if ($needsAuth) {
            $currentPerson = $currentUser->person;
            $otherPerson = $recipientUser ? $recipientUser->person : null;
            if ($this->isFamilyOf($currentPerson, $otherPerson)) {
                $needsAuth = false;
            }
        }

        // Autorizacion existente bypass
        if ($needsAuth && ChatAuthorization::isAuthorized($senderId, $recipientId)) {
            $needsAuth = false;
        }

        if ($needsAuth) {
            // Verificar si el otro usuario ya nos envio solicitud (auto-aceptar)
            $reverseRequest = Message::where('type', 'chat_request')
                ->where('action_status', 'pending')
                ->where('sender_id', $recipientId)
                ->where('recipient_id', $senderId)
                ->first();

            if ($reverseRequest) {
                $reverseRequest->accept();
                ChatAuthorization::authorize($senderId, $recipientId, $reverseRequest->id);

                // Entregar mensaje inicial del otro usuario si existe
                $reverseMeta = $reverseRequest->metadata ?? [];
                if (!empty($reverseMeta['initial_message'])) {
                    ChatMessage::create([
                        'sender_id' => $recipientId,
                        'recipient_id' => $senderId,
                        'message' => $reverseMeta['initial_message'],
                    ]);
                }

                $needsAuth = false;
            }
        }

        if ($needsAuth) {
            // Verificar si ya hay solicitud pendiente (en cualquier direccion)
            $existingRequest = Message::where('type', 'chat_request')
                ->where('action_status', 'pending')
                ->where(function ($q) use ($senderId, $recipientId) {
                    $q->where(function ($q2) use ($senderId, $recipientId) {
                        $q2->where('sender_id', $senderId)->where('recipient_id', $recipientId);
                    })->orWhere(function ($q2) use ($senderId, $recipientId) {
                        $q2->where('sender_id', $recipientId)->where('recipient_id', $senderId);
                    });
                })
                ->exists();

            if ($existingRequest) {
                return response()->json([
                    'error' => __('Ya tienes una solicitud de chat pendiente con este usuario.'),
                    'auth_status' => 'pending',
                ], 403);
            }

            // Crear solicitud de chat via Message
            $senderPerson = $currentUser->person;
            $senderName = $senderPerson ? $senderPerson->full_name : $currentUser->email;

            Message::create([
                'sender_id' => $senderId,
                'recipient_id' => $recipientId,
                'type' => 'chat_request',
                'subject' => __('Solicitud de chat'),
                'body' => __(':name quiere enviarte un mensaje por chat. ¿Deseas aceptar la conversacion?', [
                    'name' => $senderName,
                ]),
                'metadata' => [
                    'initial_message' => $request->input('message'),
                ],
                'action_required' => true,
                'action_status' => 'pending',
                'created_at' => now(),
            ]);

            return response()->json([
                'auth_status' => 'request_sent',
                'message_text' => __('Se ha enviado una solicitud de chat. Te notificaremos cuando responda.'),
            ], 202);
        }
        // --- Fin gate de autorizacion ---

        $attachmentPath = null;
        $attachmentType = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('chat-attachments', 'public');
            $attachmentType = $file->getMimeType();
        }

        $chatMessage = ChatMessage::create([
            'sender_id' => $senderId,
            'recipient_id' => $recipientId,
            'message' => $request->input('message'),
            'attachment_path' => $attachmentPath,
            'attachment_type' => $attachmentType,
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
                'attachment_url' => $chatMessage->attachment_url,
                'attachment_type' => $chatMessage->attachment_type,
                'is_mine' => true,
                'read_at' => null,
                'created_at' => $chatMessage->created_at->toISOString(),
                'sender_name' => $senderPerson ? $senderPerson->full_name : (__('Usuario') . ' #' . ($chatMessage->sender->id ?? '')),
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
                    'message' => $msg->message
                        ? Str::limit($msg->message, 80)
                        : ($msg->hasAttachment() ? __('[Imagen]') : ''),
                    'sender_name' => $senderPerson ? $senderPerson->full_name : (__('Usuario') . ' #' . ($msg->sender->id ?? '')),
                    'sender_photo' => $senderPhoto,
                    'created_at' => $msg->created_at->toISOString(),
                ];
            });

        return response()->json(['messages' => $messages]);
    }
}
