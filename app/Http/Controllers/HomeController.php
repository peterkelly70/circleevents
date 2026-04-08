<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\MailingList;
use App\Models\Organization;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'featuredEvents' => Event::query()
                ->with('organization')
                ->where('is_published', true)
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->take(6)
                ->get(),
            'organizations' => Organization::query()
                ->withCount(['events', 'mailingLists'])
                ->orderBy('name')
                ->take(6)
                ->get(),
            'lists' => MailingList::query()
                ->with('organization')
                ->orderBy('name')
                ->take(6)
                ->get(),
        ]);
    }
}
