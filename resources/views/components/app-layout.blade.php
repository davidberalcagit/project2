@props(['title' => config('app.name', 'BibloBook')])

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/logos/favicon.png') }}">
    <style>
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            background: #f5f2eb;
            color: #263b30;
            font-family: Arial, sans-serif;
            line-height: 1.5;
        }
        a { color: inherit; }
        .site-shell { width: min(1000px, 90%); margin: 0 auto; }
        .site-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 22px 10px;
            background-color: #e1ddd3;
            border: 1px solid #e1ddd3;
            border-radius: 12px;
        }
        .site-brand { display: flex; align-items: center; gap: 10px; font-size: 18px; font-weight: 700; text-decoration: none; }
        .site-logo {
            position: relative;
            display: block;
            width: 44px;
            height: 44px;
            overflow: hidden;
            flex: 0 0 44px;
            border-radius: 8px;
        }
        .site-logo img {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 338px;
            max-width: none;
            transform: translate(-50%, -50%);
        }
        .site-nav { display: flex; flex-wrap: wrap; gap: 20px; }
        .site-nav form { margin: 0; }
        .site-nav button {
            padding: 0;
            border: 0;
            background: none;
            color: inherit;
            font: inherit;
            cursor: pointer;
        }
        .site-nav button:hover { text-decoration: underline; }
        .site-main { padding: 0 0 24px; }
        .book-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
        .home-hero { margin: 24px auto; padding: 48px; border-radius: 12px; }
        .home-hero p { max-width: 520px; line-height: 1.6; }
        .home-section-title { margin: 42px 0 16px; }
        h1, h2, h3 { font-family: Georgia, serif; }
        h1 { margin: 0 0 12px; font-size: clamp(36px, 7vw, 58px); }
        h2 { margin: 0 0 16px; font-size: 30px; }
        h3 { margin: 8px 0; font-size: 21px; }
        p { margin: 0 0 8px; }
        .page-title { margin: 0 0 24px; }
        .muted { color: #68756c; }
        .accent-text { color: #264b3c; }
        .content-link { text-decoration: none; }
        .content-link:hover { text-decoration: underline; }
        .auth-card { width: min(480px, 100%); margin: 32px auto; }
        .auth-form { display: grid; gap: 16px; }
        .auth-form .form-label { margin-bottom: 6px; }
        .auth-checkbox-label { display: flex; align-items: center; gap: 8px; }
        .auth-submit {
            min-height: 44px;
            padding: 10px 18px;
            border: 1px solid #264b3c;
            border-radius: 6px;
            background: #264b3c;
            color: #fff;
            font: inherit;
            cursor: pointer;
        }
        .auth-submit:hover { border-color: #1d392e; background: #1d392e; }
        .support-actions { display: flex; flex-wrap: wrap; gap: 12px; }
        .support-actions form { margin: 0; }
        .support-reject {
            min-height: 44px;
            padding: 10px 18px;
            border: 1px solid #e1ddd3;
            border-radius: 6px;
            background: #fffefa;
            color: #263b30;
            font: inherit;
            cursor: pointer;
        }
        .support-reject:hover { background: #e8dfcd; }
        .form-error { color: #a12b20; font-size: 14px; }
        .auth-footer { margin-top: 20px; }
        .site-layout nav[role="navigation"] svg { display: block; width: 1.25rem; height: 1.25rem; }
        .site-footer { padding: 28px 0; color: #68756c; font-size: 14px; }

        @media (max-width: 520px) {
            .site-header { align-items: flex-start; flex-direction: column; }
            .site-nav { gap: 14px; font-size: 14px; }
            .home-hero { padding: 30px 22px; }
        }
    </style>
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
