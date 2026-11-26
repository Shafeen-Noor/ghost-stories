<?php

use App\Models\ReadingCollection;
use App\Models\Story;
use App\Models\User;

it('creates private collections and saves a story only once', function () {
    $user = User::factory()->create();
    $story = Story::factory()->create();
    $this->actingAs($user)->post(route('collections.save', $story), ['name' => 'Late night reading'])->assertRedirect();
    $collection = $user->readingCollections()->first();
    $this->post(route('collections.save', $story), ['collection_id' => $collection->id])->assertRedirect();
    expect($collection->stories()->count())->toBe(1);
    $this->get(route('collections.show', $collection))->assertOk()->assertSee($story->title);
    $this->delete(route('collections.remove', [$collection, $story]))->assertRedirect();
    expect($collection->stories()->count())->toBe(0);
});

it('protects other readers collections and never leaks hidden stories', function () {
    $collection = ReadingCollection::factory()->create();
    $story = Story::factory()->create(['status' => 'hidden']);
    $collection->stories()->attach($story);
    $this->actingAs(User::factory()->create())->get(route('collections.show', $collection))->assertForbidden();
    $this->patch(route('collections.update', $collection), ['name' => 'Stolen'])->assertForbidden();
    $this->delete(route('collections.destroy', $collection))->assertForbidden();
    $visible = Story::factory()->create();
    $this->post(route('collections.save', $visible), ['collection_id' => $collection->id])->assertSessionHasErrors('collection_id');
    $this->actingAs($collection->user)->get(route('collections.show', $collection))->assertOk()->assertDontSee($story->title);
    $this->post(route('collections.save', $story), ['name' => 'Hidden'])->assertNotFound();
});

it('renames and deletes owned lists without deleting stories', function () {
    $collection = ReadingCollection::factory()->create();
    $story = Story::factory()->create();
    $collection->stories()->attach($story);
    $this->actingAs($collection->user)->patch(route('collections.update', $collection), ['name' => 'New name'])->assertRedirect();
    expect($collection->fresh()->name)->toBe('New name');
    $this->delete(route('collections.destroy', $collection))->assertRedirect(route('collections.index'));
    $this->assertModelExists($story);
});

it('requires authentication and validates collection names', function () {
    $this->get(route('collections.index'))->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->post(route('collections.store'), ['name' => ['bad']])->assertSessionHasErrors('name');
    $this->post(route('collections.store'), ['name' => str_repeat('a', 81)])->assertSessionHasErrors('name');
});
