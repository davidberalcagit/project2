<?php

namespace Tests\Feature;

use App\Livewire\BookCatalog;
use App\Models\Book;
use App\Models\Copie;
use App\Models\Library;
use App\Models\Opinion;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BookCatalogLivewireTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_renders_the_livewire_catalog_on_the_books_page(): void
    {
        Book::factory()->create(['title' => 'Libro en el catálogo interactivo']);

        $this->get(route('books.index'))
            ->assertOk()
            ->assertSee('Libro en el catálogo interactivo')
            ->assertSee('wire:id=', false)
            ->assertSee('livewire.js');
    }

    public function test_it_filters_books_as_the_search_term_changes(): void
    {
        Book::factory()->create(['title' => 'El nombre del viento']);
        Book::factory()->create(['title' => 'La ciudad y los perros']);

        Livewire::test(BookCatalog::class)
            ->assertSee('El nombre del viento')
            ->assertSee('La ciudad y los perros')
            ->set('search', 'nombre')
            ->assertSee('El nombre del viento')
            ->assertDontSee('La ciudad y los perros');
    }

    public function test_it_filters_by_available_library_and_minimum_rating_and_can_reset_filters(): void
    {
        $library = Library::factory()->create();
        $matchingBook = Book::factory()->create(['title' => 'Libro disponible y bien valorado']);
        $lowRatedBook = Book::factory()->create(['title' => 'Libro disponible con poca valoración']);
        $otherLibraryBook = Book::factory()->create(['title' => 'Libro de otra biblioteca']);
        Copie::factory()->for($matchingBook)->for($library)->available()->create();
        Copie::factory()->for($lowRatedBook)->for($library)->available()->create();
        Copie::factory()->for($otherLibraryBook)->available()->create();
        Opinion::factory()->for($matchingBook)->create(['rating' => 5]);
        Opinion::factory()->for($lowRatedBook)->create(['rating' => 2]);
        Opinion::factory()->for($otherLibraryBook)->create(['rating' => 5]);

        Livewire::test(BookCatalog::class)
            ->set('libraryId', (string) $library->id)
            ->set('minimumRating', '4')
            ->set('sort', 'rating')
            ->assertSee('Libro disponible y bien valorado')
            ->assertDontSee('Libro disponible con poca valoración')
            ->assertDontSee('Libro de otra biblioteca')
            ->call('resetFilters')
            ->assertSet('libraryId', null)
            ->assertSet('minimumRating', null)
            ->assertSet('sort', 'copies')
            ->assertSee('Libro disponible con poca valoración')
            ->assertSee('Libro de otra biblioteca')
            ->set('libraryId', '')
            ->set('minimumRating', '')
            ->assertHasNoErrors();
    }

    public function test_it_rejects_invalid_live_filter_values(): void
    {
        Livewire::test(BookCatalog::class)
            ->set('minimumRating', '6')
            ->assertHasErrors(['minimumRating' => 'between']);
    }
}
