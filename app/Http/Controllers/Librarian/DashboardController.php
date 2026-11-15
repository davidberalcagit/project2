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
        $filters = $request->validate([
            'loan_filter' => ['nullable', 'in:active,overdue'],
        ]);
        $library = $request->user()->library;

        $library?->load([
            'copies.copyStatus',
            'copies.book' => fn ($query) => $query->withAvg('opinions', 'rating'),
        ]);
        $activeBorrows = $library
            ? Borrow::query()
                ->with(['user', 'copy.book', 'borrowStatus'])
                ->when(
                    ($filters['loan_filter'] ?? 'active') === 'overdue',
                    fn ($query) => $query->overdue(),
                    fn ($query) => $query->active(),
                )
                ->whereHas('copy', fn ($query) => $query->where('library_id', $library->id))
                ->orderBy('deadline')
                ->get()
            : collect();

        return view('librarian.dashboard', [
            'library' => $library,
            'activeBorrows' => $activeBorrows,
            'loanFilter' => $filters['loan_filter'] ?? 'active',
        ]);
    }
}
