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
    @endif
</x-app-layout>
