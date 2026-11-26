<?php

namespace App;

use App\Http\Requests\StoryRequest;
use App\Models\Story;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class StoryMedia
{
    private array $created = [];

    private array $retired = [];

    public function apply(Story $story, StoryRequest $request): void
    {
        foreach (['cover', 'background'] as $type) {
            $field = $type.'_path';
            if ($request->boolean('remove_'.$type) || $request->hasFile($type.'_image')) {
                if ($story->$field) {
                    $this->retired[] = $story->$field;
                }
                $story->$field = null;
            }
            if ($request->hasFile($type.'_image')) {
                $path = $request->file($type.'_image')->store('story-images', 'local');
                if ($path === false) {
                    throw ValidationException::withMessages([$type.'_image' => 'This image could not be saved. Please try again.']);
                }
                $this->created[] = $path;
                $story->$field = $path;
            }
        }
        $gallery = $story->gallery_paths ?? [];
        foreach ((array) $request->validated('remove_gallery', []) as $index) {
            if (isset($gallery[$index])) {
                $this->retired[] = $gallery[$index];
                unset($gallery[$index]);
            }
        }
        foreach ($request->file('gallery', []) as $image) {
            $path = $image->store('story-images', 'local');
            if ($path === false) {
                throw ValidationException::withMessages(['gallery' => 'This image could not be saved. Please try again.']);
            }
            $this->created[] = $path;
            $gallery[] = $path;
        }
        $story->gallery_paths = array_values($gallery);
        $story->save();
    }

    public function cleanUp(bool $saved): void
    {
        Storage::disk('local')->delete($saved ? $this->retired : $this->created);
    }
}
