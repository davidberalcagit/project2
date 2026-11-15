@props(['title' => config('app.name', 'BibloBook')])

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/logos/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
</head>
<body class="site-layout">
    <header class="site-shell site-header">
        <x-app-logo />
        <nav class="site-nav" aria-label="Navegación principal">
            <x-nav-link href="{{ route('books.index') }}">Libros</x-nav-link>
            <x-nav-link href="{{ route('libraries.index') }}">Bibliotecas</x-nav-link>
            @auth
                @can('access-library')
                    <x-nav-link href="{{ route('librarian.dashboard') }}">Mi biblioteca</x-nav-link>
                @endcan
                @can('access-support')
                    <x-nav-link href="{{ route('support.dashboard') }}">Soporte</x-nav-link>
                @endcan
                    <x-nav-link href="{{ route('dashboard') }}">Mi cuenta</x-nav-link>

                    <form method="POST" action="{{ route('auth.logout') }}">
                    @csrf
                    <button type="submit">Cerrar sesión</button>
                </form>
            @else
                <x-nav-link href="{{ route('auth.login') }}">Iniciar sesión</x-nav-link>
                <x-nav-link href="{{ route('auth.register') }}">Registrarse</x-nav-link>
            @endauth
        </nav>
    </header>

    <main class="site-shell site-main">
        {{ $slot }}
    </main>

    <footer class="site-shell site-footer">© {{ now()->year }} BibloBook</footer>
</body>
</html>
