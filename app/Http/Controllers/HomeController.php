<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Organization;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'featuredEvents' => Event::query()
                ->with('organization')
                ->whereHas('organization', fn ($query) => $query->where('approval_status', 'approved'))
                ->where('is_published', true)
                ->where('visibility', 'public')
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->take(6)
                ->get(),
            'organizations' => Organization::query()
                ->withCount([
                    'events' => fn ($query) => $query->where('is_published', true),
                    'mailingLists',
                ])
                ->where('approval_status', 'approved')
                ->where('visibility', 'public')
                ->orderBy('name')
                ->take(6)
                ->get(),
        ]);
    }

    public function install(): View
    {
        return view('install');
    }
}
