<?php

namespace App\Policies;

use App\Models\User;

class CopiePolicy
{
    public function create(User $user): bool
    {
        return $user->can('manage-copies') && $user->library_id !== null;
    }
}
