<x-app-layout>
    <x-slot name="title">{{ __('Eliminacion de datos') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('Eliminacion de datos de usuario') }}</h1>

        <div class="prose prose-lg max-w-none text-gray-600">
            <p class="text-sm text-gray-500 mb-6">{{ __('Ultima actualizacion') }}: {{ now()->format('d/m/Y') }}</p>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('Tu derecho a eliminar tus datos') }}</h2>
                <p>
                    {{ __('En :app respetamos tu privacidad y tu derecho a controlar tus datos personales. Puedes solicitar la eliminacion completa de tu cuenta y datos en cualquier momento.', ['app' => config('app.name')]) }}
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('Como eliminar tu cuenta') }}</h2>
                <p>{{ __('Si tienes una cuenta activa, sigue estos pasos:') }}</p>
                <ol class="list-decimal pl-6 mt-3 space-y-3">
                    <li>
                        <strong>{{ __('Inicia sesion') }}</strong> {{ __('en tu cuenta en') }}
                        <a href="{{ route('login') }}" class="text-mf-primary hover:underline">{{ config('app.name') }}</a>.
                    </li>
                    <li>
                        <strong>{{ __('Ve a tu perfil') }}</strong> → {{ __('haz click en tu nombre en la esquina superior derecha y selecciona') }} <strong>{{ __('Configuracion') }}</strong>.
                    </li>
                    <li>
                        <strong>{{ __('Desplazate hacia abajo') }}</strong> {{ __('hasta la seccion') }} <strong>"{{ __('Eliminar cuenta') }}"</strong>.
                    </li>
                    <li>
                        <strong>{{ __('Confirma la eliminacion') }}</strong> {{ __('ingresando tu contrasena y escribiendo ELIMINAR.') }}
                    </li>
                </ol>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('Que datos se eliminan') }}</h2>
                <p>{{ __('Al eliminar tu cuenta, se eliminan permanentemente:') }}</p>
                <ul class="list-disc pl-6 mt-3 space-y-2">
                    <li>{{ __('Tu cuenta de usuario (email, contrasena, preferencias).') }}</li>
                    <li>{{ __('Tu vinculacion con cualquier persona del arbol genealogico.') }}</li>
                    <li>{{ __('Tus datos de contacto (email, telefono) asociados a tu perfil de persona.') }}</li>
                </ul>
                <p class="mt-3">
                    {{ __('Los datos genealogicos que hayas aportado (nombres, fechas, relaciones familiares) pueden mantenerse de forma anonimizada para preservar la integridad del arbol genealogico compartido, segun lo establecido en nuestros') }}
                    <a href="{{ route('terms') }}" class="text-mf-primary hover:underline">{{ __('Terminos y Condiciones') }}</a>.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('Cuentas creadas con Facebook u otros proveedores') }}</h2>
                <p>
                    {{ __('Si creaste tu cuenta usando Facebook, Google u otro proveedor de inicio de sesion, el proceso es el mismo: inicia sesion y elimina tu cuenta desde la configuracion de tu perfil.') }}
                </p>
                <p class="mt-3">
                    {{ __('Adicionalmente, puedes revocar el acceso de :app desde la configuracion de tu proveedor:', ['app' => config('app.name')]) }}
                </p>
                <ul class="list-disc pl-6 mt-3 space-y-2">
                    <li><strong>Facebook:</strong> {{ __('Configuracion') }} → {{ __('Seguridad e inicio de sesion') }} → {{ __('Apps y sitios web') }}</li>
                    <li><strong>Google:</strong> {{ __('Cuenta de Google') }} → {{ __('Seguridad') }} → {{ __('Aplicaciones de terceros') }}</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('Contacto') }}</h2>
                <p>
                    {{ __('Si no puedes acceder a tu cuenta o necesitas ayuda para eliminar tus datos, contacta al administrador del sitio.') }}
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
