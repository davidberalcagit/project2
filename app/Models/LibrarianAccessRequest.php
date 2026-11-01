<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibrarianAccessRequest extends Model
{
    protected $fillable = [
        'request_type',
        'requested_library_id',
        'proposed_library_name',
        'proposed_library_location',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function requestedLibrary(): BelongsTo
    {
        return $this->belongsTo(Library::class, 'requested_library_id');
    }
}
