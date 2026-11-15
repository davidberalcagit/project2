<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'editorial',
        'publication_date',
        'edition',
        'isbn',
        'category_id',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'publication_date' => 'date',
        ];
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        $term = '%'.trim($search).'%';

        return $query->where(function (Builder $query) use ($term): void {
            $query->where('title', 'like', $term)
                ->orWhere('editorial', 'like', $term)
                ->orWhereHas('author', function (Builder $authorQuery) use ($term): void {
                    $authorQuery->where('name', 'like', $term);
                })
                ->orWhereHas('category', function (Builder $categoryQuery) use ($term): void {
                    $categoryQuery->where('name', 'like', $term);
                });
        });
    }

    public function scopeAvailableAtLibrary(Builder $query, int $libraryId): Builder
    {
        $availableAt = now();

        return $query->whereHas('copies', function (Builder $copyQuery) use ($libraryId, $availableAt): void {
            $copyQuery->where('library_id', $libraryId)
                ->whereDoesntHave('borrows', function (Builder $borrowQuery): void {
                    $borrowQuery->whereNull('returned_at');
                })
                ->whereDoesntHave('reserves', function (Builder $reserveQuery) use ($availableAt): void {
                    $reserveQuery->where('start_date', '<=', $availableAt)
                        ->where('expiration_date', '>', $availableAt);
                });
        });
    }

    public function scopeWithAverageRatingAtLeast(Builder $query, float $minimumRating): Builder
    {
        return $query->whereIn(
            $query->getModel()->getQualifiedKeyName(),
            Opinion::query()
                ->select('book_id')
                ->groupBy('book_id')
                ->havingRaw('AVG(rating) >= CAST(? AS REAL)', [$minimumRating]),
        )->withAvg('opinions', 'rating');
    }

    public function scopePopular(Builder $query, int $minimumFavorites = 1): Builder
    {
        if ($minimumFavorites > 0) {
            $query->whereIn(
                $query->getModel()->getQualifiedKeyName(),
                Favorite::query()
                    ->select('book_id')
                    ->groupBy('book_id')
                    ->havingRaw('COUNT(*) >= ?', [$minimumFavorites]),
            );
        }

        return $query->withCount('favorites')
            ->orderByDesc('favorites_count')
            ->orderBy('title');
    }

    public function copies(): HasMany
    {
        return $this->hasMany(Copie::class, 'book_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function opinions(): HasMany
    {
        return $this->hasMany(Opinion::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }
}
