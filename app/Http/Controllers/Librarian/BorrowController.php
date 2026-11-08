<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Models\Borrow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BorrowController extends Controller
{
    public function complete(Request $request, Borrow $borrow): RedirectResponse
    {
        $user = $request->user();
        $canManageAllLibraries = $user->can('manage-users') || $user->hasRole('Internos');
        $canManageOwnLibrary = $user->library_id !== null
            && $borrow->copy->library_id === $user->library_id;

        abort_unless($canManageAllLibraries || $canManageOwnLibrary, 404);

        $borrow->markAsReturned();

        return to_route('librarian.dashboard')
            ->with('status', 'La devolución del préstamo se ha registrado.');
    }
}
