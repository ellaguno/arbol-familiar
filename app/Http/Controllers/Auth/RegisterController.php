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

class RegisterController extends Controller
{
    /**
     * Muestra el formulario de registro.
     */
    public function showRegistrationForm()
    {
        return view('auth.register', [
            'regions' => config('mi-familia.heritage_regions'),
            'decades' => config('mi-familia.migration_decades'),
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

            // Herencia etnica
            'has_ethnic_heritage' => ['required', 'boolean'],
            'ancestor_first_name' => ['required_if:has_ethnic_heritage,1', 'nullable', 'string', 'max:100'],
            'ancestor_patronymic' => ['required_if:has_ethnic_heritage,1', 'nullable', 'string', 'max:100'],
            'heritage_region' => ['required_if:has_ethnic_heritage,1', 'nullable', 'in:central,dalmatia,slavonia,istria,other,unknown'],
            'migration_decade' => ['nullable', 'string', 'max:20'],
            'relationship_degree' => ['nullable', 'string', 'max:50'],

            // Familiar con herencia (para usuarios sin herencia directa)
            'is_heritage_family_member' => ['nullable', 'boolean'],
            'heritage_family_member_name' => ['nullable', 'required_if:is_heritage_family_member,1', 'string', 'max:200'],
            'heritage_family_relationship' => ['nullable', 'required_if:is_heritage_family_member,1', 'string', 'max:50'],

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
            'has_ethnic_heritage.required' => 'Indica si tienes herencia etnica.',
            'ancestor_first_name.required_if' => 'El nombre del ancestro es obligatorio.',
            'ancestor_patronymic.required_if' => 'El apellido del ancestro es obligatorio.',
            'heritage_region.required_if' => 'La region de origen es obligatoria.',
            'heritage_family_member_name.required_if' => 'El nombre del familiar con herencia es obligatorio.',
            'heritage_family_relationship.required_if' => 'La relacion con el familiar es obligatoria.',
            'privacy_accepted.accepted' => 'Debes aceptar la politica de privacidad.',
        ]);

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
                'has_ethnic_heritage' => $request->boolean('has_ethnic_heritage'),
                'heritage_region' => $request->heritage_region,
                'migration_decade' => $request->migration_decade,
                'privacy_level' => 'family',
                'consent_status' => 'not_required',
                'created_by' => $user->id,
            ];

            // Si no tiene herencia directa pero tiene familiar con herencia, guardar esa informacion
            if (!$request->boolean('has_ethnic_heritage') && $request->boolean('is_heritage_family_member')) {
                $personData['heritage_family_member_name'] = $request->heritage_family_member_name;
                $personData['heritage_family_relationship'] = $request->heritage_family_relationship;
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
