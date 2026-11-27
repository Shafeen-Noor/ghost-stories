<?php

use App\CaseFile;
use App\Models\Story;
use App\Models\User;

it('lets only admins choose a featured mystery and replaces the previous selection', function () {
    $old = Story::factory()->create(['is_featured' => true]);
    $new = Story::factory()->create();
    $this->actingAs(User::factory()->create())->patch(route('admin.stories.feature', $new), ['featured' => true])->assertForbidden();
    $this->actingAs(User::factory()->admin()->create())->patch(route('admin.stories.feature', $new), ['featured' => true])->assertRedirect();
    expect($old->fresh()->is_featured)->toBeFalse();
    expect(app(CaseFile::class)->featured()->id)->toBe($new->id);
    $this->patch(route('admin.stories.feature', $new), ['featured' => false])->assertRedirect();
    expect(app(CaseFile::class)->featured())->toBeNull();
});

it('rejects hidden mysteries and invalid feature choices', function () {
    $this->actingAs(User::factory()->admin()->create());
    $hidden = Story::factory()->create(['status' => 'hidden']);
    $this->patch(route('admin.stories.feature', $hidden), ['featured' => true])->assertUnprocessable();
    $visible = Story::factory()->create();
    $this->patch(route('admin.stories.feature', $visible), ['featured' => 'bad'])->assertSessionHasErrors('featured');
});

it('keeps publishing separate from the admin selection and routes admins to their workspace', function () {
    $this->actingAs(User::factory()->admin()->create());
    $this->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
    $this->get(route('admin.stories.create'))->assertOk()->assertSee(route('admin.stories.store'), false);
    $this->get(route('admin.dashboard', ['section' => 'mystery']))->assertOk()->assertSee('Write a mystery')->assertDontSee('Suspend account');
    $this->get(route('admin.dashboard', ['section' => 'invalid']))->assertSessionHasErrors('section');
});
