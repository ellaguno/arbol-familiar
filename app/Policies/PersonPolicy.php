<?php

namespace App\Policies;

use App\Models\Person;
use App\Models\User;
use App\Services\RelationshipService;
use Illuminate\Auth\Access\Response;

/**
 * Autorizacion centralizada sobre personas (ver/editar), reutilizable via
 * Gate/`$user->can(...)`. Antes la logica vivia inline en PersonController
 * (authorizeView/authorizeEdit). La decision de visibilidad se apoya en
 * Person::canBeViewedBy (4 niveles de privacidad) y la de edicion replica las
 * reglas de proteccion de menores + permisos de familia directa.
 */
class PersonPolicy
{
    public function __construct(protected RelationshipService $relationships)
    {
    }

    /**
     * ¿Puede el usuario ver a esta persona?
     */
    public function view(User $user, Person $person): bool
    {
        return $person->canBeViewedBy($user);
    }

    /**
     * ¿Puede el usuario editar a esta persona?
     *
     * Para menores con padres registrados en el sistema, solo los padres
     * (o el propio menor vinculado, o un admin) pueden editar.
     */
    public function update(User $user, Person $person): Response
    {
        // Menor de edad con padres registrados con cuenta: acceso restringido.
        if ($person->is_minor_calculated && $this->relationships->minorHasRegisteredParents($person)) {
            if ($user->person_id && $this->relationships->isParentOf($user->person_id, $person)) {
                return Response::allow();
            }

            // El menor vinculado puede editar su propio perfil.
            if ($user->person_id === $person->id) {
                return Response::allow();
            }

            if ($user->is_admin) {
                return Response::allow();
            }

            return Response::deny(__('Solo los padres registrados pueden editar el perfil de un menor.'));
        }

        // No-menores: creador, perfil propio, vinculado con consentimiento, o
        // permiso de edicion otorgado (familia directa).
        if ($person->created_by === $user->id) {
            return Response::allow();
        }

        if ($user->person_id === $person->id) {
            return Response::allow();
        }

        if ($person->consent_status === 'approved' && $person->user_id === $user->id) {
            return Response::allow();
        }

        if ($person->canBeEditedBy($user->id)) {
            return Response::allow();
        }

        return Response::deny(__('No tienes permiso para editar esta persona.'));
    }
}
