<?php

use App\Models\Story;

it('lists visible stories with their locations and authors', function () {
    $story = Story::factory()->create(['title' => 'Footsteps in the north tower']);
    $hidden = Story::factory()->create(['title' => 'The locked cellar', 'status' => 'hidden']);

    $this->get(route('stories.index'))
        ->assertOk()
        ->assertSee($story->title)
        ->assertSee($story->location->name)
        ->assertSee($story->author->name)
        ->assertDontSee($hidden->title);
});

it('opens a complete story', function () {
    $story = Story::factory()->create(['body' => 'The hallway was empty, but the knocking continued.']);

    $this->get(route('stories.show', $story))->assertOk()->assertSee($story->body);
});

it('keeps hidden stories out of the public detail page', function () {
    $story = Story::factory()->create(['status' => 'hidden']);

    $this->get(route('stories.show', $story))->assertNotFound();
});
