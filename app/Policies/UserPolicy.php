<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::UserView->value);
    }

    public function view(User $user, User $model): bool
    {
        return $user->can(PermissionEnum::UserView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::UserCreate->value);
    }

    public function update(User $user, User $model): bool
    {
        return $user->can(PermissionEnum::UserUpdate->value);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->id !== $model->id && $user->can(PermissionEnum::UserDelete->value);
    }

    public function activate(User $user, User $model): bool
    {
        return $user->id !== $model->id && $user->can(PermissionEnum::UserActivate->value);
    }

    public function deactivate(User $user, User $model): bool
    {
        return $user->id !== $model->id && $user->can(PermissionEnum::UserActivate->value);
    }

    public function assignRole(User $user, User $model): bool
    {
        return $user->can(PermissionEnum::UserAssignRole->value);
    }
}
