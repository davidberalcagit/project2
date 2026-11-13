<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Copie;
use App\Models\Library;
use App\Models\Opinion;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LibraryControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_lists_libraries(): void
    {
        Library::factory()->create([
            'name' => 'Biblioteca Central',
            'location' => 'Madrid',
        ]);

        $this->get(route('libraries.index'))
            ->assertOk()
            ->assertSee('Biblioteca Central')
            ->assertSee('Madrid');
    }

    public function test_it_shows_a_library_and_its_copies(): void
    {
        $library = Library::factory()->create([
            'name' => 'Biblioteca Central',
            'location' => 'Madrid',
        ]);
        $book = Book::factory()->create(['title' => 'El nombre del viento']);
        Opinion::factory()->for($book)->create(['rating' => 4]);
        Opinion::factory()->for($book)->create(['rating' => 5]);
        $copy = Copie::factory()->for($library)->for($book)->create([
            'barcode' => 'LIB-00000001',
        ]);
        Copie::factory()->for($library)->for($book)->create([
            'barcode' => 'LIB-00000002',
        ]);

        $this->get(route('libraries.show', $library))
            ->assertOk()
            ->assertSee('Biblioteca Central')
            ->assertSee('El nombre del viento')
            ->assertSee('LIB-00000001')
            ->assertSee('LIB-00000002')
            ->assertSee('Estado:')
            ->assertSee('Available')
            ->assertSee('Ver ejemplar')
            ->assertSee(route('books.show', $book).'#copy-'.$copy->id)
            ->assertSee('<details>', false)
            ->assertSee('(2 ejemplares)')
            ->assertSee('Valoración media:')
            ->assertSee('4.5 / 5');

        $this->get(route('books.show', $book))
            ->assertOk()
            ->assertSee('id="copy-'.$copy->id.'"', false)
            ->assertSee('LIB-00000001')
            ->assertSee('Biblioteca Central')
            ->assertSee('Madrid')
            ->assertSee('Available');
    }

    public function test_it_returns_not_found_for_a_missing_library(): void
    {
        $this->get(route('libraries.show', 999999))
            ->assertNotFound();
    }
}
