<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'url',
        'name',
        'is_active',
        'check_method',
        'check_interval',
        'check_timeout',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'is_up'             => 'boolean',
        'last_checked_at'   => 'datetime',
        'status_changed_at' => 'datetime',
    ];

    public static array $intervals = [1, 5, 10, 15, 30, 60];

    // ─── Relations ────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checkLogs(): HasMany
    {
        return $this->hasMany(CheckLog::class)->orderByDesc('checked_at');
    }

    // ─── Helpers ──────────────────────────────────────────────────

    /** Returns true if domain is due for a check right now */
    public function isDue(): bool
    {
        if (is_null($this->last_checked_at)) {
            return true;
        }

        return $this->last_checked_at
            ->addMinutes($this->check_interval)
            ->isPast();
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: $this->url;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->is_up) {
            true  => 'UP',
            false => 'DOWN',
            null  => 'UNKNOWN',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->is_up) {
            true  => 'green',
            false => 'red',
            null  => 'gray',
        };
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDue($query)
    {
        return $query->active()->where(function ($q) {
            $q->whereNull('last_checked_at')
              ->orWhereRaw('DATE_ADD(last_checked_at, INTERVAL check_interval MINUTE) <= NOW()');
        });
    }
}
