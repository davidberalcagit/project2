@props(['copies'])

<section>
    <h2>Ejemplares</h2>
    @forelse ($copies as $copy)
        <x-card>
            <h3>
                <a class="content-link" href="{{ route('books.show', $copy->book) }}">
                    {{ $copy->book?->title ?? 'Libro no disponible' }}
                </a>
            </h3>
            <p>Código: {{ $copy->barcode }}</p>
        </x-card>
    @empty
        <x-card>
            <p class="muted">Esta biblioteca todavía no tiene ejemplares.</p>
        </x-card>
    @endforelse
</section>
