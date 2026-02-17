<?php

namespace App\Http\Controllers;

use App\Mail\DataConsentInvitation;
use App\Models\ActivityLog;
use App\Models\Invitation;
use App\Models\Person;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class InvitationController extends Controller
{
    /**
     * Enviar invitación de consentimiento de datos a una persona.
     */
    public function sendConsentInvitation(Person $person): array
    {
        $user = Auth::user();

        // Validar que la persona tiene email
        if (empty($person->email)) {
            return [
                'success' => false,
                'message' => __('La persona no tiene correo electronico registrado.'),
            ];
        }

        // Validar que el usuario puede editar la persona
        if ($person->created_by !== $user->id && !$person->canBeEditedBy($user->id)) {
            return [
                'success' => false,
                'message' => __('No tienes permiso para invitar a esta persona.'),
            ];
        }

        // Verificar si ya existe una invitación pendiente
        $existingInvitation = Invitation::where('person_id', $person->id)
            ->where('email', $person->email)
            ->pending()
            ->first();

        if ($existingInvitation) {
            return [
                'success' => false,
                'message' => __('Ya existe una invitacion pendiente para este correo.'),
                'invitation' => $existingInvitation,
            ];
        }

        // Verificar si el email ya está registrado como usuario
        $existingUser = User::where('email', $person->email)->first();
        if ($existingUser) {
            // Si el usuario existe y ya está vinculado a otra persona
            if ($existingUser->person_id && $existingUser->person_id !== $person->id) {
                return [
                    'success' => false,
                    'message' => __('Este correo ya esta registrado y vinculado a otro perfil.'),
                ];
            }

            // Si el usuario existe pero no está vinculado, vincular directamente
            if (!$existingUser->person_id) {
                $existingUser->update(['person_id' => $person->id]);
                $person->update([
                    'user_id' => $existingUser->id,
                    'consent_status' => 'pending',
                ]);

                // Enviar mensaje interno al usuario
                \App\Models\Message::create([
                    'sender_id' => $user->id,
                    'recipient_id' => $existingUser->id,
                    'type' => 'consent_invitation',
                    'subject' => __('Solicitud de consentimiento de datos'),
                    'body' => __(':name ha registrado tu informacion en el arbol genealogico y solicita tu consentimiento para mantener tus datos.', [
                        'name' => $user->person?->full_name ?? $user->email,
                    ]),
                    'related_person_id' => $person->id,
                    'action_required' => true,
                    'action_status' => 'pending',
                    'created_at' => now(),
                ]);

                return [
                    'success' => true,
                    'message' => __('El usuario ya existe. Se le envio una solicitud de consentimiento.'),
                    'user_exists' => true,
                ];
            }
        }

        // Crear invitación
        $invitation = Invitation::create([
            'inviter_id' => $user->id,
            'person_id' => $person->id,
            'email' => $person->email,
            'status' => 'pending',
        ]);

        // Enviar email
        try {
            Mail::to($person->email)->send(new DataConsentInvitation($invitation));
            $invitation->markAsSent();

            ActivityLog::log('consent_invitation_sent', $user, $person, [
                'email' => $person->email,
                'invitation_id' => $invitation->id,
            ]);

            return [
                'success' => true,
                'message' => __('Invitacion enviada a :email.', ['email' => $person->email]),
                'invitation' => $invitation,
            ];
        } catch (\Exception $e) {
            // Si falla el envío, mantener la invitación como pendiente
            return [
                'success' => true,
                'message' => __('Invitacion creada pero hubo un problema al enviar el correo. Se puede reenviar mas tarde.'),
                'invitation' => $invitation,
                'email_error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Mostrar página de invitación (pública).
     */
    public function show(string $token)
    {
        $invitation = Invitation::findByToken($token);

        if (!$invitation) {
            return view('invitation.invalid', [
                'reason' => 'not_found',
                'message' => __('La invitacion no existe o el enlace es incorrecto.'),
            ]);
        }

        if ($invitation->isExpired()) {
            return view('invitation.invalid', [
                'reason' => 'expired',
                'message' => __('Esta invitacion ha expirado.'),
                'invitation' => $invitation,
            ]);
        }

        if ($invitation->isAccepted()) {
            return view('invitation.invalid', [
                'reason' => 'already_accepted',
                'message' => __('Esta invitacion ya fue aceptada.'),
            ]);
        }

        if ($invitation->isDeclined()) {
            return view('invitation.invalid', [
                'reason' => 'declined',
                'message' => __('Esta invitacion fue rechazada.'),
            ]);
        }

        // Cargar relaciones
        $invitation->load(['inviter', 'person']);

        // Verificar si ya existe usuario con este email
        $existingUser = User::where('email', $invitation->email)->first();

        return view('invitation.show', [
            'invitation' => $invitation,
            'existingUser' => $existingUser,
        ]);
    }

    /**
     * Aceptar invitación y crear cuenta (pública).
     */
    public function accept(Request $request, string $token)
    {
        $invitation = Invitation::findByToken($token);

        if (!$invitation || $invitation->isExpired() || !$invitation->isPending()) {
            return redirect()->route('invitation.show', $token)
                ->with('error', __('Esta invitacion no es valida.'));
        }

        // Verificar si el usuario ya está autenticado
        if (Auth::check()) {
            return $this->acceptForAuthenticatedUser($invitation);
        }

        // Verificar si existe usuario con este email
        $existingUser = User::where('email', $invitation->email)->first();

        if ($existingUser) {
            // Redirigir a login con mensaje
            return redirect()->route('login')
                ->with('info', __('Ya tienes una cuenta. Inicia sesion para aceptar la invitacion.'))
                ->with('pending_invitation', $token);
        }

        // Validar datos de registro
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['required', 'accepted'],
        ], [
            'name.required' => __('El nombre es obligatorio.'),
            'password.required' => __('La contrasena es obligatoria.'),
            'password.min' => __('La contrasena debe tener al menos 8 caracteres.'),
            'password.confirmed' => __('Las contrasenas no coinciden.'),
            'terms.accepted' => __('Debes aceptar los terminos y condiciones.'),
        ]);

        // Crear usuario
        $user = User::create([
            'name' => $validated['name'],
            'email' => $invitation->email,
            'password' => $validated['password'],
            'person_id' => $invitation->person_id,
            'email_verified_at' => now(), // Verificado porque viene del email
        ]);

        // Actualizar persona
        $person = $invitation->person;
        $person->update([
            'user_id' => $user->id,
            'consent_status' => 'approved',
            'consent_responded_at' => now(),
        ]);

        // Aceptar invitación
        $invitation->accept();

        // Registrar actividad
        ActivityLog::log('consent_invitation_accepted', $user, $person, [
            'invitation_id' => $invitation->id,
        ]);

        // Notificar al invitador
        \App\Models\Message::create([
            'recipient_id' => $invitation->inviter_id,
            'type' => 'system',
            'subject' => __(':name acepto tu invitacion', ['name' => $person->full_name]),
            'body' => __(':name ha aceptado la invitacion y ahora tiene control sobre su perfil en el arbol genealogico.', [
                'name' => $person->full_name,
            ]),
            'related_person_id' => $person->id,
            'action_required' => false,
            'created_at' => now(),
        ]);

        // Autenticar al usuario
        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', __('Bienvenido/a :name! Tu cuenta ha sido creada y vinculada a tu perfil.', [
                'name' => $user->name,
            ]));
    }

    /**
     * Aceptar invitación para usuario autenticado.
     */
    protected function acceptForAuthenticatedUser(Invitation $invitation)
    {
        $user = Auth::user();

        // Verificar que el email coincide
        if ($user->email !== $invitation->email) {
            return redirect()->route('invitation.show', $invitation->token)
                ->with('error', __('Esta invitacion es para otro correo electronico.'));
        }

        // Verificar que el usuario no tiene otra persona vinculada
        if ($user->person_id && $user->person_id !== $invitation->person_id) {
            return redirect()->route('invitation.show', $invitation->token)
                ->with('error', __('Ya tienes otro perfil vinculado a tu cuenta.'));
        }

        // Vincular persona al usuario
        $person = $invitation->person;

        $user->update(['person_id' => $person->id]);
        $person->update([
            'user_id' => $user->id,
            'consent_status' => 'approved',
            'consent_responded_at' => now(),
        ]);

        // Aceptar invitación
        $invitation->accept();

        // Registrar actividad
        ActivityLog::log('consent_invitation_accepted', $user, $person, [
            'invitation_id' => $invitation->id,
        ]);

        // Notificar al invitador
        \App\Models\Message::create([
            'recipient_id' => $invitation->inviter_id,
            'type' => 'system',
            'subject' => __(':name acepto tu invitacion', ['name' => $person->full_name]),
            'body' => __(':name ha aceptado la invitacion y ahora tiene control sobre su perfil.', [
                'name' => $person->full_name,
            ]),
            'related_person_id' => $person->id,
            'action_required' => false,
            'created_at' => now(),
        ]);

        return redirect()->route('persons.show', $person)
            ->with('success', __('Has aceptado la invitacion. Ahora tienes control sobre tu perfil.'));
    }

    /**
     * Rechazar invitación (pública).
     */
    public function decline(Request $request, string $token)
    {
        $invitation = Invitation::findByToken($token);

        if (!$invitation || !$invitation->isPending()) {
            return redirect()->route('invitation.show', $token)
                ->with('error', __('Esta invitacion no es valida.'));
        }

        $invitation->decline();

        // Actualizar persona - marcar que rechazó el consentimiento
        $person = $invitation->person;
        $person->update([
            'consent_status' => 'declined',
            'consent_responded_at' => now(),
        ]);

        // Notificar al invitador
        \App\Models\Message::create([
            'recipient_id' => $invitation->inviter_id,
            'type' => 'system',
            'subject' => __('Invitacion rechazada'),
            'body' => __('La persona con correo :email ha rechazado la invitacion para :person. Considera eliminar o anonimizar sus datos.', [
                'email' => $invitation->email,
                'person' => $person->full_name,
            ]),
            'related_person_id' => $person->id,
            'action_required' => true,
            'action_status' => 'pending',
            'created_at' => now(),
        ]);

        ActivityLog::log('consent_invitation_declined', null, $person, [
            'invitation_id' => $invitation->id,
            'email' => $invitation->email,
        ]);

        return view('invitation.declined', [
            'invitation' => $invitation,
        ]);
    }

    /**
     * Reenviar invitación.
     */
    public function resend(Person $person)
    {
        $user = Auth::user();
        $isAjax = request()->ajax();

        // Validar permisos
        if ($person->created_by !== $user->id && !$person->canBeEditedBy($user->id)) {
            $msg = __('No tienes permiso para reenviar esta invitacion.');
            return $isAjax
                ? response()->json(['success' => false, 'message' => $msg])
                : back()->with('error', $msg);
        }

        // Buscar invitación existente
        $invitation = Invitation::where('person_id', $person->id)
            ->where('email', $person->email)
            ->latest('created_at')
            ->first();

        if (!$invitation) {
            // Crear nueva invitación
            $result = $this->sendConsentInvitation($person);
            return $isAjax
                ? response()->json($result)
                : ($result['success']
                    ? back()->with('success', $result['message'])
                    : back()->with('error', $result['message']));
        }

        // Si está expirada, crear una nueva
        if ($invitation->isExpired()) {
            $invitation->markAsExpired();

            $newInvitation = Invitation::create([
                'inviter_id' => $user->id,
                'person_id' => $person->id,
                'email' => $person->email,
                'status' => 'pending',
            ]);

            try {
                Mail::to($person->email)->send(new DataConsentInvitation($newInvitation));
                $newInvitation->markAsSent();
                $msg = __('Nueva invitacion enviada a :email.', ['email' => $person->email]);
                return $isAjax
                    ? response()->json(['success' => true, 'message' => $msg])
                    : back()->with('success', $msg);
            } catch (\Exception $e) {
                $msg = __('Invitacion creada pero hubo un problema al enviar el correo.');
                return $isAjax
                    ? response()->json(['success' => false, 'message' => $msg])
                    : back()->with('warning', $msg);
            }
        }

        // Si está pendiente, reenviar
        if ($invitation->isPending()) {
            try {
                Mail::to($person->email)->send(new DataConsentInvitation($invitation));
                $invitation->markAsSent();
                $msg = __('Invitacion reenviada a :email.', ['email' => $person->email]);
                return $isAjax
                    ? response()->json(['success' => true, 'message' => $msg])
                    : back()->with('success', $msg);
            } catch (\Exception $e) {
                $msg = __('Error al enviar el correo: :error', ['error' => $e->getMessage()]);
                return $isAjax
                    ? response()->json(['success' => false, 'message' => $msg])
                    : back()->with('error', $msg);
            }
        }

        $msg = __('La invitacion ya fue respondida.');
        return $isAjax
            ? response()->json(['success' => false, 'message' => $msg])
            : back()->with('info', $msg);
    }
}
