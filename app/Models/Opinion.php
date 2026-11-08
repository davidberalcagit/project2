<?php

namespace App\Models;

use Database\Factories\OpinionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Validator;

class Opinion extends Model
{
    /** @use HasFactory<OpinionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'rating',
        'comment',
    ];

    protected static function booted(): void
    {
        static::saving(function (Opinion $opinion): void {
            Validator::make(
                ['rating' => $opinion->rating],
                ['rating' => ['required', 'integer', 'between:1,5']],
            )->validate();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
