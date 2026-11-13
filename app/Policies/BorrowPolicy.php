<?php

namespace App\Policies;

use App\Models\Borrow;
use App\Models\User;

class BorrowPolicy
{
    public function complete(User $user, Borrow $borrow): bool
    {
        return $user->can('complete-borrows')
            && (
                $user->can('manage-users')
                || $user->hasRole('Internos')
                || ($user->library_id !== null
                    && $borrow->copy()->where('library_id', $user->library_id)->exists())
            );
    }
}
