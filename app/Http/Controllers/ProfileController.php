<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Services\SiteSettingsService;

class ProfileController extends Controller
{
    /**
     * Muestra el formulario de edicion de perfil.
     */
    public function edit()
    {
        $user = auth()->user();
        $person = $user->person;

        return view('profile.edit', compact('user', 'person'));
    }

    /**
     * Actualiza el perfil del usuario.
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        $person = $user->person;

        $validated = $request->validate([
            // Datos de la persona
            'first_name' => ['required', 'string', 'max:100'],
            'patronymic' => ['required', 'string', 'max:100'],
            'matronymic' => ['nullable', 'string', 'max:100'],
            'nickname' => ['nullable', 'string', 'max:100'],
            'gender' => ['required', 'in:M,F,U'],
            'marital_status' => ['nullable', 'in:single,married,common_law,divorced,widowed'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_country' => ['nullable', 'string', 'max:100'],
            'residence_place' => ['nullable', 'string', 'max:255'],
            'residence_country' => ['nullable', 'string', 'max:100'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],

            // Configuracion de usuario
            'language' => ['required', 'in:es,en'],
            'privacy_level' => ['required', 'in:direct_family,extended_family,selected_users,community'],
        ], [
            'first_name.required' => 'El nombre es obligatorio.',
            'patronymic.required' => 'El apellido paterno es obligatorio.',
            'gender.required' => 'El genero es obligatorio.',
            'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
        ]);

        // Heritage fields (only if feature is enabled)
        $heritageData = [];
        if (app(SiteSettingsService::class)->heritageEnabled()) {
            $heritageData = [
                'has_ethnic_heritage' => (bool) $request->input('has_ethnic_heritage'),
                'heritage_region' => $request->input('heritage_region'),
                'origin_town' => $request->input('origin_town'),
                'migration_decade' => $request->input('migration_decade'),
            ];
        }

        // Actualizar o crear persona
        if ($person) {
            $person->update(array_merge([
                'first_name' => $validated['first_name'],
                'patronymic' => $validated['patronymic'],
                'matronymic' => $validated['matronymic'] ?? null,
                'nickname' => $validated['nickname'] ?? null,
                'gender' => $validated['gender'],
                'marital_status' => $validated['marital_status'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'birth_place' => $validated['birth_place'] ?? null,
                'birth_country' => $validated['birth_country'] ?? null,
                'residence_place' => $validated['residence_place'] ?? null,
                'residence_country' => $validated['residence_country'] ?? null,
                'occupation' => $validated['occupation'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'updated_by' => $user->id,
            ], $heritageData));
        } else {
            $person = Person::create(array_merge([
                'first_name' => $validated['first_name'],
                'patronymic' => $validated['patronymic'],
                'matronymic' => $validated['matronymic'] ?? null,
                'nickname' => $validated['nickname'] ?? null,
                'gender' => $validated['gender'],
                'marital_status' => $validated['marital_status'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'birth_place' => $validated['birth_place'] ?? null,
                'birth_country' => $validated['birth_country'] ?? null,
                'residence_place' => $validated['residence_place'] ?? null,
                'residence_country' => $validated['residence_country'] ?? null,
                'occupation' => $validated['occupation'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $user->email,
                'is_living' => true,
                'privacy_level' => $validated['privacy_level'],
                'created_by' => $user->id,
            ], $heritageData));

            $user->update(['person_id' => $person->id]);
        }

        // Actualizar configuracion de usuario
        $user->update([
            'language' => $validated['language'],
            'privacy_level' => $validated['privacy_level'],
        ]);

        ActivityLog::log('profile_updated', $user, $person);

        return redirect()->route('profile.edit')
            ->with('success', 'Perfil actualizado correctamente.');
    }

    /**
     * Actualiza la foto de perfil.
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // 5MB max
        ], [
            'photo.required' => 'Selecciona una imagen.',
            'photo.image' => 'El archivo debe ser una imagen.',
            'photo.mimes' => 'La imagen debe ser JPG, PNG o WebP.',
            'photo.max' => 'La imagen no debe superar 5MB.',
        ]);

        $user = auth()->user();
        $person = $user->person;

        if (!$person) {
            return back()->with('error', 'Primero debes completar tu perfil.');
        }

        // Eliminar foto anterior si existe
        if ($person->photo_path) {
            Storage::disk('public')->delete($person->photo_path);
        }

        // Guardar nueva foto
        $path = $request->file('photo')->store('photos/profiles', 'public');
        $person->update(['photo_path' => $path]);

        ActivityLog::log('photo_updated', $user, $person);

        return back()->with('success', 'Foto de perfil actualizada.');
    }

    /**
     * Elimina la foto de perfil.
     */
    public function deletePhoto()
    {
        $user = auth()->user();
        $person = $user->person;

        if ($person && $person->photo_path) {
            Storage::disk('public')->delete($person->photo_path);
            $person->update(['photo_path' => null]);

            ActivityLog::log('photo_deleted', $user, $person);
        }

        return back()->with('success', 'Foto de perfil eliminada.');
    }

    /**
     * Muestra la pagina de configuracion.
     */
    public function settings()
    {
        $user = auth()->user();

        return view('profile.settings', compact('user'));
    }

    /**
     * Actualiza la contrasena.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ], [
            'current_password.required' => 'Ingresa tu contrasena actual.',
            'current_password.current_password' => 'La contrasena actual es incorrecta.',
            'password.required' => 'Ingresa la nueva contrasena.',
            'password.confirmed' => 'Las contrasenas no coinciden.',
            'password.min' => 'La contrasena debe tener al menos 8 caracteres.',
        ]);

        $user = auth()->user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        ActivityLog::log('password_changed', $user);

        return back()->with('success', 'Contrasena actualizada correctamente.');
    }

    /**
     * Elimina la cuenta del usuario.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
            'confirmation' => ['required', 'in:ELIMINAR'],
        ], [
            'password.current_password' => 'La contrasena es incorrecta.',
            'confirmation.in' => 'Escribe ELIMINAR para confirmar.',
        ]);

        $user = auth()->user();

        ActivityLog::log('account_deleted', $user);

        // Soft delete de la persona si existe
        if ($user->person) {
            $user->person->update([
                'email' => null,
                'phone' => null,
                'is_living' => true,
                'consent_status' => 'revoked',
            ]);
        }

        // Eliminar usuario
        $user->delete();

        return redirect()->route('home')
            ->with('success', 'Tu cuenta ha sido eliminada.');
    }
}
