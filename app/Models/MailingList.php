<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'organization_id',
    'name',
    'slug',
    'description',
    'audience',
])]
class MailingList extends Model
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('status', 'subscribed_at')
            ->withTimestamps();
    }
}
