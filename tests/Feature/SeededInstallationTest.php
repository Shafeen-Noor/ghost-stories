<?php

use App\Models\CaseOpinion;
use App\Models\Comment;
use App\Models\Location;
use App\Models\ReadingCollection;
use App\Models\Story;
use App\Models\Tag;
use App\Models\User;
use App\Models\Vote;

it('seeds every model and the required administrator on a clean database', function () {
    $this->seed();
    $admin = User::where('email', 'admin@admin.com')->firstOrFail();
    expect($admin->is_admin)->toBeTrue();
    foreach ([User::class, Story::class, Location::class, Comment::class, Vote::class, Tag::class, ReadingCollection::class, CaseOpinion::class] as $model) {
        expect($model::count())->toBeGreaterThan(0);
    }

    $this->get(route('welcome'))->assertRedirect(route('stories.index'));
    $this->get(route('stories.index'))->assertOk()->assertSee('Someone is still upstairs');
    $this->post(route('login'), ['email' => 'admin@admin.com', 'password' => 'password'])->assertSessionHasNoErrors()->assertRedirect();
    $this->get(route('admin.dashboard'))->assertOk();
    $this->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
    $this->get(route('userzone.stories.index'))->assertOk()->assertSee('The floor above the top floor');
    $this->get(route('profile.edit'))->assertOk();
});

it('can run the seeder again without duplicating sample data', function () {
    $this->seed();
    $counts = [Story::count(), Location::count(), User::count(), Comment::count(), Vote::count(), Tag::count(), ReadingCollection::count(), CaseOpinion::count()];

    $this->seed();

    expect([Story::count(), Location::count(), User::count(), Comment::count(), Vote::count(), Tag::count(), ReadingCollection::count(), CaseOpinion::count()])->toBe($counts);
});
