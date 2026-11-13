<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class BookCatalogService
{
    /**
     * @param  array{search?: string, library_id?: int|string, min_rating?: int|float|string, sort?: 'rating'|'copies'}  $filters
     */
    public function listing(array $filters): LengthAwarePaginator
    {
        $query = $this->query($filters)->withCount('copies');

        if (($filters['sort'] ?? 'copies') === 'rating') {
            $query->orderByRaw('CASE WHEN EXISTS (SELECT 1 FROM opinions WHERE opinions.book_id = books.id) THEN 0 ELSE 1 END')
                ->orderByDesc('opinions_avg_rating')
                ->orderBy('title');
        } else {
            $query->orderByDesc('copies_count')
                ->orderBy('title');
        }

        return $query->paginate(16)->withQueryString();
    }

    public function featured(int $limit = 6): Collection
    {
        return $this->query()
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * @param  array{search?: string, library_id?: int|string, min_rating?: int|float|string, sort?: 'rating'|'copies'}  $filters
     */
    private function query(array $filters = []): Builder
    {
        $query = Book::query()
            ->with(['author', 'category'])
            ->when(isset($filters['search']), fn (Builder $query) => $query->search($filters['search']))
            ->when(
                isset($filters['library_id']),
                fn (Builder $query) => $query->availableAtLibrary((int) $filters['library_id']),
            );

        if (isset($filters['min_rating'])) {
            $query->withAverageRatingAtLeast((float) $filters['min_rating']);
        } else {
            $query->withAvg('opinions', 'rating');
        }

        return $query;
    }
}
