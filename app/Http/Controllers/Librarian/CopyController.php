<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCopyRequest;
use App\Models\Book;
use App\Models\Copie;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CopyController extends Controller
{
    public function create(): View
    {
        Gate::authorize('create', Copie::class);

        $books = Book::query()->orderBy('title')->get();

        return view('librarian.copies.create', compact('books'));
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
