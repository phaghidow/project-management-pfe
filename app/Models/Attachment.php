<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'path',
        'mime_type',
        'size',
        'disk',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachable()
    {
        return $this->morphTo();
    }
}
