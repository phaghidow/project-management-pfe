<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isChefProjet() || $user->isChefDepartement();
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isChefProjet()) {
            return $task->milestone?->project?->user_id === $user->id;
        }

        if ($user->isChefDepartement()) {
            $structureIds = \App\Models\Project::getStructureTreeIds($user->structure_id);
            $ownerStructureId = $task->milestone?->project?->user?->structure_id;

            return in_array($ownerStructureId, $structureIds, true);
        }

        return $task->users()->where('users.id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isChefProjet();
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $task->milestone?->project?->user_id === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $task->milestone?->project?->user_id === $user->id;
    }

    public function start(User $user, Task $task): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $task->milestone?->project?->user_id === $user->id;
    }

    public function validateTask(User $user, Task $task): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $task->users()->where('users.id', $user->id)->exists();
    }

    public function manageDependencies(User $user, Task $task): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $task->milestone?->project?->user_id === $user->id;
    }
}
