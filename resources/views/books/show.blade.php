<x-app-layout :title="$book->title.' · BibloBook'">
    <x-button-link :href="route('books.index')">Volver a los libros</x-button-link>

    <x-card>
        <h1>{{ $book->title }}</h1>
        <p><strong>Autor:</strong> {{ $book->author?->name ?? 'Sin autor' }}</p>
        <p><strong>Categoría:</strong> {{ $book->category?->name ?? 'Sin categoría' }}</p>
        <p><strong>Editorial:</strong> {{ $book->editorial ?? 'No especificada' }}</p>
        <p><strong>Fecha de publicación:</strong> {{ $book->publication_date?->format('d/m/Y') ?? 'No especificada' }}</p>
        <p><strong>Edición:</strong> {{ $book->edition ?? 'No especificada' }}</p>
        <p><strong>ISBN:</strong> {{ $book->isbn ?? 'No especificado' }}</p>
    </x-card>

    <section>
        <h2>Ejemplares físicos ({{ $book->copies->count() }})</h2>
        @forelse ($book->copies as $copy)
            <x-card>
                <h3>Código: {{ $copy->barcode }}</h3>
                <p><strong>Biblioteca:</strong> <a href="{{ route('libraries.show', $copy->library) }}">{{ $copy->library?->name ?? 'No asignada' }}</a></p>
                <p><strong>Ubicación:</strong> {{ $copy->library?->location ?? 'No especificada' }}</p>
                <p><strong>Estado:</strong> {{ $copy->copyStatus?->name ?? 'Sin estado' }}</p>
            </x-card>
        @empty
            <x-card>
                <p class="muted">Este libro todavía no tiene ejemplares físicos registrados.</p>
            </x-card>
        @endforelse
    </section>
</x-app-layout>
