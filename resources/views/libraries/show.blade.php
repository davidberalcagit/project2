<x-app-layout :title="$library->name.' · BibloBook'">
    <x-button-link :href="route('libraries.index')">Volver a bibliotecas</x-button-link>

    <x-card>
        <h1>{{ $library->name }}</h1>
        <p><strong>Ubicación:</strong> {{ $library->location }}</p>
    </x-card>

    <x-library-copies :copies="$library->copies" />
</x-app-layout>
