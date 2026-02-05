<?php

namespace Plugin\PresenceCommunication\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Plugin\PresenceCommunication\Models\UserPresence;

class PresenceController extends Controller
{
    /**
     * Heartbeat: actualizar presencia del usuario actual.
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $user = Auth::user();

        UserPresence::updateOrCreate(
            ['user_id' => $user->id],
            [
                'status' => 'online',
                'last_seen_at' => now(),
                'current_page' => $request->input('page', $request->header('Referer', '')),
            ]
        );

        return response()->json(['status' => 'ok']);
    }

    /**
     * Obtener lista de usuarios en linea, separados en 3 categorias:
     * - "Familia": comparten arbol o parentesco directo/extendido
     * - "Comunidad": no son familia pero su Person es visible (canBeViewedBy)
     * - "Publico": todos los demas usuarios online
     *
     * Usuarios con show_online_status=false no aparecen en ninguna lista.
     * El usuario actual nunca aparece en la lista.
     */
    public function online(): JsonResponse
    {
        $currentUser = Auth::user();

        $presences = UserPresence::online()
            ->where('user_id', '!=', $currentUser->id)
            ->with(['user' => function ($q) {
                $q->with('person');
            }])
            ->get()
            ->filter(fn ($p) => $p->user !== null)
            ->filter(fn ($p) => $p->user->show_online_status !== false);

        $family = [];
        $community = [];
        $public = [];
        $categorizedIds = [];

        $currentPerson = $currentUser->person_id ? $currentUser->person : null;
        $isAdmin = $currentUser->is_admin;

        foreach ($presences as $presence) {
            $otherUser = $presence->user;
            $otherPerson = $otherUser->person;

            $photoUrl = ($otherPerson && $otherPerson->photo_path)
                ? Storage::url($otherPerson->photo_path)
                : null;

            $userData = [
                'id' => $presence->user_id,
                'name' => $otherPerson
                    ? $otherPerson->full_name
                    : $otherUser->email,
                'photo' => $photoUrl,
                'sex' => $otherPerson->sex ?? null,
                'status' => $presence->status,
                'current_page' => $presence->current_page,
                'last_seen_at' => $presence->last_seen_at->toISOString(),
            ];

            $isMyFamily = $this->isFamilyOf($currentPerson, $otherPerson);

            if ($isMyFamily) {
                $family[] = $userData;
                $categorizedIds[] = $presence->user_id;
            } elseif ($isAdmin) {
                // Admin ve a todos: no-familia va a comunidad
                $community[] = $userData;
                $categorizedIds[] = $presence->user_id;
            } elseif ($otherPerson && $otherPerson->canBeViewedBy($currentUser)) {
                $community[] = $userData;
                $categorizedIds[] = $presence->user_id;
            } elseif (!$otherPerson) {
                $otherPrivacy = $otherUser->privacy_level ?? 'direct_family';
                if ($otherPrivacy === 'community') {
                    $community[] = $userData;
                    $categorizedIds[] = $presence->user_id;
                }
            }
        }

        // Publico: todos los usuarios online que no quedaron en familia ni comunidad
        foreach ($presences as $presence) {
            if (!in_array($presence->user_id, $categorizedIds)) {
                $otherUser = $presence->user;
                $otherPerson = $otherUser->person;
                $pubPhotoUrl = ($otherPerson && $otherPerson->photo_path)
                    ? Storage::url($otherPerson->photo_path)
                    : null;

                $public[] = [
                    'id' => $presence->user_id,
                    'name' => $otherPerson
                        ? $otherPerson->full_name
                        : $otherUser->email,
                    'photo' => $pubPhotoUrl,
                    'sex' => $otherPerson->sex ?? null,
                    'status' => $presence->status,
                    'current_page' => $presence->current_page,
                    'last_seen_at' => $presence->last_seen_at->toISOString(),
                ];
            }
        }

        $allUsers = array_merge($family, $community, $public);

        return response()->json([
            'users' => $allUsers,
            'family' => $family,
            'community' => $community,
            'public' => $public,
            'count' => count($allUsers),
            'family_count' => count($family),
            'community_count' => count($community),
            'public_count' => count($public),
        ]);
    }

    /**
     * Determina si la otra persona es "familia" del usuario actual.
     */
    private function isFamilyOf($currentPerson, $otherPerson): bool
    {
        if (!$currentPerson || !$otherPerson) {
            return false;
        }

        if ($currentPerson->created_by === $otherPerson->created_by) {
            return true;
        }

        if (in_array($otherPerson->id, $currentPerson->directFamilyIds)) {
            return true;
        }

        if (in_array($otherPerson->id, $currentPerson->extendedFamilyIds)) {
            return true;
        }

        return false;
    }

    /**
     * Marcar usuario como offline (al cerrar sesion o cerrar ventana).
     */
    public function offline(): JsonResponse
    {
        $user = Auth::user();

        UserPresence::where('user_id', $user->id)->update([
            'status' => 'offline',
        ]);

        return response()->json(['status' => 'ok']);
    }
}
