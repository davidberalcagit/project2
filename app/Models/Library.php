<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Library extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
    ];

    public function copies(): HasMany
    {
        return $this->hasMany(Copie::class, 'library_id');
    }
}
