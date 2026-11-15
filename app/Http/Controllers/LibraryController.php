<?php

namespace App\Http\Controllers;

use App\Models\Library;
use Illuminate\Contracts\View\View;

class LibraryController extends Controller
{
    public function index(): View
    {
        $libraries = Library::query()
            ->withCount('copies')
            ->orderBy('name')
            ->paginate(12);

        return view('libraries.index', compact('libraries'));
    }

    public function show(Library $library): View
    {
        $library->load([
            'copies.copyStatus',
            'copies.book' => fn ($query) => $query->withAvg('opinions', 'rating'),
        ]);

        return view('libraries.show', compact('library'));
    }
}
