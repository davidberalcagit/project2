<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Borrow extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'copy_id',
        'loaned_at',
        'deadline',
        'returned_at',
        'borrow_status_id',
    ];

    protected function casts(): array
    {
        return [
            'loaned_at' => 'datetime',
            'deadline' => 'date',
            'returned_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Borrow $borrow): void {
            if (! $borrow->exists) {
                $borrow->loaned_at ??= now();
            }

            $deadlineRules = ['required', 'date'];

            if (! $borrow->exists || $borrow->isDirty('deadline')) {
                $deadlineRules[] = 'after:today';
            }

            $borrow->borrow_status_id = BorrowStatus::query()
                ->firstOrCreate(['name' => $borrow->statusName()])
                ->getKey();

            Validator::make([
                'loaned_at' => $borrow->loaned_at,
                'deadline' => $borrow->deadline,
                'returned_at' => $borrow->returned_at,
                'borrow_status_id' => $borrow->borrow_status_id,
            ], [
                'loaned_at' => ['required', 'date'],
                'deadline' => $deadlineRules,
                'returned_at' => ['nullable', 'date', 'after_or_equal:loaned_at'],
                'borrow_status_id' => ['required', 'integer', 'exists:borrow_statuses,id'],
            ])->validate();

            $activeBorrowExists = static::query()
                ->where('copy_id', $borrow->copy_id)
                ->whereNull('returned_at')
                ->when($borrow->exists, function ($query) use ($borrow): void {
                    $query->where('id', '!=', $borrow->getKey());
                })
                ->exists();

            if ($borrow->returned_at === null && $activeBorrowExists) {
                throw ValidationException::withMessages([
                    'copy_id' => 'Este ejemplar ya tiene un préstamo activo.',
                ]);
            }
        });

        static::saved(function (Borrow $borrow): void {
            Copie::query()->findOrFail($borrow->copy_id)->syncAvailabilityStatus();
        });

        static::deleted(function (Borrow $borrow): void {
            Copie::query()->findOrFail($borrow->copy_id)->syncAvailabilityStatus();
        });
    }

    public function markAsReturned(): void
    {
        DB::transaction(function (): void {
            $this->refresh();

            if ($this->returned_at !== null) {
                throw ValidationException::withMessages([
                    'returned_at' => 'Este préstamo ya está devuelto.',
                ]);
            }

            $this->returned_at = now();
            $this->save();
        });
    }

    public function renewUntil(DateTimeInterface $deadline): void
    {
        if ($this->returned_at !== null) {
            throw ValidationException::withMessages([
                'deadline' => 'No se puede renovar un préstamo ya devuelto.',
            ]);
        }

        $this->deadline = $deadline;
        $this->save();
    }

    public function markAsOverdue(): bool
    {
        if ($this->returned_at !== null || ! $this->deadline->isBefore(today())) {
            return false;
        }

        $overdueStatusId = BorrowStatus::query()
            ->where('name', 'Overdue')
            ->firstOrFail()
            ->getKey();

        if ($this->borrow_status_id === $overdueStatusId) {
            return false;
        }

        $this->borrow_status_id = $overdueStatusId;
        $this->save();

        return true;
    }

    private function statusName(): string
    {
        if ($this->returned_at !== null) {
            return 'Returned';
        }

        if ($this->deadline !== null && $this->deadline->isBefore(today())) {
            return 'Overdue';
        }

        return 'Active';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function copy(): BelongsTo
    {
        return $this->belongsTo(Copie::class, 'copy_id');
    }

    public function borrowStatus(): BelongsTo
    {
        return $this->belongsTo(BorrowStatus::class);
    }
}
