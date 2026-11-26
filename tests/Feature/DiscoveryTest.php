<?php

use App\Models\Story;

it('discovers a visible story and avoids the last discovery when possible', function () {
    $stories = Story::factory()->count(2)->create();
    Story::factory()->create(['status' => 'hidden']);
    $this->withSession(['discovery.last_story' => $stories[0]->id])->get(route('discovery.random'))->assertRedirect(route('stories.show', $stories[1]));
    $this->get(route('discovery.random'))->assertRedirect(route('stories.show', $stories[0]));
});

it('handles an empty discovery and validates filters', function () {
    $this->get(route('discovery.random'))->assertRedirect(route('stories.index'));
    $this->get(route('discovery.random', ['category' => ['bad']]))->assertSessionHasErrors('category');
    $this->get(route('discovery.random', ['country' => 'invalid']))->assertSessionHasErrors('country');
});

it('never discovers hidden or trashed stories', function () {
    Story::factory()->create(['status' => 'hidden']);
    Story::factory()->create()->delete();
    $this->get(route('discovery.random'))->assertRedirect(route('stories.index'));
});
