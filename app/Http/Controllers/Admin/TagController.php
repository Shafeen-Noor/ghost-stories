<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TagRequest;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;

class TagController extends Controller
{
    public function store(TagRequest $request): RedirectResponse
    {
        Tag::create($request->validated());

        return back()->with('status', 'Tag created.');
    }

    public function update(TagRequest $request, Tag $tag): RedirectResponse
    {
        $tag->update($request->validated());

        return back()->with('status', 'Tag updated.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return back()->with('status', 'Tag removed from the archive and its stories.');
    }
}
