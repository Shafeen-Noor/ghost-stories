<?php

use App\Models\Location;
use App\Models\Story;

it('renders a publication preview without creating or updating a story', function () {
    $story = Story::factory()->create();
    $originalBody = $story->body;
    $this->actingAs($story->author)->postJson(route('stories.preview'), [
        'title' => 'Not published', 'body' => '**A different account of the same mystery.**', 'id' => $story->id, 'status' => 'visible',
    ])->assertOk()->assertJsonPath('html', "<p><strong>A different account of the same mystery.</strong></p>\n");
    $this->assertDatabaseCount('stories', 1);
    expect($story->fresh()->body)->toBe($originalBody);
});

it('keeps existing photographs when editing only the writing and location', function () {
    $story = Story::factory()->create(['cover_path' => 'story-images/kept.jpg', 'gallery_paths' => ['story-images/kept-photo.jpg']]);
    $this->actingAs($story->author)->patch(route('userzone.stories.update', $story), [
        'title' => 'The account revisited', 'body' => 'I remember a second sound that night. It came from the empty room.', 'category' => 'conspiracy', 'location_id' => Location::factory()->create()->id,
    ])->assertSessionHasNoErrors()->assertRedirect();
    expect($story->fresh()->cover_path)->toBe('story-images/kept.jpg')->and($story->fresh()->gallery_paths)->toBe(['story-images/kept-photo.jpg']);
});
