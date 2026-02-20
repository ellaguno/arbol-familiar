<x-app-layout>
    <x-slot name="title">{{ __('Política de Privacidad') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold text-theme mb-8">{{ __('Política de Privacidad') }}</h1>

        <div class="prose prose-lg dark:prose-invert max-w-none text-theme-secondary">
            <p class="text-sm text-theme-muted mb-6">{{ __('Última actualización') }}: {{ now()->format('d/m/Y') }}</p>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-theme mb-4">1. {{ __('Introducción') }}</h2>
                <p>
                    {{ __('Bienvenido a') }} {{ config('app.name') }}, {{ __('una plataforma genealógica de código abierto.
                    Nos comprometemos a proteger la privacidad de nuestros usuarios y la información que comparten en nuestra plataforma.') }}
                </p>
                <p class="mt-3">
                    {{ __('Esta política describe cómo recopilamos, usamos, almacenamos y protegemos su información personal.') }}
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-theme mb-4">2. {{ __('Información que Recopilamos') }}</h2>
                <p>{{ __('Recopilamos los siguientes tipos de información:') }}</p>
                <ul class="list-disc pl-6 mt-3 space-y-2">
                    <li><strong>{{ __('Información de registro') }}:</strong> {{ __('nombre, correo electrónico, contraseña.') }}</li>
                    <li><strong>{{ __('Información genealógica') }}:</strong> {{ __('nombres, fechas de nacimiento y defunción, lugares, relaciones familiares, fotografías.') }}</li>
                    <li><strong>{{ __('Información de origen') }}:</strong> {{ __('región de origen, herencia cultural, información de migración.') }}</li>
                    <li><strong>{{ __('Información de uso') }}:</strong> {{ __('actividad en la plataforma, preferencias, configuraciones.') }}</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-theme mb-4">3. {{ __('Uso de la Información') }}</h2>
                <p>{{ __('Utilizamos su información para:') }}</p>
                <ul class="list-disc pl-6 mt-3 space-y-2">
                    <li>{{ __('Proporcionar y mantener el servicio de la plataforma genealógica.') }}</li>
                    <li>{{ __('Permitir la conexión entre miembros de la comunidad familiar.') }}</li>
                    <li>{{ __('Preservar la historia y el patrimonio de las familias.') }}</li>
                    <li>{{ __('Mejorar y personalizar la experiencia del usuario.') }}</li>
                    <li>{{ __('Comunicarnos con usted sobre actualizaciones y eventos relevantes.') }}</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-theme mb-4">4. {{ __('Protección de Datos') }}</h2>
                <p>
                    {{ __('Implementamos medidas de seguridad técnicas y organizativas para proteger su información personal, incluyendo:') }}
                </p>
                <ul class="list-disc pl-6 mt-3 space-y-2">
                    <li>{{ __('Encriptación de datos sensibles.') }}</li>
                    <li>{{ __('Acceso restringido a la información personal.') }}</li>
                    <li>{{ __('Copias de seguridad regulares.') }}</li>
                    <li>{{ __('Monitoreo de seguridad continuo.') }}</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-theme mb-4">5. {{ __('Compartir Información') }}</h2>
                <p>
                    {{ __('La información genealógica que usted registra puede ser visible para otros usuarios según la configuración de privacidad que elija. Usted puede controlar:') }}
                </p>
                <ul class="list-disc pl-6 mt-3 space-y-2">
                    <li>{{ __('Quién puede ver su información personal.') }}</li>
                    <li>{{ __('Quién puede ver la información de sus familiares.') }}</li>
                    <li>{{ __('Si desea que su perfil sea visible en búsquedas.') }}</li>
                </ul>
                <p class="mt-3">
                    {{ __('No vendemos ni compartimos su información con terceros para fines comerciales.') }}
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-theme mb-4">6. {{ __('Sus Derechos') }}</h2>
                <p>{{ __('Usted tiene derecho a:') }}</p>
                <ul class="list-disc pl-6 mt-3 space-y-2">
                    <li>{{ __('Acceder a su información personal.') }}</li>
                    <li>{{ __('Rectificar datos incorrectos.') }}</li>
                    <li>{{ __('Solicitar la eliminación de su cuenta y datos.') }}</li>
                    <li>{{ __('Exportar su información genealógica en formato GEDCOM.') }}</li>
                    <li>{{ __('Modificar sus preferencias de privacidad en cualquier momento.') }}</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-theme mb-4">7. {{ __('Cookies') }}</h2>
                <p>
                    {{ __('Utilizamos cookies esenciales para el funcionamiento de la plataforma, incluyendo:') }}
                </p>
                <ul class="list-disc pl-6 mt-3 space-y-2">
                    <li>{{ __('Cookies de sesión para mantener su inicio de sesión.') }}</li>
                    <li>{{ __('Cookies de preferencias para recordar su configuración.') }}</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-theme mb-4">8. {{ __('Cambios a esta Política') }}</h2>
                <p>
                    {{ __('Nos reservamos el derecho de actualizar esta política de privacidad. Los cambios significativos serán notificados a través de la plataforma o por correo electrónico.') }}
                </p>
            </section>
        </div>

        <div class="mt-8 pt-8 border-t border-theme">
            <a href="{{ url()->previous() }}" class="text-mf-primary hover:underline">
                &larr; {{ __('Volver') }}
            </a>
        </div>
    </div>
</x-app-layout>
