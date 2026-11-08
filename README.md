# BibloBook

Aplicación para consultar libros y gestionar bibliotecas, ejemplares, préstamos, reservas, opiniones y favoritos.

## Requisitos

- PHP 8.4 y Composer
- Node.js y npm
- MySQL

## Instalación

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
```

Configura la conexión a MySQL en `.env` y ejecuta:

```bash
php artisan migrate --seed
npm run build
```

Para ejecutar las pruebas:

```bash
php artisan test --compact
```

## Datos principales

- Un libro puede tener varios ejemplares.
- Cada ejemplar pertenece a una biblioteca.
- Los préstamos guardan la fecha de inicio, vencimiento y devolución.
- Sus estados son `Active`, `Returned` y `Overdue`; son distintos de los estados de los ejemplares.
- El programador de Laravel marca a diario los préstamos vencidos.
- Las reservas duran 24 horas.
- Las opiniones aceptan puntuaciones del 1 al 5.

```mermaid
erDiagram
    AUTHORS ||--o{ BOOKS : escribe
    CATEGORIES o|--o{ BOOKS : clasifica
    BOOKS ||--o{ COPIES : tiene
    LIBRARIES ||--o{ COPIES : almacena
    COPY_STATUSES ||--o{ COPIES : estado
    USERS ||--o{ BORROWS : realiza
    COPIES ||--o{ BORROWS : historial
    BORROW_STATUSES ||--o{ BORROWS : estado
    USERS ||--o{ RESERVES : solicita
    COPIES ||--o{ RESERVES : recibe
    USERS ||--o{ OPINIONS : escribe
    BOOKS ||--o{ OPINIONS : recibe
    USERS ||--o{ FAVORITES : guarda
    BOOKS ||--o{ FAVORITES : favorito
    LIBRARIES o|--o{ USERS : asigna
    USERS ||--o{ LIBRARIAN_ACCESS_REQUESTS : solicita
    LIBRARIES o|--o{ LIBRARIAN_ACCESS_REQUESTS : biblioteca
    USERS o|--o{ LIBRARIAN_ACCESS_REQUESTS : revisa
```

Los datos principales son:

- **Libro:** título, autor, editorial, fecha de publicación, edición, ISBN y categoría.
- **Ejemplar:** libro, biblioteca, código de barras y estado.
- **Préstamo:** usuario, ejemplar, inicio, vencimiento, devolución y estado. No se pueden borrar usuarios o ejemplares con historial de préstamos.
- **Reserva:** usuario, ejemplar, inicio y vencimiento.
- **Opinión:** usuario, libro, puntuación del 1 al 5 y comentario opcional.

## Ejemplo bibliográfico

| Dato | Ejemplo |
|---|---|
| Título | The Hobbit |
| Autor | J. R. R. Tolkien |
| Editorial | HarperCollins |
| Fecha de publicación | 2001-09-01 |
| Edición | Paperback |
| ISBN-13 | 9780007118359 |
