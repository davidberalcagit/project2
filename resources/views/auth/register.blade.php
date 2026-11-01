<x-app-layout title="Crear cuenta · BibloBook">
    <x-card class="auth-card">
        <h1 class="page-title">Crear cuenta</h1>

        <form class="auth-form" method="POST" action="{{ route('auth.register.store') }}">
            @csrf

            <div>
                <x-label for="name">Nombre</x-label>
                <x-input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name" />
                @error('name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-label for="email">Correo electrónico</x-label>
                <x-input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" />
                @error('email')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-label for="password">Contraseña</x-label>
                <x-input id="password" name="password" type="password" required autocomplete="new-password" />
                @error('password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-label for="password_confirmation">Confirmar contraseña</x-label>
                <x-input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" />
            </div>

            <button class="auth-submit" type="submit">Crear cuenta</button>
        </form>

        <p class="auth-footer">¿Ya tienes cuenta? <a class="content-link" href="{{ route('auth.login') }}">Inicia sesión</a></p>
    </x-card>
</x-app-layout>
