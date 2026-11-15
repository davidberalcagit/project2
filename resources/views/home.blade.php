<x-app-layout title="BibloBook">
    <x-card variant="intro" class="home-hero" id="inicio">
        <h1>Un lugar para cada historia.</h1>
        <p>Descubre nuevas lecturas y encuentra tu próximo libro favorito.</p>
        <x-button-link href="{{ route('books.index') }}">Explorar libros</x-button-link>
    </x-card>

    <section id="coleccion">
        <h2 class="home-section-title">Libros</h2>
        <div class="book-grid">
            @forelse ($books as $book)
                <x-card>
                    <span class="muted">{{ $book->category?->name ?? 'En la biblioteca' }}</span>
                    <h3>
                        <a class="content-link" href="{{ route('books.show', $book) }}">{{ $book->title }}</a>
                    </h3>
                    <p class="muted">
                        {{ $book->author?->name ?? 'Autor por descubrir' }}@if ($book->publication_date) · {{ $book->publication_date->format('Y') }}@endif
                    </p>
                    <p class="muted">
                        Valoración media:
                        {{ $book->opinions_avg_rating !== null ? number_format((float) $book->opinions_avg_rating, 1).' / 5' : 'Sin valoraciones' }}
                    </p>
                </x-card>
            @empty
                <x-card>
                    <p class="muted">La colección está vacía por ahora. Vuelve pronto para descubrir nuevos libros.</p>
                </x-card>
            @endforelse
        </div>
    </section>
</x-app-layout>
