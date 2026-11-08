<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Copie extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::created(function (Copie $copy): void {
            $copy->syncAvailabilityStatus();
        });
    }

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

    public function syncAvailabilityStatus(): bool
    {
        $statusName = $this->borrows()->whereNull('returned_at')->exists()
            ? 'Borrowed'
            : ($this->reserves()
                ->where('start_date', '<=', now())
                ->where('expiration_date', '>', now())
                ->exists() ? 'Reserved' : 'Available');

        $statusId = CopyStatus::query()->firstOrCreate(['name' => $statusName])->getKey();

        if ((int) $this->copy_status_id === (int) $statusId) {
            return false;
        }

        $this->copy_status_id = $statusId;
        $this->saveQuietly();

        return true;
    }
}
