<x-app-layout title="Crear libro · BibloBook">
    <x-button-link :href="route('support.dashboard')">Volver a Soporte</x-button-link>

    <x-card class="auth-card">
        <h1 class="page-title">Crear libro</h1>

        <form class="auth-form" method="POST" action="{{ route('support.books.store') }}">
            @csrf

            <div>
                <x-label for="title">Título</x-label>
                <x-input id="title" name="title" value="{{ old('title') }}" required />
                @error('title')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-label for="editorial">Editorial</x-label>
                <x-input id="editorial" name="editorial" value="{{ old('editorial') }}" required />
                @error('editorial')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-label for="publication_date">Fecha de publicación</x-label>
                <x-date-input id="publication_date" name="publication_date" value="{{ old('publication_date') }}" required />
                @error('publication_date')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-label for="edition">Edición</x-label>
                <x-input id="edition" name="edition" value="{{ old('edition') }}" required />
                @error('edition')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-label for="isbn">ISBN</x-label>
                <x-input id="isbn" name="isbn" value="{{ old('isbn') }}" required />
                @error('isbn')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-label for="category_id">Categoría</x-label>
                <x-select id="category_id" name="category_id">
                    <option value="">Sin categoría</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </x-select>
                @error('category_id')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-label for="author_id">Autor</x-label>
                <x-select id="author_id" name="author_id">
                    <option value="">Sin autor</option>
                    @foreach ($authors as $author)
                        <option value="{{ $author->id }}" @selected(old('author_id') == $author->id)>
                            {{ $author->name }}
                        </option>
                    @endforeach
                </x-select>
                @error('author_id')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <button class="auth-submit" type="submit">Guardar libro</button>
        </form>
    </x-card>
</x-app-layout>
