<x-app-layout title="Mi biblioteca · BibloBook">
    <x-card variant="intro">
        <h1>Mi biblioteca</h1>
        @if ($library)
            <p><strong>{{ $library->name }}</strong></p>
            <p>Ubicación: {{ $library->location }}</p>
            <p>Zona de gestión de ejemplares, préstamos y reservas.</p>
            <x-button-link href="{{ route('librarian.copies.create') }}">Añadir ejemplar</x-button-link>
        @else
            <p class="muted">Tu cuenta todavía no está asociada a una biblioteca. Contacta con Soporte.</p>
        @endif
    </x-card>

    @if (session('status'))
        <x-card>
            <p role="status">{{ session('status') }}</p>
        </x-card>
    @endif

    @if ($library)
        <x-library-copies :copies="$library->copies" />

        <x-card>
            <h2>Préstamos activos</h2>
            @forelse ($activeBorrows as $borrow)
                <article>
                    <h3>{{ $borrow->copy->book->title }}</h3>
                    <p>Usuario: {{ $borrow->user->name }}</p>
                    <p>Estado: {{ $borrow->borrowStatus->name }}</p>
                    <p>Prestado: {{ $borrow->loaned_at->format('d/m/Y H:i') }}</p>
                    <p>Fecha límite: {{ $borrow->deadline->format('d/m/Y') }}</p>
                    @can('complete-borrows')
                        <form method="POST" action="{{ route('librarian.borrows.complete', $borrow) }}">
                            @csrf
                            <button type="submit">Registrar devolución</button>
                        </form>
                    @endcan
                </article>
            @empty
                <p class="muted">No hay préstamos activos en esta biblioteca.</p>
            @endforelse
        </x-card>
    @endif
</x-app-layout>
