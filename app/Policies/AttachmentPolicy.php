<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;

class AttachmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isChefProjet() || $user->isChefDepartement();
    }

    public function view(User $user, Attachment $attachment): bool
    {
        if ($user->isAdmin()) return true;

        $attachable = $attachment->attachable;
        
        if ($attachable instanceof \App\Models\Project) {
            return $attachable->user_id === $user->id || $user->isChefDepartement() && in_array($attachable->user->structure_id, \App\Models\Project::getStructureTreeIds($user->structure_id));
        }

        if ($attachable instanceof \App\Models\Task) {
            return $attachable->users->contains($user->id) || $attachable->milestone->project->user_id === $user->id || $user->isChefDepartement() && in_array($attachable->milestone->project->user->structure_id, \App\Models\Project::getStructureTreeIds($user->structure_id));
        }

        return false;
    }

    public function create(User $user): bool
    {
        return true; // Anyone authenticated can upload to accessible entities
    }

    public function update(User $user, Attachment $attachment): bool
    {
        return $this->delete($user, $attachment);
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        if ($user->isAdmin()) return true;

        $attachable = $attachment->attachable;
        
        // Chef projet can delete on their projects/tasks
        if ($attachable instanceof \App\Models\Project && $attachable->user_id === $user->id) return true;
        if ($attachable instanceof \App\Models\Task && $attachable->milestone->project->user_id === $user->id) return true;

        // Chef dept on their dept
        if ($user->isChefDepartement()) {
            $structureIds = \App\Models\Project::getStructureTreeIds($user->structure_id);
            if ($attachable instanceof \App\Models\Project && in_array($attachable->user->structure_id, $structureIds)) return true;
            if ($attachable instanceof \App\Models\Task && in_array($attachable->milestone->project->user->structure_id, $structureIds)) return true;
        }

        // Membre cannot delete any
        return false;
    }
}

