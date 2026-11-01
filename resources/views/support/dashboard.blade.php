<x-app-layout title="Soporte · BibloBook">
    <x-card variant="intro">
        <h1>Soporte</h1>
        <p>Zona de administración de usuarios, bibliotecas, libros, préstamos y reservas.</p>
        <x-button-link href="{{ route('support.books.create') }}">Crear libro</x-button-link>
    </x-card>

    @if (session('status'))
        <x-card>
            <p role="status">{{ session('status') }}</p>
        </x-card>
    @endif

    <h2>Solicitudes de creación de bibliotecas</h2>
    @forelse ($requests as $accessRequest)
        <x-card>
            <h3>{{ $accessRequest->user->name }}</h3>
            <p class="muted">{{ $accessRequest->user->email }}</p>
            @if ($accessRequest->request_type === 'new')
                <p><strong>Solicitud:</strong> Crear biblioteca</p>
                <p><strong>Nombre propuesto:</strong> {{ $accessRequest->proposed_library_name }}</p>
                <p><strong>Ubicación propuesta:</strong> {{ $accessRequest->proposed_library_location }}</p>
            @else
                <p><strong>Solicitud:</strong> Acceder a biblioteca existente</p>
                @if ($accessRequest->requestedLibrary)
                    <p><strong>Biblioteca:</strong> {{ $accessRequest->requestedLibrary->name }} — {{ $accessRequest->requestedLibrary->location }}</p>
                @else
                    <p class="muted">Solicitud heredada: no especifica una biblioteca.</p>
                @endif
            @endif
            <div class="support-actions">
                <form method="POST" action="{{ route('support.librarian-requests.approve', $accessRequest) }}">
                    @csrf
                    @if ($accessRequest->request_type === 'existing' && ! $accessRequest->requestedLibrary)
                        <x-label for="library_id_{{ $accessRequest->id }}">Biblioteca asignada</x-label>
                        <x-select id="library_id_{{ $accessRequest->id }}" name="library_id" required>
                            <option value="">Selecciona una biblioteca</option>
                            @foreach ($libraries as $library)
                                <option value="{{ $library->id }}" @selected(old('library_id') == $library->id)>
                                    {{ $library->name }}
                                </option>
                            @endforeach
                        </x-select>
                        @error('library_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    @endif
                    <button class="auth-submit" type="submit" @disabled($accessRequest->request_type === 'existing' && ! $accessRequest->requestedLibrary && $libraries->isEmpty())>Aprobar</button>
                </form>
                <form method="POST" action="{{ route('support.librarian-requests.reject', $accessRequest) }}">
                    @csrf
                    <button class="support-reject" type="submit">Rechazar</button>
                </form>
            </div>
        </x-card>
    @empty
        <x-card>
            <p class="muted">No hay solicitudes pendientes.</p>
        </x-card>
    @endforelse

    @if ($requests->isNotEmpty() && $libraries->isEmpty())
        <x-card>
            <p class="muted">Registra una biblioteca antes de aprobar solicitudes de acceso.</p>
        </x-card>
    @endif
</x-app-layout>
