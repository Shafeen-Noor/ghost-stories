<?php

use App\Models\Comment;
use App\Models\Story;
use App\Models\User;

it('posts a comment as the signed in author and ignores privileged input', function () {
    $story = Story::factory()->create();
    $reader = User::factory()->create();

    $this->actingAs($reader)->post(route('comments.store', $story), ['body' => 'I heard it too.', 'author_id' => $story->author_id, 'status' => 'hidden'])->assertRedirect();

    $this->assertDatabaseHas('comments', ['story_id' => $story->id, 'author_id' => $reader->id, 'body' => 'I heard it too.', 'status' => 'visible']);
});

it('shows visible comments and escapes their content', function () {
    $comment = Comment::factory()->create(['body' => '<script>alert(1)</script>']);
    Comment::factory()->for($comment->story)->create(['status' => 'hidden', 'body' => 'Hidden comment']);

    $this->get(route('stories.show', $comment->story))->assertOk()->assertSee($comment->body)->assertDontSee($comment->body, false)->assertDontSee('Hidden comment');
});

it('validates comment input without saving it', function (mixed $body) {
    $story = Story::factory()->create();

    $this->actingAs(User::factory()->create())->post(route('comments.store', $story), ['body' => $body])->assertSessionHasErrors('body');

    $this->assertDatabaseCount('comments', 0);
})->with(['empty' => '', 'short' => 'a', 'long' => str_repeat('a', 3001), 'array' => [['a']]]);

it('requires login and a visible story before commenting', function () {
    $story = Story::factory()->create(['status' => 'hidden']);

    $this->post(route('comments.store', $story), ['body' => 'A comment'])->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->post(route('comments.store', $story), ['body' => 'A comment'])->assertNotFound();
    $this->assertDatabaseCount('comments', 0);
});

it('only allows a comments author to delete it', function () {
    $comment = Comment::factory()->create();

    $this->actingAs(User::factory()->create())->delete(route('comments.destroy', $comment))->assertForbidden();
    $this->assertModelExists($comment);
    $this->actingAs($comment->author)->delete(route('comments.destroy', $comment))->assertRedirect();
    $this->assertModelMissing($comment);
});
