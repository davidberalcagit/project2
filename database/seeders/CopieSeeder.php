<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Copie;
use App\Models\Library;
use Illuminate\Database\Seeder;

class CopieSeeder extends Seeder
{
    private const COPIES_PER_BOOK = 3;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = Book::query()->orderBy('id')->get();

        if ($books->isEmpty()) {
            return;
        }

        $libraries = Library::query()->orderBy('id')->get();
        $libraries->firstOrFail();
        $existingCopies = Copie::query()
            ->selectRaw('book_id, library_id, COUNT(*) as copies_count')
            ->groupBy('book_id', 'library_id')
            ->get()
            ->keyBy(fn (Copie $copy): string => "{$copy->book_id}:{$copy->library_id}");
        $assignments = [];
        $assignmentIndex = 0;

        foreach ($books as $book) {
            for ($copyNumber = 0; $copyNumber < self::COPIES_PER_BOOK; $copyNumber++) {
                $library = $libraries[$assignmentIndex % $libraries->count()];
                $assignmentKey = "{$book->getKey()}:{$library->getKey()}";

                if (! isset($assignments[$assignmentKey])) {
                    $assignments[$assignmentKey] = [
                        'book' => $book,
                        'library' => $library,
                        'count' => 0,
                    ];
                }

                $assignments[$assignmentKey]['count']++;
                $assignmentIndex++;
            }
        }

        $assignedLibraryIds = collect($assignments)
            ->pluck('library')
            ->map(fn (Library $library): int => (int) $library->getKey())
            ->all();

        foreach ($libraries as $libraryIndex => $library) {
            if (in_array((int) $library->getKey(), $assignedLibraryIds, true)) {
                continue;
            }

            $book = $books[$libraryIndex % $books->count()];
            $assignmentKey = "{$book->getKey()}:{$library->getKey()}";
            $assignments[$assignmentKey] = [
                'book' => $book,
                'library' => $library,
                'count' => 1,
            ];
        }

        foreach ($assignments as $assignmentKey => $assignment) {
            $existingCount = (int) ($existingCopies->get($assignmentKey)?->copies_count ?? 0);
            $missingCopiesCount = max(0, $assignment['count'] - $existingCount);

            for ($copyNumber = 0; $copyNumber < $missingCopiesCount; $copyNumber++) {
                $copy = Copie::factory()
                    ->for($assignment['book'])
                    ->for($assignment['library'])
                    ->available()
                    ->make();

                while (Copie::query()->where('barcode', $copy->barcode)->exists()) {
                    $copy = Copie::factory()
                        ->for($assignment['book'])
                        ->for($assignment['library'])
                        ->available()
                        ->make();
                }

                $copy->save();
            }
        }
    }
}
