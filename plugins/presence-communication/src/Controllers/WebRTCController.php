<?php

namespace Plugin\PresenceCommunication\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Plugin\PresenceCommunication\Models\UserPresence;
use Plugin\PresenceCommunication\Models\WebrtcRoom;
use Plugin\PresenceCommunication\Models\WebrtcSignal;

class WebRTCController extends Controller
{
    /**
     * Iniciar una llamada.
     */
    public function initiateCall(Request $request): JsonResponse
    {
        $request->validate([
            'callee_id' => ['required', 'integer', 'exists:users,id'],
            'media_type' => ['required', 'in:voice,video'],
        ]);

        $caller = Auth::user();
        $calleeId = $request->input('callee_id');

        if ($calleeId == $caller->id) {
            return response()->json(['error' => __('No puedes llamarte a ti mismo.')], 422);
        }

        // Verificar que el callee esta online
        $calleeOnline = UserPresence::where('user_id', $calleeId)
            ->where('last_seen_at', '>=', now()->subMinutes(2))
            ->exists();

        if (!$calleeOnline) {
            return response()->json(['error' => __('El usuario no esta en linea.')], 422);
        }

        // Crear room para la llamada
        $room = WebrtcRoom::create([
            'created_by' => $caller->id,
            'media_type' => $request->input('media_type'),
            'status' => WebrtcRoom::STATUS_ACTIVE,
        ]);

        // Agregar caller como participante
        $room->addParticipant($caller->id);

        // Limpiar senales antiguas entre estos usuarios (solo las sin room)
        WebrtcSignal::where(function ($q) use ($caller, $calleeId) {
            $q->where(function ($inner) use ($caller, $calleeId) {
                $inner->where('caller_id', $caller->id)->where('callee_id', $calleeId);
            })->orWhere(function ($inner) use ($caller, $calleeId) {
                $inner->where('caller_id', $calleeId)->where('callee_id', $caller->id);
            });
        })->whereNull('room_id')->delete();

        $signal = WebrtcSignal::create([
            'room_id' => $room->id,
            'caller_id' => $caller->id,
            'callee_id' => $calleeId,
            'sent_by' => $caller->id,
            'type' => 'call-request',
            'media_type' => $request->input('media_type'),
        ]);

        return response()->json([
            'status' => 'ok',
            'signal_id' => $signal->id,
            'room_id' => $room->id,
        ]);
    }

    /**
     * Responder a una llamada (accept/reject).
     */
    public function respondCall(Request $request): JsonResponse
    {
        $request->validate([
            'caller_id' => ['required', 'integer', 'exists:users,id'],
            'response' => ['required', 'in:accept,reject'],
        ]);

        $callee = Auth::user();
        $callerId = $request->input('caller_id');
        $responseType = $request->input('response') === 'accept' ? 'call-accept' : 'call-reject';

        // Obtener el media_type y room_id de la solicitud original
        $originalRequest = WebrtcSignal::where('caller_id', $callerId)
            ->where('callee_id', $callee->id)
            ->where('type', 'call-request')
            ->latest()
            ->first();

        if (!$originalRequest) {
            return response()->json(['error' => __('No hay solicitud de llamada pendiente.')], 404);
        }

        $roomId = $originalRequest->room_id;
        $mediaType = $originalRequest->media_type;

        WebrtcSignal::create([
            'room_id' => $roomId,
            'caller_id' => $callerId,
            'callee_id' => $callee->id,
            'sent_by' => $callee->id,
            'type' => $responseType,
            'media_type' => $mediaType,
        ]);

        // Si aceptó, agregar como participante del room
        if ($responseType === 'call-accept' && $roomId) {
            $room = WebrtcRoom::find($roomId);
            if ($room) {
                $room->addParticipant($callee->id);
            }
        }

        return response()->json([
            'status' => 'ok',
            'room_id' => $roomId,
            'media_type' => $mediaType,
        ]);
    }

    /**
     * Enviar senal WebRTC (offer, answer, ice-candidate).
     */
    public function signal(Request $request): JsonResponse
    {
        $request->validate([
            'peer_id' => ['required', 'integer', 'exists:users,id'],
            'type' => ['required', 'in:offer,answer,ice-candidate'],
            'payload' => ['required', 'string', 'max:65535'],
            'target_id' => ['nullable', 'integer', 'exists:users,id'],
            'room_id' => ['nullable', 'integer', 'exists:webrtc_rooms,id'],
        ]);

        $currentUser = Auth::user();
        $peerId = $request->input('peer_id');
        $targetId = $request->input('target_id', $peerId);
        $roomId = $request->input('room_id');

        // Verificar autorizacion
        if ($roomId) {
            // Verificar que el usuario es participante del room
            $room = WebrtcRoom::active()->find($roomId);
            if (!$room || !$room->hasParticipant($currentUser->id)) {
                return response()->json(['error' => __('No hay llamada activa.')], 422);
            }
        } else {
            // Verificar que hay una llamada activa entre estos usuarios
            $hasActiveCall = WebrtcSignal::where(function ($q) use ($currentUser, $peerId) {
                $q->where(function ($inner) use ($currentUser, $peerId) {
                    $inner->where('caller_id', $currentUser->id)->where('callee_id', $peerId);
                })->orWhere(function ($inner) use ($currentUser, $peerId) {
                    $inner->where('caller_id', $peerId)->where('callee_id', $currentUser->id);
                });
            })->whereIn('type', ['call-request', 'call-accept'])->exists();

            if (!$hasActiveCall) {
                return response()->json(['error' => __('No hay llamada activa.')], 422);
            }
        }

        // Determinar caller_id y callee_id segun quien envia
        $existingSignal = WebrtcSignal::where(function ($q) use ($currentUser, $peerId) {
            $q->where(function ($inner) use ($currentUser, $peerId) {
                $inner->where('caller_id', $currentUser->id)->where('callee_id', $peerId);
            })->orWhere(function ($inner) use ($currentUser, $peerId) {
                $inner->where('caller_id', $peerId)->where('callee_id', $currentUser->id);
            });
        })->where('type', 'call-request')->latest()->first();

        WebrtcSignal::create([
            'room_id' => $roomId,
            'caller_id' => $existingSignal->caller_id ?? $currentUser->id,
            'callee_id' => $existingSignal->callee_id ?? $peerId,
            'sent_by' => $currentUser->id,
            'target_id' => $targetId,
            'type' => $request->input('type'),
            'media_type' => $existingSignal->media_type ?? 'video',
            'payload' => $request->input('payload'),
        ]);

        return response()->json(['status' => 'ok']);
    }

    /**
     * Obtener senales pendientes para el usuario actual.
     */
    public function pollSignals(): JsonResponse
    {
        $currentUser = Auth::user();

        // Limpiar senales expiradas (10 minutos para soportar llamadas largas)
        WebrtcSignal::expired(10)->delete();

        $signals = DB::transaction(function () use ($currentUser) {
            $pending = WebrtcSignal::forUser($currentUser->id)
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->get();

            if ($pending->isNotEmpty()) {
                WebrtcSignal::whereIn('id', $pending->pluck('id'))->update(['consumed' => true]);
            }

            return $pending;
        });

        // Enriquecer con nombre del caller para llamadas entrantes
        $formatted = $signals->map(function ($signal) use ($currentUser) {
            $data = [
                'id' => $signal->id,
                'room_id' => $signal->room_id,
                'caller_id' => $signal->caller_id,
                'callee_id' => $signal->callee_id,
                'sent_by' => $signal->sent_by,
                'target_id' => $signal->target_id,
                'type' => $signal->type,
                'media_type' => $signal->media_type,
                'payload' => $signal->payload,
                'created_at' => $signal->created_at->toISOString(),
            ];

            // Agregar nombre del llamante para call-request
            if ($signal->type === 'call-request' && $signal->callee_id === $currentUser->id) {
                $caller = $signal->caller;
                $data['caller_name'] = $caller?->person
                    ? $caller->person->full_name
                    : (__('Usuario') . ' #' . ($caller?->id ?? ''));
            }

            // Agregar info del invitante para room-invite
            if ($signal->type === 'room-invite' && $signal->target_id === $currentUser->id) {
                $sender = $signal->sender;
                $data['sender_name'] = $sender?->person
                    ? $sender->person->full_name
                    : (__('Usuario') . ' #' . ($sender?->id ?? ''));

                // Incluir participantes actuales del room
                if ($signal->room_id) {
                    $room = WebrtcRoom::find($signal->room_id);
                    if ($room) {
                        $participantIds = $room->getParticipantIds();
                        $participants = \App\Models\User::whereIn('id', $participantIds)->get();
                        $data['room_participants'] = $participants->map(function ($user) {
                            return [
                                'id' => $user->id,
                                'name' => $user->person ? $user->person->full_name : (__('Usuario') . ' #' . $user->id),
                                'photo' => $user->person && $user->person->photo_path
                                    ? asset('storage/' . $user->person->photo_path) : null,
                            ];
                        })->toArray();
                    }
                }
            }

            // Para room-accept, incluir info del que se unio
            if ($signal->type === 'room-accept') {
                $sender = $signal->sender;
                $data['sender_name'] = $sender?->person
                    ? $sender->person->full_name
                    : (__('Usuario') . ' #' . ($sender?->id ?? ''));
                $data['sender_photo'] = $sender?->person && $sender->person->photo_path
                    ? asset('storage/' . $sender->person->photo_path) : null;
            }

            return $data;
        });

        return response()->json(['signals' => $formatted]);
    }

    /**
     * Finalizar una llamada o salir de un room.
     */
    public function endCall(Request $request): JsonResponse
    {
        $request->validate([
            'peer_id' => ['required', 'integer', 'exists:users,id'],
            'room_id' => ['nullable', 'integer', 'exists:webrtc_rooms,id'],
        ]);

        $currentUser = Auth::user();
        $peerId = $request->input('peer_id');
        $roomId = $request->input('room_id');

        if ($roomId) {
            $room = WebrtcRoom::find($roomId);

            if ($room) {
                $participantIds = $room->getParticipantIds();

                // Marcar usuario como salido
                $room->removeParticipant($currentUser->id);

                $remainingIds = array_values(array_diff($participantIds, [$currentUser->id]));

                if (count($remainingIds) > 1) {
                    // Mas de 1 restante: enviar room-leave a cada uno
                    foreach ($remainingIds as $pid) {
                        WebrtcSignal::create([
                            'room_id' => $roomId,
                            'caller_id' => $currentUser->id,
                            'callee_id' => $pid,
                            'sent_by' => $currentUser->id,
                            'target_id' => $pid,
                            'type' => 'room-leave',
                            'media_type' => $room->media_type,
                        ]);
                    }

                    return response()->json(['status' => 'ok']);
                }

                // 1 o 0 restantes: enviar call-end y cerrar room
                foreach ($remainingIds as $pid) {
                    WebrtcSignal::create([
                        'room_id' => $roomId,
                        'caller_id' => $currentUser->id,
                        'callee_id' => $pid,
                        'sent_by' => $currentUser->id,
                        'type' => 'call-end',
                        'media_type' => $room->media_type,
                    ]);
                }

                $room->update(['status' => WebrtcRoom::STATUS_ENDED]);

                return response()->json(['status' => 'ok']);
            }
        }

        // Sin room: llamada 1-a-1 legacy
        $existingSignal = WebrtcSignal::where(function ($q) use ($currentUser, $peerId) {
            $q->where(function ($inner) use ($currentUser, $peerId) {
                $inner->where('caller_id', $currentUser->id)->where('callee_id', $peerId);
            })->orWhere(function ($inner) use ($currentUser, $peerId) {
                $inner->where('caller_id', $peerId)->where('callee_id', $currentUser->id);
            });
        })->where('type', 'call-request')->latest()->first();

        WebrtcSignal::create([
            'room_id' => $roomId,
            'caller_id' => $existingSignal->caller_id ?? $currentUser->id,
            'callee_id' => $existingSignal->callee_id ?? $peerId,
            'sent_by' => $currentUser->id,
            'type' => 'call-end',
            'media_type' => $existingSignal->media_type ?? 'video',
        ]);

        return response()->json(['status' => 'ok']);
    }

    /**
     * Agregar un participante a un room activo.
     */
    public function addParticipant(Request $request): JsonResponse
    {
        $request->validate([
            'room_id' => ['required', 'integer', 'exists:webrtc_rooms,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $currentUser = Auth::user();
        $roomId = $request->input('room_id');
        $newUserId = $request->input('user_id');

        $room = WebrtcRoom::active()->find($roomId);
        if (!$room) {
            return response()->json(['error' => __('La sala no esta activa.')], 422);
        }

        // Verificar que el invitante participa en el room
        if (!$room->hasParticipant($currentUser->id)) {
            return response()->json(['error' => __('No perteneces a esta llamada.')], 403);
        }

        // Verificar que no esta lleno
        if ($room->isFull()) {
            return response()->json(['error' => __('La sala esta llena (maximo :max participantes).', ['max' => $room->max_participants])], 422);
        }

        // Verificar que el nuevo usuario no esta ya en la llamada
        if ($room->hasParticipant($newUserId)) {
            return response()->json(['error' => __('El usuario ya esta en la llamada.')], 422);
        }

        // Verificar que esta online
        $online = UserPresence::where('user_id', $newUserId)
            ->where('last_seen_at', '>=', now()->subMinutes(2))
            ->exists();

        if (!$online) {
            return response()->json(['error' => __('El usuario no esta en linea.')], 422);
        }

        // No invitarse a uno mismo
        if ($newUserId == $currentUser->id) {
            return response()->json(['error' => __('No puedes invitarte a ti mismo.')], 422);
        }

        // Verificar que no hay invitacion pendiente
        $pendingInvite = WebrtcSignal::where('room_id', $roomId)
            ->where('target_id', $newUserId)
            ->where('type', 'room-invite')
            ->where('consumed', false)
            ->exists();

        if ($pendingInvite) {
            return response()->json(['error' => __('Ya se envio una invitacion a este usuario.')], 422);
        }

        // Enviar room-invite dirigido al nuevo usuario
        WebrtcSignal::create([
            'room_id' => $roomId,
            'caller_id' => $currentUser->id,
            'callee_id' => $newUserId,
            'sent_by' => $currentUser->id,
            'target_id' => $newUserId,
            'type' => 'room-invite',
            'media_type' => $room->media_type,
        ]);

        return response()->json([
            'status' => 'ok',
            'participants' => $room->getParticipantIds(),
        ]);
    }

    /**
     * Responder a una invitacion de room.
     */
    public function respondRoomInvite(Request $request): JsonResponse
    {
        $request->validate([
            'room_id' => ['required', 'integer', 'exists:webrtc_rooms,id'],
            'response' => ['required', 'in:accept,reject'],
        ]);

        $currentUser = Auth::user();
        $roomId = $request->input('room_id');
        $response = $request->input('response');

        $room = WebrtcRoom::active()->find($roomId);
        if (!$room) {
            return response()->json(['error' => __('La sala no esta activa.')], 422);
        }

        // Verificar que el usuario fue invitado
        $wasInvited = WebrtcSignal::where('room_id', $roomId)
            ->where('target_id', $currentUser->id)
            ->where('type', 'room-invite')
            ->exists();

        if (!$wasInvited) {
            return response()->json(['error' => __('No tienes invitacion a esta sala.')], 403);
        }

        if ($response === 'reject') {
            return response()->json(['status' => 'ok']);
        }

        // Verificar que no esta lleno
        if ($room->isFull()) {
            return response()->json(['error' => __('La sala esta llena (maximo :max participantes).', ['max' => $room->max_participants])], 422);
        }

        // Agregar como participante
        $room->addParticipant($currentUser->id);

        // Enviar room-accept para que cada participante existente lo reciba
        $participantIds = $room->getParticipantIds();

        foreach ($participantIds as $pid) {
            if ($pid == $currentUser->id) continue;

            WebrtcSignal::create([
                'room_id' => $roomId,
                'caller_id' => $currentUser->id,
                'callee_id' => $pid,
                'sent_by' => $currentUser->id,
                'target_id' => $pid,
                'type' => 'room-accept',
                'media_type' => $room->media_type,
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'participants' => array_values(array_diff($participantIds, [$currentUser->id])),
            'media_type' => $room->media_type,
        ]);
    }

    /**
     * Obtener info del room (participantes activos).
     */
    public function getRoomInfo(Request $request): JsonResponse
    {
        $request->validate([
            'room_id' => ['required', 'integer', 'exists:webrtc_rooms,id'],
        ]);

        $currentUser = Auth::user();
        $room = WebrtcRoom::find($request->input('room_id'));

        if (!$room) {
            return response()->json(['error' => __('Sala no encontrada.')], 404);
        }

        // Verificar que el usuario participa o fue invitado
        $isParticipant = $room->hasParticipant($currentUser->id);
        $wasInvited = WebrtcSignal::where('room_id', $room->id)
            ->where('target_id', $currentUser->id)
            ->where('type', 'room-invite')
            ->exists();

        if (!$isParticipant && !$wasInvited) {
            return response()->json(['error' => __('No perteneces a esta llamada.')], 403);
        }

        $participantIds = $room->getParticipantIds();
        $participants = \App\Models\User::whereIn('id', $participantIds)->get();

        $formatted = $participants->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->person ? $user->person->full_name : (__('Usuario') . ' #' . $user->id),
                'photo' => $user->person && $user->person->photo_path
                    ? asset('storage/' . $user->person->photo_path) : null,
            ];
        });

        return response()->json([
            'status' => $room->status,
            'media_type' => $room->media_type,
            'participants' => $formatted,
        ]);
    }
}
