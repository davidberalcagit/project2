<x-app-layout title="Libros · BibloBook">
    <x-card variant="intro" class="home-hero" id="inicio">
        <h1 class="page-title">Libros</h1>
    </x-card>
    <livewire:book-catalog :filters="$filters" />
</x-app-layout>
