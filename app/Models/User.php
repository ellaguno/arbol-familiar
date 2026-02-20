<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     * NOTA: is_admin NO está incluido por seguridad - usar setAdmin() para cambiar
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'person_id',
        'language',
        'theme_preference',
        'privacy_level',
        'show_online_status',
        'confirmation_code',
        'first_login_completed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'confirmation_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'first_login_completed' => 'boolean',
        'show_online_status' => 'boolean',
    ];

    /**
     * La persona asociada a este usuario.
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Personas creadas por este usuario.
     */
    public function createdPersons(): HasMany
    {
        return $this->hasMany(Person::class, 'created_by');
    }

    /**
     * Familias creadas por este usuario.
     */
    public function createdFamilies(): HasMany
    {
        return $this->hasMany(Family::class, 'created_by');
    }

    /**
     * Media creada por este usuario.
     */
    public function createdMedia(): HasMany
    {
        return $this->hasMany(Media::class, 'created_by');
    }

    /**
     * Mensajes recibidos.
     */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }

    /**
     * Mensajes enviados.
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Mensajes directos no leidos.
     */
    public function unreadMessages(): HasMany
    {
        return $this->receivedMessages()
            ->whereNull('read_at')
            ->whereNull('deleted_at');
    }

    /**
     * Conteo total de mensajes no leidos (directos + broadcasts).
     */
    public function getUnreadMessageCountAttribute(): int
    {
        $directCount = $this->unreadMessages()->count();

        $broadcastCount = MessageRecipient::where('user_id', $this->id)
            ->whereNull('read_at')
            ->whereNull('deleted_at')
            ->count();

        return $directCount + $broadcastCount;
    }

    /**
     * Invitaciones enviadas.
     */
    public function sentInvitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'inviter_id');
    }

    /**
     * Accesos otorgados a otros usuarios para ver el arbol.
     */
    public function grantedAccess(): HasMany
    {
        return $this->hasMany(TreeAccess::class, 'owner_id');
    }

    /**
     * Accesos recibidos para ver arboles de otros.
     */
    public function receivedAccess(): HasMany
    {
        return $this->hasMany(TreeAccess::class, 'accessor_id');
    }

    /**
     * Registro de actividad del usuario.
     */
    public function activityLog(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Permisos de edición recibidos para personas.
     */
    public function personEditPermissions(): HasMany
    {
        return $this->hasMany(PersonEditPermission::class);
    }

    /**
     * Permisos de edición otorgados por este usuario.
     */
    public function grantedEditPermissions(): HasMany
    {
        return $this->hasMany(PersonEditPermission::class, 'granted_by');
    }

    /**
     * IDs de personas que este usuario puede editar (por permisos).
     */
    public function getEditablePersonIdsAttribute(): array
    {
        // IDs de personas creadas por el usuario
        $createdIds = $this->createdPersons()->pluck('id')->toArray();

        // ID de su propia persona
        $ownId = $this->person_id ? [$this->person_id] : [];

        // IDs de personas con permiso activo
        $permissionIds = $this->personEditPermissions()
            ->active()
            ->pluck('person_id')
            ->toArray();

        return array_unique(array_merge($createdIds, $ownId, $permissionIds));
    }

    /**
     * Verifica si el usuario esta bloqueado.
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Incrementa los intentos de login fallidos.
     */
    public function incrementLoginAttempts(): void
    {
        $this->login_attempts++;
        if ($this->login_attempts >= 5) {
            $this->locked_until = now()->addMinutes(15);
        }
        $this->save();
    }

    /**
     * Resetea los intentos de login.
     */
    public function resetLoginAttempts(): void
    {
        $this->login_attempts = 0;
        $this->locked_until = null;
        $this->last_login_at = now();
        $this->save();
    }

    /**
     * Obtiene el nombre completo del usuario a traves de su persona.
     */
    public function getFullNameAttribute(): string
    {
        if ($this->person) {
            return $this->person->full_name;
        }
        return $this->email;
    }

    /**
     * Verifica si el usuario es administrador.
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    /**
     * Establece el rol de administrador de forma segura.
     * Este método existe porque is_admin no está en $fillable por seguridad.
     */
    public function setAdmin(bool $isAdmin): void
    {
        $this->is_admin = $isAdmin;
        $this->save();
    }

    /**
     * Envía la notificación de verificación de email en español.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    /**
     * Envía la notificación de restablecimiento de contraseña en español.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
