<?php

use App\Models\Location;
use App\Models\Story;
use App\Models\User;

it('maps only public stories with coordinates and applies category filters', function () {
    $place = Location::factory()->create(['latitude' => 41.4, 'longitude' => 2.1]);
    $visible = Story::factory()->for($place)->create(['title' => 'Mapped public mystery', 'category' => 'ghost']);
    Story::factory()->for($place)->create(['title' => 'Secret hidden mystery', 'status' => 'hidden']);
    Story::factory()->for($place)->create(['title' => 'Trashed mystery'])->delete();
    $this->get(route('discovery.map'))->assertOk()->assertSee($visible->title)->assertDontSee('Secret hidden mystery')->assertDontSee('Trashed mystery')->assertViewHas('pins', fn ($pins) => $pins->count() === 1 && $pins[0]['latitude'] === 41.4);
    $this->get(route('discovery.map', ['category' => 'conspiracy']))->assertOk()->assertDontSee($visible->title);
});

it('does not invent coordinates for stories without a pin', function () {
    $place = Location::factory()->create(['latitude' => null, 'longitude' => null]);
    Story::factory()->for($place)->create(['latitude' => null, 'longitude' => null]);
    $this->get(route('discovery.map'))->assertOk()->assertViewHas('unmapped', 1)->assertViewHas('pins', fn ($pins) => $pins->isEmpty());
});

it('validates paired map coordinates while publishing', function () {
    $user = User::factory()->create();
    $data = ['title' => 'A strange encounter', 'body' => str_repeat('An unexplained sound. ', 4), 'category' => 'ghost', 'location_id' => Location::factory()->create()->id];
    $this->actingAs($user)->post(route('userzone.stories.store'), $data + ['latitude' => 41])->assertSessionHasErrors('longitude');
    $this->post(route('userzone.stories.store'), $data + ['latitude' => 91, 'longitude' => 2])->assertSessionHasErrors('latitude');
    $this->post(route('userzone.stories.store'), $data + ['latitude' => 41.4, 'longitude' => 2.1])->assertRedirect();
    $this->assertDatabaseHas('stories', ['author_id' => $user->id, 'latitude' => 41.4, 'longitude' => 2.1]);
});

it('keeps mapped stories beyond the old 250 story limit', function () {
    $place = Location::factory()->create(['latitude' => 41.4, 'longitude' => 2.1]);
    Story::factory()->for($place)->count(260)->create();
    $this->get(route('discovery.map'))->assertOk()->assertViewHas('pins', fn ($pins) => $pins->count() === 260);
});
