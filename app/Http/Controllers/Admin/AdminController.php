<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Family;
use App\Models\Media;
use App\Models\Message;
use App\Models\Person;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /**
     * Dashboard de administracion.
     */
    public function index()
    {
        $stats = [
            'users' => [
                'total' => User::count(),
                'verified' => User::whereNotNull('email_verified_at')->count(),
                'active' => User::where('last_login_at', '>=', now()->subDays(30))->count(),
                'admins' => User::where('is_admin', true)->count(),
                'recent' => User::where('created_at', '>=', now()->subDays(30))->count(),
            ],
            'content' => [
                'persons' => Person::count(),
                'families' => Family::count(),
                'media' => Media::count(),
                'messages' => Message::count(),
            ],
            'activity' => [
                'today' => ActivityLog::whereDate('created_at', today())->count(),
                'week' => ActivityLog::where('created_at', '>=', now()->subWeek())->count(),
            ],
        ];

        // Usuarios recientes
        $recentUsers = User::with('person')
            ->latest()
            ->limit(10)
            ->get();

        // Actividad reciente
        $recentActivity = ActivityLog::with('user')
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.index', compact('stats', 'recentUsers', 'recentActivity'));
    }

    /**
     * Lista de usuarios.
     */
    public function users(Request $request)
    {
        $query = User::with('person');

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhereHas('person', function ($sub) use ($search) {
                      $sub->where('first_name', 'like', "%{$search}%")
                          ->orWhere('patronymic', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            switch ($request->status) {
                case 'verified':
                    $query->whereNotNull('email_verified_at');
                    break;
                case 'unverified':
                    $query->whereNull('email_verified_at');
                    break;
                case 'admin':
                    $query->where('is_admin', true);
                    break;
                case 'locked':
                    $query->whereNotNull('locked_until')
                          ->where('locked_until', '>', now());
                    break;
            }
        }

        $users = $query->latest()->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Ver usuario.
     */
    public function showUser(User $user)
    {
        $user->load(['person', 'createdPersons', 'createdFamilies', 'activityLog' => function ($q) {
            $q->latest()->limit(20);
        }]);

        $stats = [
            'persons_created' => $user->createdPersons()->count(),
            'families_created' => $user->createdFamilies()->count(),
            'media_created' => $user->createdMedia()->count(),
            'messages_sent' => $user->sentMessages()->count(),
            'messages_received' => $user->receivedMessages()->count(),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    /**
     * Editar usuario.
     */
    public function editUser(User $user)
    {
        $persons = Person::whereNull('user_id')
            ->orWhere('user_id', $user->id)
            ->orderBy('patronymic')
            ->orderBy('first_name')
            ->get();

        return view('admin.users.edit', compact('user', 'persons'));
    }

    /**
     * Actualizar usuario.
     */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email,' . $user->id,
            'person_id' => 'nullable|exists:persons,id',
            'is_admin' => 'boolean',
            'privacy_level' => 'required|in:direct_family,extended_family,selected_users,community',
        ]);

        // No permitir que el usuario actual se quite el rol de admin
        if ($user->id === Auth::id() && !$request->boolean('is_admin')) {
            return back()->with('error', __('No puedes quitarte el rol de administrador a ti mismo.'));
        }

        $user->update([
            'email' => $validated['email'],
            'person_id' => $validated['person_id'],
            'privacy_level' => $validated['privacy_level'],
        ]);

        // Actualizar is_admin de forma segura (no está en $fillable)
        if ($user->is_admin !== $request->boolean('is_admin')) {
            $user->setAdmin($request->boolean('is_admin'));
        }

        // Actualizar user_id en persona
        if ($validated['person_id']) {
            Person::where('id', $validated['person_id'])->update(['user_id' => $user->id]);
        }

        $this->logActivity('user_updated', $user);

        return redirect()->route('admin.users.show', $user)
            ->with('success', __('Usuario actualizado correctamente.'));
    }

    /**
     * Resetear contrasena de usuario.
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        $this->logActivity('password_reset', $user);

        return back()->with('success', __('Contrasena actualizada correctamente.'));
    }

    /**
     * Bloquear/desbloquear usuario.
     */
    public function toggleLock(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', __('No puedes bloquearte a ti mismo.'));
        }

        if ($user->isLocked()) {
            $user->update([
                'locked_until' => null,
                'login_attempts' => 0,
            ]);
            $action = 'user_unlocked';
            $message = __('Usuario desbloqueado.');
        } else {
            $user->update([
                'locked_until' => now()->addDays(30),
            ]);
            $action = 'user_locked';
            $message = __('Usuario bloqueado.');
        }

        $this->logActivity($action, $user);

        return back()->with('success', $message);
    }

    /**
     * Verificar email manualmente.
     */
    public function verifyEmail(User $user)
    {
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            $this->logActivity('email_verified_manual', $user);
            return back()->with('success', __('Email verificado manualmente.'));
        }

        return back()->with('info', __('El email ya estaba verificado.'));
    }

    /**
     * Eliminar usuario.
     */
    public function destroyUser(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', __('No puedes eliminarte a ti mismo.'));
        }

        $email = $user->email;
        $adminId = Auth::id();

        // Reasignar contenido creado por este usuario al admin actual
        \App\Models\Person::where('created_by', $user->id)->update(['created_by' => $adminId]);
        \App\Models\Family::where('created_by', $user->id)->update(['created_by' => $adminId]);
        \App\Models\Media::where('created_by', $user->id)->update(['created_by' => $adminId]);

        // Eliminar registros relacionados que no necesitan reasignación
        \App\Models\Message::where('sender_id', $user->id)->orWhere('recipient_id', $user->id)->delete();
        \App\Models\ActivityLog::where('user_id', $user->id)->delete();

        $user->delete();

        $this->logActivity('user_deleted', null, ['email' => $email]);

        return redirect()->route('admin.users')
            ->with('success', __('Usuario eliminado correctamente.'));
    }

    /**
     * Registro de actividad.
     */
    public function activityLog(Request $request)
    {
        $query = ActivityLog::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->latest()->paginate(50);

        $users = User::orderBy('email')->get();
        $actions = ActivityLog::select('action')->distinct()->pluck('action');

        return view('admin.activity', compact('activities', 'users', 'actions'));
    }

    /**
     * Limpiar registro de actividad.
     */
    public function clearActivityLog(Request $request)
    {
        $days = $request->input('days', 0);

        if ($days > 0) {
            // Eliminar registros mas antiguos que X dias
            $count = ActivityLog::where('created_at', '<', now()->subDays($days))->count();
            ActivityLog::where('created_at', '<', now()->subDays($days))->delete();
            $message = __('Se eliminaron :count registros con mas de :days dias de antiguedad.', [
                'count' => $count,
                'days' => $days,
            ]);
        } else {
            // Eliminar todos los registros
            $count = ActivityLog::count();
            ActivityLog::truncate();
            $message = __('Se eliminaron todos los :count registros de actividad.', ['count' => $count]);
        }

        // Registrar la accion de limpieza
        $this->logActivity('activity_log_cleared', null, ['deleted_count' => $count, 'days' => $days]);

        return back()->with('success', $message);
    }

    /**
     * Configuracion del sistema.
     */
    public function settings()
    {
        return view('admin.settings');
    }

    /**
     * Actualizar configuracion.
     */
    public function updateSettings(Request $request)
    {
        // Guardar configuraciones en archivo .env o en base de datos
        // Por ahora solo un placeholder
        return back()->with('success', __('Configuracion actualizada.'));
    }

    /**
     * Limpiar caches.
     */
    public function clearCache()
    {
        \Artisan::call('optimize:clear');

        $this->logActivity('cache_cleared');

        return back()->with('success', __('Caches limpiados correctamente.'));
    }

    /**
     * Optimizar aplicacion.
     */
    public function optimize()
    {
        $results = [];
        $errors = [];

        // Primero limpiar caches antiguos para evitar conflictos
        try {
            \Artisan::call('config:clear');
            \Artisan::call('view:clear');
        } catch (\Exception $e) {
            // Ignorar errores de limpieza
        }

        // Intentar cachear vistas (esto es seguro)
        try {
            \Artisan::call('view:cache');
            $results[] = 'vistas';
        } catch (\Exception $e) {
            $errors[] = 'vistas: ' . $e->getMessage();
        }

        // NO cachear configuracion - puede fallar con closures en config files
        // NO cachear rutas - falla con closures en web.php
        // Estos comandos son problematicos en hosting compartido

        $this->logActivity('app_optimized', null, ['cached' => $results, 'errors' => $errors]);

        if (empty($errors)) {
            return back()->with('success', __('Aplicacion optimizada. Se han cacheado las vistas.'));
        } elseif (!empty($results)) {
            return back()->with('warning', __('Optimizacion parcial. Cacheado: :results. Errores: :errors', [
                'results' => implode(', ', $results),
                'errors' => implode(', ', $errors)
            ]));
        } else {
            return back()->with('error', __('Error al optimizar: :errors', ['errors' => implode(', ', $errors)]));
        }
    }

    /**
     * Enviar correo de prueba.
     */
    public function testMail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            \Mail::raw(
                __('Este es un correo de prueba desde Mi Familia. Si puedes leer este mensaje, la configuracion de correo funciona correctamente.'),
                function ($message) use ($request) {
                    $message->to($request->test_email)
                            ->subject(__('Correo de prueba - Mi Familia'));
                }
            );

            $this->logActivity('test_mail_sent', null, ['email' => $request->test_email]);

            return back()->with('success', __('Correo de prueba enviado a :email', ['email' => $request->test_email]));
        } catch (\Exception $e) {
            return back()->with('error', __('Error al enviar correo: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Diagnostico de configuracion de correo.
     */
    public function mailDiagnostic()
    {
        $diagnostic = [
            'config' => [],
            'checks' => [],
            'dns' => [],
        ];

        // 1. Configuracion actual
        $diagnostic['config'] = [
            'mailer' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'encryption' => config('mail.mailers.smtp.encryption'),
            'username' => config('mail.mailers.smtp.username'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'ehlo_domain' => config('mail.mailers.smtp.local_domain'),
        ];

        // 2. Verificaciones
        $checks = [];

        // Check: Mailer configurado como SMTP
        $checks['smtp_configured'] = [
            'name' => 'SMTP como mailer principal',
            'status' => config('mail.default') === 'smtp',
            'message' => config('mail.default') === 'smtp'
                ? 'Correcto: usando SMTP'
                : 'Advertencia: usando "' . config('mail.default') . '" en lugar de SMTP',
        ];

        // Check: Host configurado
        $host = config('mail.mailers.smtp.host');
        $checks['host_configured'] = [
            'name' => 'Servidor SMTP configurado',
            'status' => !empty($host) && $host !== 'smtp.mailgun.org' && $host !== 'mailpit',
            'message' => !empty($host) && $host !== 'smtp.mailgun.org'
                ? "Correcto: $host"
                : 'Error: host no configurado o usando valor por defecto',
        ];

        // Check: Usuario configurado
        $username = config('mail.mailers.smtp.username');
        $checks['username_configured'] = [
            'name' => 'Usuario SMTP configurado',
            'status' => !empty($username),
            'message' => !empty($username)
                ? "Correcto: $username"
                : 'Error: usuario no configurado',
        ];

        // Check: Password configurado
        $password = config('mail.mailers.smtp.password');
        $checks['password_configured'] = [
            'name' => 'Password SMTP configurado',
            'status' => !empty($password),
            'message' => !empty($password)
                ? 'Correcto: password configurado'
                : 'Error: password no configurado',
        ];

        // Check: EHLO Domain configurado
        $ehloDomain = config('mail.mailers.smtp.local_domain');
        $checks['ehlo_configured'] = [
            'name' => 'MAIL_EHLO_DOMAIN configurado',
            'status' => !empty($ehloDomain),
            'message' => !empty($ehloDomain)
                ? "Correcto: $ehloDomain"
                : 'CRITICO: No configurado - los correos se identificaran como 127.0.0.1',
        ];

        // Check: Encriptacion
        $encryption = config('mail.mailers.smtp.encryption');
        $checks['encryption'] = [
            'name' => 'Encriptacion SSL/TLS',
            'status' => in_array($encryption, ['ssl', 'tls']),
            'message' => in_array($encryption, ['ssl', 'tls'])
                ? "Correcto: usando $encryption"
                : 'Advertencia: sin encriptacion configurada',
        ];

        // Check: From address coincide con dominio
        $fromAddress = config('mail.from.address');
        $fromDomain = $fromAddress ? substr($fromAddress, strpos($fromAddress, '@') + 1) : '';
        $hostDomain = $host ? preg_replace('/^mail\./', '', $host) : '';
        $checks['from_domain_match'] = [
            'name' => 'Dominio remitente coincide con host',
            'status' => $fromDomain === $hostDomain || str_contains($host, $fromDomain),
            'message' => $fromDomain === $hostDomain || str_contains($host, $fromDomain)
                ? "Correcto: remitente @$fromDomain"
                : "Advertencia: remitente @$fromDomain no coincide con host $host",
        ];

        $diagnostic['checks'] = $checks;

        // 3. Verificaciones DNS (si es posible)
        $dns = [];
        if ($fromDomain && function_exists('dns_get_record')) {
            try {
                // SPF
                $spfRecords = @dns_get_record($fromDomain, DNS_TXT);
                $spfFound = false;
                $spfRecord = '';
                if ($spfRecords) {
                    foreach ($spfRecords as $record) {
                        if (isset($record['txt']) && str_starts_with($record['txt'], 'v=spf1')) {
                            $spfFound = true;
                            $spfRecord = $record['txt'];
                            break;
                        }
                    }
                }
                $dns['spf'] = [
                    'name' => 'Registro SPF',
                    'status' => $spfFound,
                    'message' => $spfFound ? $spfRecord : 'No encontrado',
                ];

                // DMARC
                $dmarcRecords = @dns_get_record('_dmarc.' . $fromDomain, DNS_TXT);
                $dmarcFound = false;
                $dmarcRecord = '';
                if ($dmarcRecords) {
                    foreach ($dmarcRecords as $record) {
                        if (isset($record['txt']) && str_starts_with($record['txt'], 'v=DMARC1')) {
                            $dmarcFound = true;
                            $dmarcRecord = $record['txt'];
                            break;
                        }
                    }
                }
                $dns['dmarc'] = [
                    'name' => 'Registro DMARC',
                    'status' => $dmarcFound,
                    'message' => $dmarcFound ? $dmarcRecord : 'No encontrado - Recomendado agregar',
                ];

                // MX
                $mxRecords = @dns_get_record($fromDomain, DNS_MX);
                $dns['mx'] = [
                    'name' => 'Registros MX',
                    'status' => !empty($mxRecords),
                    'message' => !empty($mxRecords)
                        ? implode(', ', array_map(fn($r) => $r['target'], $mxRecords))
                        : 'No encontrado',
                ];

            } catch (\Exception $e) {
                $dns['error'] = [
                    'name' => 'Error DNS',
                    'status' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }
        $diagnostic['dns'] = $dns;

        // 4. Test de conexion SMTP
        $smtpTest = ['status' => false, 'message' => 'No probado'];
        if (config('mail.default') === 'smtp' && !empty($host)) {
            try {
                $port = config('mail.mailers.smtp.port', 587);
                $timeout = 5;
                $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
                if ($fp) {
                    $response = fgets($fp, 256);
                    fclose($fp);
                    $smtpTest = [
                        'status' => true,
                        'message' => "Conexion exitosa a $host:$port - " . trim($response),
                    ];
                } else {
                    $smtpTest = [
                        'status' => false,
                        'message' => "No se puede conectar a $host:$port - $errstr ($errno)",
                    ];
                }
            } catch (\Exception $e) {
                $smtpTest = [
                    'status' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                ];
            }
        }
        $diagnostic['smtp_test'] = $smtpTest;

        // Calcular resumen
        $totalChecks = count($checks);
        $passedChecks = count(array_filter($checks, fn($c) => $c['status']));
        $diagnostic['summary'] = [
            'total' => $totalChecks,
            'passed' => $passedChecks,
            'failed' => $totalChecks - $passedChecks,
            'percentage' => $totalChecks > 0 ? round(($passedChecks / $totalChecks) * 100) : 0,
        ];

        $this->logActivity('mail_diagnostic');

        return response()->json($diagnostic);
    }

    /**
     * Actualizar colores y/o tipografia del sitio.
     */
    public function updateColors(Request $request)
    {
        // Handle font update
        if ($request->has('font')) {
            $fontName = $request->input('font');
            $availableFonts = array_keys(\App\Services\SiteSettingsService::AVAILABLE_FONTS);

            if (in_array($fontName, $availableFonts)) {
                SiteSetting::set('colors', 'font', $fontName, 'text');
                $this->logActivity('font_updated', null, ['font' => $fontName]);
                return back()->with('success', __('Tipografia actualizada correctamente.'));
            }

            return back()->with('error', __('Tipografia no valida.'));
        }

        // Handle colors update
        $validated = $request->validate([
            'primary' => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
            'secondary' => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
            'accent' => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
            'light' => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
            'dark' => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
        ]);

        foreach ($validated as $key => $value) {
            SiteSetting::set('colors', $key, $value, 'color');
        }

        $this->logActivity('colors_updated', null, $validated);

        return back()->with('success', __('Colores actualizados correctamente.'));
    }

    /**
     * Actualizar configuracion de herencia cultural.
     */
    public function updateHeritage(Request $request)
    {
        // Toggle
        SiteSetting::set('heritage', 'heritage_enabled', $request->has('heritage_enabled') ? '1' : '0', 'text');

        // Label
        if ($request->has('heritage_label')) {
            SiteSetting::set('heritage', 'heritage_label', $request->input('heritage_label', 'Herencia cultural'), 'text');
        }

        // Regions (formato: clave|Nombre, una por linea)
        if ($request->has('heritage_regions')) {
            $regions = $this->parsePipeFormat($request->input('heritage_regions', ''));
            SiteSetting::set('heritage', 'heritage_regions', json_encode($regions), 'json');
        }

        // Decades (formato: clave|Nombre, una por linea)
        if ($request->has('heritage_decades')) {
            $decades = $this->parsePipeFormat($request->input('heritage_decades', ''));
            SiteSetting::set('heritage', 'heritage_decades', json_encode($decades), 'json');
        }

        $this->logActivity('heritage_settings_updated');

        return back()->with('success', __('Configuracion de herencia actualizada correctamente.'));
    }

    /**
     * Parsear formato "clave|Nombre" (una por linea) a array asociativo.
     */
    protected function parsePipeFormat(string $text): array
    {
        $result = [];
        $lines = array_filter(array_map('trim', explode("\n", $text)));
        foreach ($lines as $line) {
            if (str_contains($line, '|')) {
                [$key, $value] = explode('|', $line, 2);
                $key = trim($key);
                $value = trim($value);
                if ($key !== '') {
                    $result[$key] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * Listado de secciones de contenido editable.
     */
    public function content()
    {
        $groups = [
            'welcome' => [
                'name' => __('Pagina de bienvenida'),
                'description' => __('Pagina principal con login y registro'),
                'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
            ],
            'welcome_first' => [
                'name' => __('Primera vez'),
                'description' => __('Pagina de bienvenida para nuevos usuarios'),
                'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
            ],
            'login' => [
                'name' => __('Inicio de sesion'),
                'description' => __('Pantalla de login'),
                'icon' => 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1',
            ],
            'mail' => [
                'name' => __('Correos electronicos'),
                'description' => __('Configuracion de correos del sistema'),
                'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
            ],
            'footer' => [
                'name' => __('Pie de pagina'),
                'description' => __('Columnas de contenido del pie de pagina'),
                'icon' => 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z',
            ],
        ];

        return view('admin.content.index', compact('groups'));
    }

    /**
     * Formulario de edicion de contenido.
     */
    public function editContent(string $group)
    {
        $allowedGroups = ['welcome', 'welcome_first', 'login', 'mail', 'footer'];
        if (!in_array($group, $allowedGroups)) {
            abort(404);
        }

        $settings = SiteSetting::where('group', $group)->get()->keyBy('key');

        $groupNames = [
            'welcome' => __('Pagina de bienvenida'),
            'welcome_first' => __('Primera vez'),
            'login' => __('Inicio de sesion'),
            'mail' => __('Correos electronicos'),
            'footer' => __('Pie de pagina'),
        ];

        return view('admin.content.edit', [
            'group' => $group,
            'groupName' => $groupNames[$group] ?? $group,
            'settings' => $settings,
        ]);
    }

    /**
     * Guardar contenido editado.
     */
    public function updateContent(Request $request, string $group)
    {
        $allowedGroups = ['welcome', 'welcome_first', 'login', 'mail', 'footer'];
        if (!in_array($group, $allowedGroups)) {
            abort(404);
        }

        $settings = SiteSetting::where('group', $group)->get();

        foreach ($settings as $setting) {
            $fieldName = "settings_{$setting->key}";

            if ($setting->type === 'image') {
                // Handle file upload
                if ($request->hasFile($fieldName)) {
                    $file = $request->file($fieldName);
                    $path = $file->store('content', 'public');
                    SiteSetting::set($group, $setting->key, 'storage/' . $path, 'image');
                }
            } else {
                if ($request->has($fieldName)) {
                    SiteSetting::set($group, $setting->key, $request->input($fieldName), $setting->type);
                }
            }
        }

        $this->logActivity('content_updated', null, ['group' => $group]);

        return redirect()->route('admin.content.edit', $group)
            ->with('success', __('Contenido actualizado correctamente.'));
    }

    /**
     * Subir imagen para contenido.
     */
    public function uploadContentImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        $path = $request->file('image')->store('content', 'public');

        return response()->json([
            'path' => 'storage/' . $path,
            'url' => Storage::disk('public')->url($path),
        ]);
    }

    /**
     * Registrar actividad.
     */
    protected function logActivity(string $action, ?User $targetUser = null, array $details = []): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => 'user',
            'entity_id' => $targetUser?->id,
            'details' => json_encode(array_merge($details, [
                'target_email' => $targetUser?->email,
            ])),
        ]);
    }
}
