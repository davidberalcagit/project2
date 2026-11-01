<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load('librarianAccessRequest');

        return view('dashboard', [
            'user' => $user,
            'canAccessLibrary' => $user->can('access-library'),
            'canAccessSupport' => $user->can('access-support'),
        ]);
    }
}
