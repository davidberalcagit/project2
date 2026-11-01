<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $library = $request->user()->library;

        $library?->load(['copies.book']);

        return view('librarian.dashboard', compact('library'));
    }
}
