<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Copie;
use App\Models\Library;
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
        Copie::factory()->for($library)->for($book)->create([
            'barcode' => 'LIB-00000001',
        ]);

        $this->get(route('libraries.show', $library))
            ->assertOk()
            ->assertSee('Biblioteca Central')
            ->assertSee('El nombre del viento')
            ->assertSee('LIB-00000001');
    }

    public function test_it_returns_not_found_for_a_missing_library(): void
    {
        $this->get(route('libraries.show', 999999))
            ->assertNotFound();
    }
}
