<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\MessageRecipient;
use App\Models\Person;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Bandeja de entrada.
     */
    public function inbox(Request $request)
    {
        $userId = Auth::id();

        // IDs de mensajes directos (no eliminados)
        $directQuery = Message::where('recipient_id', $userId)
            ->whereNull('deleted_at');

        // IDs de mensajes broadcast para este usuario (no eliminados en pivote)
        $broadcastIds = MessageRecipient::where('user_id', $userId)
            ->whereNull('deleted_at')
            ->pluck('message_id');

        // Combinar IDs
        $directIds = $directQuery->pluck('id');
        $allMessageIds = $directIds->merge($broadcastIds)->unique();

        $query = Message::whereIn('id', $allMessageIds)
            ->with(['sender', 'relatedPerson', 'currentUserRecipient'])
            ->latest('created_at');

        // Filtros
        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        if ($request->filled('unread')) {
            $unreadDirectIds = Message::where('recipient_id', $userId)
                ->whereNull('deleted_at')
                ->whereNull('read_at')
                ->pluck('id');

            $unreadBroadcastIds = MessageRecipient::where('user_id', $userId)
                ->whereNull('deleted_at')
                ->whereNull('read_at')
                ->pluck('message_id');

            $query->whereIn('id', $unreadDirectIds->merge($unreadBroadcastIds)->unique());
        }

        if ($request->filled('action_required')) {
            $query->actionRequired();
        }

        $messages = $query->paginate(20);

        // Contar no leidos (directos + broadcasts)
        $unreadCount = Auth::user()->unread_message_count;

        // Contar acciones pendientes (solo directos)
        $actionCount = Message::where('recipient_id', $userId)
            ->whereNull('deleted_at')
            ->actionRequired()
            ->count();

        return view('messages.inbox', compact('messages', 'unreadCount', 'actionCount'));
    }

    /**
     * Mensajes enviados.
     */
    public function sent(Request $request)
    {
        $messages = Message::where('sender_id', Auth::id())
            ->with(['recipient', 'relatedPerson'])
            ->latest('created_at')
            ->paginate(20);

        return view('messages.sent', compact('messages'));
    }

    /**
     * Formulario para componer mensaje.
     */
    public function compose(Request $request)
    {
        $recipient = null;
        $relatedPerson = null;

        if ($request->filled('to')) {
            $recipient = User::find($request->to);
        }

        if ($request->filled('person_id')) {
            $relatedPerson = Person::find($request->person_id);
        }

        // Opciones de difusion
        $canBroadcastAll = Auth::user()->isAdmin();
        $canBroadcastFamily = Auth::user()->person_id !== null;

        // Lista de usuarios a los que se puede enviar mensaje (solo campos necesarios)
        $users = User::select(['id', 'email', 'person_id'])
            ->with(['person:id,first_name,patronymic'])
            ->where('id', '!=', Auth::id())
            ->orderBy('email')
            ->get();

        // Lista de personas para relacionar (limitado y solo campos necesarios)
        $persons = Person::select(['id', 'first_name', 'patronymic'])
            ->orderBy('patronymic')
            ->orderBy('first_name')
            ->limit(500)
            ->get();

        return view('messages.compose', compact(
            'users', 'persons', 'recipient', 'relatedPerson',
            'canBroadcastAll', 'canBroadcastFamily'
        ));
    }

    /**
     * Enviar mensaje.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|string',
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:10000',
            'related_person_id' => 'nullable|exists:persons,id',
        ]);

        $recipientValue = $validated['recipient_id'];

        // Broadcast: "Todos los usuarios"
        if ($recipientValue === 'broadcast_all') {
            if (!Auth::user()->isAdmin()) {
                abort(403);
            }
            return $this->storeBroadcast($validated, 'all');
        }

        // Broadcast: "Mi familia"
        if ($recipientValue === 'broadcast_family') {
            if (!Auth::user()->person_id) {
                abort(403);
            }
            return $this->storeBroadcast($validated, 'family');
        }

        // Mensaje directo (flujo existente)
        if (!User::where('id', $recipientValue)->exists()) {
            return back()->withErrors(['recipient_id' => __('Destinatario no valido.')]);
        }

        Message::create([
            'sender_id' => Auth::id(),
            'recipient_id' => (int) $recipientValue,
            'type' => 'general',
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'related_person_id' => $validated['related_person_id'] ?? null,
            'action_required' => false,
            'created_at' => now(),
        ]);

        return redirect()->route('messages.sent')
            ->with('success', __('Mensaje enviado correctamente.'));
    }

    /**
     * Almacenar mensaje de difusion.
     */
    protected function storeBroadcast(array $validated, string $scope): \Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();

        // Determinar IDs de destinatarios
        if ($scope === 'all') {
            $recipientIds = User::where('id', '!=', $user->id)
                ->pluck('id')
                ->toArray();
        } else {
            // scope = 'family'
            $person = $user->person;
            if (!$person) {
                abort(403);
            }

            $connectedPersonIds = $person->getAllConnectedPersonIds();

            $recipientIds = User::whereIn('person_id', $connectedPersonIds)
                ->where('id', '!=', $user->id)
                ->pluck('id')
                ->toArray();
        }

        if (empty($recipientIds)) {
            return back()->with('error', __('No hay destinatarios para este mensaje.'));
        }

        // Crear registro unico de mensaje
        $message = Message::create([
            'sender_id' => $user->id,
            'recipient_id' => null,
            'type' => 'broadcast',
            'broadcast_scope' => $scope,
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'related_person_id' => $validated['related_person_id'] ?? null,
            'action_required' => false,
            'created_at' => now(),
        ]);

        // Bulk insert destinatarios (chunked para rendimiento)
        $recipientRecords = array_map(fn($userId) => [
            'message_id' => $message->id,
            'user_id' => $userId,
            'read_at' => null,
            'deleted_at' => null,
        ], $recipientIds);

        foreach (array_chunk($recipientRecords, 500) as $chunk) {
            MessageRecipient::insert($chunk);
        }

        return redirect()->route('messages.sent')
            ->with('success', __('Mensaje enviado a :count destinatarios.', ['count' => count($recipientIds)]));
    }

    /**
     * Ver mensaje.
     */
    public function show(Message $message)
    {
        $userId = Auth::id();

        if ($message->isBroadcast()) {
            // Verificar acceso via pivote
            $pivot = $message->recipientPivot($userId);
            if (!$pivot && $message->sender_id !== $userId) {
                abort(403);
            }

            // Marcar como leido en pivote
            if ($pivot && !$pivot->isRead()) {
                $pivot->markAsRead();
            }
        } else {
            // Verificar acceso directo
            if ($message->recipient_id !== $userId && $message->sender_id !== $userId) {
                abort(403);
            }

            // Marcar como leido si es el destinatario
            if ($message->recipient_id === $userId && !$message->isRead()) {
                $message->markAsRead();
            }
        }

        return view('messages.show', compact('message'));
    }

    /**
     * Responder mensaje.
     */
    public function reply(Message $message)
    {
        $userId = Auth::id();

        if ($message->isBroadcast()) {
            // Verificar que es un destinatario del broadcast
            if (!$message->recipientPivot($userId)) {
                abort(403);
            }
        } else {
            if ($message->recipient_id !== $userId) {
                abort(403);
            }
        }

        // Reply siempre va al sender original
        $recipient = $message->sender;
        $relatedPerson = $message->relatedPerson;
        $replySubject = 'Re: ' . preg_replace('/^Re: /i', '', $message->subject);
        $originalMessage = $message;

        $canBroadcastAll = Auth::user()->isAdmin();
        $canBroadcastFamily = Auth::user()->person_id !== null;

        $users = User::select(['id', 'email', 'person_id'])
            ->with(['person:id,first_name,patronymic'])
            ->where('id', '!=', Auth::id())
            ->orderBy('email')
            ->get();

        $persons = Person::select(['id', 'first_name', 'patronymic'])
            ->orderBy('patronymic')
            ->orderBy('first_name')
            ->limit(500)
            ->get();

        return view('messages.compose', compact(
            'users', 'persons', 'recipient', 'relatedPerson',
            'replySubject', 'originalMessage',
            'canBroadcastAll', 'canBroadcastFamily'
        ));
    }

    /**
     * Eliminar mensaje (soft delete).
     */
    public function destroy(Message $message)
    {
        $userId = Auth::id();

        if ($message->isBroadcast()) {
            $pivot = $message->recipientPivot($userId);
            if ($pivot) {
                // Recipient elimina su copia
                $pivot->softDelete();
            } elseif ($message->sender_id === $userId) {
                // Sender elimina el mensaje principal
                $message->softDelete();
            } else {
                abort(403);
            }
        } else {
            if ($message->recipient_id !== $userId && $message->sender_id !== $userId) {
                abort(403);
            }
            $message->softDelete();
        }

        $redirect = $message->sender_id === $userId ? 'messages.sent' : 'messages.inbox';

        return redirect()->route($redirect)
            ->with('success', __('Mensaje eliminado.'));
    }

    /**
     * Marcar como leido/no leido.
     */
    public function toggleRead(Message $message)
    {
        $userId = Auth::id();

        if ($message->isBroadcast()) {
            $pivot = $message->recipientPivot($userId);
            if (!$pivot) {
                abort(403);
            }

            if ($pivot->isRead()) {
                $pivot->update(['read_at' => null]);
            } else {
                $pivot->markAsRead();
            }
        } else {
            if ($message->recipient_id !== $userId) {
                abort(403);
            }

            if ($message->isRead()) {
                $message->update(['read_at' => null]);
            } else {
                $message->markAsRead();
            }
        }

        return back();
    }

    /**
     * Marcar multiples como leidos.
     */
    public function markAllRead()
    {
        $userId = Auth::id();

        // Directos
        Message::where('recipient_id', $userId)
            ->notDeleted()
            ->unread()
            ->update(['read_at' => now()]);

        // Broadcasts
        MessageRecipient::where('user_id', $userId)
            ->whereNull('deleted_at')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', __('Todos los mensajes marcados como leidos.'));
    }

    /**
     * Aceptar accion requerida.
     */
    public function accept(Message $message)
    {
        if ($message->recipient_id !== Auth::id()) {
            abort(403);
        }

        if (!$message->action_required || $message->action_status !== 'pending') {
            return back()->with('error', __('Esta accion ya no esta disponible.'));
        }

        $message->accept();

        // Manejar logica segun el tipo
        $this->handleAcceptAction($message);

        return back()->with('success', __('Solicitud aceptada.'));
    }

    /**
     * Denegar accion requerida.
     */
    public function deny(Message $message)
    {
        if ($message->recipient_id !== Auth::id()) {
            abort(403);
        }

        if (!$message->action_required || $message->action_status !== 'pending') {
            return back()->with('error', __('Esta accion ya no esta disponible.'));
        }

        $message->deny();

        // Notificar al remitente
        $this->notifyActionDenied($message);

        return back()->with('success', __('Solicitud denegada.'));
    }

    /**
     * Manejar aceptacion segun tipo.
     */
    protected function handleAcceptAction(Message $message): void
    {
        switch ($message->type) {
            case 'invitation':
                $this->handleInvitationAccepted($message);
                break;

            case 'consent_request':
                $this->handleConsentAccepted($message);
                break;

            case 'person_claim':
                $this->handlePersonClaimAccepted($message);
                break;

            case 'person_merge':
                $this->handlePersonMergeAccepted($message);
                break;

            case 'family_edit_request':
                $this->handleFamilyEditRequestAccepted($message);
                break;
        }

        // Notificar al remitente (solo si existe)
        if ($message->sender_id) {
            $relatedPersonId = $message->related_person_id;
            if ($relatedPersonId && !\App\Models\Person::where('id', $relatedPersonId)->exists()) {
                $relatedPersonId = null;
            }

            Message::create([
                'recipient_id' => $message->sender_id,
                'type' => 'system',
                'subject' => __('Tu solicitud fue aceptada'),
                'body' => __(':name ha aceptado tu solicitud: :subject', [
                    'name' => Auth::user()->full_name,
                    'subject' => $message->subject,
                ]),
                'related_person_id' => $relatedPersonId,
                'action_required' => false,
                'created_at' => now(),
            ]);
        }
    }

    /**
     * Manejar invitacion aceptada.
     */
    protected function handleInvitationAccepted(Message $message): void
    {
        if ($message->related_person_id && $message->relatedPerson) {
            // La logica de TreeAccess se puede implementar aqui
        }
    }

    /**
     * Manejar consentimiento aceptado.
     */
    protected function handleConsentAccepted(Message $message): void
    {
        if ($message->related_person_id) {
            $person = $message->relatedPerson;
            if ($person && $person->user_id === Auth::id()) {
                // El usuario tiene control sobre esta persona
            }
        }
    }

    /**
     * Manejar reclamacion de persona aceptada.
     */
    protected function handlePersonClaimAccepted(Message $message): void
    {
        if (!$message->related_person_id || !$message->sender_id) {
            return;
        }

        $person = $message->relatedPerson;
        $claimingUser = User::find($message->sender_id);

        if (!$person || !$claimingUser) {
            return;
        }

        if ($claimingUser->person_id || $person->user_id) {
            return;
        }

        $claimingUser->update(['person_id' => $person->id]);
        $person->update([
            'user_id' => $claimingUser->id,
            'consent_status' => 'approved',
            'consent_responded_at' => now(),
        ]);

        \App\Models\ActivityLog::log('person_claimed', $claimingUser, $person);
    }

    /**
     * Manejar fusion de personas aceptada.
     */
    protected function handlePersonMergeAccepted(Message $message): void
    {
        if (!$message->related_person_id || !$message->sender_id) {
            return;
        }

        $sourcePerson = $message->relatedPerson;
        $requestingUser = User::find($message->sender_id);

        if (!$sourcePerson || !$requestingUser || !$requestingUser->person_id) {
            return;
        }

        $targetPerson = $requestingUser->person;

        if (!$targetPerson) {
            return;
        }

        if ($sourcePerson->user_id) {
            return;
        }

        $mergeResult = \App\Http\Controllers\PersonController::mergePersons($sourcePerson, $targetPerson);

        \App\Models\ActivityLog::log('persons_merged', $requestingUser, $targetPerson, [
            'source_person_id' => $message->related_person_id,
            'merged_relationships' => $mergeResult['relationships'],
            'merged_media' => $mergeResult['media'],
            'merged_fields' => $mergeResult['fields'],
        ]);
    }

    /**
     * Manejar solicitud de edicion de familia aceptada.
     */
    protected function handleFamilyEditRequestAccepted(Message $message): void
    {
        if (!$message->related_person_id || !$message->sender_id) {
            return;
        }

        $person = $message->relatedPerson;
        $requestingUser = User::find($message->sender_id);

        if (!$person || !$requestingUser) {
            return;
        }

        $metadata = json_decode($message->metadata ?? '{}', true);
        $relationshipType = $metadata['relationship_type'] ?? 'other';

        $existingPermission = \App\Models\PersonEditPermission::where('person_id', $person->id)
            ->where('user_id', $requestingUser->id)
            ->first();

        if ($existingPermission) {
            $existingPermission->update([
                'granted_at' => now(),
                'expires_at' => null,
            ]);
        } else {
            \App\Models\PersonEditPermission::create([
                'person_id' => $person->id,
                'user_id' => $requestingUser->id,
                'granted_by' => Auth::id(),
                'relationship_type' => $relationshipType,
                'granted_at' => now(),
            ]);
        }

        \App\Models\ActivityLog::log('family_edit_access_granted', Auth::user(), $person, [
            'requester_user_id' => $requestingUser->id,
            'relationship_type' => $relationshipType,
        ]);
    }

    /**
     * Notificar denegacion.
     */
    protected function notifyActionDenied(Message $message): void
    {
        if (!$message->sender_id) {
            return;
        }

        Message::create([
            'recipient_id' => $message->sender_id,
            'type' => 'system',
            'subject' => __('Tu solicitud fue denegada'),
            'body' => __(':name ha denegado tu solicitud: :subject', [
                'name' => Auth::user()->full_name,
                'subject' => $message->subject,
            ]),
            'related_person_id' => $message->related_person_id,
            'action_required' => false,
            'created_at' => now(),
        ]);
    }

    /**
     * Enviar invitacion para colaborar en arbol.
     */
    public function sendInvitation(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'person_id' => 'required|exists:persons,id',
            'message' => 'nullable|string|max:1000',
        ]);

        $person = Person::findOrFail($validated['person_id']);
        $recipient = User::findOrFail($validated['recipient_id']);

        Message::create([
            'sender_id' => Auth::id(),
            'recipient_id' => $validated['recipient_id'],
            'type' => 'invitation',
            'subject' => __('Invitacion para colaborar'),
            'body' => $validated['message'] ?? __(':name te invita a colaborar en el arbol genealogico de :person.', [
                'name' => Auth::user()->full_name,
                'person' => $person->full_name,
            ]),
            'related_person_id' => $validated['person_id'],
            'action_required' => true,
            'action_status' => 'pending',
            'created_at' => now(),
        ]);

        return back()->with('success', __('Invitacion enviada a :name.', ['name' => $recipient->full_name]));
    }

    /**
     * Solicitar consentimiento.
     */
    public function requestConsent(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'person_id' => 'required|exists:persons,id',
            'reason' => 'required|string|max:1000',
        ]);

        $person = Person::findOrFail($validated['person_id']);

        Message::create([
            'sender_id' => Auth::id(),
            'recipient_id' => $validated['recipient_id'],
            'type' => 'consent_request',
            'subject' => __('Solicitud de consentimiento'),
            'body' => __(':name solicita tu consentimiento para :person.', [
                'name' => Auth::user()->full_name,
                'person' => $person->full_name,
            ]) . "\n\n" . __('Razon:') . ' ' . $validated['reason'],
            'related_person_id' => $validated['person_id'],
            'action_required' => true,
            'action_status' => 'pending',
            'created_at' => now(),
        ]);

        return back()->with('success', __('Solicitud de consentimiento enviada.'));
    }
}
