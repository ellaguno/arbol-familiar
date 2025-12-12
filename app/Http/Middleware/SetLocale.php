<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Idiomas soportados.
     */
    protected array $supportedLocales = ['es', 'en'];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Prioridad: 1. Query param, 2. Session, 3. User preference, 4. Default
        $locale = $request->query('lang');

        if ($locale && in_array($locale, $this->supportedLocales)) {
            session(['locale' => $locale]);
        } else {
            $locale = session('locale');
        }

        // Si el usuario está autenticado, usar su preferencia
        if (!$locale && auth()->check() && auth()->user()->language) {
            $locale = auth()->user()->language;
        }

        // Usar idioma por defecto si no se encontró ninguno
        if (!$locale || !in_array($locale, $this->supportedLocales)) {
            $locale = config('app.locale', 'es');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
