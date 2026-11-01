<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Contracts\View\View;

class BookController extends Controller
{
    public function index(): View
    {
        $books = Book::query()
            ->with(['author', 'category'])
            ->withCount('copies')
            ->orderBy('copies_count', 'desc')
            ->paginate(16);

        return view('books.index', compact('books'));
    }

    public function show(Book $book): View
    {
        $book->load(['author', 'category', 'copies.library', 'copies.copyStatus']);

        return view('books.show', compact('book'));
    }
}
