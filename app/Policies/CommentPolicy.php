<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Project;
use App\Models\User;

class CommentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isChefProjet() || $user->isChefDepartement() || $user->isMembre();
    }

    public function view(User $user, Comment $comment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isChefProjet()) {
            return $comment->task?->milestone?->project?->user_id === $user->id;
        }

        if ($user->isChefDepartement()) {
            $structureIds = Project::getStructureTreeIds($user->structure_id);
            $ownerStructureId = $comment->task?->milestone?->project?->user?->structure_id;

            return in_array($ownerStructureId, $structureIds, true);
        }

        return $comment->task?->users?->contains($user->id) ?? false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isChefProjet() || $user->isChefDepartement() || $user->isMembre();
    }

    public function update(User $user, Comment $comment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $comment->user_id === $user->id;
    }

    public function delete(User $user, Comment $comment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isChefDepartement()) {
            $structureIds = Project::getStructureTreeIds($user->structure_id);
            $ownerStructureId = $comment->task?->milestone?->project?->user?->structure_id;

            return in_array($ownerStructureId, $structureIds, true);
        }

        return $comment->user_id === $user->id;
    }
}

