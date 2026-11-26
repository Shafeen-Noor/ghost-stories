<?php

use App\Models\Story;
use App\Models\User;
use App\Models\Vote;

it('saves an upvote or downvote from another reader', function (int $value) {
    $story = Story::factory()->create();
    $reader = User::factory()->create();

    $this->actingAs($reader)->from(route('stories.show', $story))
        ->post(route('stories.vote.store', $story), ['value' => $value, 'user_id' => $story->author_id])
        ->assertRedirect(route('stories.show', $story));

    $this->assertDatabaseHas('votes', ['story_id' => $story->id, 'user_id' => $reader->id, 'value' => $value]);
    $this->assertDatabaseCount('votes', 1);
})->with(['upvote' => 1, 'downvote' => -1]);

it('changes a vote without creating a second vote', function () {
    $vote = Vote::factory()->create();

    $this->actingAs($vote->user)->post(route('stories.vote.store', $vote->story), ['value' => -1])
        ->assertSessionHasNoErrors();
    $this->post(route('stories.vote.store', $vote->story), ['value' => -1])->assertSessionHasNoErrors();

    $this->assertDatabaseCount('votes', 1);
    $this->assertDatabaseHas('votes', ['id' => $vote->id, 'value' => -1]);
});

it('removes only the signed in readers vote', function () {
    $vote = Vote::factory()->create();
    $otherVote = Vote::factory()->for($vote->story)->create();

    $this->actingAs($vote->user)->delete(route('stories.vote.destroy', $vote->story))
        ->assertRedirect();

    $this->assertModelMissing($vote);
    $this->assertModelExists($otherVote);
});

it('forbids voting on your own story', function (string $method, string $route) {
    $story = Story::factory()->create();

    $this->actingAs($story->author)->{$method}(route($route, $story), ['value' => 1])->assertForbidden();

    $this->assertDatabaseCount('votes', 0);
})->with(['save' => ['post', 'stories.vote.store'], 'remove' => ['delete', 'stories.vote.destroy']]);

it('redirects guests who try to vote', function (string $method, string $route) {
    $story = Story::factory()->create();

    $this->{$method}(route($route, $story), ['value' => 1])->assertRedirect(route('login'));

    $this->assertDatabaseCount('votes', 0);
})->with(['save' => ['post', 'stories.vote.store'], 'remove' => ['delete', 'stories.vote.destroy']]);

it('rejects votes for hidden stories', function (string $method, string $route) {
    $story = Story::factory()->create(['status' => 'hidden']);

    $this->actingAs(User::factory()->create())->{$method}(route($route, $story), ['value' => 1])->assertNotFound();

    $this->assertDatabaseCount('votes', 0);
})->with(['save' => ['post', 'stories.vote.store'], 'remove' => ['delete', 'stories.vote.destroy']]);

it('rejects invalid vote values', function (mixed $value) {
    $story = Story::factory()->create();

    $this->actingAs(User::factory()->create())->post(route('stories.vote.store', $story), ['value' => $value])
        ->assertSessionHasErrors('value');

    $this->assertDatabaseCount('votes', 0);
})->with(['zero' => 0, 'too large' => 2, 'string' => 'up', 'missing' => null, 'array' => [[1]]]);

it('shows vote totals and marks the readers current choice', function () {
    $vote = Vote::factory()->create();
    Vote::factory()->for($vote->story)->create(['value' => -1]);

    $this->actingAs($vote->user)->get(route('stories.show', $vote->story))
        ->assertOk()->assertSee('1 up · 1 down')->assertSee('aria-pressed="true"', false)->assertSee('Remove vote');
    $this->get(route('stories.index'))->assertOk()->assertSee('1 up · 1 down');
});

it('preserves votes while a story is in the trash', function () {
    $vote = Vote::factory()->create();

    $this->actingAs($vote->story->author)->delete(route('userzone.stories.destroy', $vote->story))
        ->assertRedirect(route('userzone.stories.index'));

    $this->assertSoftDeleted($vote->story);
    $this->assertModelExists($vote);
});
