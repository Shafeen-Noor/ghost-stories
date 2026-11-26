<?php

use App\Models\Comment;
use App\Models\Location;
use App\Models\Story;
use App\Models\Tag;
use App\Models\User;

it('blocks guests and ordinary users from moderation', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->get(route('admin.dashboard'))->assertForbidden();
});

it('allows an administrator to open every moderation section', function () {
    Comment::factory()->create();
    Tag::factory()->create();

    $this->actingAs(User::factory()->admin()->create())->get(route('admin.dashboard'))->assertOk()->assertSee('Moderation')->assertSee('Suspend account')->assertSee('Save place details')->assertSee('Add tag');
});

it('lets an admin hide stories and comments without changing their authors', function () {
    $comment = Comment::factory()->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->patch(route('admin.stories.update', $comment->story), ['status' => 'hidden', 'author_id' => $admin->id])->assertRedirect();
    $this->assertDatabaseHas('stories', ['id' => $comment->story_id, 'status' => 'hidden', 'author_id' => $comment->story->author_id]);
    $this->patch(route('admin.comments.update', $comment), ['status' => 'hidden'])->assertRedirect();
    $this->assertDatabaseHas('comments', ['id' => $comment->id, 'status' => 'hidden']);
});

it('lets administrators trash and restore stories', function () {
    $story = Story::factory()->create();

    $this->actingAs(User::factory()->admin()->create())->delete(route('admin.stories.destroy', $story))->assertRedirect();
    $this->assertSoftDeleted($story);
    $this->patch(route('admin.stories.restore', $story))->assertRedirect();
    $this->assertNotSoftDeleted($story);
});

it('rejects arbitrary moderation statuses', function () {
    $story = Story::factory()->create();

    $this->actingAs(User::factory()->admin()->create())->patch(route('admin.stories.update', $story), ['status' => 'invented'])->assertSessionHasErrors('status');
    $this->assertDatabaseHas('stories', ['id' => $story->id, 'status' => 'visible']);
});

it('blocks direct moderation mutations from a regular user', function () {
    $story = Story::factory()->create();
    $tag = Tag::factory()->create();

    $this->actingAs(User::factory()->create())->patch(route('admin.stories.update', $story), ['status' => 'hidden'])->assertForbidden();
    $this->delete(route('admin.tags.destroy', $tag))->assertForbidden();
    $this->assertModelExists($tag);
    $this->assertDatabaseHas('stories', ['id' => $story->id, 'status' => 'visible']);
});

it('suspends users and prevents login and existing authenticated actions', function () {
    $user = User::factory()->create();

    $this->actingAs(User::factory()->admin()->create())->patch(route('admin.users.update', $user), ['is_banned' => 1])->assertRedirect();
    $this->assertDatabaseHas('users', ['id' => $user->id, 'is_banned' => true]);
    $this->actingAs($user->fresh())->get(route('dashboard'))->assertRedirect(route('login'));
    $this->assertGuest();
    $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('does not allow suspending an administrator', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->patch(route('admin.users.update', $admin), ['is_banned' => true])->assertForbidden();
    $this->assertDatabaseHas('users', ['id' => $admin->id, 'is_banned' => false]);
});

it('validates map coordinates and protects places which have stories', function () {
    $story = Story::factory()->create();

    $this->actingAs(User::factory()->admin()->create())->patch(route('admin.locations.update', $story->location), ['latitude' => 200, 'longitude' => 2])->assertSessionHasErrors('latitude');
    $this->delete(route('admin.locations.destroy', $story->location))->assertSessionHasErrors('location');
    $this->assertModelExists($story->location);
});

it('saves valid map coordinates and removes unused places', function () {
    $location = Location::factory()->create();
    $unused = Location::factory()->create();

    $this->actingAs(User::factory()->admin()->create())->patch(route('admin.locations.update', $location), ['latitude' => 41.4, 'longitude' => 2.1, 'description' => 'A place near the city.'])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('locations', ['id' => $location->id, 'latitude' => 41.4, 'longitude' => 2.1]);
    $this->delete(route('admin.locations.destroy', $unused))->assertRedirect();
    $this->assertModelMissing($unused);
});

it('creates canonical tags and rejects colliding slugs', function () {
    $this->actingAs(User::factory()->admin()->create())->post(route('admin.tags.store'), ['name' => 'Haunted Places', 'slug' => 'user-supplied'])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('tags', ['name' => 'Haunted Places', 'slug' => 'haunted-places']);
    $this->post(route('admin.tags.store'), ['name' => 'Haunted-Places'])->assertSessionHasErrors('slug');
    $this->assertDatabaseCount('tags', 1);
});

it('does not grant admin access through registration', function () {
    $this->post(route('register'), ['name' => 'A reader', 'email' => 'reader@example.test', 'password' => 'password', 'password_confirmation' => 'password', 'is_admin' => true])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('users', ['email' => 'reader@example.test', 'is_admin' => false]);
});
