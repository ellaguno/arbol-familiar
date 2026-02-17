<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Person extends Model
{
    use HasFactory;

    /**
     * Cache en memoria para evitar recalcular el BFS múltiples veces por request.
     */
    protected ?array $_cachedConnectedIds = null;

    /**
     * The table associated with the model.
     */
    protected $table = 'persons';

    protected $fillable = [
        'gedcom_id',
        'user_id',
        'first_name',
        'patronymic',
        'matronymic',
        'nickname',
        'gender',
        'marital_status',
        'birth_date',
        'birth_year',
        'birth_month',
        'birth_day',
        'birth_date_approx',
        'birth_place',
        'birth_country',
        'death_date',
        'death_year',
        'death_month',
        'death_day',
        'death_date_approx',
        'death_place',
        'death_country',
        'is_living',
        'is_minor',
        'residence_place',
        'residence_country',
        'occupation',
        'email',
        'phone',
        'has_ethnic_heritage',
        'heritage_region',
        'origin_town',
        'migration_decade',
        'migration_destination',
        'heritage_family_member_name',
        'heritage_family_relationship',
        'photo_path',
        'privacy_level',
        'consent_status',
        'consent_requested_at',
        'consent_responded_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'birth_year' => 'integer',
        'birth_month' => 'integer',
        'birth_day' => 'integer',
        'death_date' => 'date',
        'death_year' => 'integer',
        'death_month' => 'integer',
        'death_day' => 'integer',
        'birth_date_approx' => 'boolean',
        'death_date_approx' => 'boolean',
        'is_living' => 'boolean',
        'is_minor' => 'boolean',
        'has_ethnic_heritage' => 'boolean',
        'consent_requested_at' => 'datetime',
        'consent_responded_at' => 'datetime',
    ];

    /**
     * Usuario asociado a esta persona (si tiene cuenta).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Usuario que creo este registro.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuario que actualizo este registro.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Familias donde esta persona es el esposo.
     */
    public function familiesAsHusband(): HasMany
    {
        return $this->hasMany(Family::class, 'husband_id');
    }

    /**
     * Familias donde esta persona es la esposa.
     */
    public function familiesAsWife(): HasMany
    {
        return $this->hasMany(Family::class, 'wife_id');
    }

    /**
     * Todas las familias donde esta persona es conyuge.
     */
    public function familiesAsSpouse()
    {
        return Family::where('husband_id', $this->id)
            ->orWhere('wife_id', $this->id);
    }

    /**
     * Relaciones familiares como hijo.
     */
    public function childRelations(): HasMany
    {
        return $this->hasMany(FamilyChild::class, 'person_id');
    }

    /**
     * Familias donde esta persona es hijo.
     */
    public function familiesAsChild(): BelongsToMany
    {
        return $this->belongsToMany(Family::class, 'family_children', 'person_id', 'family_id')
            ->withPivot('child_order', 'relationship_type', 'created_at');
    }

    /**
     * Variantes de apellidos.
     */
    public function surnameVariants(): HasMany
    {
        return $this->hasMany(SurnameVariant::class);
    }

    /**
     * Media asociada (fotos, documentos, enlaces).
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Eventos GEDCOM asociados.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Invitaciones relacionadas con esta persona.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    /**
     * Mensajes relacionados con esta persona.
     */
    public function relatedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'related_person_id');
    }

    /**
     * Nombre completo.
     */
    public function getFullNameAttribute(): string
    {
        $name = $this->first_name . ' ' . $this->patronymic;
        if ($this->matronymic) {
            $name .= ' ' . $this->matronymic;
        }
        return $name;
    }

    /**
     * Determina si la persona es menor de edad basándose en su fecha de nacimiento.
     * Considera menor a quien tenga menos de 18 años.
     */
    public function getIsMinorCalculatedAttribute(): bool
    {
        // Si está marcado manualmente como menor, respetar eso
        if ($this->is_minor) {
            return true;
        }

        // Si tiene fecha de nacimiento, calcular edad
        if ($this->birth_date) {
            return $this->birth_date->age < 18;
        }

        // Si solo tiene año de nacimiento, calcular aproximadamente
        if ($this->birth_year) {
            $age = now()->year - $this->birth_year;
            return $age < 18;
        }

        // Si no hay fecha de nacimiento, no se considera menor automáticamente
        return false;
    }

    /**
     * Nombre protegido para menores de edad.
     * Solo muestra el nombre de pila si es menor y el usuario no es el creador/familiar directo.
     */
    public function getProtectedNameAttribute(): string
    {
        if ($this->shouldProtectMinorData()) {
            return $this->first_name;
        }
        return $this->full_name;
    }

    /**
     * Verifica si se deben proteger los datos de este menor.
     * Los datos se protegen si es menor Y el usuario actual no es padre/creador.
     * Los padres registrados en el arbol siempre pueden ver los datos de sus hijos menores.
     */
    public function shouldProtectMinorData(): bool
    {
        // Usar cálculo automático de menor de edad
        if (!$this->is_minor_calculated) {
            return false;
        }

        $user = auth()->user();
        if (!$user) {
            return true; // Sin usuario autenticado, siempre proteger
        }

        // El creador puede ver todos los datos del menor
        if ($this->created_by === $user->id) {
            return false;
        }

        // El usuario vinculado puede ver sus propios datos (si el menor tiene cuenta)
        if ($this->user_id === $user->id) {
            return false;
        }

        // Los padres registrados pueden ver datos de sus hijos menores
        if ($user->person_id) {
            $family = $this->familiesAsChild()->first();
            if ($family) {
                // Si el usuario es el padre o la madre en la familia del menor
                if ($family->husband_id === $user->person_id || $family->wife_id === $user->person_id) {
                    return false;
                }
            }
        }

        // Para todos los demás, proteger los datos
        return true;
    }

    /**
     * Obtiene el año de nacimiento protegido (null si es menor protegido).
     */
    public function getProtectedBirthYearAttribute(): ?int
    {
        if ($this->shouldProtectMinorData()) {
            return null;
        }
        return $this->birth_year;
    }

    /**
     * Obtiene la fecha de nacimiento formateada protegida.
     */
    public function getProtectedBirthDateFormattedAttribute(): ?string
    {
        if ($this->shouldProtectMinorData()) {
            return null;
        }
        return $this->birth_date_formatted;
    }

    /**
     * Edad calculada.
     */
    public function getAgeAttribute(): ?int
    {
        // Si tenemos fecha completa de nacimiento, usar Carbon para calculo exacto
        if ($this->birth_date) {
            if ($this->is_living) {
                return $this->birth_date->age;
            }
            if ($this->death_date) {
                return $this->birth_date->diffInYears($this->death_date);
            }
            $deathYear = $this->death_year;
            return $deathYear ? $deathYear - $this->birth_date->year : $this->birth_date->age;
        }

        // Fallback: solo tenemos año parcial
        $birthYear = $this->birth_year;
        if (!$birthYear) {
            return null;
        }

        if ($this->is_living) {
            return now()->year - $birthYear;
        }

        $endYear = $this->death_year ?? ($this->death_date ? $this->death_date->year : now()->year);
        return $endYear - $birthYear;
    }

    /**
     * Obtiene la fecha de nacimiento formateada (soporta fechas parciales).
     * Retorna: "1985", "Mar 1985", "15 Mar 1985" dependiendo de los datos disponibles.
     */
    public function getBirthDateFormattedAttribute(): ?string
    {
        return $this->formatPartialDate($this->birth_year, $this->birth_month, $this->birth_day);
    }

    /**
     * Obtiene la fecha de defunción formateada (soporta fechas parciales).
     */
    public function getDeathDateFormattedAttribute(): ?string
    {
        return $this->formatPartialDate($this->death_year, $this->death_month, $this->death_day);
    }

    /**
     * Formatea una fecha parcial.
     */
    protected function formatPartialDate(?int $year, ?int $month, ?int $day): ?string
    {
        if (!$year) {
            return null;
        }

        $months = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
        ];

        if ($month && $day) {
            return $day . ' ' . $months[$month] . ' ' . $year;
        } elseif ($month) {
            return $months[$month] . ' ' . $year;
        } else {
            return (string) $year;
        }
    }

    /**
     * Obtiene los padres de esta persona.
     */
    public function getParentsAttribute()
    {
        $family = $this->familiesAsChild()->first();
        if (!$family) {
            return collect();
        }

        return collect([
            'father' => $family->husband,
            'mother' => $family->wife,
        ])->filter();
    }

    /**
     * Obtiene el padre.
     */
    public function getFatherAttribute(): ?Person
    {
        $family = $this->familiesAsChild()->first();
        return $family?->husband;
    }

    /**
     * Obtiene la madre.
     */
    public function getMotherAttribute(): ?Person
    {
        $family = $this->familiesAsChild()->first();
        return $family?->wife;
    }

    /**
     * Obtiene los hermanos.
     */
    public function getSiblingsAttribute()
    {
        $family = $this->familiesAsChild()->first();
        if (!$family) {
            return collect();
        }

        return $family->children->where('id', '!=', $this->id);
    }

    /**
     * Obtiene el conyuge actual (el primero con estado married/partners).
     */
    public function getCurrentSpouseAttribute(): ?Person
    {
        $family = $this->familiesAsSpouse()
            ->whereIn('status', ['married', 'partners'])
            ->first();

        if (!$family) {
            return null;
        }

        return $family->husband_id === $this->id ? $family->wife : $family->husband;
    }

    /**
     * Obtiene TODOS los cónyuges (actuales y anteriores) con información del matrimonio.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllSpousesAttribute()
    {
        $spouses = collect();

        // Familias donde esta persona es el esposo
        foreach ($this->familiesAsHusband as $family) {
            if ($family->wife) {
                $spouses->push([
                    'person' => $family->wife,
                    'family' => $family,
                    'status' => $family->status,
                    'marriage_date' => $family->marriage_date,
                    'is_current' => in_array($family->status, ['married', 'partners']),
                ]);
            }
        }

        // Familias donde esta persona es la esposa
        foreach ($this->familiesAsWife as $family) {
            if ($family->husband) {
                $spouses->push([
                    'person' => $family->husband,
                    'family' => $family,
                    'status' => $family->status,
                    'marriage_date' => $family->marriage_date,
                    'is_current' => in_array($family->status, ['married', 'partners']),
                ]);
            }
        }

        // Ordenar: actuales primero, luego por fecha de matrimonio
        return $spouses->sortByDesc(function ($spouse) {
            return ($spouse['is_current'] ? '1' : '0') . ($spouse['marriage_date'] ?? '0000');
        })->values();
    }

    /**
     * Obtiene solo los cónyuges como objetos Person.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSpousesAttribute()
    {
        return $this->allSpouses->pluck('person');
    }

    /**
     * Obtiene todos los hijos.
     */
    public function getChildrenAttribute()
    {
        $familyIds = $this->familiesAsSpouse()->pluck('id');

        return Person::whereHas('familiesAsChild', function ($query) use ($familyIds) {
            $query->whereIn('family_id', $familyIds);
        })->get();
    }

    /**
     * Verifica si requiere consentimiento para edicion.
     */
    public function requiresConsent(): bool
    {
        return $this->is_living
            && !$this->is_minor
            && $this->email
            && $this->consent_status !== 'approved';
    }

    /**
     * Verifica si esta pendiente de autorizacion.
     */
    public function isPendingAuthorization(): bool
    {
        return $this->consent_status === 'pending';
    }

    /**
     * Scope para personas con herencia etnica.
     */
    public function scopeEthnicHeritage($query)
    {
        return $query->where('has_ethnic_heritage', true);
    }

    /**
     * Scope para personas vivas.
     */
    public function scopeLiving($query)
    {
        return $query->where('is_living', true);
    }

    /**
     * Scope para busqueda inteligente por nombre.
     * Soporta busquedas como "Bruno Guardia" buscando en combinaciones de campos.
     */
    public function scopeSearchByName($query, string $search)
    {
        $search = trim($search);

        // Si no hay espacios, buscar en campos individuales
        if (!str_contains($search, ' ')) {
            return $query->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('patronymic', 'LIKE', "%{$search}%")
                  ->orWhere('matronymic', 'LIKE', "%{$search}%")
                  ->orWhere('nickname', 'LIKE', "%{$search}%");
            });
        }

        // Si hay espacios, buscar de forma inteligente
        $terms = preg_split('/\s+/', $search);

        return $query->where(function ($q) use ($search, $terms) {
            // Buscar en combinaciones concatenadas (nombre completo)
            $q->whereRaw("CONCAT(first_name, ' ', patronymic) LIKE ?", ["%{$search}%"])
              ->orWhereRaw("CONCAT(first_name, ' ', patronymic, ' ', COALESCE(matronymic, '')) LIKE ?", ["%{$search}%"])
              ->orWhereRaw("CONCAT(first_name, ' ', COALESCE(matronymic, '')) LIKE ?", ["%{$search}%"]);

            // Tambien buscar cada termino en cualquier campo (para mayor flexibilidad)
            $q->orWhere(function ($sub) use ($terms) {
                foreach ($terms as $term) {
                    $sub->where(function ($inner) use ($term) {
                        $inner->where('first_name', 'LIKE', "%{$term}%")
                              ->orWhere('patronymic', 'LIKE', "%{$term}%")
                              ->orWhere('matronymic', 'LIKE', "%{$term}%")
                              ->orWhere('nickname', 'LIKE', "%{$term}%");
                    });
                }
            });
        });
    }

    /**
     * Permisos de edición otorgados para esta persona.
     */
    public function editPermissions(): HasMany
    {
        return $this->hasMany(PersonEditPermission::class);
    }

    /**
     * Obtiene toda la familia directa de esta persona.
     * Incluye: padres, hermanos, cónyuge e hijos.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getDirectFamilyAttribute()
    {
        $family = collect();

        // Padres
        if ($this->father) {
            $family->push([
                'person' => $this->father,
                'relationship' => 'father',
                'label' => __('Padre'),
            ]);
        }
        if ($this->mother) {
            $family->push([
                'person' => $this->mother,
                'relationship' => 'mother',
                'label' => __('Madre'),
            ]);
        }

        // Cónyuge
        if ($this->currentSpouse) {
            $family->push([
                'person' => $this->currentSpouse,
                'relationship' => 'spouse',
                'label' => __('Cónyuge'),
            ]);
        }

        // Hermanos
        foreach ($this->siblings as $sibling) {
            $family->push([
                'person' => $sibling,
                'relationship' => 'sibling',
                'label' => __('Hermano/a'),
            ]);
        }

        // Hijos
        foreach ($this->children as $child) {
            $family->push([
                'person' => $child,
                'relationship' => 'child',
                'label' => __('Hijo/a'),
            ]);
        }

        return $family;
    }

    /**
     * Obtiene los IDs de toda la familia directa.
     *
     * @return array
     */
    public function getDirectFamilyIdsAttribute(): array
    {
        $ids = [];

        if ($this->father) {
            $ids[] = $this->father->id;
        }
        if ($this->mother) {
            $ids[] = $this->mother->id;
        }
        if ($this->currentSpouse) {
            $ids[] = $this->currentSpouse->id;
        }
        foreach ($this->siblings as $sibling) {
            $ids[] = $sibling->id;
        }
        foreach ($this->children as $child) {
            $ids[] = $child->id;
        }

        return array_unique($ids);
    }

    /**
     * Obtiene los IDs de todos los ancestros en línea directa (padres, abuelos, bisabuelos, etc.).
     * Recorrido recursivo con límite de profundidad para evitar loops.
     *
     * @param int $maxDepth Profundidad máxima (default 15 generaciones)
     * @param array $visited IDs ya visitados para evitar ciclos
     * @return array
     */
    public function getAllAncestorIds(int $maxDepth = 15, array $visited = []): array
    {
        if ($maxDepth <= 0) {
            return [];
        }

        $ids = [];
        $family = $this->familiesAsChild()->first();

        if (!$family) {
            return [];
        }

        if ($family->husband_id && !in_array($family->husband_id, $visited)) {
            $ids[] = $family->husband_id;
            $father = $family->husband;
            if ($father) {
                $ids = array_merge($ids, $father->getAllAncestorIds($maxDepth - 1, array_merge($visited, $ids)));
            }
        }

        if ($family->wife_id && !in_array($family->wife_id, $visited)) {
            $ids[] = $family->wife_id;
            $mother = $family->wife;
            if ($mother) {
                $ids = array_merge($ids, $mother->getAllAncestorIds($maxDepth - 1, array_merge($visited, $ids)));
            }
        }

        return array_unique($ids);
    }

    /**
     * Obtiene los IDs de todos los descendientes en línea directa (hijos, nietos, bisnietos, etc.).
     * Recorrido recursivo con límite de profundidad para evitar loops.
     *
     * @param int $maxDepth Profundidad máxima (default 15 generaciones)
     * @param array $visited IDs ya visitados para evitar ciclos
     * @return array
     */
    public function getAllDescendantIds(int $maxDepth = 15, array $visited = []): array
    {
        if ($maxDepth <= 0) {
            return [];
        }

        $ids = [];
        $familyIds = $this->familiesAsSpouse()->pluck('id');
        $children = Person::whereHas('familiesAsChild', function ($query) use ($familyIds) {
            $query->whereIn('family_id', $familyIds);
        })->get();

        foreach ($children as $child) {
            if (!in_array($child->id, $visited)) {
                $ids[] = $child->id;
                $ids = array_merge($ids, $child->getAllDescendantIds($maxDepth - 1, array_merge($visited, $ids)));
            }
        }

        return array_unique($ids);
    }

    /**
     * Verifica si esta persona y el usuario están en la misma línea directa
     * (ascendente o descendente). Esto permite que abuelos, bisabuelos, nietos, etc.
     * siempre puedan verse mutuamente independientemente del nivel de privacidad.
     *
     * @param User $user
     * @return bool
     */
    public function isLineageOf(User $user): bool
    {
        if (!$user->person_id) {
            return false;
        }

        $userPersonId = $user->person_id;

        // Verificar si el usuario es ancestro de esta persona
        $myAncestors = $this->getAllAncestorIds();
        if (in_array($userPersonId, $myAncestors)) {
            return true;
        }

        // Verificar si el usuario es descendiente de esta persona
        $myDescendants = $this->getAllDescendantIds();
        if (in_array($userPersonId, $myDescendants)) {
            return true;
        }

        return false;
    }

    /**
     * Verifica si un usuario tiene permiso de edición para esta persona.
     *
     * @param int $userId
     * @return bool
     */
    public function canBeEditedBy(int $userId): bool
    {
        // El creador siempre puede editar
        if ($this->created_by === $userId) {
            return true;
        }

        // El usuario vinculado puede editar su propio perfil
        if ($this->user_id === $userId) {
            return true;
        }

        // Verificar permisos de edición activos
        return $this->editPermissions()
            ->active()
            ->forUser($userId)
            ->exists();
    }

    /**
     * Verifica si un usuario tiene permiso para VER esta persona.
     * Implementa los 4 niveles de privacidad:
     * - direct_family: Solo familia directa (ascendentes, descendientes, conyuge, hermanos)
     * - extended_family: Familia extendida (directa + politica, cunados, tios, sobrinos)
     * - selected_users: Familia extendida + visible en lista de comunidad (permite solicitudes)
     * - community: Todos los usuarios registrados
     *
     * @param User|null $user
     * @return bool
     */
    public function canBeViewedBy(?User $user): bool
    {
        // Si no hay usuario autenticado, no se permite ver ningun perfil
        if (!$user) {
            return false;
        }

        // El creador siempre puede ver
        if ($this->created_by === $user->id) {
            return true;
        }

        // El usuario vinculado puede ver su propio perfil
        if ($this->user_id === $user->id) {
            return true;
        }

        // Los ascendentes y descendientes directos SIEMPRE pueden verse mutuamente,
        // independientemente del nivel de privacidad (excepto perfil completo de menores).
        // Requerimiento del cliente: "Todos los usuarios deben tener acceso a los perfiles
        // de todos sus ascendentes y descendientes directos."
        if ($this->isLineageOf($user)) {
            return true;
        }

        // Verificar según nivel de privacidad
        switch ($this->privacy_level) {
            case 'direct_family':
                // Solo familia directa del usuario
                return $this->isDirectFamilyOf($user);

            case 'extended_family':
                // Familia extendida del usuario
                return $this->isExtendedFamilyOf($user);

            case 'selected_users':
                // Familia extendida + visible en lista (pero sin acceso completo al perfil para otros)
                return $this->isExtendedFamilyOf($user);

            case 'community':
                // Cualquier usuario registrado puede ver
                return true;

            // Valores legacy (por si quedan en BD antes de migrar)
            case 'private':
                return $this->isDirectFamilyOf($user);
            case 'family':
                return $this->isExtendedFamilyOf($user);
            case 'public':
                return true;

            default:
                return false;
        }
    }

    /**
     * Verifica si un usuario es familia directa de esta persona.
     * Familia directa incluye: padres, hijos, cónyuges, hermanos,
     * y toda la línea de ascendentes y descendientes directos.
     * Verificacion bidireccional para evitar asimetrias.
     *
     * @param User $user
     * @return bool
     */
    public function isDirectFamilyOf(User $user): bool
    {
        // Si el usuario no tiene persona asociada, no puede ser familia
        if (!$user->person_id) {
            return false;
        }

        $userPerson = $user->person;
        if (!$userPerson) {
            return false;
        }

        $userPersonId = $userPerson->id;

        // Obtener los IDs de familia directa de ESTA persona (1 nivel)
        $myFamilyIds = $this->directFamilyIds;

        // Si el usuario (su persona) está en mi familia directa
        if (in_array($userPersonId, $myFamilyIds)) {
            return true;
        }

        // Verificar también al revés: si YO estoy en la familia directa del usuario
        $userFamilyIds = $userPerson->directFamilyIds;
        if (in_array($this->id, $userFamilyIds)) {
            return true;
        }

        // Verificar línea directa completa (abuelos, bisabuelos, nietos, etc.)
        // isLineageOf ya cubre esto, pero lo dejamos aquí por si se llama
        // isDirectFamilyOf directamente sin pasar por canBeViewedBy
        if ($this->isLineageOf($user)) {
            return true;
        }

        return false;
    }

    /**
     * Obtiene los IDs de TODAS las personas conectadas en el árbol familiar.
     * Nivel 2 de privacidad según requerimientos del cliente:
     * "todos los ligados sin ninguna restricción".
     *
     * Usa un recorrido BFS (Breadth-First Search) sobre el grafo de relaciones
     * familiares cargado en memoria con solo 2 queries a la BD.
     *
     * @param int $maxNodes Límite de seguridad para evitar recorridos infinitos
     * @return array
     */
    public function getAllConnectedPersonIds(int $maxNodes = 5000): array
    {
        // Memoización: evitar recalcular el BFS múltiples veces en la misma request
        if (isset($this->_cachedConnectedIds)) {
            return $this->_cachedConnectedIds;
        }

        // Cargar todo el grafo familiar en 2 queries
        $families = DB::table('families')
            ->select('id', 'husband_id', 'wife_id')
            ->get();

        $familyChildren = DB::table('family_children')
            ->select('family_id', 'person_id')
            ->get();

        // Construir índices en memoria para recorrido eficiente
        // personToFamilies: person_id => [family_ids] (familias donde es cónyuge)
        $personToSpouseFamilies = [];
        // familyToSpouses: family_id => [person_ids] (cónyuges de la familia)
        $familyToSpouses = [];
        foreach ($families as $family) {
            if ($family->husband_id) {
                $personToSpouseFamilies[$family->husband_id][] = $family->id;
                $familyToSpouses[$family->id][] = $family->husband_id;
            }
            if ($family->wife_id) {
                $personToSpouseFamilies[$family->wife_id][] = $family->id;
                $familyToSpouses[$family->id][] = $family->wife_id;
            }
        }

        // personToChildFamilies: person_id => [family_ids] (familias donde es hijo)
        $personToChildFamilies = [];
        // familyToChildren: family_id => [person_ids] (hijos de la familia)
        $familyToChildren = [];
        foreach ($familyChildren as $fc) {
            $personToChildFamilies[$fc->person_id][] = $fc->family_id;
            $familyToChildren[$fc->family_id][] = $fc->person_id;
        }

        // BFS desde esta persona
        $visited = [$this->id => true];
        $queue = [$this->id];
        $result = [];

        while (!empty($queue) && count($visited) < $maxNodes) {
            $currentId = array_shift($queue);

            // 1. Familias donde esta persona es cónyuge → obtener otro cónyuge + hijos
            if (isset($personToSpouseFamilies[$currentId])) {
                foreach ($personToSpouseFamilies[$currentId] as $familyId) {
                    // Cónyuges de esta familia
                    if (isset($familyToSpouses[$familyId])) {
                        foreach ($familyToSpouses[$familyId] as $spouseId) {
                            if (!isset($visited[$spouseId])) {
                                $visited[$spouseId] = true;
                                $result[] = $spouseId;
                                $queue[] = $spouseId;
                            }
                        }
                    }
                    // Hijos de esta familia
                    if (isset($familyToChildren[$familyId])) {
                        foreach ($familyToChildren[$familyId] as $childId) {
                            if (!isset($visited[$childId])) {
                                $visited[$childId] = true;
                                $result[] = $childId;
                                $queue[] = $childId;
                            }
                        }
                    }
                }
            }

            // 2. Familias donde esta persona es hijo → obtener padres + hermanos
            if (isset($personToChildFamilies[$currentId])) {
                foreach ($personToChildFamilies[$currentId] as $familyId) {
                    // Padres (cónyuges de esta familia)
                    if (isset($familyToSpouses[$familyId])) {
                        foreach ($familyToSpouses[$familyId] as $parentId) {
                            if (!isset($visited[$parentId])) {
                                $visited[$parentId] = true;
                                $result[] = $parentId;
                                $queue[] = $parentId;
                            }
                        }
                    }
                    // Hermanos (otros hijos de esta familia)
                    if (isset($familyToChildren[$familyId])) {
                        foreach ($familyToChildren[$familyId] as $siblingId) {
                            if (!isset($visited[$siblingId])) {
                                $visited[$siblingId] = true;
                                $result[] = $siblingId;
                                $queue[] = $siblingId;
                            }
                        }
                    }
                }
            }
        }

        $this->_cachedConnectedIds = $result;
        return $result;
    }

    /**
     * Obtiene los IDs de familia extendida.
     * Nivel 2 de privacidad: todas las personas conectadas en el árbol.
     *
     * @return array
     */
    public function getExtendedFamilyIdsAttribute(): array
    {
        return $this->getAllConnectedPersonIds();
    }

    /**
     * Verifica si un usuario es familia extendida de esta persona.
     * El BFS es simétrico: si A alcanza B, B alcanza A a través del mismo grafo.
     * Por lo tanto no se necesita verificación bidireccional.
     *
     * @param User $user
     * @return bool
     */
    public function isExtendedFamilyOf(User $user): bool
    {
        if (!$user->person_id) {
            return false;
        }

        $connectedIds = $this->extendedFamilyIds;
        return in_array($user->person_id, $connectedIds);
    }
}
