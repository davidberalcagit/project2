<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCopyRequest;
use App\Models\Book;
use App\Models\Copie;
use App\Models\CopyStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CopyController extends Controller
{
    public function create(Request $request): View
    {
        abort_unless($request->user()->library_id !== null, 403);

        $books = Book::query()->orderBy('title')->get();
        $copyStatuses = CopyStatus::query()->orderBy('name')->get();

        return view('librarian.copies.create', compact('books', 'copyStatuses'));
    }

    public function store(StoreCopyRequest $request): RedirectResponse
    {
        Copie::create([
            ...$request->validated(),
            'library_id' => $request->user()->library_id,
        ]);

        return to_route('librarian.dashboard')
            ->with('status', 'El ejemplar se ha añadido a la biblioteca.');
    }
}
