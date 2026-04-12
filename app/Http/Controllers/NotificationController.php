<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function memberMessages(): View
    {
        $messages = auth()->user()->memberMessages()
            ->with(['organization', 'fromUser'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('notifications.member-messages', compact('messages'));
    }

    public function markMessageRead(int $id): RedirectResponse
    {
        $message = auth()->user()->memberMessages()->findOrFail($id);
        $message->update(['read_at' => now()]);

        return back();
    }
}
