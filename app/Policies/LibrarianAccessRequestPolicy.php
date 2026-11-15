<?php

namespace App\Policies;

use App\Models\LibrarianAccessRequest;
use App\Models\User;

class LibrarianAccessRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage-users');
    }

    public function approve(User $user, LibrarianAccessRequest $accessRequest): bool
    {
        return $user->can('manage-users');
    }

    public function reject(User $user, LibrarianAccessRequest $accessRequest): bool
    {
        return $user->can('manage-users');
    }
}
