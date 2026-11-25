<?php

namespace App\Livewire;

use App\Models\Library;
use App\Services\BookCatalogService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class BookCatalog extends Component
{
    use WithPagination;

    #[Url]
    #[Validate('nullable|string|max:255')]
    public ?string $search = null;

    #[Url(as: 'library_id')]
    #[Validate('nullable|integer|exists:libraries,id')]
    public ?string $libraryId = null;

    #[Url(as: 'min_rating')]
    #[Validate('nullable|numeric|between:1,5')]
    public ?string $minimumRating = null;

    #[Url(except: 'copies')]
    #[Validate('required|in:rating,copies')]
    public string $sort = 'copies';

    protected BookCatalogService $bookCatalog;

    public function boot(BookCatalogService $bookCatalog): void
    {
        $this->bookCatalog = $bookCatalog;
    }

    /**
     * @param  array{search?: string|null, library_id?: int|string|null, min_rating?: int|float|string|null, sort?: string|null}  $filters
     */
    public function mount(array $filters = []): void
    {
        $this->search = $filters['search'] ?? null;
        $this->libraryId = isset($filters['library_id']) ? (string) $filters['library_id'] : null;
        $this->minimumRating = isset($filters['min_rating']) ? (string) $filters['min_rating'] : null;
        $this->sort = $filters['sort'] ?? 'copies';
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'libraryId', 'minimumRating', 'sort'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'libraryId', 'minimumRating', 'sort']);
        $this->resetPage();
    }

    public function render(): View
    {
        $filters = array_filter([
            'search' => $this->search,
            'library_id' => $this->libraryId,
            'min_rating' => $this->minimumRating,
            'sort' => $this->sort,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');

        return view('livewire.book-catalog', [
            'books' => $this->bookCatalog->listing($filters),
            'libraries' => Library::query()->orderBy('name')->get(),
        ]);
    }
}
