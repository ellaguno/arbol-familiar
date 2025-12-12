<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /**
     * Muestra la pagina de verificacion pendiente.
     */
    public function show()
    {
        return auth()->user()->hasVerifiedEmail()
            ? redirect()->intended(route('dashboard'))
            : view('auth.verify-email');
    }

    /**
     * Verifica el email del usuario.
     */
    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        if ($request->user()->markEmailAsVerified()) {
            ActivityLog::log('email_verified', $request->user());
            event(new Verified($request->user()));
        }

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Tu correo electronico ha sido verificado.');
    }

    /**
     * Reenvia el correo de verificacion.
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        // Regenerar el codigo de verificacion
        $user = $request->user();
        $user->confirmation_code = \Illuminate\Support\Str::random(6);
        $user->save();

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'Se ha enviado un nuevo enlace de verificacion a tu correo.');
    }

    /**
     * Verifica usando codigo (alternativo).
     */
    public function verifyWithCode(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        if ($user->confirmation_code !== $request->code) {
            return back()->withErrors(['code' => 'El codigo ingresado es incorrecto.']);
        }

        $user->markEmailAsVerified();
        $user->confirmation_code = null;
        $user->save();

        ActivityLog::log('email_verified', $user);
        event(new Verified($user));

        return redirect()->route('dashboard')
            ->with('success', 'Tu correo electronico ha sido verificado.');
    }
}
