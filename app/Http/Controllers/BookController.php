<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookCatalogFiltersRequest;
use App\Models\Book;
use Illuminate\Contracts\View\View;

class BookController extends Controller
{
    public function index(BookCatalogFiltersRequest $request): View
    {
        return view('books.index', ['filters' => $request->validated()]);
    }

    public function show(Book $book): View
    {
        $book->load(['author', 'category', 'copies.library', 'copies.copyStatus']);
        $book->loadAvg('opinions', 'rating');

        return view('books.show', compact('book'));
    }
}
