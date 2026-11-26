<?php

use App\Models\Story;
use App\Models\User;

it('lists places with visible stories and excludes hidden-only places', function () {
    $visible = Story::factory()->create();
    $hidden = Story::factory()->create(['status' => 'hidden']);

    $this->get(route('locations.index'))->assertOk()->assertSee($visible->location->name)->assertDontSee($hidden->location->name);
    $this->get(route('locations.show', $visible->location))->assertOk()->assertSee($visible->title);
});

it('searches places and validates search input', function () {
    $story = Story::factory()->create();
    $story->location->update(['name' => 'Bellweather House']);

    $this->get(route('locations.index', ['q' => 'Bellweather']))->assertOk()->assertSee('Bellweather House');
    $this->get(route('locations.index', ['q' => str_repeat('a', 81)]))->assertSessionHasErrors('q');
});

it('shows an authors public stories without exposing private account data', function () {
    $story = Story::factory()->create();
    $hidden = Story::factory()->for($story->author, 'author')->create(['status' => 'hidden']);

    $this->get(route('authors.show', $story->author))->assertOk()->assertSee($story->title)->assertDontSee($hidden->title)->assertDontSee($story->author->email);
});

it('lets authors preview a hidden story through their complete story resource', function () {
    $story = Story::factory()->create(['status' => 'hidden']);

    $this->actingAs($story->author)->get(route('userzone.stories.show', $story))->assertOk()->assertSee('private preview')->assertSee($story->body);
    $this->actingAs(User::factory()->create())->get(route('userzone.stories.show', $story))->assertForbidden();
    $this->get(route('stories.show', $story))->assertNotFound();
});

it('opens a personal dashboard using the logged in users data', function () {
    $user = User::factory()->create();
    $story = Story::factory()->for($user, 'author')->create();

    $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee($user->name)->assertSee($story->title)->assertSee('Account settings');
});
