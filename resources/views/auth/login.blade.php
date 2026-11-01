<x-app-layout title="Iniciar sesión · BibloBook">
    <x-card class="auth-card">
        <h1 class="page-title">Iniciar sesión</h1>

        <form class="auth-form" method="POST" action="{{ route('auth.login') }}">
            @csrf

            <div>
                <x-label for="email">Correo electrónico</x-label>
                <x-input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" />
                @error('email')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-label for="password">Contraseña</x-label>
                <x-input id="password" name="password" type="password" required autocomplete="current-password" />
                @error('password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <label class="auth-checkbox-label" for="remember">
                <x-checkbox id="remember" name="remember" value="1" />
                Recordarme
            </label>

            <button class="auth-submit" type="submit">Entrar</button>
        </form>

        <p class="auth-footer">¿No tienes cuenta? <a class="content-link" href="{{ route('auth.register') }}">Regístrate</a></p>
    </x-card>
</x-app-layout>
