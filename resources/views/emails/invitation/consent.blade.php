<x-mail::message>
# {{ __('Has sido registrado en :app', ['app' => config('app.name')]) }}

{{ __('Hola') }} **{{ $person->first_name }}**,

**{{ $inviter->person?->full_name ?? $inviter->email }}** {{ __('te ha registrado en el arbol genealogico de :app.', ['app' => config('app.name')]) }}

## {{ __('Tu informacion registrada:') }}

- **{{ __('Nombre') }}:** {{ $person->full_name }}
@if($person->birth_year)
- **{{ __('Año de nacimiento') }}:** {{ $person->birth_year }}
@endif
@if($person->birth_place)
- **{{ __('Lugar de nacimiento') }}:** {{ $person->birth_place }}
@endif

---

{{ __('Para cumplir con las regulaciones de proteccion de datos, necesitamos tu consentimiento para mantener esta informacion.') }}

{{ __('Al aceptar esta invitacion, podras:') }}
- {{ __('Ver y editar tu informacion personal') }}
- {{ __('Explorar tu arbol genealogico') }}
- {{ __('Conectar con familiares') }}

<x-mail::button :url="$acceptUrl" color="primary">
{{ __('Ver invitacion y decidir') }}
</x-mail::button>

{{ __('Si no reconoces a la persona que te invito o no deseas que tus datos esten en esta plataforma, puedes rechazar la invitacion desde el enlace anterior.') }}

---

<x-mail::subcopy>
{{ __('Este enlace expira el :date.', ['date' => $invitation->expires_at->format('d/m/Y')]) }}

{{ __('Si tienes problemas con el boton, copia y pega esta URL en tu navegador:') }}
{{ $acceptUrl }}
</x-mail::subcopy>

{{ __('Gracias') }},<br>
{{ config('app.name') }}
</x-mail::message>
