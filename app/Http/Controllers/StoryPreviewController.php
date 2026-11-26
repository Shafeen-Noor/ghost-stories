<?php

namespace App\Http\Controllers;

use App\StoryText;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoryPreviewController extends Controller
{
    public function __invoke(Request $request, StoryText $text): JsonResponse
    {
        $validated = $request->validate(['body' => ['required', 'string', 'max:30000']]);

        return response()->json(['html' => $text->render($validated['body'])]);
    }
}
