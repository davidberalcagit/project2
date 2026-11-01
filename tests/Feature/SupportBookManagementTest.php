<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

it('allows support to create a book with its category and author', function (): void {
    $support = User::factory()->create();
    $support->assignRole('Soporte');
    $category = Category::factory()->create(['name' => 'Fantasía']);
    $author = Author::factory()->create(['name' => 'Patrick Rothfuss']);

    $this->actingAs($support)
        ->get(route('support.books.create'))
        ->assertOk()
        ->assertSee('Crear libro')
        ->assertSee('name="publication_date"', false)
        ->assertSee('Fantasía')
        ->assertSee('Patrick Rothfuss');

    $this->actingAs($support)
        ->post(route('support.books.store'), [
            'title' => 'El nombre del viento',
            'editorial' => 'Plaza & Janés',
            'publication_date' => '2007-03-27',
            'edition' => 'Primera',
            'isbn' => '9788401337209',
            'category_id' => $category->id,
            'author_id' => $author->id,
        ])
        ->assertRedirect(route('support.dashboard'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('books', [
        'title' => 'El nombre del viento',
        'isbn' => '9788401337209',
        'category_id' => $category->id,
        'author_id' => $author->id,
    ]);

    expect(Book::query()->where('isbn', '9788401337209')->firstOrFail()->publication_date->toDateString())
        ->toBe('2007-03-27');
});

it('rejects a book with a duplicate ISBN', function (): void {
    $support = User::factory()->create();
    $support->assignRole('Soporte');
    $existingBook = Book::factory()->create(['isbn' => '9788401337209']);

    $this->actingAs($support)
        ->from(route('support.books.create'))
        ->post(route('support.books.store'), [
            'title' => 'Otro libro',
            'editorial' => 'Editorial',
            'publication_date' => '2020-01-01',
            'edition' => 'Primera',
            'isbn' => $existingBook->isbn,
        ])
        ->assertRedirect(route('support.books.create'))
        ->assertSessionHasErrors('isbn');

    $this->assertDatabaseCount('books', 1);
});

it('allows content managers to create books without support dashboard access', function (): void {
    $contentManager = User::factory()->create();
    $contentManager->assignRole('Gestor de contenidos');

    $this->actingAs($contentManager)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee(route('support.books.create'));

    $this->actingAs($contentManager)
        ->get(route('support.books.create'))
        ->assertOk();

    $this->actingAs($contentManager)
        ->post(route('support.books.store'), [
            'title' => 'Dune',
            'editorial' => 'Chilton Books',
            'publication_date' => '1965-08-01',
            'edition' => 'Primera',
            'isbn' => '9780441172719',
        ])
        ->assertRedirect(route('books.index'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('books', [
        'title' => 'Dune',
        'isbn' => '9780441172719',
    ]);
});

it('prevents clients from creating books', function (): void {
    $client = User::factory()->create();
    $client->assignRole('Cliente');

    $this->actingAs($client)
        ->get(route('support.books.create'))
        ->assertForbidden();

    $this->actingAs($client)
        ->post(route('support.books.store'), [])
        ->assertForbidden();

    $this->assertDatabaseCount('books', 0);
});
