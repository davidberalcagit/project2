<?php

namespace App\Models;

use Database\Factories\CopyStatusFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CopyStatus extends Model
{
    /** @use HasFactory<CopyStatusFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function copies(): HasMany
    {
        return $this->hasMany(Copie::class);
    }
}
