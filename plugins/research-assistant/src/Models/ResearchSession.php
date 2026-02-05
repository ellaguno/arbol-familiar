<?php

namespace Plugin\ResearchAssistant\Models;

use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchSession extends Model
{
    protected $fillable = [
        'user_id',
        'person_id',
        'query',
        'status',
        'search_results',
        'ai_analysis',
        'suggestions',
        'ai_provider',
        'ai_model',
        'tokens_used',
    ];

    protected $casts = [
        'search_results' => 'array',
        'ai_analysis' => 'array',
        'suggestions' => 'array',
        'tokens_used' => 'integer',
    ];

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_SEARCHING = 'searching';
    public const STATUS_ANALYZING = 'analyzing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    /**
     * Get the user that owns the research session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the person being researched.
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Check if the session is still processing.
     */
    public function isProcessing(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_SEARCHING,
            self::STATUS_ANALYZING,
        ]);
    }

    /**
     * Check if the session has completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if the session has failed.
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Get the status label for display.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => __('Pendiente'),
            self::STATUS_SEARCHING => __('Buscando...'),
            self::STATUS_ANALYZING => __('Analizando...'),
            self::STATUS_COMPLETED => __('Completado'),
            self::STATUS_FAILED => __('Error'),
            default => $this->status,
        };
    }

    /**
     * Scope to get sessions for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get recent sessions.
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->latest()->take($limit);
    }
}
