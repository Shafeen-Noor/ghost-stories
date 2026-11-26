<?php

use App\Models\Story;
use App\Models\User;
use App\Models\Vote;

it('hides trashed stories from the archive and rejects votes', function () {
    $story = Story::factory()->create();
    $story->delete();

    $this->get(route('stories.index'))->assertOk()->assertDontSee($story->title);
    $this->get(route('stories.show', $story))->assertNotFound();
    $this->actingAs(User::factory()->create())->post(route('stories.vote.store', $story), ['value' => 1])->assertNotFound();
});

it('restores an authors story with its original URL and votes', function () {
    $vote = Vote::factory()->create();
    $story = $vote->story;
    $story->delete();

    $this->actingAs($story->author)->get(route('userzone.trash.index'))->assertOk()->assertSee($story->title);
    $this->patch(route('userzone.stories.restore', $story))->assertRedirect(route('userzone.stories.index'));

    $this->assertNotSoftDeleted($story);
    $this->assertModelExists($vote);
    $this->get(route('stories.show', $story))->assertOk()->assertSee('1 up');
});

it('forbids restoring another authors story', function () {
    $story = Story::factory()->create();
    $story->delete();

    $this->actingAs(User::factory()->create())->patch(route('userzone.stories.restore', $story))->assertForbidden();

    $this->assertSoftDeleted($story);
    $this->get(route('userzone.trash.index'))->assertOk()->assertDontSee($story->title);
});

it('keeps trashed slugs reserved for restoration', function () {
    $story = Story::factory()->create(['title' => 'The empty house']);
    $story->delete();
    $other = Story::factory()->create(['title' => 'The empty house']);

    expect($other->slug)->toBe('the-empty-house-2');
});

it('redirects guests who try to restore stories', function () {
    $story = Story::factory()->create();
    $story->delete();

    $this->patch(route('userzone.stories.restore', $story))->assertRedirect(route('login'));
    $this->assertSoftDeleted($story);
});
