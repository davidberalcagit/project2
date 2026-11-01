<?php

namespace Tests\Feature;

use App\Models\Book;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_the_homepage_displays_recent_books(): void
    {
        Book::factory()->create(['title' => 'La biblioteca de medianoche']);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Libros')
            ->assertSee('La biblioteca de medianoche')
            ->assertSee('storage/logos/favicon.png')
            ->assertSee('rel="icon" type="image/png"', false)
            ->assertSee('class="site-logo"', false);
    }
}
