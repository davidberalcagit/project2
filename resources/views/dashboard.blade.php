<x-app-layout title="Mi cuenta · BibloBook">
    <x-card variant="intro">
        <h1>Zona privada</h1>
        <p>Hola, {{ $user->name }}. Has iniciado sesión en BibloBook.</p>
        <p class="muted">{{ $user->email }}</p>
        @if (session('status'))
            <p role="status">{{ session('status') }}</p>
        @endif
        @if ($user->librarianAccessRequest?->status === 'pending')
            <p role="status">Tu solicitud para acceder como bibliotecario está pendiente de aprobación.</p>
        @elseif ($user->librarianAccessRequest?->status === 'rejected')
            <p role="status">Tu solicitud anterior fue rechazada. Puedes enviar una nueva solicitud.</p>
        @endif
        @if (! $canAccessLibrary && $user->librarianAccessRequest?->status !== 'pending')
            <p><a class="content-link" href="{{ route('librarian-access-requests.create') }}">Solicitar acceso como bibliotecario</a></p>
        @endif
        @if ($canAccessLibrary)
            <p><a class="content-link" href="{{ route('librarian.dashboard') }}">Ir a mi biblioteca</a></p>
        @endif
        @if ($canAccessSupport)
            <p><a class="content-link" href="{{ route('support.dashboard') }}">Ir al soporte</a></p>
        @endif
        @if ($user->can('manage-books'))
            <p><a class="content-link" href="{{ route('support.books.create') }}">Crear libro</a></p>
        @endif
    </x-card>
    <x-card>
        <h2>Mis préstamos recientes</h2>
        @forelse ($borrows as $borrow)
            <article>
                <h3>
                    <a class="content-link" href="{{ route('books.show', $borrow->copy->book) }}">
                        {{ $borrow->copy->book->title }}
                    </a>
                </h3>
                <p>Estado: {{ $borrow->borrowStatus->name }}</p>
                <p>Fecha límite: {{ $borrow->deadline->format('d/m/Y') }}</p>
                @if ($borrow->returned_at)
                    <p>Devuelto: {{ $borrow->returned_at->format('d/m/Y H:i') }}</p>
                @endif
            </article>
        @empty
            <p class="muted">Todavía no tienes préstamos registrados.</p>
        @endforelse
    </x-card>
</x-app-layout>
