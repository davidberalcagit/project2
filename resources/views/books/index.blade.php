<x-app-layout title="Libros · BibloBook">
    <x-card variant="intro" class="home-hero" id="inicio">
        <h1 class="page-title">Libros</h1>
    </x-card>
    <x-card class="book-filter-card">
        <style>
            .book-filter-heading { margin: 0 0 4px; font-size: 24px; }
            .book-filter-description { margin-bottom: 20px; }
            .book-filter-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 16px;
                align-items: end;
            }
            .book-filter-search { grid-column: 1 / -1; }
            .book-filter-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 16px; }
            .book-filter-reset { color: #264b3c; font-weight: 600; }
            @media (max-width: 700px) {
                .book-filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
                .book-filter-sort { grid-column: 1 / -1; }
            }
            @media (max-width: 480px) {
                .book-filter-grid { grid-template-columns: 1fr; }
                .book-filter-search, .book-filter-sort { grid-column: auto; }
            }
        </style>
        <h2 class="book-filter-heading">Encuentra tu próxima lectura</h2>
        <p class="muted book-filter-description">Busca por título o filtra por disponibilidad y valoración.</p>
        <form method="GET" action="{{ route('books.index') }}" class="book-filter-grid">
            <div class="book-filter-search">
                <label class="form-label" for="search">Buscar</label>
                <x-input id="search" name="search" value="{{ request('search') }}" placeholder="Título, editorial, autor o categoría" />
                @error('search')
                    <p class="form-error" role="alert">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="form-label" for="library_id">Biblioteca con ejemplares disponibles</label>
                <x-select id="library_id" name="library_id">
                    <option value="">Todas las bibliotecas</option>
                    @foreach ($libraries as $library)
                        <option value="{{ $library->id }}" @selected((string) request('library_id') === (string) $library->id)>
                            {{ $library->name }}
                        </option>
                    @endforeach
                </x-select>
                @error('library_id')
                    <p class="form-error" role="alert">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="form-label" for="min_rating">Valoración mínima</label>
                <x-select id="min_rating" name="min_rating">
                    <option value="">Cualquier valoración</option>
                    @foreach ([1, 2, 3, 4, 5] as $rating)
                        <option value="{{ $rating }}" @selected((string) request('min_rating') === (string) $rating)>
                            {{ $rating }} o más
                        </option>
                    @endforeach
                </x-select>
                @error('min_rating')
                    <p class="form-error" role="alert">{{ $message }}</p>
                @enderror
            </div>
            <div class="book-filter-sort">
                <label class="form-label" for="sort">Ordenar por</label>
                <x-select id="sort" name="sort">
                    <option value="copies" @selected(request('sort', 'copies') === 'copies')>Más ejemplares</option>
                    <option value="rating" @selected(request('sort') === 'rating')>Mejor valorados</option>
                </x-select>
                @error('sort')
                    <p class="form-error" role="alert">{{ $message }}</p>
                @enderror
            </div>
            <div class="book-filter-actions">
                <button class="auth-submit" type="submit">Aplicar filtros</button>
                <a class="book-filter-reset" href="{{ route('books.index') }}">Limpiar filtros</a>
            </div>
        </form>
    </x-card>
    <div class="book-grid">
        @forelse ($books as $book)
            <x-card>
                <h3>
                    <a class="content-link" href="{{ route('books.show', $book) }}">{{ $book->title }}</a>
                </h3>
                <p class="muted">{{ $book->author?->name ?? 'Sin autor' }}</p>
                <p class="muted">{{ $book->category?->name ?? 'Sin categoría' }}</p>
                @if (isset($book->copies_count))
                    <p>Ejemplares físicos: {{ $book->copies_count }}</p>
                @endif
                @if (isset($book->favorites_count))
                    <p>Favoritos: {{ $book->favorites_count }}</p>
                @endif
                <p>Valoración media: {{ $book->opinions_avg_rating !== null ? number_format((float) $book->opinions_avg_rating, 1).' / 5' : 'Sin valoraciones' }}</p>
            </x-card>
        @empty
            <x-card>
                <p class="muted">No hay libros que coincidan con los filtros seleccionados.</p>
            </x-card>
        @endforelse
    </div>

    {{ $books->links() }}
</x-app-layout>
