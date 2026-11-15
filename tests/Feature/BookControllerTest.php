<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Copie;
use App\Models\Library;
use App\Models\Opinion;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_lists_books(): void
    {
        $book = Book::factory()->create(['title' => 'El nombre del viento']);
        Opinion::factory()->for($book)->create(['rating' => 4]);
        Opinion::factory()->for($book)->create(['rating' => 5]);

        $this->get(route('books.index'))
            ->assertOk()
            ->assertSee('Libros')
            ->assertSee('El nombre del viento')
            ->assertSee('Valoración media:')
            ->assertSee('4.5 / 5');
    }

    public function test_it_shows_the_average_rating_on_the_home_page(): void
    {
        $book = Book::factory()->create(['title' => 'Libro en portada']);
        Opinion::factory()->for($book)->create(['rating' => 4]);
        Opinion::factory()->for($book)->create(['rating' => 5]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Libro en portada')
            ->assertSee('Valoración media:')
            ->assertSee('4.5 / 5');
    }

    public function test_it_filters_books_by_search_term(): void
    {
        Book::factory()->create(['title' => 'El nombre del viento']);
        Book::factory()->create(['title' => 'La ciudad y los perros']);

        $this->get(route('books.index', ['search' => 'nombre']))
            ->assertOk()
            ->assertSee('El nombre del viento')
            ->assertDontSee('La ciudad y los perros');
    }

    public function test_it_filters_books_by_library_availability_and_minimum_rating(): void
    {
        $library = Library::factory()->create();
        $availableBook = Book::factory()->create(['title' => 'Libro disponible']);
        $borrowedBook = Book::factory()->create(['title' => 'Libro prestado']);
        Copie::factory()->for($availableBook)->for($library)->create();
        $borrowedCopy = Copie::factory()->for($borrowedBook)->for($library)->create();
        $borrower = User::factory()->create();
        $borrowedCopy->borrows()->create([
            'user_id' => $borrower->id,
            'deadline' => today()->addWeek(),
        ]);
        Opinion::factory()->for($availableBook)->create(['rating' => 5]);
        Opinion::factory()->for($borrowedBook)->create(['rating' => 5]);

        $this->get(route('books.index', [
            'library_id' => $library->id,
            'min_rating' => 4,
        ]))
            ->assertOk()
            ->assertSee('Libro disponible')
            ->assertSee('Valoración media:')
            ->assertSee('5.0 / 5')
            ->assertDontSee('Libro prestado');
    }

    public function test_it_orders_books_by_average_rating_from_highest_to_lowest(): void
    {
        $highlyRatedBook = Book::factory()->create(['title' => 'Libro mejor valorado']);
        $mediumRatedBook = Book::factory()->create(['title' => 'Libro valoración media']);
        $lowRatedBook = Book::factory()->create(['title' => 'Libro menos valorado']);
        $unratedBook = Book::factory()->create(['title' => 'Libro sin valoración']);
        Copie::factory()->for($highlyRatedBook)->create();
        Copie::factory()->count(2)->for($mediumRatedBook)->create();
        Opinion::factory()->for($highlyRatedBook)->create(['rating' => 5]);
        Opinion::factory()->for($mediumRatedBook)->create(['rating' => 5]);
        Opinion::factory()->for($mediumRatedBook)->create(['rating' => 4]);
        Opinion::factory()->for($lowRatedBook)->create(['rating' => 2]);

        $this->get(route('books.index', ['sort' => 'rating']))
            ->assertOk()
            ->assertSee('Mejor valorados')
            ->assertDontSee('Más favoritos')
            ->assertSeeInOrder([
                'Libro mejor valorado',
                'Libro valoración media',
                'Libro menos valorado',
                'Libro sin valoración',
            ])
            ->assertSee('Ejemplares físicos: 1')
            ->assertSee('Ejemplares físicos: 2');
    }

    public function test_it_rejects_invalid_book_catalog_filters(): void
    {
        $this->get(route('books.index', ['min_rating' => 6]))
            ->assertSessionHasErrors('min_rating');
    }

    public function test_it_shows_a_book(): void
    {
        $book = Book::factory()->create([
            'title' => 'El nombre del viento',
            'isbn' => '9788401352836',
            'publication_date' => '2021-06-15',
        ]);
        Opinion::factory()->for($book)->create(['rating' => 4]);
        Opinion::factory()->for($book)->create(['rating' => 5]);
        $library = Library::factory()->create([
            'name' => 'Biblioteca Central',
            'location' => 'Madrid',
        ]);
        Copie::factory()->for($book)->for($library)->create([
            'barcode' => 'LIB-00000001',
        ]);

        $this->get(route('books.show', $book))
            ->assertOk()
            ->assertSee('El nombre del viento')
            ->assertSee('9788401352836')
            ->assertSee('15/06/2021')
            ->assertSee('Valoración media:')
            ->assertSee('4.5 / 5')
            ->assertSee('Ejemplares físicos (1)')
            ->assertSee('LIB-00000001')
            ->assertSee('Biblioteca Central')
            ->assertSee('Madrid');
    }

    public function test_it_returns_not_found_for_a_missing_book(): void
    {
        $this->get(route('books.show', 999999))
            ->assertNotFound();
    }
}
