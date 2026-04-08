<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'reporter_user_id',
    'reportable_type',
    'reportable_id',
    'reason',
    'details',
    'status',
])]
class Report extends Model
{
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }
}
