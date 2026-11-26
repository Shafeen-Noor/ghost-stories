<?php

namespace App\Http\Controllers\Userzone;

use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TrashController extends Controller
{
    public function index(Request $request): View
    {
        $stories = $request->user()->stories()->onlyTrashed()->with('location')->latest('deleted_at')->get();

        return view('userzone.stories.trash', compact('stories'));
    }

    public function restore(Story $story): RedirectResponse
    {
        Gate::authorize('restore', $story);
        abort_unless($story->trashed(), 404);
        $story->restore();

        return redirect()->route('userzone.stories.index')->with('status', 'Your story has been restored.');
    }
}
