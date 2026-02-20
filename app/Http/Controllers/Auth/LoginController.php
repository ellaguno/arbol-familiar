<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\RecaptchaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Muestra el formulario de login.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Procesa el intento de login.
     */
    public function login(Request $request)
    {
        // Validar reCAPTCHA v3 si está habilitado
        if (RecaptchaService::isEnabled()) {
            if (!RecaptchaService::verify($request->input('g-recaptcha-response'), 'login')) {
                throw ValidationException::withMessages([
                    'g-recaptcha-response' => __('Verificacion de seguridad fallida. Por favor, intenta de nuevo.'),
                ]);
            }
        }

        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'El correo electronico es obligatorio.',
            'email.email' => 'Ingresa un correo electronico valido.',
            'password.required' => 'La contrasena es obligatoria.',
        ]);

        // Buscar usuario
        $user = User::where('email', $request->email)->first();

        // Verificar si el usuario existe
        if (!$user) {
            // Hashear parcialmente el email para proteger privacidad en logs
            $maskedEmail = $this->maskEmail($request->email);
            ActivityLog::log('login_failed', null, null, [
                'email_masked' => $maskedEmail,
                'reason' => 'user_not_found',
            ]);

            throw ValidationException::withMessages([
                'email' => 'No existe una cuenta con este correo electronico.',
            ]);
        }

        // Verificar si esta bloqueado
        if ($user->isLocked()) {
            $minutesLeft = now()->diffInMinutes($user->locked_until);

            throw ValidationException::withMessages([
                'email' => "Tu cuenta esta bloqueada. Intenta de nuevo en {$minutesLeft} minutos.",
            ]);
        }

        // Verificar contrasena
        if (!Hash::check($request->password, $user->password)) {
            $user->incrementLoginAttempts();

            ActivityLog::log('login_failed', $user, null, [
                'reason' => 'invalid_password',
                'attempts' => $user->login_attempts,
            ]);

            $attemptsLeft = 5 - $user->login_attempts;
            $message = 'La contrasena es incorrecta.';

            if ($attemptsLeft > 0 && $attemptsLeft <= 3) {
                $message .= " Te quedan {$attemptsLeft} intentos.";
            }

            throw ValidationException::withMessages([
                'password' => $message,
            ]);
        }

        // Login exitoso
        Auth::login($user, $request->boolean('remember'));
        $user->resetLoginAttempts();

        ActivityLog::log('login', $user);

        $request->session()->regenerate();

        // Redirigir segun estado del usuario
        if (!$user->email_verified_at) {
            return redirect()->route('verification.notice');
        }

        if (!$user->first_login_completed) {
            return redirect()->route('welcome.first');
        }

        // Limpiar URLs de AJAX/polling que no deben ser destino post-login
        $intended = $request->session()->pull('url.intended', route('dashboard'));
        if (str_contains($intended, '/call/') || str_contains($intended, '/api/') || str_contains($intended, '/poll')) {
            $intended = route('dashboard');
        }

        return redirect($intended);
    }

    /**
     * Cierra la sesion.
     */
    public function logout(Request $request)
    {
        ActivityLog::log('logout', Auth::user());

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /**
     * Enmascara un email para proteger privacidad en logs.
     * Ejemplo: usuario@ejemplo.com -> us***@ej***.com
     */
    protected function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return '***@***.***';
        }

        $local = $parts[0];
        $domain = $parts[1];

        // Enmascarar parte local (mantener primeros 2 caracteres)
        $maskedLocal = strlen($local) > 2
            ? substr($local, 0, 2) . str_repeat('*', min(strlen($local) - 2, 5))
            : $local;

        // Enmascarar dominio (mantener primeros 2 caracteres y TLD)
        $domainParts = explode('.', $domain);
        if (count($domainParts) >= 2) {
            $tld = array_pop($domainParts);
            $domainName = implode('.', $domainParts);
            $maskedDomain = strlen($domainName) > 2
                ? substr($domainName, 0, 2) . str_repeat('*', min(strlen($domainName) - 2, 5)) . '.' . $tld
                : $domain;
        } else {
            $maskedDomain = $domain;
        }

        return $maskedLocal . '@' . $maskedDomain;
    }
}
