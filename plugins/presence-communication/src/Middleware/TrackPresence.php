<?php

namespace Plugin\PresenceCommunication\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Plugin\PresenceCommunication\Models\UserPresence;

class TrackPresence
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Schema::hasTable('user_presences')) {
            try {
                UserPresence::updateOrCreate(
                    ['user_id' => Auth::id()],
                    [
                        'status' => 'online',
                        'last_seen_at' => now(),
                        'current_page' => $request->path(),
                    ]
                );
            } catch (\Throwable $e) {
                // No interrumpir el request si falla el tracking
            }
        }

        return $next($request);
    }
}
