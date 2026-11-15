<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Library;
use App\Services\BookCatalogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request, BookCatalogService $bookCatalog): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'library_id' => ['nullable', 'integer', 'exists:libraries,id'],
            'min_rating' => ['nullable', 'numeric', 'between:1,5'],
            'sort' => ['nullable', 'in:rating,copies'],
        ]);

        $books = $bookCatalog->listing($filters);
        $libraries = Library::query()->orderBy('name')->get();

        return view('books.index', compact('books', 'libraries'));
    }

    public function show(Book $book): View
    {
        $book->load(['author', 'category', 'copies.library', 'copies.copyStatus']);
        $book->loadAvg('opinions', 'rating');

        return view('books.show', compact('book'));
    }
}
