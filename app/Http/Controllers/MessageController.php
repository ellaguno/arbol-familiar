<?php

namespace App\Http\Controllers;

use App\Models\Message;
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
        $query = Message::where('recipient_id', Auth::id())
            ->notDeleted()
            ->with(['sender', 'relatedPerson'])
            ->latest('created_at');

        // Filtros
        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        if ($request->filled('unread')) {
            $query->unread();
        }

        if ($request->filled('action_required')) {
            $query->actionRequired();
        }

        $messages = $query->paginate(20);

        // Contar no leidos
        $unreadCount = Message::where('recipient_id', Auth::id())
            ->notDeleted()
            ->unread()
            ->count();

        // Contar acciones pendientes
        $actionCount = Message::where('recipient_id', Auth::id())
            ->notDeleted()
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

        return view('messages.compose', compact('users', 'persons', 'recipient', 'relatedPerson'));
    }

    /**
     * Enviar mensaje.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:10000',
            'related_person_id' => 'nullable|exists:persons,id',
        ]);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'recipient_id' => $validated['recipient_id'],
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
     * Ver mensaje.
     */
    public function show(Message $message)
    {
        // Verificar acceso
        if ($message->recipient_id !== Auth::id() && $message->sender_id !== Auth::id()) {
            abort(403);
        }

        // Marcar como leido si es el destinatario
        if ($message->recipient_id === Auth::id() && !$message->isRead()) {
            $message->markAsRead();
        }

        return view('messages.show', compact('message'));
    }

    /**
     * Responder mensaje.
     */
    public function reply(Message $message)
    {
        // Verificar que es el destinatario
        if ($message->recipient_id !== Auth::id()) {
            abort(403);
        }

        $recipient = $message->sender;
        $relatedPerson = $message->relatedPerson;
        $replySubject = 'Re: ' . preg_replace('/^Re: /i', '', $message->subject);
        $originalMessage = $message;

        $users = User::where('id', '!=', Auth::id())
            ->orderBy('email')
            ->get();

        $persons = Person::orderBy('patronymic')
            ->orderBy('first_name')
            ->get();

        return view('messages.compose', compact(
            'users', 'persons', 'recipient', 'relatedPerson',
            'replySubject', 'originalMessage'
        ));
    }

    /**
     * Eliminar mensaje (soft delete).
     */
    public function destroy(Message $message)
    {
        // Verificar acceso
        if ($message->recipient_id !== Auth::id() && $message->sender_id !== Auth::id()) {
            abort(403);
        }

        $message->softDelete();

        $redirect = $message->sender_id === Auth::id() ? 'messages.sent' : 'messages.inbox';

        return redirect()->route($redirect)
            ->with('success', __('Mensaje eliminado.'));
    }

    /**
     * Marcar como leido/no leido.
     */
    public function toggleRead(Message $message)
    {
        if ($message->recipient_id !== Auth::id()) {
            abort(403);
        }

        if ($message->isRead()) {
            $message->update(['read_at' => null]);
        } else {
            $message->markAsRead();
        }

        return back();
    }

    /**
     * Marcar multiples como leidos.
     */
    public function markAllRead()
    {
        Message::where('recipient_id', Auth::id())
            ->notDeleted()
            ->unread()
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
                // Logica para aceptar invitacion
                $this->handleInvitationAccepted($message);
                break;

            case 'consent_request':
                // Logica para aceptar consentimiento
                $this->handleConsentAccepted($message);
                break;

            case 'person_claim':
                // Logica para aceptar reclamacion de persona
                $this->handlePersonClaimAccepted($message);
                break;

            case 'person_merge':
                // Logica para aceptar fusion de personas
                $this->handlePersonMergeAccepted($message);
                break;

            case 'family_edit_request':
                // Logica para aceptar solicitud de edicion de familia
                $this->handleFamilyEditRequestAccepted($message);
                break;
        }

        // Notificar al remitente (solo si existe)
        if ($message->sender_id) {
            // Verificar que la persona relacionada aun existe (puede haber sido eliminada en una fusion)
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
        // Si hay una persona relacionada, dar acceso al arbol
        if ($message->related_person_id && $message->relatedPerson) {
            // La logica de TreeAccess se puede implementar aqui
        }
    }

    /**
     * Manejar consentimiento aceptado.
     */
    protected function handleConsentAccepted(Message $message): void
    {
        // Actualizar el nivel de privacidad si es necesario
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

        // Verificar que el usuario aun no tiene persona y la persona no tiene usuario
        if ($claimingUser->person_id || $person->user_id) {
            return;
        }

        // Vincular usuario con persona (doble enlace)
        $claimingUser->update(['person_id' => $person->id]);
        $person->update([
            'user_id' => $claimingUser->id,
            'consent_status' => 'approved',
            'consent_responded_at' => now(),
        ]);

        // Registrar actividad
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

        $sourcePerson = $message->relatedPerson; // Persona a eliminar
        $requestingUser = User::find($message->sender_id);

        if (!$sourcePerson || !$requestingUser || !$requestingUser->person_id) {
            return;
        }

        $targetPerson = $requestingUser->person; // Persona del usuario (se conserva)

        if (!$targetPerson) {
            return;
        }

        // Verificar que source no tenga usuario (no se pueden fusionar personas con cuenta)
        if ($sourcePerson->user_id) {
            return;
        }

        // Ejecutar la fusion
        $mergeResult = \App\Http\Controllers\PersonController::mergePersons($sourcePerson, $targetPerson);

        // Registrar actividad
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

        // Obtener metadata con el tipo de relación
        $metadata = json_decode($message->metadata ?? '{}', true);
        $relationshipType = $metadata['relationship_type'] ?? 'other';

        // Verificar que no exista ya un permiso
        $existingPermission = \App\Models\PersonEditPermission::where('person_id', $person->id)
            ->where('user_id', $requestingUser->id)
            ->first();

        if ($existingPermission) {
            // Actualizar si ya existe
            $existingPermission->update([
                'granted_at' => now(),
                'expires_at' => null, // Sin expiracion
            ]);
        } else {
            // Crear nuevo permiso
            \App\Models\PersonEditPermission::create([
                'person_id' => $person->id,
                'user_id' => $requestingUser->id,
                'granted_by' => Auth::id(),
                'relationship_type' => $relationshipType,
                'granted_at' => now(),
            ]);
        }

        // Registrar actividad
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
        // Solo notificar si hay un remitente
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
