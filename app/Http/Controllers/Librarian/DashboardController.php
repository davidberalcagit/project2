<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Models\Borrow;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $library = $request->user()->library;

        $library?->load(['copies.book']);
        $activeBorrows = $library
            ? Borrow::query()
                ->with(['user', 'copy.book', 'borrowStatus'])
                ->whereNull('returned_at')
                ->whereHas('copy', fn ($query) => $query->where('library_id', $library->id))
                ->orderBy('deadline')
                ->get()
            : collect();

        return view('librarian.dashboard', compact('library', 'activeBorrows'));
    }
}
