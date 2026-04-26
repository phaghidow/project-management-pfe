<?php

namespace App\Policies;

use App\Models\Milestone;
use App\Models\User;

class MilestonePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isChefProjet() || $user->isChefDepartement();
    }

    public function view(User $user, Milestone $milestone): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $project = $milestone->project;

        if ($user->isChefProjet()) {
            return $project->user_id === $user->id;
        }

        if ($user->isChefDepartement()) {
            $structureIds = \App\Models\Project::getStructureTreeIds($user->structure_id);
            return in_array($project->user?->structure_id, $structureIds, true);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isChefProjet();
    }

    public function update(User $user, Milestone $milestone): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $milestone->project->user_id === $user->id;
    }

    public function delete(User $user, Milestone $milestone): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $milestone->project->user_id === $user->id;
    }
}

