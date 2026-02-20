<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\MessageRecipient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard principal.
     */
    public function index()
    {
        $user = auth()->user();
        $person = $user->person;

        // Estadisticas del arbol con cache de 5 minutos
        $stats = Cache::remember("dashboard_stats_{$user->id}", 300, function () use ($user) {
            return [
                'persons_count' => $user->createdPersons()->count(),
                'families_count' => $user->createdFamilies()->count(),
                'media_count' => $user->createdMedia()->count(),
            ];
        });

        // Mensajes no leidos (sin cache, deben ser siempre actuales)
        // Directos
        $directUnread = $user->unreadMessages()
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Broadcasts
        $broadcastUnreadIds = MessageRecipient::where('user_id', $user->id)
            ->whereNull('read_at')
            ->whereNull('deleted_at')
            ->pluck('message_id');

        $broadcastUnread = $broadcastUnreadIds->isNotEmpty()
            ? Message::whereIn('id', $broadcastUnreadIds)
                ->with('sender')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
            : collect();

        $unreadMessages = $directUnread->merge($broadcastUnread)
            ->sortByDesc('created_at')
            ->take(5);

        // Familia cercana (si tiene persona asociada)
        $family = null;
        if ($person) {
            $family = Cache::remember("dashboard_family_{$person->id}", 600, function () use ($person) {
                return [
                    'father' => $person->father,
                    'mother' => $person->mother,
                    'spouse' => $person->current_spouse,
                    'children' => $person->children->take(4),
                    'siblings' => $person->siblings->take(4),
                ];
            });
        }

        return view('dashboard', compact('user', 'person', 'stats', 'unreadMessages', 'family'));
    }

    /**
     * Muestra la pantalla de bienvenida para primer ingreso.
     */
    public function welcome()
    {
        $user = auth()->user();

        if ($user->first_login_completed) {
            return redirect()->route('dashboard');
        }

        return view('welcome-first', compact('user'));
    }

    /**
     * Marca el primer login como completado.
     */
    public function completeWelcome()
    {
        $user = auth()->user();
        $user->update(['first_login_completed' => true]);

        return redirect()->route('profile.edit')
            ->with('success', 'Bienvenido a Mi Familia. Completa tu perfil para comenzar.');
    }
}
