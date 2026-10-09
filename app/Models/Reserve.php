<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserve extends Model
{
    use HasFactory;

    /**
     * ID de las relaciones.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'copy_id',
        'user_id',
    ];

    /**
     * Relación: Una reserva pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: Una reserva pertenece a una copia (ejemplar físico).
     */
    public function copy()
    {
        return $this->belongsTo(Copie::class, 'copy_id');
    }
}
