<x-app-layout title="Libros · BibloBook">
    <x-card variant="intro" class="home-hero" id="inicio">
        <h1 class="page-title">Libros</h1>
    </x-card>
    <div class="book-grid">
        @forelse ($books as $book)
            <x-card>
                <h3>
                    <a class="content-link" href="{{ route('books.show', $book) }}">{{ $book->title }}</a>
                </h3>
                <p class="muted">{{ $book->author?->name ?? 'Sin autor' }}</p>
                <p class="muted">{{ $book->category?->name ?? 'Sin categoría' }}</p>
                <p>Ejemplares físicos: {{ $book->copies_count }}</p>
            </x-card>
        @empty
            <x-card>
                <p class="muted">Aún no hay libros en la biblioteca.</p>
            </x-card>
        @endforelse
    </div>

    {{ $books->links() }}
</x-app-layout>
