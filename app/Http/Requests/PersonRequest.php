<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validacion reutilizable para crear/actualizar personas (antes inline en
 * PersonController::validatePerson). La autorizacion de edicion se maneja en el
 * controlador (authorizeEdit -> PersonPolicy), por eso authorize() devuelve true.
 */
class PersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'patronymic' => ['required', 'string', 'max:100'],
            'matronymic' => ['nullable', 'string', 'max:100'],
            'nickname' => ['nullable', 'string', 'max:100'],
            'gender' => ['required', 'in:M,F,U,O'],
            'marital_status' => ['nullable', 'in:single,married,common_law,divorced,widowed'],
            'birth_year' => ['nullable', 'integer', 'min:1000', 'max:' . date('Y')],
            'birth_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'birth_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'birth_date_approx' => ['boolean'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_country' => ['nullable', 'string', 'max:100'],
            'death_year' => ['nullable', 'integer', 'min:1000', 'max:' . date('Y')],
            'death_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'death_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'death_date_approx' => ['boolean'],
            'death_place' => ['nullable', 'string', 'max:255'],
            'death_country' => ['nullable', 'string', 'max:100'],
            'is_living' => ['boolean'],
            'is_minor' => ['boolean'],
            'residence_place' => ['nullable', 'string', 'max:255'],
            'residence_country' => ['nullable', 'string', 'max:100'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'has_ethnic_heritage' => ['nullable', 'boolean'],
            'heritage_region' => ['nullable', 'string', 'max:100'],
            'origin_town' => ['nullable', 'string', 'max:255'],
            'migration_decade' => ['nullable', 'string', 'max:10'],
            'migration_destination' => ['nullable', 'string', 'max:255'],
            'privacy_level' => ['required', 'in:direct_family,extended_family,selected_users,community'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'El nombre es obligatorio.',
            'patronymic.required' => 'El apellido paterno es obligatorio.',
            'gender.required' => 'El genero es obligatorio.',
        ];
    }

    /**
     * Datos validados con las fechas de nacimiento/defuncion derivadas de sus
     * componentes (year/month/day) y los numericos vacios normalizados a null.
     *
     * @return array<string, mixed>
     */
    public function personData(): array
    {
        $validated = $this->validated();

        // Construir birth_date si tenemos todos los componentes
        if (!empty($validated['birth_year']) && !empty($validated['birth_month']) && !empty($validated['birth_day'])) {
            $validated['birth_date'] = sprintf(
                '%04d-%02d-%02d',
                $validated['birth_year'],
                $validated['birth_month'],
                $validated['birth_day']
            );
        } else {
            $validated['birth_date'] = null;
        }

        // Construir death_date si tenemos todos los componentes
        if (!empty($validated['death_year']) && !empty($validated['death_month']) && !empty($validated['death_day'])) {
            $validated['death_date'] = sprintf(
                '%04d-%02d-%02d',
                $validated['death_year'],
                $validated['death_month'],
                $validated['death_day']
            );
        } else {
            $validated['death_date'] = null;
        }

        // Limpiar valores vacios para campos numericos
        foreach (['birth_year', 'birth_month', 'birth_day', 'death_year', 'death_month', 'death_day'] as $field) {
            if (empty($validated[$field])) {
                $validated[$field] = null;
            }
        }

        return $validated;
    }
}
