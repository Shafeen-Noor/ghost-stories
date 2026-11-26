<?php

use App\Models\Comment;
use App\Models\Story;
use App\Models\User;

it('creates explanations experiences and replies as the current reader', function () {
    $story = Story::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('comments.store', $story), ['body' => 'A reflected light may explain it.', 'kind' => 'explanation'])->assertRedirect();
    $root = $story->comments()->first();
    $this->post(route('comments.store', $story), ['body' => 'I saw the light last winter too.', 'kind' => 'experience'])->assertRedirect();
    $this->post(route('comments.store', $story), ['body' => 'What about the weather?', 'parent_id' => $root->id, 'kind' => 'experience'])->assertRedirect();
    expect($root->replies()->first()->kind)->toBe('comment');
    $this->get(route('stories.show', $story))->assertOk()->assertSee('Possible explanation')->assertSee('What about the weather?')->assertSee('I saw the light last winter too.');
});

it('rejects replies to another story hidden threads and nested replies', function () {
    $root = Comment::factory()->create();
    $story = Story::factory()->create();
    $this->actingAs(User::factory()->create())->post(route('comments.store', $story), ['body' => 'A cross story reply', 'parent_id' => $root->id])->assertSessionHasErrors('parent_id');
    $root->update(['status' => 'hidden']);
    $this->post(route('comments.store', $root->story), ['body' => 'A hidden reply', 'parent_id' => $root->id])->assertSessionHasErrors('parent_id');
    $root->update(['status' => 'visible']);
    $child = Comment::factory()->create(['story_id' => $root->story_id, 'parent_id' => $root->id]);
    $this->post(route('comments.store', $root->story), ['body' => 'A deep reply', 'parent_id' => $child->id])->assertSessionHasErrors('parent_id');
});

it('allows one removable vote on another readers explanation', function () {
    $comment = Comment::factory()->create(['kind' => 'explanation']);
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('comments.support', $comment))->assertRedirect();
    $this->post(route('comments.support', $comment))->assertRedirect();
    expect($comment->supporters()->count())->toBe(1);
    $this->delete(route('comments.unsupport', $comment))->assertRedirect();
    expect($comment->supporters()->count())->toBe(0);
    $this->actingAs($comment->author)->post(route('comments.support', $comment))->assertForbidden();
    $comment->update(['status' => 'hidden']);
    $this->actingAs($user)->post(route('comments.support', $comment))->assertNotFound();
});

it('keeps replies invisible when their thread is moderated', function () {
    $root = Comment::factory()->create(['status' => 'hidden']);
    Comment::factory()->create(['story_id' => $root->story_id, 'parent_id' => $root->id, 'body' => 'This must stay inside the hidden thread']);
    $this->get(route('stories.show', $root->story))->assertOk()->assertDontSee('This must stay inside the hidden thread');
});
