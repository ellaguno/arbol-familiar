<?php

namespace Plugin\PresenceCommunication\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Plugin\PresenceCommunication\Models\UserPresence;
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

        // Limpiar senales antiguas entre estos usuarios
        WebrtcSignal::where(function ($q) use ($caller, $calleeId) {
            $q->where('caller_id', $caller->id)->where('callee_id', $calleeId);
        })->orWhere(function ($q) use ($caller, $calleeId) {
            $q->where('caller_id', $calleeId)->where('callee_id', $caller->id);
        })->delete();

        $signal = WebrtcSignal::create([
            'caller_id' => $caller->id,
            'callee_id' => $calleeId,
            'type' => 'call-request',
            'media_type' => $request->input('media_type'),
        ]);

        return response()->json([
            'status' => 'ok',
            'signal_id' => $signal->id,
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

        // Obtener el media_type de la solicitud original
        $originalRequest = WebrtcSignal::where('caller_id', $callerId)
            ->where('callee_id', $callee->id)
            ->where('type', 'call-request')
            ->latest()
            ->first();

        WebrtcSignal::create([
            'caller_id' => $callerId,
            'callee_id' => $callee->id,
            'type' => $responseType,
            'media_type' => $originalRequest->media_type ?? 'video',
        ]);

        return response()->json(['status' => 'ok']);
    }

    /**
     * Enviar senal WebRTC (offer, answer, ice-candidate).
     */
    public function signal(Request $request): JsonResponse
    {
        $request->validate([
            'peer_id' => ['required', 'integer', 'exists:users,id'],
            'type' => ['required', 'in:offer,answer,ice-candidate'],
            'payload' => ['required', 'string'],
        ]);

        $currentUser = Auth::user();
        $peerId = $request->input('peer_id');

        // Verificar que hay una llamada activa entre estos usuarios
        $hasActiveCall = WebrtcSignal::where(function ($q) use ($currentUser, $peerId) {
            $q->where('caller_id', $currentUser->id)->where('callee_id', $peerId);
        })->orWhere(function ($q) use ($currentUser, $peerId) {
            $q->where('caller_id', $peerId)->where('callee_id', $currentUser->id);
        })->whereIn('type', ['call-request', 'call-accept'])->exists();

        if (!$hasActiveCall) {
            return response()->json(['error' => __('No hay llamada activa.')], 422);
        }

        // Determinar caller_id y callee_id segun quien envia
        $existingSignal = WebrtcSignal::where(function ($q) use ($currentUser, $peerId) {
            $q->where('caller_id', $currentUser->id)->where('callee_id', $peerId);
        })->orWhere(function ($q) use ($currentUser, $peerId) {
            $q->where('caller_id', $peerId)->where('callee_id', $currentUser->id);
        })->where('type', 'call-request')->latest()->first();

        WebrtcSignal::create([
            'caller_id' => $existingSignal->caller_id ?? $currentUser->id,
            'callee_id' => $existingSignal->callee_id ?? $peerId,
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

        // Limpiar senales expiradas
        WebrtcSignal::expired(5)->delete();

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
                'caller_id' => $signal->caller_id,
                'callee_id' => $signal->callee_id,
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
                    : ($caller?->email ?? 'Usuario');
            }

            return $data;
        });

        return response()->json(['signals' => $formatted]);
    }

    /**
     * Finalizar una llamada.
     */
    public function endCall(Request $request): JsonResponse
    {
        $request->validate([
            'peer_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $currentUser = Auth::user();
        $peerId = $request->input('peer_id');

        // Buscar la llamada original para mantener caller/callee correctos
        $existingSignal = WebrtcSignal::where(function ($q) use ($currentUser, $peerId) {
            $q->where('caller_id', $currentUser->id)->where('callee_id', $peerId);
        })->orWhere(function ($q) use ($currentUser, $peerId) {
            $q->where('caller_id', $peerId)->where('callee_id', $currentUser->id);
        })->where('type', 'call-request')->latest()->first();

        WebrtcSignal::create([
            'caller_id' => $existingSignal->caller_id ?? $currentUser->id,
            'callee_id' => $existingSignal->callee_id ?? $peerId,
            'type' => 'call-end',
            'media_type' => $existingSignal->media_type ?? 'video',
        ]);

        return response()->json(['status' => 'ok']);
    }
}
