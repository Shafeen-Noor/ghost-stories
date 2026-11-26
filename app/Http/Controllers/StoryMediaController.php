<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StoryMediaController extends Controller
{
    public function __invoke(Request $request, Story $story, string $asset): StreamedResponse
    {
        abort_unless($story->status === 'visible' || $request->user()?->can('update', $story) || $request->user()?->can('admin'), 404);
        $path = $story->mediaPath($asset);
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, headers: ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }
}
