<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Reserve extends Model
{
    use HasFactory;

    protected $fillable = [
        'copy_id',
        'user_id',
        'start_date',
        'expiration_date',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'expiration_date' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Reserve $reserve): void {
            if (! $reserve->exists) {
                $reserve->start_date ??= now();
                $reserve->expiration_date ??= $reserve->start_date->copy()->addHours(24);
            }

            Validator::make([
                'start_date' => $reserve->start_date,
                'expiration_date' => $reserve->expiration_date,
            ], [
                'start_date' => ['required', 'date'],
                'expiration_date' => ['required', 'date', 'after:start_date'],
            ])->validate();

            if (Borrow::query()
                ->where('copy_id', $reserve->copy_id)
                ->whereNull('returned_at')
                ->exists()) {
                throw ValidationException::withMessages([
                    'copy_id' => 'No se puede reservar un ejemplar que está prestado.',
                ]);
            }

            $overlappingReservationExists = static::query()
                ->where('copy_id', $reserve->copy_id)
                ->where('start_date', '<', $reserve->expiration_date)
                ->where('expiration_date', '>', $reserve->start_date)
                ->when($reserve->exists, function ($query) use ($reserve): void {
                    $query->where('id', '!=', $reserve->getKey());
                })
                ->exists();

            if ($overlappingReservationExists) {
                throw ValidationException::withMessages([
                    'copy_id' => 'Este ejemplar ya tiene una reserva en ese periodo.',
                ]);
            }
        });

        static::saved(function (Reserve $reserve): void {
            Copie::query()->findOrFail($reserve->copy_id)->syncAvailabilityStatus();
        });

        static::deleted(function (Reserve $reserve): void {
            Copie::query()->findOrFail($reserve->copy_id)->syncAvailabilityStatus();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function copy(): BelongsTo
    {
        return $this->belongsTo(Copie::class, 'copy_id');
    }
}
