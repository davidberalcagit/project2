<?php

namespace App\Http\Controllers;

use App\Models\Borrow;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load('librarianAccessRequest');
        $borrows = Borrow::query()
            ->forUser($user->id)
            ->with(['copy.book', 'borrowStatus'])
            ->latest('loaned_at')
            ->take(5)
            ->get();

        return view('dashboard', [
            'user' => $user,
            'canAccessLibrary' => $user->can('access-library'),
            'canAccessSupport' => $user->can('access-support'),
            'borrows' => $borrows,
        ]);
    }
}
