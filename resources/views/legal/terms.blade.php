<x-app-layout>
    <x-slot name="title">{{ __('Términos y Condiciones') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('Términos y Condiciones') }}</h1>

        <div class="prose prose-lg max-w-none text-gray-600">
            <p class="text-sm text-gray-500 mb-6">{{ __('Última actualización') }}: {{ now()->format('d/m/Y') }}</p>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">1. {{ __('Aceptación de los Términos') }}</h2>
                <p>
                    {{ __('Al acceder y utilizar {{ config('app.name') }}, usted acepta estos términos y condiciones en su totalidad.
                    Si no está de acuerdo con alguna parte de estos términos, no debe utilizar esta plataforma.') }}
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">2. {{ __('Descripción del Servicio') }}</h2>
                <p>
                    {{ config('app.name') }} {{ __('es una plataforma genealógica de código abierto que permite a los usuarios:') }}
                </p>
                <ul class="list-disc pl-6 mt-3 space-y-2">
                    <li>{{ __('Crear y administrar árboles genealógicos familiares.') }}</li>
                    <li>{{ __('Registrar información de personas y familias.') }}</li>
                    <li>{{ __('Conectar con otros miembros de la comunidad familiar.') }}</li>
                    <li>{{ __('Importar y exportar datos genealógicos en formato GEDCOM.') }}</li>
                    <li>{{ __('Almacenar fotografías y documentos familiares.') }}</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">3. {{ __('Registro de Usuario') }}</h2>
                <p>{{ __('Para utilizar la plataforma, usted debe:') }}</p>
                <ul class="list-disc pl-6 mt-3 space-y-2">
                    <li>{{ __('Ser mayor de 18 años o contar con autorización de un tutor legal.') }}</li>
                    <li>{{ __('Proporcionar información veraz y actualizada.') }}</li>
                    <li>{{ __('Mantener la confidencialidad de su contraseña.') }}</li>
                    <li>{{ __('Notificar inmediatamente cualquier uso no autorizado de su cuenta.') }}</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">4. {{ __('Uso Aceptable') }}</h2>
                <p>{{ __('Al utilizar {{ config('app.name') }}, usted se compromete a:') }}</p>
                <ul class="list-disc pl-6 mt-3 space-y-2">
                    <li>{{ __('Proporcionar información genealógica precisa y verificable.') }}</li>
                    <li>{{ __('Respetar la privacidad de otros usuarios y sus familias.') }}</li>
                    <li>{{ __('No cargar contenido ofensivo, difamatorio o ilegal.') }}</li>
                    <li>{{ __('No utilizar la plataforma para fines comerciales sin autorización.') }}</li>
                    <li>{{ __('No intentar acceder a cuentas o datos de otros usuarios.') }}</li>
                    <li>{{ __('No utilizar bots, scripts o herramientas automatizadas sin permiso.') }}</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">5. {{ __('Contenido del Usuario') }}</h2>
                <p>
                    {{ __('Usted es responsable del contenido que publique en la plataforma, incluyendo:') }}
                </p>
                <ul class="list-disc pl-6 mt-3 space-y-2">
                    <li>{{ __('Información personal de familiares (con su consentimiento cuando sea necesario).') }}</li>
                    <li>{{ __('Fotografías y documentos que suba a la plataforma.') }}</li>
                    <li>{{ __('Mensajes enviados a otros usuarios.') }}</li>
                </ul>
                <p class="mt-3">
                    {{ __('Usted garantiza que tiene los derechos necesarios para compartir el contenido que publique y que dicho contenido no infringe los derechos de terceros.') }}
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">6. {{ __('Propiedad Intelectual') }}</h2>
                <p>
                    {{ __('La plataforma, incluyendo su diseño, código, logotipos y contenido original, es software de código abierto. Consulte la licencia del proyecto para más detalles.') }}
                </p>
                <p class="mt-3">
                    {{ __('El contenido genealógico que usted aporte permanece de su propiedad, otorgando a la plataforma una licencia para mostrarlo según sus configuraciones de privacidad.') }}
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">7. {{ __('Privacidad de Terceros') }}</h2>
                <p>
                    {{ __('Al registrar información de otras personas (familiares), usted debe:') }}
                </p>
                <ul class="list-disc pl-6 mt-3 space-y-2">
                    <li>{{ __('Obtener consentimiento de personas vivas antes de publicar su información.') }}</li>
                    <li>{{ __('Respetar los deseos de familiares que no deseen aparecer en la plataforma.') }}</li>
                    <li>{{ __('No publicar información sensible sin autorización.') }}</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">8. {{ __('Limitación de Responsabilidad') }}</h2>
                <p>
                    {{ __('La plataforma se proporciona "tal cual" sin garantías de ningún tipo. No nos hacemos responsables de:') }}
                </p>
                <ul class="list-disc pl-6 mt-3 space-y-2">
                    <li>{{ __('Inexactitudes en la información genealógica proporcionada por usuarios.') }}</li>
                    <li>{{ __('Pérdida de datos debido a circunstancias fuera de nuestro control.') }}</li>
                    <li>{{ __('Interrupciones temporales del servicio.') }}</li>
                    <li>{{ __('Acciones de terceros que violen estos términos.') }}</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">9. {{ __('Suspensión y Terminación') }}</h2>
                <p>
                    {{ __('Nos reservamos el derecho de suspender o terminar su cuenta si:') }}
                </p>
                <ul class="list-disc pl-6 mt-3 space-y-2">
                    <li>{{ __('Viola estos términos y condiciones.') }}</li>
                    <li>{{ __('Proporciona información falsa o engañosa.') }}</li>
                    <li>{{ __('Realiza actividades que perjudiquen a la comunidad o la plataforma.') }}</li>
                </ul>
                <p class="mt-3">
                    {{ __('Usted puede solicitar la eliminación de su cuenta en cualquier momento desde su perfil.') }}
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">10. {{ __('Modificaciones') }}</h2>
                <p>
                    {{ __('Nos reservamos el derecho de modificar estos términos en cualquier momento.
                    Los cambios entrarán en vigor al publicarse en la plataforma.
                    El uso continuado del servicio después de cambios constituye su aceptación de los nuevos términos.') }}
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">11. {{ __('Ley Aplicable') }}</h2>
                <p>
                    {{ __('Estos términos se rigen por las leyes de los Estados Unidos Mexicanos.
                    Cualquier disputa será resuelta en los tribunales competentes de la Ciudad de México.') }}
                </p>
            </section>

        </div>

        <div class="mt-8 pt-8 border-t border-gray-200">
            <a href="{{ url()->previous() }}" class="text-mf-primary hover:underline">
                &larr; {{ __('Volver') }}
            </a>
        </div>
    </div>
</x-app-layout>
