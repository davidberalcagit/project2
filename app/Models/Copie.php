<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Copie extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'book_id',
        'library_id',
        'barcode',
        'status',
    ];

    /**
     * Relación: Una copia pertenece a un libro.
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Relación: Una copia pertenece a una librería.
     */
    public function library()
    {
        return $this->belongsTo(Library::class);
    }

    /**
     * Relación: Una copia puede tener muchas reservas.
     */
    public function reserves()
    {
        return $this->hasMany(Reserve::class, 'copy_id');
    }

    /**
     * Relación: Una copia puede tener muchos préstamos (si usas borrows).
     */
    public function borrows()
    {
        return $this->hasMany(Borrow::class, 'copy_id');
    }
}
