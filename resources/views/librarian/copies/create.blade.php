<x-app-layout title="Añadir ejemplar · BibloBook">
    <x-button-link :href="route('librarian.dashboard')">Volver a Mi biblioteca</x-button-link>

    <x-card class="auth-card">
        <h1 class="page-title">Añadir ejemplar</h1>

        <form class="auth-form" method="POST" action="{{ route('librarian.copies.store') }}">
            @csrf

            <div>
                <x-label for="book_id">Libro</x-label>
                <x-select id="book_id" name="book_id" required>
                    <option value="">Selecciona un libro</option>
                    @foreach ($books as $book)
                        <option value="{{ $book->id }}" @selected(old('book_id') == $book->id)>
                            {{ $book->title }}
                        </option>
                    @endforeach
                </x-select>
                @error('book_id')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-label for="barcode">Código de barras</x-label>
                <x-input id="barcode" name="barcode" value="{{ old('barcode') }}" required />
                @error('barcode')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <button class="auth-submit" type="submit">Guardar ejemplar</button>
        </form>
    </x-card>
</x-app-layout>
