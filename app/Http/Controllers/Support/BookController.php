<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class BookController extends Controller
{
    public function create(): View
    {
        Gate::authorize('create', Book::class);

        $authors = Author::query()->orderBy('name')->get();
        $categories = Category::query()->orderBy('name')->get();

        return view('support.books.create', compact('authors', 'categories'));
    }

    public function store(StoreBookRequest $request): RedirectResponse
    {
        Book::create($request->validated());

        return to_route($request->user()->can('access-support') ? 'support.dashboard' : 'books.index')
            ->with('status', 'El libro se ha creado correctamente.');
    }
}
