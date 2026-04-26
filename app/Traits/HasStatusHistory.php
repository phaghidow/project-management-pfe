<?php

namespace App\Traits;

use App\Models\StatusHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait HasStatusHistory
{
    protected static function bootHasStatusHistory(): void
    {
        static::saving(function (Model $model) {
            if ($model->isDirty('status') || $model->isDirty('is_active')) {
                $oldStatus = $model->getOriginal('status') ?? $model->getOriginal('is_active') ? 'active' : 'inactive';
                $newStatus = $model->status ?? ($model->is_active ? 'active' : 'inactive');
                $model->logStatusChange($oldStatus, $newStatus);
            }
        });
    }

    public function logStatusChange(?string $oldStatus, string $newStatus, ?string $reason = null): void
    {
        StatusHistory::create([
            'entity_type' => (new \ReflectionClass($this))->getShortName(),
            'entity_id' => $this->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => Auth::id(),
            'reason' => $reason,
            'changed_at' => now(),
        ]);
    }

    public function statusHistory()
    {
        return $this->morphMany(StatusHistory::class, 'entity');
    }
}

