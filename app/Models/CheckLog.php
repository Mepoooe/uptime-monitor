<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'domain_id',
        'checked_at',
        'is_up',
        'status_code',
        'response_time',
        'error',
        'check_method',
    ];

    protected $casts = [
        'checked_at'    => 'datetime',
        'is_up'         => 'boolean',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->is_up ? 'UP' : 'DOWN';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->is_up ? 'green' : 'red';
    }
}
