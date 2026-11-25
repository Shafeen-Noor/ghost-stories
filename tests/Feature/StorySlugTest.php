<?php

use App\Models\Story;

it('opens a story using its generated title slug', function () {
    $story = Story::factory()->create(['title' => 'Footsteps in the North Tower']);

    $this->get('/stories/footsteps-in-the-north-tower')->assertOk()->assertSee($story->title);

    expect($story->slug)->toBe('footsteps-in-the-north-tower');
    expect(route('stories.show', $story))->toEndWith('/stories/footsteps-in-the-north-tower');
});

it('gives duplicate titles different URLs', function () {
    $first = Story::factory()->create(['title' => 'The empty house']);
    $second = Story::factory()->create(['title' => 'The empty house']);
    $third = Story::factory()->create(['title' => 'The empty house']);

    expect($first->slug)->toBe('the-empty-house');
    expect($second->slug)->toBe('the-empty-house-2');
    expect($third->slug)->toBe('the-empty-house-3');
    $this->get(route('stories.show', $second))->assertOk()->assertSee($second->body);
});

it('keeps the original URL when a story is renamed', function () {
    $story = Story::factory()->create(['title' => 'The old name']);

    $story->update(['title' => 'The new name']);

    $this->get('/stories/the-old-name')->assertOk()->assertSee('The new name');
    $this->assertDatabaseHas('stories', ['id' => $story->id, 'slug' => 'the-old-name']);
});

it('generates usable slugs for titles without Latin characters', function () {
    $story = Story::factory()->create(['title' => '👻👻👻']);

    expect($story->slug)->toBe('story');
    $this->get('/stories/story')->assertOk();
});
