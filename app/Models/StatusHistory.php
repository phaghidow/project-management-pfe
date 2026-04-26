<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusHistory extends Model
{
    protected $fillable = [
        'entity_type',
        'entity_id',
        'old_status',
        'new_status',
        'changed_by',
        'reason',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
