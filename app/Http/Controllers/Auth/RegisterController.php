<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Person;
use App\Models\User;
use App\Services\RecaptchaService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use App\Services\SiteSettingsService;

class RegisterController extends Controller
{
    /**
     * Muestra el formulario de registro.
     */
    public function showRegistrationForm()
    {
        return view('auth.register', [
            'relationships' => config('mi-familia.relationship_degrees'),
            'familyRelationships' => config('mi-familia.family_relationships'),
        ]);
    }

    /**
     * Procesa el registro de usuario.
     */
    public function register(Request $request)
    {
        // Validar reCAPTCHA v3 si está habilitado
        if (RecaptchaService::isEnabled()) {
            if (!RecaptchaService::verify($request->input('g-recaptcha-response'), 'register')) {
                return back()
                    ->withInput()
                    ->withErrors(['g-recaptcha-response' => __('Verificacion de seguridad fallida. Por favor, intenta de nuevo.')]);
            }
        }

        $request->validate([
            // Datos del usuario
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'email_confirmation' => ['required', 'same:email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],

            // Datos personales
            'first_name' => ['required', 'string', 'max:100'],
            'patronymic' => ['required', 'string', 'max:100'],
            'matronymic' => ['nullable', 'string', 'max:100'],
            'gender' => ['required', 'in:M,F,U'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'birth_country' => ['nullable', 'string', 'max:255'],
            'residence_country' => ['nullable', 'string', 'max:255'],

            // Privacidad
            'privacy_accepted' => ['required', 'accepted'],
        ], [
            'email.required' => 'El correo electronico es obligatorio.',
            'email.email' => 'Ingresa un correo electronico valido.',
            'email.unique' => 'Ya existe una cuenta con este correo electronico.',
            'email_confirmation.same' => 'Los correos electronicos no coinciden.',
            'password.required' => 'La contrasena es obligatoria.',
            'password.confirmed' => 'Las contrasenas no coinciden.',
            'first_name.required' => 'El nombre es obligatorio.',
            'patronymic.required' => 'El apellido paterno es obligatorio.',
            'gender.required' => 'El genero es obligatorio.',
            'privacy_accepted.accepted' => 'Debes aceptar la politica de privacidad.',
        ]);

        // Heritage validation (only if feature is enabled)
        $heritageService = app(SiteSettingsService::class);
        if ($heritageService->heritageEnabled()) {
            $request->validate([
                'has_ethnic_heritage' => ['required', 'boolean'],
                'heritage_region' => ['nullable', 'string', 'max:100'],
                'migration_decade' => ['nullable', 'string', 'max:20'],
            ]);
        }

        try {
            DB::beginTransaction();

            // Crear usuario
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'language' => app()->getLocale(),
                'privacy_level' => 'direct_family',
                'confirmation_code' => Str::random(6),
            ]);

            // Crear persona del usuario
            $personData = [
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'patronymic' => $request->patronymic,
                'matronymic' => $request->matronymic,
                'gender' => $request->gender,
                'birth_date' => $request->birth_date,
                'birth_country' => $request->birth_country,
                'is_living' => true,
                'residence_country' => $request->residence_country,
                'email' => $request->email,
                'privacy_level' => 'extended_family',
                'consent_status' => 'not_required',
                'created_by' => $user->id,
            ];

            // Heritage data (only if feature is enabled)
            if ($heritageService->heritageEnabled()) {
                $personData['has_ethnic_heritage'] = $request->boolean('has_ethnic_heritage');
                $personData['heritage_region'] = $request->heritage_region;
                $personData['migration_decade'] = $request->migration_decade;
            }

            $person = Person::create($personData);

            // Vincular persona al usuario
            $user->update(['person_id' => $person->id]);

            // Registrar actividad
            ActivityLog::log('register', $user, $person);

            DB::commit();

            // Disparar evento de registro (esto debería activar el listener SendEmailVerificationNotification)
            event(new Registered($user));

            // Login automatico
            Auth::login($user);

            // Enviar correo de verificación de forma explícita como respaldo
            // Esto asegura que el correo se envíe incluso si hay problemas con los listeners
            try {
                if (!$user->hasVerifiedEmail()) {
                    $user->sendEmailVerificationNotification();
                }
            } catch (\Exception $mailException) {
                // Log del error de correo pero no fallar el registro
                Log::warning('No se pudo enviar correo de verificación al registrar usuario', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $mailException->getMessage(),
                ]);
            }

            return redirect()->route('verification.notice')
                ->with('success', 'Cuenta creada exitosamente. Revisa tu correo para verificar tu cuenta.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al registrar usuario', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Ocurrio un error al crear tu cuenta. Intenta de nuevo.');
        }
    }
}
