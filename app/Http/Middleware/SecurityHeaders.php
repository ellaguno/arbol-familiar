<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     * Agrega headers de seguridad a todas las respuestas.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevenir que el navegador adivine el tipo de contenido
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevenir que la página sea embebida en iframes de otros dominios
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Habilitar protección XSS del navegador
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Controlar qué información del referrer se envía
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Forzar conexiones HTTPS
        if (config('app.env') === 'production') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Política de permisos del navegador
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        return $response;
    }
}
