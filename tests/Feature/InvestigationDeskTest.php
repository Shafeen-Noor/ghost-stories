<?php

use App\Models\Comment;
use App\Models\ReadingCollection;
use App\Models\Story;
use App\Models\User;

it('shows only owned saved cases and counts visible new discussion', function () {
    $user = User::factory()->create();
    $collection = ReadingCollection::factory()->create(['user_id' => $user->id]);
    $story = Story::factory()->create();
    $hidden = Story::factory()->create(['status' => 'hidden']);
    $collection->stories()->attach([$story->id, $hidden->id]);
    Comment::factory()->create(['story_id' => $story->id]);
    $moderated = Comment::factory()->create(['story_id' => $story->id, 'status' => 'hidden']);
    Comment::factory()->create(['story_id' => $story->id, 'parent_id' => $moderated->id]);
    $private = Story::factory()->create();
    ReadingCollection::factory()->create()->stories()->attach($private);
    $this->actingAs($user)->withSession(['desk.last_visit.'.$user->id => now()->subDay()->toDateTimeString()])
        ->get(route('dashboard'))->assertOk()->assertSee($story->title)->assertDontSee($hidden->title)->assertDontSee($private->title)
        ->assertViewHas('investigations', fn ($cases) => $cases->count() === 1 && $cases[0]->new_comments === 1);
    $this->get(route('dashboard'))->assertViewHas('investigations', fn ($cases) => $cases[0]->new_comments === 0);
});
