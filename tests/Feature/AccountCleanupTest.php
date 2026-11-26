<?php

use App\ArchiveStatistics;
use App\Models\Comment;
use App\Models\Story;
use App\Models\Tag;
use App\Models\User;
use App\Models\Vote;

it('deletes an account with its published and trashed stories without breaking foreign keys', function () {
    $user = User::factory()->create();
    $story = Story::factory()->for($user, 'author')->create();
    $trash = Story::factory()->for($user, 'author')->create();
    $trash->delete();
    $otherStory = Story::factory()->create();
    $voteOnStory = Vote::factory()->for($story)->create();
    $usersVote = Vote::factory()->for($otherStory)->for($user)->create();
    $comment = Comment::factory()->for($otherStory)->for($user, 'author')->create();
    $tag = Tag::factory()->create();
    $story->tags()->attach($tag);
    app(ArchiveStatistics::class)->get();

    $this->actingAs($user)->delete(route('profile.destroy'), ['password' => 'password'])->assertRedirect('/')->assertSessionHasNoErrors();

    $this->assertGuest();
    $this->assertModelMissing($user);
    expect(Story::withTrashed()->find($story->id))->toBeNull();
    expect(Story::withTrashed()->find($trash->id))->toBeNull();
    $this->assertModelMissing($voteOnStory);
    $this->assertModelMissing($usersVote);
    $this->assertModelMissing($comment);
    $this->assertModelExists($otherStory);
    $this->assertModelExists($tag);
    expect(app(ArchiveStatistics::class)->get()['stories'])->toBe(1);
});

it('keeps account content intact when the deletion password is wrong', function () {
    $story = Story::factory()->create();

    $this->actingAs($story->author)->delete(route('profile.destroy'), ['password' => 'wrong'])->assertSessionHasErrorsIn('userDeletion', 'password');

    $this->assertModelExists($story);
    $this->assertModelExists($story->author);
});
