@props(['copies'])

<section>
    <h2>Libros</h2>
    @forelse ($copies->groupBy('book_id') as $bookCopies)
        @php
            $book = $bookCopies->first()->book;
        @endphp

        <x-card>
            <details>
                <summary>
                    <strong>{{ $book?->title ?? 'Libro no disponible' }}</strong>
                    <span>({{ $bookCopies->count() }} {{ $bookCopies->count() === 1 ? 'ejemplar' : 'ejemplares' }})</span>
                </summary>

                @if ($book)
                    <p>
                        <a class="content-link" href="{{ route('books.show', $book) }}">Ver libro</a>
                        · Valoración media:
                        {{ $book->opinions_avg_rating !== null ? number_format((float) $book->opinions_avg_rating, 1).' / 5' : 'Sin valoraciones' }}
                    </p>
                @endif

                <ul>
                    @foreach ($bookCopies as $copy)
                        <li>
                            <strong>Código:</strong> {{ $copy->barcode }}
                            <span>· <strong>Estado:</strong> {{ $copy->copyStatus?->name ?? 'Sin estado' }}</span>
                            @if ($book)
                                · <a class="content-link" href="{{ route('books.show', $book) }}#copy-{{ $copy->id }}">Ver ejemplar</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </details>
        </x-card>
    @empty
        <x-card>
            <p class="muted">Esta biblioteca todavía no tiene ejemplares.</p>
        </x-card>
    @endforelse
</section>
