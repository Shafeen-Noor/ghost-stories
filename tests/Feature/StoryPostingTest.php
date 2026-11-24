<?php

use App\Models\Location;
use App\Models\Story;
use App\Models\User;

it('publishes a story under the signed in user', function () {
    $author = User::factory()->create();
    $other = User::factory()->create();
    $location = Location::factory()->create();

    $this->actingAs($author)->post(route('userzone.stories.store'), [
        'title' => 'The voice on the landing',
        'body' => 'Every night at eleven, someone called my name from the empty landing.',
        'category' => 'ghost',
        'location_id' => $location->id,
        'author_id' => $other->id,
        'status' => 'hidden',
    ])->assertSessionHasNoErrors();

    $story = Story::where('title', 'The voice on the landing')->firstOrFail();
    expect($story->author_id)->toBe($author->id);
    expect($story->status)->toBe('visible');
    $this->get(route('stories.show', $story))->assertSee($story->title);
});

it('requires a valid story and location', function () {
    $this->actingAs(User::factory()->create())->post(route('userzone.stories.store'), [
        'title' => '',
        'body' => 'Short',
        'category' => 'unknown',
        'location_id' => 999,
    ])->assertSessionHasErrors(['title', 'body', 'category', 'location_id']);

    $this->assertDatabaseCount('stories', 0);
});

it('redirects guests who try to publish', function () {
    $this->post(route('userzone.stories.store'), [])->assertRedirect(route('login'));
    $this->assertDatabaseCount('stories', 0);
});

it('adds a location before writing a story', function () {
    $this->actingAs(User::factory()->create())->post(route('userzone.locations.store'), [
        'name' => 'The old glass house',
        'description' => 'A greenhouse beyond the orchard.',
    ])->assertSessionHasNoErrors();

    $location = Location::where('name', 'The old glass house')->firstOrFail();
    $this->get(route('userzone.stories.create'))->assertSee($location->name);
});
