<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BlockController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['user', 'organization'])],
            'id' => ['required', 'integer'],
        ]);

        $target = $validated['type'] === 'user'
            ? User::findOrFail($validated['id'])
            : Organization::findOrFail($validated['id']);

        Block::firstOrCreate([
            'user_id' => $request->user()->id,
            'blockable_type' => $target::class,
            'blockable_id' => $target->id,
        ]);

        return back()->with('status', 'Block saved.');
    }
}
