<?php

use App\ArchiveStatistics;
use App\Models\Story;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;

it('reuses cached archive statistics without querying the database again', function () {
    Story::factory()->create();
    $stats = app(ArchiveStatistics::class);
    $stats->forget();
    expect($stats->get()['stories'])->toBe(1);
    DB::enableQueryLog();

    $again = $stats->get();

    expect($again['stories'])->toBe(1);
    expect(DB::getQueryLog())->toBe([]);
    DB::disableQueryLog();
});

it('invalidates statistics on publishing hiding deleting and restoring stories', function () {
    $stats = app(ArchiveStatistics::class);
    $stats->forget();
    expect($stats->get()['stories'])->toBe(0);
    $story = Story::factory()->create();
    expect($stats->get()['stories'])->toBe(1);
    $story->update(['status' => 'hidden']);
    expect($stats->get()['stories'])->toBe(0);
    $story->update(['status' => 'visible']);
    $story->delete();
    expect($stats->get()['stories'])->toBe(0);
    $story->restore();
    expect($stats->get()['stories'])->toBe(1);
});

it('invalidates the public vote total when a vote is saved or removed', function () {
    $story = Story::factory()->create();
    $stats = app(ArchiveStatistics::class);
    expect($stats->get()['votes'])->toBe(0);
    $vote = Vote::factory()->for($story)->create();
    expect($stats->get()['votes'])->toBe(1);
    $vote->delete();
    expect($stats->get()['votes'])->toBe(0);
});

it('does not cache one readers vote controls for another reader', function () {
    $vote = Vote::factory()->create();

    $this->actingAs($vote->user)->get(route('stories.index'))->assertOk()->assertSee('Remove vote');
    $this->actingAs(User::factory()->create())->get(route('stories.index'))->assertOk()->assertDontSee('Remove vote');
});
