<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Structure;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view any users (admin index).
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if user can view specific user.
     */
    public function view(User $user, User $model): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : ($user->id === $model->id ? Response::allow() : Response::deny('You can only view your own profile.'));
    }

    /**
     * Determine if user can create users.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if user can update user.
     */
    public function update(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if user can delete user.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin() && !$model->isAdmin(); // Can't delete admins
    }

    /**
     * Determine if user can activate user.
     */
    public function activate(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if user can deactivate user.
     */
    public function deactivate(User $user, User $model): bool
    {
        return $user->isAdmin() && $model->id !== $user->id;
    }
}

