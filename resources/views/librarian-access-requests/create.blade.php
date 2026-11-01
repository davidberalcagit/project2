<x-app-layout title="Solicitar acceso como bibliotecario · BibloBook">
    <x-button-link :href="route('dashboard')">Volver a mi cuenta</x-button-link>

    <x-card class="auth-card">
        <h1 class="page-title">Solicitar acceso como bibliotecario</h1>
        <p>Soporte revisará tu solicitud. No tendrás acceso a la gestión de biblioteca hasta que sea aprobada.</p>

        <form class="auth-form" method="POST" action="{{ route('librarian-access-requests.store') }}" data-access-request-form>
            @csrf

            <div>
                <x-label for="request_type">Tipo de solicitud</x-label>
                <x-select id="request_type" name="request_type" required>
                    <option value="existing" @selected(old('request_type', 'existing') === 'existing')>Entrar en una biblioteca existente</option>
                    <option value="new" @selected(old('request_type') === 'new')>Crear una biblioteca nueva</option>
                </x-select>
                @error('request_type')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <fieldset data-request-panel="existing">
                <legend>Biblioteca existente</legend>
                @if ($libraries->isNotEmpty())
                    <div>
                        <x-label for="library_id">Biblioteca</x-label>
                        <x-select id="library_id" name="library_id">
                            <option value="">Selecciona una biblioteca</option>
                            @foreach ($libraries as $library)
                                <option value="{{ $library->id }}" @selected(old('library_id') == $library->id)>
                                    {{ $library->name }} — {{ $library->location }}
                                </option>
                            @endforeach
                        </x-select>
                        @error('library_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <p class="muted">No hay bibliotecas registradas. Puedes solicitar crear una nueva.</p>
                @endif
            </fieldset>

            <fieldset data-request-panel="new">
                <legend>Nueva biblioteca</legend>
                <div>
                    <x-label for="proposed_library_name">Nombre</x-label>
                    <x-input id="proposed_library_name" name="proposed_library_name" value="{{ old('proposed_library_name') }}" />
                    @error('proposed_library_name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-label for="proposed_library_location">Ubicación</x-label>
                    <x-input id="proposed_library_location" name="proposed_library_location" value="{{ old('proposed_library_location') }}" />
                    @error('proposed_library_location')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </fieldset>

            <button class="auth-submit" type="submit">Enviar solicitud</button>
        </form>
    </x-card>

    <script>
        const accessRequestForm = document.querySelector('[data-access-request-form]');
        const requestType = accessRequestForm.querySelector('[name="request_type"]');

        function updateRequestFields() {
            accessRequestForm.querySelectorAll('[data-request-panel]').forEach((panel) => {
                const isActive = panel.dataset.requestPanel === requestType.value;

                panel.hidden = !isActive;
                panel.querySelectorAll('input, select, textarea').forEach((field) => {
                    field.disabled = !isActive;
                });
            });
        }

        requestType.addEventListener('change', updateRequestFields);
        updateRequestFields();
    </script>
</x-app-layout>
