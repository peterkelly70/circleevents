<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvitationAuditLogger
{
    public static function log(Model $invitation, string $action, ?Request $request = null, ?User $actor = null, array $details = []): void
    {
        DB::table('invitation_audits')->insert([
            'invitation_type' => $invitation::class,
            'invitation_id' => $invitation->getKey(),
            'actor_user_id' => $actor?->id,
            'action' => $action,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'details' => $details ? json_encode($details, JSON_THROW_ON_ERROR) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
