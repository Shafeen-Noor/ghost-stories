<?php

use App\Models\Location;
use App\Models\Story;
use App\Models\User;

it('lets an author open the edit form with their current content', function () {
    $story = Story::factory()->create(['title' => 'The original title']);

    $this->actingAs($story->author)->get(route('userzone.stories.edit', $story))
        ->assertOk()->assertSee('The original title')->assertSee($story->body);
});

it('saves edits while keeping ownership status and the original slug', function () {
    $story = Story::factory()->create(['title' => 'The original title']);
    $other = User::factory()->create();
    $location = Location::factory()->create();
    $body = 'The knocking returned every evening after the house was empty.';

    $this->actingAs($story->author)->patch(route('userzone.stories.update', $story), [
        'title' => 'An updated title', 'body' => $body, 'category' => 'urban_legend',
        'location_id' => $location->id, 'author_id' => $other->id,
        'status' => 'hidden', 'slug' => 'hijacked-link',
    ])->assertRedirect(route('userzone.stories.index'))->assertSessionHasNoErrors();

    $this->assertDatabaseHas('stories', [
        'id' => $story->id, 'title' => 'An updated title', 'body' => $body,
        'category' => 'urban_legend', 'location_id' => $location->id,
        'author_id' => $story->author_id, 'status' => 'visible', 'slug' => 'the-original-title',
    ]);
});

it('rejects invalid edits without changing the story', function () {
    $story = Story::factory()->create();

    $this->actingAs($story->author)->patch(route('userzone.stories.update', $story), [
        'title' => '', 'body' => 'Short', 'category' => 'invalid', 'location_id' => 999,
    ])->assertSessionHasErrors(['title', 'body', 'category', 'location_id']);

    $this->assertDatabaseHas('stories', ['id' => $story->id, 'title' => $story->title, 'body' => $story->body]);
});

it('lets an author delete their story', function () {
    $story = Story::factory()->create();

    $this->actingAs($story->author)->delete(route('userzone.stories.destroy', $story))
        ->assertRedirect(route('userzone.stories.index'));

    $this->assertSoftDeleted($story);
    $this->get(route('stories.show', $story))->assertNotFound();
});

it('forbids another user from managing a story', function (string $method, string $route) {
    $story = Story::factory()->create();
    $other = User::factory()->create();

    $this->actingAs($other)->{$method}(route($route, $story))->assertForbidden();

    $this->assertDatabaseHas('stories', ['id' => $story->id, 'title' => $story->title]);
})->with([
    'edit form' => ['get', 'userzone.stories.edit'],
    'update' => ['patch', 'userzone.stories.update'],
    'delete' => ['delete', 'userzone.stories.destroy'],
]);

it('redirects guests who try to manage a story', function (string $method, string $route) {
    $story = Story::factory()->create();

    $this->{$method}(route($route, $story))->assertRedirect(route('login'));

    $this->assertModelExists($story);
})->with([
    'edit form' => ['get', 'userzone.stories.edit'],
    'update' => ['patch', 'userzone.stories.update'],
    'delete' => ['delete', 'userzone.stories.destroy'],
]);

it('only shows story management controls to its author', function () {
    $story = Story::factory()->create();
    $editUrl = route('userzone.stories.edit', $story);

    $this->actingAs(User::factory()->create())->get(route('stories.show', $story))
        ->assertOk()->assertDontSee($editUrl, false);
    $this->actingAs($story->author)->get(route('userzone.stories.index'))
        ->assertOk()->assertSee($editUrl, false)->assertSee('Delete story');
});

it('escapes story content in the edit form', function () {
    $story = Story::factory()->create(['title' => '<script>alert(1)</script>', 'body' => '</textarea><script>alert(2)</script>']);

    $this->actingAs($story->author)->get(route('userzone.stories.edit', $story))
        ->assertOk()->assertSee($story->title)->assertSee($story->body)
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('</textarea><script>alert(2)</script>', false);
});
