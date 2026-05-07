<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isChefProjet() || $user->isChefDepartement();
    }

    public function view(User $user, Project $project): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isChefProjet()) {
            return $project->user_id === $user->id;
        }

        if ($user->isChefDepartement()) {
            $structureIds = Project::getStructureTreeIds($user->structure_id);

            return in_array($project->user?->structure_id, $structureIds, true);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isChefProjet() || $user->isChefDepartement();
    }

    public function update(User $user, Project $project): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isChefProjet()) {
            return $project->user_id === $user->id;
        }

        if ($user->isChefDepartement()) {
            $structureIds = Project::getStructureTreeIds($user->structure_id);
            return in_array($project->user?->structure_id, $structureIds, true);
        }

        return false;
    }

    public function delete(User $user, Project $project): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $project->user_id === $user->id;
    }

    public function closeProject(User $user, Project $project): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($project->status === 'completed') {
            return false;
        }

        if ($user->isChefProjet()) {
            return $project->user_id === $user->id;
        }

        if ($user->isChefDepartement()) {
            $structureIds = Project::getStructureTreeIds($user->structure_id);

            return in_array($project->user?->structure_id, $structureIds, true);
        }

        return false;
    }
}
