<?php

namespace App\Models;

use Database\Factories\BorrowStatusFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BorrowStatus extends Model
{
    /** @use HasFactory<BorrowStatusFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function borrows(): HasMany
    {
        return $this->hasMany(Borrow::class);
    }
}
