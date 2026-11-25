<?php

use App\Models\Location;
use App\Models\Story;
use App\Models\User;

it('shows countries without requiring users to create a place first', function () {
    $this->actingAs(User::factory()->create())->get(route('userzone.stories.create'))
        ->assertOk()->assertSee('Country')->assertSee('Spain')->assertSee('Pakistan')
        ->assertSee('Region / state / province (optional)')->assertDontSee('Add a location first');
});

it('publishes a story using a standard country and region name', function () {
    $author = User::factory()->create();

    $this->actingAs($author)->post(route('userzone.stories.store'), [
        'title' => 'The footsteps in the corridor',
        'body' => 'There was nobody home when the footsteps came from upstairs.',
        'category' => 'ghost', 'country_code' => 'ES', 'region_code' => 'ES-CT',
        'name' => 'misspelled spain',
    ])->assertSessionHasNoErrors()->assertRedirect();

    $location = Location::where('country_code', 'ES')->firstOrFail();
    $this->assertDatabaseHas('locations', ['id' => $location->id, 'region_code' => 'ES-CT', 'name' => 'Catalunya [Cataluña], Spain']);
    $this->assertDatabaseHas('stories', ['title' => 'The footsteps in the corridor', 'location_id' => $location->id, 'author_id' => $author->id]);
});

it('allows a country without a region and reuses the location', function () {
    $author = User::factory()->create();
    $payload = ['title' => 'A light in the window', 'body' => 'The light appeared again in the window of the empty house.', 'category' => 'ghost', 'country_code' => 'PK'];

    $this->actingAs($author)->post(route('userzone.stories.store'), $payload)->assertSessionHasNoErrors();
    $this->post(route('userzone.stories.store'), array_replace($payload, ['title' => 'Another light']))->assertSessionHasNoErrors();

    $this->assertDatabaseCount('locations', 1);
    $this->assertDatabaseHas('locations', ['name' => 'Pakistan', 'country_code' => 'PK', 'region_code' => '']);
    $this->assertDatabaseCount('stories', 2);
});

it('rejects unknown countries and regions belonging to another country', function (array $selection, string $field) {
    $payload = ['title' => 'A light in the window', 'body' => 'The light appeared again in the window of the empty house.', 'category' => 'ghost'];

    $this->actingAs(User::factory()->create())->post(route('userzone.stories.store'), $payload + $selection)
        ->assertSessionHasErrors($field);

    $this->assertDatabaseCount('locations', 0);
    $this->assertDatabaseCount('stories', 0);
})->with([
    'unknown country' => [['country_code' => 'ZZ'], 'country_code'],
    'region in another country' => [['country_code' => 'ES', 'region_code' => 'PK-PB'], 'region_code'],
    'unknown region' => [['country_code' => 'ES', 'region_code' => 'ES-NOWHERE'], 'region_code'],
    'country array' => [['country_code' => ['ES']], 'country_code'],
    'region array' => [['country_code' => 'ES', 'region_code' => ['ES-CT']], 'region_code'],
]);

it('rejects choosing a saved place and country together', function () {
    $location = Location::factory()->create();

    $this->actingAs(User::factory()->create())->post(route('userzone.stories.store'), [
        'title' => 'A light in the window', 'body' => 'The light appeared again in the window of the empty house.',
        'category' => 'ghost', 'country_code' => 'ES', 'location_id' => $location->id,
    ])->assertSessionHasErrors('location_id');

    $this->assertDatabaseCount('stories', 0);
    $this->assertDatabaseCount('locations', 1);
});

it('prefills the selected country and region when editing a story', function () {
    $location = Location::fromGeography('PK', 'PK-PB');
    $story = Story::factory()->for($location)->create();

    $this->actingAs($story->author)->get(route('userzone.stories.edit', $story))
        ->assertOk()->assertSee('value="PK" selected', false)->assertSee('value="PK-PB" selected', false);
});

it('lets an author change their storys location through the dropdowns', function () {
    $story = Story::factory()->create();

    $this->actingAs($story->author)->patch(route('userzone.stories.update', $story), [
        'title' => $story->title, 'body' => $story->body, 'category' => $story->category,
        'country_code' => 'PK', 'region_code' => 'PK-PB',
    ])->assertRedirect(route('userzone.stories.index'))->assertSessionHasNoErrors();

    $location = Location::where('country_code', 'PK')->firstOrFail();
    $this->assertDatabaseHas('stories', ['id' => $story->id, 'location_id' => $location->id]);
    expect($location->name)->toBe('Punjab, Pakistan');
});

it('keeps existing named places available', function () {
    $location = Location::factory()->create(['name' => 'Bellweather House']);

    $this->actingAs(User::factory()->create())->get(route('userzone.stories.create'))
        ->assertOk()->assertSee('Bellweather House')->assertSee('Or use an existing place');
});

it('returns only regions belonging to the selected country', function () {
    $this->actingAs(User::factory()->create())->getJson(route('geography.regions', 'PK'))
        ->assertOk()->assertJsonPath('regions.PK-PB', 'Punjab')->assertJsonMissingPath('regions.ES-CT');
});

it('returns an empty list for a country without subdivisions', function () {
    $this->actingAs(User::factory()->create())->getJson(route('geography.regions', 'AQ'))
        ->assertOk()->assertExactJson(['regions' => []]);
});

it('rejects an unknown country in the region endpoint', function () {
    $this->actingAs(User::factory()->create())->getJson(route('geography.regions', 'ZZ'))->assertNotFound();
});

it('requires login to load regions', function () {
    $this->get(route('geography.regions', 'PK'))->assertRedirect(route('login'));
});

it('reuses an existing location with a matching canonical name', function () {
    $location = Location::factory()->create(['name' => 'Pakistan']);

    $this->actingAs(User::factory()->create())->post(route('userzone.locations.store'), ['country_code' => 'PK'])
        ->assertRedirect(route('userzone.stories.create', ['location' => $location->id]));

    $this->assertDatabaseCount('locations', 1);
    $this->assertDatabaseHas('locations', ['id' => $location->id, 'country_code' => 'PK']);
});

it('distinguishes different kinds of subdivisions with the same name', function () {
    $community = Location::fromGeography('ES', 'ES-CB');
    $province = Location::fromGeography('ES', 'ES-S');

    expect($community->id)->not->toBe($province->id);
    expect($community->name)->not->toBe($province->name);
    $this->assertDatabaseCount('locations', 2);
});

it('rejects manually invented place names', function () {
    $this->actingAs(User::factory()->create())->post(route('userzone.locations.store'), ['name' => 'spian'])
        ->assertSessionHasErrors('country_code');

    $this->assertDatabaseCount('locations', 0);
});
