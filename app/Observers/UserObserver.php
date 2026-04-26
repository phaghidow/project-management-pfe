<?php

namespace App\Observers;

use App\Models\StatusHistory;
use App\Models\User;

class UserObserver
{
    public function updated(User $user): void
    {
        if (! $user->wasChanged('status')) {
            return;
        }

        StatusHistory::create([
            'entity_type' => User::class,
            'entity_id' => $user->id,
            'old_status' => $user->getOriginal('status'),
            'new_status' => $user->status,
            'changed_by' => auth()->id(),
            'changed_at' => now(),
        ]);
    }
}
