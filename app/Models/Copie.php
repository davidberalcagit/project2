<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Copie extends Model
{
    use HasFactory;

    protected $table = 'copies';

    protected $fillable = [
        'book_id',
        'library_id',
        'barcode',
        'copy_status_id',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function library(): BelongsTo
    {
        return $this->belongsTo(Library::class);
    }

    public function copyStatus(): BelongsTo
    {
        return $this->belongsTo(CopyStatus::class);
    }

    public function reserves(): HasMany
    {
        return $this->hasMany(Reserve::class, 'copy_id');
    }

    public function borrows(): HasMany
    {
        return $this->hasMany(Borrow::class, 'copy_id');
    }

    public function borrowers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'borrows', 'copy_id', 'user_id')
            ->withPivot('deadline')
            ->withTimestamps();
    }
}
