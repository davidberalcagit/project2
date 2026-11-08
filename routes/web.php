<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Librarian\BorrowController;
use App\Http\Controllers\Librarian\CopyController;
use App\Http\Controllers\Librarian\DashboardController as LibrarianDashboardController;
use App\Http\Controllers\LibrarianAccessRequestController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\Support\BookController as SupportBookController;
use App\Http\Controllers\Support\LibrarianAccessRequestController as SupportLibrarianAccessRequestController;
use App\Models\Book;
use Illuminate\Support\Facades\Route;

// Zona pública: página principal y consulta del catálogo.
Route::get('/', function () {
    $books = Book::query()
        ->with(['author', 'category'])
        ->latest()
        ->take(6)
        ->get();

    return view('home', compact('books'));
})->name('home');

Route::name('books.')
    ->controller(BookController::class)
    ->group(function (): void {
        Route::get('/books', 'index')->name('index');
        Route::get('/books/{book}', 'show')->name('show');
    });

Route::name('libraries.')
    ->controller(LibraryController::class)
    ->group(function (): void {
        Route::get('/libraries', 'index')->name('index');
        Route::get('/libraries/{library}', 'show')->name('show');
    });

Route::name('auth.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('/login', [AuthenticatedSessionController::class, 'store'])
            ->middleware('throttle:login')
            ->name('login.store');
        Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
        Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
    });

    Route::middleware('auth')->post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/solicitar-acceso-bibliotecario', [LibrarianAccessRequestController::class, 'create'])
        ->name('librarian-access-requests.create');
    Route::post('/solicitar-acceso-bibliotecario', [LibrarianAccessRequestController::class, 'store'])
        ->name('librarian-access-requests.store');

    Route::middleware('permission:access-library')->group(function (): void {
        Route::get('/mi-biblioteca', LibrarianDashboardController::class)
            ->name('librarian.dashboard');

        Route::get('/mi-biblioteca/ejemplares/crear', [CopyController::class, 'create'])
            ->middleware('permission:manage-copies')
            ->name('librarian.copies.create');
        Route::post('/mi-biblioteca/ejemplares', [CopyController::class, 'store'])
            ->middleware('permission:manage-copies')
            ->name('librarian.copies.store');
    });

    Route::post('/mi-biblioteca/prestamos/{borrow}/devolver', [BorrowController::class, 'complete'])
        ->middleware('permission:complete-borrows')
        ->name('librarian.borrows.complete');

    Route::get('/soporte', [SupportLibrarianAccessRequestController::class, 'index'])
        ->middleware('permission:access-support')
        ->name('support.dashboard');

    Route::middleware('permission:manage-books')->group(function (): void {
        Route::get('/soporte/libros/crear', [SupportBookController::class, 'create'])
            ->name('support.books.create');
        Route::post('/soporte/libros', [SupportBookController::class, 'store'])
            ->name('support.books.store');
    });

    Route::middleware('permission:manage-users')->group(function (): void {
        Route::post('/soporte/solicitudes-biblioteca/{librarianAccessRequest}/aprobar', [SupportLibrarianAccessRequestController::class, 'approve'])
            ->name('support.librarian-requests.approve');
        Route::post('/soporte/solicitudes-biblioteca/{librarianAccessRequest}/rechazar', [SupportLibrarianAccessRequestController::class, 'reject'])
            ->name('support.librarian-requests.reject');
    });
});
