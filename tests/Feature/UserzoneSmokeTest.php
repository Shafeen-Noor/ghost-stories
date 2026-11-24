<?php

use App\Models\User;

it('opens the story pages for a signed in user', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('userzone.stories.index'))->assertOk();
    $this->get(route('userzone.stories.create'))->assertOk();
    $this->get(route('userzone.locations.create'))->assertOk();
});

it('redirects guests to login', function () {
    $this->get(route('userzone.stories.index'))->assertRedirect(route('login'));
    $this->get(route('userzone.stories.create'))->assertRedirect(route('login'));
    $this->get(route('userzone.locations.create'))->assertRedirect(route('login'));
});
