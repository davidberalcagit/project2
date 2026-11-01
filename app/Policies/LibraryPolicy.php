<?php

namespace App\Policies;

use App\Models\Library;
use App\Models\User;

class LibraryPolicy
{
    public function create(User $user): bool
    {
        return $user->can('manage-libraries');
    }

    public function update(User $user, Library $library): bool
    {
        return $user->can('manage-libraries');
    }

    public function delete(User $user, Library $library): bool
    {
        return $user->can('manage-libraries');
    }
}
