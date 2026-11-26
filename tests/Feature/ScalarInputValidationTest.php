<?php

use App\Models\Comment;
use App\Models\Story;
use App\Models\User;

it('rejects array values for single archive filters', function (string $field, array $value) {
    $this->get(route('stories.index', [$field => $value]))->assertSessionHasErrors($field);
})->with([
    'category' => ['category', ['ghost']],
    'country' => ['country', ['ES']],
    'sort' => ['sort', ['latest']],
]);

it('rejects an array instead of a single location or category', function (string $field) {
    $story = Story::factory()->create();
    $payload = ['title' => $story->title, 'body' => $story->body, 'category' => 'ghost', 'location_id' => $story->location_id];
    $payload[$field] = [$payload[$field]];

    $this->actingAs($story->author)->patch(route('userzone.stories.update', $story), $payload)->assertSessionHasErrors($field);

    $this->assertDatabaseHas('stories', ['id' => $story->id, 'title' => $story->title]);
})->with(['location_id', 'category']);

it('rejects arrays for administrative visibility updates', function () {
    $comment = Comment::factory()->create();

    $this->actingAs(User::factory()->admin()->create())->patch(route('admin.stories.update', $comment->story), ['status' => ['hidden']])->assertSessionHasErrors('status');
    $this->patch(route('admin.comments.update', $comment), ['status' => ['hidden']])->assertSessionHasErrors('status');

    $this->assertDatabaseHas('stories', ['id' => $comment->story_id, 'status' => 'visible']);
    $this->assertDatabaseHas('comments', ['id' => $comment->id, 'status' => 'visible']);
});
