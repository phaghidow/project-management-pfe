<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'is_read',
        'read_at',
        'related_type',
        'related_id',
        'metadata',
        'sent_at',
        'acknowledged_at'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function related()
    {
        return $this->morphTo();
    }

    /**
     * Scope: Unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function markAsRead()
    {
        $now = now();

        $this->update([
            'is_read' => true,
            'read_at' => $now,
            // When a user reads a notification, we also acknowledge it for dedup logic.
            'acknowledged_at' => $this->acknowledged_at ?? $now,
        ]);
    }

    /**
     * Scope: Notifications sent in last N days
     */
    public function scopeRecentlySent($query, $days = 1)
    {
        return $query->where('sent_at', '>', now()->subDays($days));
    }

    /**
     * Scope: Unacknowledged notifications
     */
    public function scopeUnacknowledged($query)
    {
        return $query->whereNull('acknowledged_at');
    }


}