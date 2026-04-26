<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'entity_type',
        'entity_id',
        'action',
        'actor_id',
        'action_at',
        'old_data',
        'new_data',
        'technical_context',
    ];

    protected $casts = [
        'action_at' => 'datetime',
        'old_data' => 'array',
        'new_data' => 'array',
        'technical_context' => 'array',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function entity()
    {
        return $this->morphTo();
    }
}

