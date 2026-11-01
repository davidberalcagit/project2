<x-app-layout title="Bibliotecas · BibloBook">
    <x-card variant="intro" class="home-hero" id="inicio">
        <h1>Bibliotecas</h1>
    </x-card>
    <div class="book-grid">
        @forelse ($libraries as $library)
            <x-card>
                <h2>
                    <a class="content-link" href="{{ route('libraries.show', $library) }}">{{ $library->name }}</a>
                </h2>
                <p class="muted">Localización: {{ $library->location }}</p>
                <p>Nº de ejemplares: {{ $library->copies_count }}</p>
            </x-card>
        @empty
            <x-card>
                <p class="muted">Aún no hay bibliotecas registradas.</p>
            </x-card>
        @endforelse
    </div>

    {{ $libraries->links() }}
</x-app-layout>
