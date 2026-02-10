<x-app-layout>
    <x-slot name="title">{{ __('Login Social') }} - {{ __('Administracion') }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="flex items-center"><a href="{{ route('admin.index') }}" class="text-theme-muted hover:text-theme-secondary">{{ __('Admin') }}</a></li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-theme-muted" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-theme-secondary font-medium ml-1 md:ml-2">{{ __('Login Social') }}</span>
                </li>
            </ol>
        </nav>

        <h1 class="text-2xl font-bold text-theme mb-6">{{ __('Configuracion de Login Social') }}</h1>

        @if(session('success'))
            <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
        @endif

        @if($errors->any())
            <x-alert type="error" class="mb-6">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </x-alert>
        @endif

        <form action="{{ route('admin.social-login.settings.update') }}" method="POST" class="space-y-8">
            @csrf

            {{-- Google --}}
            <div class="card" x-data="{ open: {{ !empty($settings['google_enabled']) ? 'true' : 'false' }} }">
                <div class="card-header flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6" viewBox="0 0 24 24">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        <h2 class="text-lg font-semibold text-theme">Google</h2>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="google_enabled" value="0">
                        <input type="checkbox" name="google_enabled" value="1"
                               {{ !empty($settings['google_enabled']) ? 'checked' : '' }}
                               @change="open = $el.checked"
                               class="w-4 h-4 text-blue-600 rounded border-theme focus:ring-blue-500">
                        <span class="text-sm text-theme-secondary">{{ __('Habilitado') }}</span>
                    </label>
                </div>
                <div class="card-body" x-show="open" x-collapse>
                    <div class="space-y-4">
                        <div>
                            <label class="form-label">Client ID</label>
                            <input type="text" name="google_client_id"
                                   value="{{ $settings['google_client_id'] ?? '' }}"
                                   class="form-input" placeholder="123456789.apps.googleusercontent.com">
                        </div>
                        <div>
                            <label class="form-label">Client Secret</label>
                            <input type="password" name="google_client_secret"
                                   class="form-input"
                                   placeholder="{{ !empty($settings['google_client_secret']) ? '********' : '' }}">
                            @if(!empty($settings['google_client_secret']))
                                <p class="text-xs text-green-600 mt-1">{{ __('Configurado') }}. {{ __('Deja vacio para mantener el actual.') }}</p>
                            @endif
                        </div>
                        <div>
                            <label class="form-label">{{ __('URL de redireccion') }}</label>
                            <div class="flex items-center gap-2">
                                <input type="text" value="{{ $callbackUrls['google'] }}" class="form-input bg-theme-secondary" readonly>
                                <button type="button" onclick="navigator.clipboard.writeText('{{ $callbackUrls['google'] }}')"
                                        class="btn-outline px-3 py-2 text-sm whitespace-nowrap">{{ __('Copiar') }}</button>
                            </div>
                        </div>
                        <details class="text-sm text-theme-muted">
                            <summary class="cursor-pointer hover:text-theme-secondary">{{ __('Instrucciones de configuracion') }}</summary>
                            <ol class="list-decimal pl-5 mt-2 space-y-1">
                                <li>{{ __('Ve a') }} <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="text-mf-primary hover:underline">Google Cloud Console</a></li>
                                <li>{{ __('Crea un proyecto o selecciona uno existente') }}</li>
                                <li>{{ __('Ve a Credenciales > Crear credenciales > ID de cliente OAuth') }}</li>
                                <li>{{ __('Tipo de aplicacion: Aplicacion web') }}</li>
                                <li>{{ __('Agrega la URL de redireccion de arriba') }}</li>
                                <li>{{ __('Copia el Client ID y Client Secret') }}</li>
                            </ol>
                        </details>
                    </div>
                </div>
            </div>

            {{-- Microsoft --}}
            <div class="card" x-data="{ open: {{ !empty($settings['microsoft_enabled']) ? 'true' : 'false' }} }">
                <div class="card-header flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6" viewBox="0 0 24 24">
                            <rect x="1" y="1" width="10" height="10" fill="#F25022"/>
                            <rect x="13" y="1" width="10" height="10" fill="#7FBA00"/>
                            <rect x="1" y="13" width="10" height="10" fill="#00A4EF"/>
                            <rect x="13" y="13" width="10" height="10" fill="#FFB900"/>
                        </svg>
                        <h2 class="text-lg font-semibold text-theme">Microsoft</h2>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="microsoft_enabled" value="0">
                        <input type="checkbox" name="microsoft_enabled" value="1"
                               {{ !empty($settings['microsoft_enabled']) ? 'checked' : '' }}
                               @change="open = $el.checked"
                               class="w-4 h-4 text-blue-600 rounded border-theme focus:ring-blue-500">
                        <span class="text-sm text-theme-secondary">{{ __('Habilitado') }}</span>
                    </label>
                </div>
                <div class="card-body" x-show="open" x-collapse>
                    <div class="space-y-4">
                        <div>
                            <label class="form-label">Application (Client) ID</label>
                            <input type="text" name="microsoft_client_id"
                                   value="{{ $settings['microsoft_client_id'] ?? '' }}"
                                   class="form-input" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
                        </div>
                        <div>
                            <label class="form-label">Client Secret</label>
                            <input type="password" name="microsoft_client_secret"
                                   class="form-input"
                                   placeholder="{{ !empty($settings['microsoft_client_secret']) ? '********' : '' }}">
                            @if(!empty($settings['microsoft_client_secret']))
                                <p class="text-xs text-green-600 mt-1">{{ __('Configurado') }}. {{ __('Deja vacio para mantener el actual.') }}</p>
                            @endif
                        </div>
                        <div>
                            <label class="form-label">Tenant ID</label>
                            <input type="text" name="microsoft_tenant"
                                   value="{{ $settings['microsoft_tenant'] ?? 'common' }}"
                                   class="form-input" placeholder="common">
                            <p class="text-xs text-theme-muted mt-1">{{ __('Usa "common" para cuentas personales y de trabajo, o un tenant ID especifico.') }}</p>
                        </div>
                        <div>
                            <label class="form-label">{{ __('URL de redireccion') }}</label>
                            <div class="flex items-center gap-2">
                                <input type="text" value="{{ $callbackUrls['microsoft'] }}" class="form-input bg-theme-secondary" readonly>
                                <button type="button" onclick="navigator.clipboard.writeText('{{ $callbackUrls['microsoft'] }}')"
                                        class="btn-outline px-3 py-2 text-sm whitespace-nowrap">{{ __('Copiar') }}</button>
                            </div>
                        </div>
                        <details class="text-sm text-theme-muted">
                            <summary class="cursor-pointer hover:text-theme-secondary">{{ __('Instrucciones de configuracion') }}</summary>
                            <ol class="list-decimal pl-5 mt-2 space-y-1">
                                <li>{{ __('Ve a') }} <a href="https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationsListBlade" target="_blank" class="text-mf-primary hover:underline">Azure Portal - App Registrations</a></li>
                                <li>{{ __('Crea un nuevo registro de aplicacion') }}</li>
                                <li>{{ __('Agrega la URL de redireccion como "Web" redirect URI') }}</li>
                                <li>{{ __('Copia el Application (client) ID y Directory (tenant) ID') }}</li>
                                <li>{{ __('En Certificates & secrets, crea un nuevo client secret') }}</li>
                                <li>{{ __('En API permissions, agrega Microsoft Graph > User.Read') }}</li>
                            </ol>
                        </details>
                    </div>
                </div>
            </div>

            {{-- Facebook --}}
            <div class="card" x-data="{ open: {{ !empty($settings['facebook_enabled']) ? 'true' : 'false' }} }">
                <div class="card-header flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="#1877F2">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        <h2 class="text-lg font-semibold text-theme">Facebook</h2>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="facebook_enabled" value="0">
                        <input type="checkbox" name="facebook_enabled" value="1"
                               {{ !empty($settings['facebook_enabled']) ? 'checked' : '' }}
                               @change="open = $el.checked"
                               class="w-4 h-4 text-blue-600 rounded border-theme focus:ring-blue-500">
                        <span class="text-sm text-theme-secondary">{{ __('Habilitado') }}</span>
                    </label>
                </div>
                <div class="card-body" x-show="open" x-collapse>
                    <div class="space-y-4">
                        <div>
                            <label class="form-label">App ID</label>
                            <input type="text" name="facebook_client_id"
                                   value="{{ $settings['facebook_client_id'] ?? '' }}"
                                   class="form-input" placeholder="123456789012345">
                        </div>
                        <div>
                            <label class="form-label">App Secret</label>
                            <input type="password" name="facebook_client_secret"
                                   class="form-input"
                                   placeholder="{{ !empty($settings['facebook_client_secret']) ? '********' : '' }}">
                            @if(!empty($settings['facebook_client_secret']))
                                <p class="text-xs text-green-600 mt-1">{{ __('Configurado') }}. {{ __('Deja vacio para mantener el actual.') }}</p>
                            @endif
                        </div>
                        <div>
                            <label class="form-label">{{ __('URL de redireccion') }}</label>
                            <div class="flex items-center gap-2">
                                <input type="text" value="{{ $callbackUrls['facebook'] }}" class="form-input bg-theme-secondary" readonly>
                                <button type="button" onclick="navigator.clipboard.writeText('{{ $callbackUrls['facebook'] }}')"
                                        class="btn-outline px-3 py-2 text-sm whitespace-nowrap">{{ __('Copiar') }}</button>
                            </div>
                        </div>
                        <details class="text-sm text-theme-muted">
                            <summary class="cursor-pointer hover:text-theme-secondary">{{ __('Instrucciones de configuracion') }}</summary>
                            <ol class="list-decimal pl-5 mt-2 space-y-1">
                                <li>{{ __('Ve a') }} <a href="https://developers.facebook.com/apps/" target="_blank" class="text-mf-primary hover:underline">Facebook Developers</a></li>
                                <li>{{ __('Crea una nueva app (tipo: Consumer)') }}</li>
                                <li>{{ __('Agrega el producto "Facebook Login"') }}</li>
                                <li>{{ __('En Settings > Valid OAuth Redirect URIs, agrega la URL de arriba') }}</li>
                                <li>{{ __('Copia el App ID y App Secret desde Settings > Basic') }}</li>
                            </ol>
                        </details>
                    </div>
                </div>
            </div>

            {{-- General Settings --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold text-theme">{{ __('Configuracion general') }}</h2>
                </div>
                <div class="card-body space-y-4">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="hidden" name="auto_create_users" value="0">
                        <input type="checkbox" name="auto_create_users" value="1"
                               {{ ($settings['auto_create_users'] ?? true) ? 'checked' : '' }}
                               class="w-4 h-4 mt-0.5 text-blue-600 rounded border-theme focus:ring-blue-500">
                        <div>
                            <span class="text-sm font-medium text-theme">{{ __('Crear cuentas automaticamente') }}</span>
                            <p class="text-xs text-theme-muted">{{ __('Cuando un usuario inicia sesion con OAuth y no existe una cuenta con ese email, se crea automaticamente.') }}</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="hidden" name="auto_verify_email" value="0">
                        <input type="checkbox" name="auto_verify_email" value="1"
                               {{ ($settings['auto_verify_email'] ?? true) ? 'checked' : '' }}
                               class="w-4 h-4 mt-0.5 text-blue-600 rounded border-theme focus:ring-blue-500">
                        <div>
                            <span class="text-sm font-medium text-theme">{{ __('Verificar email automaticamente') }}</span>
                            <p class="text-xs text-theme-muted">{{ __('Los emails de cuentas OAuth se consideran verificados automaticamente.') }}</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="hidden" name="link_existing_accounts" value="0">
                        <input type="checkbox" name="link_existing_accounts" value="1"
                               {{ ($settings['link_existing_accounts'] ?? true) ? 'checked' : '' }}
                               class="w-4 h-4 mt-0.5 text-blue-600 rounded border-theme focus:ring-blue-500">
                        <div>
                            <span class="text-sm font-medium text-theme">{{ __('Vincular cuentas existentes') }}</span>
                            <p class="text-xs text-theme-muted">{{ __('Si un usuario OAuth tiene el mismo email que una cuenta existente, se vinculan automaticamente.') }}</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-primary">
                    {{ __('Guardar cambios') }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
