<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'event_id',
    'user_id',
    'status',
    'notes',
    'reminder_sent_at',
    'remind_one_week_before',
    'remind_one_day_before',
    'remind_one_hour_before',
    'reminder_one_week_sent_at',
    'reminder_one_day_sent_at',
    'reminder_one_hour_sent_at',
])]
class EventRsvp extends Model
{
    protected function casts(): array
    {
        return [
            'reminder_sent_at' => 'datetime',
            'remind_one_week_before' => 'boolean',
            'remind_one_day_before' => 'boolean',
            'remind_one_hour_before' => 'boolean',
            'reminder_one_week_sent_at' => 'datetime',
            'reminder_one_day_sent_at' => 'datetime',
            'reminder_one_hour_sent_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
