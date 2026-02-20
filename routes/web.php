<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\TreeController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\GedcomController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\PluginController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ToolsController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\EventController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ============================================================================
// STORAGE FILES (Para hosting sin symlinks)
// ============================================================================

Route::get('/storage/{path}', function ($path) {
    // Sanitizar path para evitar directory traversal
    $path = str_replace(['../', '..\\', '..'], '', $path);

    // Construir path completo
    $fullPath = storage_path('app/public/' . $path);

    // Verificar que el path está dentro del directorio permitido
    $realPath = realpath($fullPath);
    $allowedPath = realpath(storage_path('app/public'));

    // Si el archivo no existe o está fuera del directorio permitido, 404
    if (!$realPath || !$allowedPath || !str_starts_with($realPath, $allowedPath)) {
        abort(404);
    }

    if (!file_exists($fullPath)) {
        abort(404);
    }

    $mimeType = mime_content_type($fullPath);

    return response()->file($fullPath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('path', '.*')->middleware('auth')->name('storage.serve');

// ============================================================================
// RUTAS PUBLICAS
// ============================================================================

// Landing page
Route::get('/', function () {
    // Si el usuario ya está autenticado, redirigir al dashboard
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('home');

// Páginas legales
Route::get('/privacy', function () {
    return view('legal.privacy');
})->name('privacy');

Route::get('/terms', function () {
    return view('legal.terms');
})->name('terms');

Route::get('/ancestors-info', function () {
    return view('legal.ancestors-info');
})->name('ancestors-info');

// ============================================================================
// INVITACIONES DE CONSENTIMIENTO (PUBLICAS)
// ============================================================================

Route::prefix('invitation')->name('invitation.')->group(function () {
    Route::get('/{token}', [InvitationController::class, 'show'])->name('show');
    Route::post('/{token}/accept', [InvitationController::class, 'accept'])->name('accept');
    Route::post('/{token}/decline', [InvitationController::class, 'decline'])->name('decline');
});

// ============================================================================
// RUTAS DE AUTENTICACION (INVITADOS)
// ============================================================================

Route::middleware('guest')->group(function () {
    // Login (con rate limiting: 5 intentos por minuto)
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');

    // Registro (con rate limiting: 3 registros por minuto)
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:3,1');

    // Recuperación de contraseña (con rate limiting)
    Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email')->middleware('throttle:3,1');
    Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update')->middleware('throttle:5,1');
});

// ============================================================================
// RUTAS DE AUTENTICACION (USUARIOS AUTENTICADOS)
// ============================================================================

Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Verificación de email
    Route::get('/email/verify', [VerificationController::class, 'show'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.resend');
    Route::post('/email/verify-code', [VerificationController::class, 'verifyWithCode'])->name('verification.verify.code');

    // Bienvenida primer ingreso
    Route::get('/welcome', [DashboardController::class, 'welcome'])->name('welcome.first');
    Route::post('/welcome/complete', [DashboardController::class, 'completeWelcome'])->name('welcome.complete');
});

// ============================================================================
// RUTAS PROTEGIDAS (USUARIOS VERIFICADOS)
// ============================================================================

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Pagina de ayuda
    Route::get('/help', function () {
        return view('help.index');
    })->name('help');

    // ========================================================================
    // PERFIL DE USUARIO
    // ========================================================================
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', function () {
            return redirect()->route('profile.edit');
        })->name('index');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');

        // Foto de perfil
        Route::post('/photo', [ProfileController::class, 'updatePhoto'])->name('photo.update');
        Route::delete('/photo', [ProfileController::class, 'deletePhoto'])->name('photo.delete');

        // Tema
        Route::put('/theme', [ProfileController::class, 'updateTheme'])->name('theme.update');

        // Estado en linea
        Route::put('/online-status', [ProfileController::class, 'updateOnlineStatus'])->name('online-status.update');

        // Contraseña
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');

        // Eliminar cuenta
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // ========================================================================
    // ARBOL GENEALOGICO
    // ========================================================================
    Route::prefix('tree')->name('tree.')->group(function () {
        Route::get('/', [TreeController::class, 'index'])->name('index');
        Route::get('/view/{person?}', [TreeController::class, 'view'])->name('view');

        // API para D3.js
        Route::get('/api/{person}/data', [TreeController::class, 'getData'])->name('api.data');
        Route::get('/api/{person}/fan', [TreeController::class, 'getFanData'])->name('api.fan');
        Route::get('/api/{person}/timeline', [TreeController::class, 'getTimeline'])->name('api.timeline');
    });

    // ========================================================================
    // PERSONAS
    // ========================================================================
    Route::prefix('persons')->name('persons.')->group(function () {
        Route::get('/', [PersonController::class, 'index'])->name('index');
        Route::get('/create', [PersonController::class, 'create'])->name('create');
        Route::post('/', [PersonController::class, 'store'])->name('store');

        // Búsqueda de personas para relaciones (AJAX) - DEBE IR ANTES de rutas con {person}
        Route::get('/search-for-relationship', [PersonController::class, 'searchForRelationship'])->name('search-for-relationship');

        Route::get('/{person}', [PersonController::class, 'show'])->name('show');
        Route::get('/{person}/edit', [PersonController::class, 'edit'])->name('edit');
        Route::put('/{person}', [PersonController::class, 'update'])->name('update');
        Route::delete('/{person}', [PersonController::class, 'destroy'])->name('destroy');

        // Foto de persona
        Route::post('/{person}/photo', [PersonController::class, 'updatePhoto'])->name('photo.update');
        Route::delete('/{person}/photo', [PersonController::class, 'deletePhoto'])->name('photo.delete');

        // Reclamar persona (vincular usuario con persona existente)
        Route::get('/{person}/claim', [PersonController::class, 'claimForm'])->name('claim');
        Route::post('/{person}/claim', [PersonController::class, 'sendClaimRequest'])->name('claim.send');

        // Fusionar persona (cuando ya tienes perfil y encuentras duplicado)
        Route::get('/{person}/merge', [PersonController::class, 'mergeForm'])->name('merge');
        Route::post('/{person}/merge', [PersonController::class, 'sendMergeRequest'])->name('merge.send');

        // Agregar persona existente a mi árbol
        Route::get('/{person}/add-to-tree', [PersonController::class, 'addToTreeForm'])->name('add-to-tree');
        Route::post('/{person}/add-to-tree', [PersonController::class, 'addToTreeStore'])->name('add-to-tree.store');

        // Solicitar acceso para editar familia directa
        Route::get('/family-edit-access', [PersonController::class, 'familyEditAccessForm'])->name('family-edit-access');
        Route::post('/family-edit-access', [PersonController::class, 'sendFamilyEditAccessRequest'])->name('family-edit-access.send');

        // Relaciones familiares
        Route::get('/{person}/relationships', [PersonController::class, 'relationships'])->name('relationships');
        Route::post('/{person}/relationships', [PersonController::class, 'storeRelationship'])->name('relationships.store');
        Route::post('/{person}/relationships-with-auth', [PersonController::class, 'storeRelationshipWithAuth'])->name('relationships.store-with-auth');
        Route::delete('/{person}/relationships/{type}/{related}', [PersonController::class, 'destroyRelationship'])->name('relationships.destroy');

        // Invitación de consentimiento
        Route::post('/{person}/send-invitation', [InvitationController::class, 'resend'])->name('send-invitation');

        // Eventos de persona
        Route::post('/{person}/events', [EventController::class, 'store'])->name('events.store');
        Route::put('/{person}/events/{event}', [EventController::class, 'update'])->name('events.update');
        Route::delete('/{person}/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    });

    // ========================================================================
    // FAMILIAS
    // ========================================================================
    Route::prefix('families')->name('families.')->group(function () {
        Route::get('/', [FamilyController::class, 'index'])->name('index');
        Route::get('/create', [FamilyController::class, 'create'])->name('create');
        Route::post('/', [FamilyController::class, 'store'])->name('store');
        Route::get('/{family}', [FamilyController::class, 'show'])->name('show');
        Route::get('/{family}/edit', [FamilyController::class, 'edit'])->name('edit');
        Route::put('/{family}', [FamilyController::class, 'update'])->name('update');
        Route::delete('/{family}', [FamilyController::class, 'destroy'])->name('destroy');

        // Hijos
        Route::post('/{family}/children', [FamilyController::class, 'addChild'])->name('children.add');
        Route::delete('/{family}/children/{child}', [FamilyController::class, 'removeChild'])->name('children.remove');
    });

    // ========================================================================
    // MENSAJES
    // ========================================================================
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'inbox'])->name('inbox');
        Route::get('/sent', [MessageController::class, 'sent'])->name('sent');
        Route::get('/compose', [MessageController::class, 'compose'])->name('compose');
        Route::post('/', [MessageController::class, 'store'])->name('store')->middleware('throttle:10,1'); // 10 mensajes por minuto
        Route::get('/{message}', [MessageController::class, 'show'])->name('show');
        Route::get('/{message}/reply', [MessageController::class, 'reply'])->name('reply');
        Route::delete('/{message}', [MessageController::class, 'destroy'])->name('destroy');

        // Acciones de mensajes
        Route::post('/mark-all-read', [MessageController::class, 'markAllRead'])->name('markAllRead');
        Route::post('/{message}/toggle-read', [MessageController::class, 'toggleRead'])->name('toggleRead');
        Route::post('/{message}/accept', [MessageController::class, 'accept'])->name('accept');
        Route::post('/{message}/deny', [MessageController::class, 'deny'])->name('deny');

        // Invitaciones y consentimientos (con rate limiting para evitar spam)
        Route::post('/invitation', [MessageController::class, 'sendInvitation'])->name('invitation')->middleware('throttle:5,1');
        Route::post('/consent', [MessageController::class, 'requestConsent'])->name('consent')->middleware('throttle:5,1');
    });

    // ========================================================================
    // BUSQUEDA
    // ========================================================================
    Route::prefix('search')->name('search.')->group(function () {
        Route::get('/', [SearchController::class, 'index'])->name('index');
        Route::get('/advanced', [SearchController::class, 'advanced'])->name('advanced');
        Route::get('/quick', [SearchController::class, 'quick'])->name('quick');
        Route::post('/clear-recent', [SearchController::class, 'clearRecent'])->name('clearRecent');
    });

    // ========================================================================
    // GEDCOM (Importación/Exportación)
    // ========================================================================
    Route::prefix('gedcom')->name('gedcom.')->group(function () {
        // Importacion
        Route::get('/import', [GedcomController::class, 'import'])->name('import');
        Route::post('/preview', [GedcomController::class, 'preview'])->name('preview');
        Route::post('/confirm', [GedcomController::class, 'confirmImport'])->name('confirm');
        Route::get('/result', [GedcomController::class, 'result'])->name('result');

        // Exportacion
        Route::get('/export', [GedcomController::class, 'export'])->name('export');
        Route::post('/download', [GedcomController::class, 'download'])->name('download');
        Route::get('/quick', [GedcomController::class, 'quickExport'])->name('quick');
        Route::get('/tree/{person}', [GedcomController::class, 'exportTree'])->name('tree');

        // Plantilla
        Route::get('/template', [GedcomController::class, 'template'])->name('template');
    });

    // ========================================================================
    // MEDIA (Archivos)
    // ========================================================================
    Route::prefix('media')->name('media.')->group(function () {
        Route::get('/', [MediaController::class, 'index'])->name('index');
        Route::get('/create', [MediaController::class, 'create'])->name('create');
        Route::post('/', [MediaController::class, 'store'])->name('store');
        Route::get('/{media}', [MediaController::class, 'show'])->name('show');
        Route::get('/{media}/edit', [MediaController::class, 'edit'])->name('edit');
        Route::put('/{media}', [MediaController::class, 'update'])->name('update');
        Route::delete('/{media}', [MediaController::class, 'destroy'])->name('destroy');
        Route::get('/{media}/download', [MediaController::class, 'download'])->name('download');
        Route::post('/{media}/primary', [MediaController::class, 'togglePrimary'])->name('primary');

        // Galeria por persona
        Route::get('/person/{person}', [MediaController::class, 'personGallery'])->name('person');
    });

    // ========================================================================
    // ADMINISTRACION
    // ========================================================================
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/', [AdminController::class, 'index'])->name('index');

        // Gestion de usuarios
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('users.show');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');

        // Acciones de usuario
        Route::post('/users/{user}/reset-password', [AdminController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('/users/{user}/toggle-lock', [AdminController::class, 'toggleLock'])->name('users.toggle-lock');
        Route::post('/users/{user}/verify', [AdminController::class, 'verifyEmail'])->name('users.verify');

        // Actividad
        Route::get('/activity', [AdminController::class, 'activityLog'])->name('activity');
        Route::delete('/activity', [AdminController::class, 'clearActivityLog'])->name('activity.clear');

        // Configuracion
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings/clear-cache', [AdminController::class, 'clearCache'])->name('settings.clear-cache');
        Route::post('/settings/optimize', [AdminController::class, 'optimize'])->name('settings.optimize');
        Route::post('/settings/test-mail', [AdminController::class, 'testMail'])->name('settings.test-mail');
        Route::get('/settings/mail-diagnostic', [AdminController::class, 'mailDiagnostic'])->name('settings.mail-diagnostic');
        Route::put('/settings/colors', [AdminController::class, 'updateColors'])->name('settings.colors');
        Route::put('/settings/theme', [AdminController::class, 'updateTheme'])->name('settings.theme');
        Route::put('/settings/heritage', [AdminController::class, 'updateHeritage'])->name('settings.heritage');
        Route::put('/settings/navigation', [AdminController::class, 'updateNavigation'])->name('settings.navigation');

        // Editor de contenido
        Route::get('/content', [AdminController::class, 'content'])->name('content');
        Route::get('/content/{group}', [AdminController::class, 'editContent'])->name('content.edit');
        Route::put('/content/{group}', [AdminController::class, 'updateContent'])->name('content.update');
        Route::post('/content/upload-image', [AdminController::class, 'uploadContentImage'])->name('content.upload-image');

        // Plugins
        Route::get('/plugins', [PluginController::class, 'index'])->name('plugins');
        Route::get('/plugins/manual', [PluginController::class, 'manual'])->name('plugins.manual');
        Route::post('/plugins/upload', [PluginController::class, 'upload'])->name('plugins.upload');
        Route::post('/plugins/{slug}/install', [PluginController::class, 'install'])->name('plugins.install');
        Route::post('/plugins/{slug}/uninstall', [PluginController::class, 'uninstall'])->name('plugins.uninstall');
        Route::post('/plugins/{slug}/toggle', [PluginController::class, 'toggle'])->name('plugins.toggle');
        Route::delete('/plugins/{slug}', [PluginController::class, 'delete'])->name('plugins.delete');

        // Herramientas
        Route::get('/tools', [ToolsController::class, 'index'])->name('tools');
        Route::get('/tools/fix-surnames', [ToolsController::class, 'fixSurnames'])->name('tools.fix-surnames');
        Route::post('/tools/fix-surnames', [ToolsController::class, 'applyFixSurnames'])->name('tools.fix-surnames.apply');

        // Reportes
        Route::get('/reports', [ReportController::class, 'index'])->name('reports');
        Route::get('/reports/demographics', [ReportController::class, 'demographics'])->name('reports.demographics');
        Route::get('/reports/geographic', [ReportController::class, 'geographic'])->name('reports.geographic');
        Route::get('/reports/surnames', [ReportController::class, 'surnames'])->name('reports.surnames');
        Route::get('/reports/families', [ReportController::class, 'families'])->name('reports.families');
        Route::get('/reports/events', [ReportController::class, 'events'])->name('reports.events');
        Route::get('/reports/data-quality', [ReportController::class, 'dataQuality'])->name('reports.data-quality');
        Route::post('/reports/export', [ReportController::class, 'exportCsv'])->name('reports.export');
    });
});
