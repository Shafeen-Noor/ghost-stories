<?php

use App\Models\Story;
use App\Models\Tag;
use App\Models\Vote;

it('filters the archive and remembers the choice in the session', function () {
    $ghost = Story::factory()->create(['title' => 'The tower knocking', 'category' => 'ghost']);
    $legend = Story::factory()->create(['category' => 'urban_legend']);

    $this->get(route('stories.index', ['q' => 'tower', 'category' => 'ghost']))->assertOk()->assertSee($ghost->title)->assertDontSee($legend->title)
        ->assertSessionHas('archive.filters.q', 'tower');
    $this->get(route('stories.index'))->assertOk()->assertSee($ghost->title)->assertDontSee($legend->title);
    $this->get(route('stories.index', ['reset' => 1]))->assertRedirect(route('stories.index'));
    $this->get(route('stories.index'))->assertOk()->assertSee($legend->title);
});

it('filters stories by tag and country', function () {
    $tag = Tag::factory()->create();
    $story = Story::factory()->create();
    $story->location->update(['country_code' => 'ES']);
    $story->tags()->attach($tag);
    $other = Story::factory()->create();

    $this->get(route('stories.index', ['tag' => $tag->id, 'country' => 'ES']))->assertOk()->assertSee($story->title)->assertDontSee($other->title);
});

it('sorts stories by their truth score', function () {
    $popular = Story::factory()->create(['title' => 'Most vouched story']);
    $doubted = Story::factory()->create(['title' => 'Doubted story']);
    Vote::factory()->for($popular)->create(['value' => 1]);
    Vote::factory()->for($doubted)->create(['value' => -1]);

    $this->get(route('stories.index', ['sort' => 'score']))->assertOk()->assertSeeInOrder(['Most vouched story', 'Doubted story']);
});

it('paginates the public archive', function () {
    Story::factory()->count(12)->create();

    $this->get(route('stories.index'))->assertOk()->assertViewHas('stories', fn ($stories) => $stories->count() === 9 && $stories->total() === 12)->assertSee('Next');
});

it('validates filters instead of accepting arbitrary sort fields', function () {
    $this->get(route('stories.index', ['sort' => 'author_id', 'q' => str_repeat('a', 81), 'country' => 'ZZ']))->assertSessionHasErrors(['sort', 'q', 'country']);
});

it('saves and replaces a storys selected tags', function () {
    $story = Story::factory()->create();
    $tag = Tag::factory()->create();
    $payload = ['title' => $story->title, 'body' => $story->body, 'category' => $story->category, 'location_id' => $story->location_id, 'tags' => [$tag->id]];

    $this->actingAs($story->author)->patch(route('userzone.stories.update', $story), $payload)->assertSessionHasNoErrors();
    $this->assertDatabaseHas('story_tag', ['story_id' => $story->id, 'tag_id' => $tag->id]);
    $this->patch(route('userzone.stories.update', $story), array_replace($payload, ['tags' => []]))->assertSessionHasNoErrors();
    $this->assertDatabaseCount('story_tag', 0);
});

it('rejects tags which do not exist', function () {
    $story = Story::factory()->create();

    $this->actingAs($story->author)->patch(route('userzone.stories.update', $story), ['title' => $story->title, 'body' => $story->body, 'category' => 'ghost', 'location_id' => $story->location_id, 'tags' => [999]])->assertSessionHasErrors('tags.0');

    $this->assertDatabaseCount('story_tag', 0);
});
