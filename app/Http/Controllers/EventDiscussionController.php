<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Support\ImageUploads;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class EventDiscussionController extends Controller
{
    public function store(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:12288'],
        ]);

        $event->discussionPosts()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
            'image_path' => $request->file('image')
                ? ImageUploads::storeResizedPublicImage($request->file('image'), 'event-discussion-images', 1600, 1600)
                : null,
        ]);

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'Discussion post added.');
    }
}
