<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Copie;
use App\Models\Library;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_lists_books(): void
    {
        Book::factory()->create(['title' => 'El nombre del viento']);

        $this->get(route('books.index'))
            ->assertOk()
            ->assertSee('Libros')
            ->assertSee('El nombre del viento');
    }

    public function test_it_shows_a_book(): void
    {
        $book = Book::factory()->create([
            'title' => 'El nombre del viento',
            'isbn' => '9788401352836',
            'publication_date' => '2021-06-15',
        ]);
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
