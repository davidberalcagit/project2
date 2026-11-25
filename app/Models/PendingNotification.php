<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingNotification extends Model
{
    public const string TYPE_OVERDUE_BORROW = 'overdue_borrow';

    public const string TYPE_AVAILABLE_RESERVATION = 'available_reservation';

    public const string STATUS_PENDING = 'pending';

    protected $fillable = [
        'notification_type',
        'deduplication_key',
        'user_id',
        'borrow_id',
        'reserve_id',
        'status',
    ];
}
