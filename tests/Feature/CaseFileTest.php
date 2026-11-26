<?php

use App\CaseFile;
use App\Models\CaseOpinion;
use App\Models\Story;
use App\Models\User;

it('reveals account sections sequentially without leaking unopened text', function () {
    $story = Story::factory()->create(['body' => "An opening account.\n\nA concealed second detail.\n\nA final observation."]);
    $this->get(route('cases.show', $story))->assertOk()->assertSee('An opening account.')->assertDontSee('A concealed second detail.');
    $this->postJson(route('cases.reveal', $story), ['revealed' => 999])->assertOk()->assertJsonPath('html', fn (string $html): bool => str_contains($html, 'A concealed second detail.') && ! str_contains($html, 'A final observation.'));
    $this->post(route('cases.reveal', $story))->assertRedirect();
    $this->get(route('cases.show', $story))->assertOk()->assertSee('A final observation.');
});

it('keeps results sealed until the signed in reader finishes and chooses', function () {
    $story = Story::factory()->create(['body' => "The opening account.\n\nThe later detail."]);
    CaseOpinion::factory()->create(['story_id' => $story->id, 'revision' => sha1($story->body)]);
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('cases.show', $story))->assertDontSee("The community's first impressions");
    $this->postJson(route('cases.opinion', $story), ['choice' => 'natural'])->assertStatus(409);
    $this->post(route('cases.reveal', $story));
    $this->get(route('cases.show', $story))->assertDontSee("The community's first impressions");
    $this->postJson(route('cases.opinion', $story), ['choice' => 'natural', 'user_id' => $story->author_id])->assertOk()->assertJsonPath('html', fn (string $html): bool => str_contains($html, "The community's first impressions"));
    $this->postJson(route('cases.opinion', $story), ['choice' => 'undecided'])->assertOk();
    expect($story->caseOpinions()->where('user_id', $user->id)->count())->toBe(1);
    $this->assertDatabaseHas('case_opinions', ['story_id' => $story->id, 'user_id' => $user->id, 'choice' => 'undecided']);
});

it('resets progress and results when an author changes the account', function () {
    $story = Story::factory()->create(['body' => 'The complete original account.']);
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('cases.opinion', $story), ['choice' => 'natural'])->assertRedirect();
    $story->update(['body' => "A revised opening.\n\nA revised last detail."]);
    $this->get(route('cases.show', $story))->assertOk()->assertDontSee('A revised last detail.')->assertDontSee("The community's first impressions");
});

it('protects hidden and deleted cases and validates interpretation input', function () {
    $story = Story::factory()->create(['status' => 'hidden']);
    $this->get(route('cases.show', $story))->assertNotFound();
    $this->post(route('cases.reveal', $story))->assertNotFound();
    $this->get(route('cases.trail', $story))->assertNotFound();
    $story->update(['status' => 'visible']);
    $this->postJson(route('cases.opinion', $story), ['choice' => 'natural'])->assertUnauthorized();
    $this->actingAs(User::factory()->create())->postJson(route('cases.opinion', $story), ['choice' => ['invalid']])->assertUnprocessable();
    $this->postJson(route('cases.opinion', $story), ['choice' => 'definitely true'])->assertUnprocessable();
    $story->delete();
    $this->get(route('cases.show', $story))->assertNotFound();
});

it('selects a visible featured case and excludes hidden accounts from trails', function () {
    $story = Story::factory()->create(['category' => 'ghost']);
    $related = Story::factory()->create(['category' => 'ghost']);
    $hidden = Story::factory()->create(['category' => 'ghost', 'status' => 'hidden']);
    $this->get(route('cases.tonight'))->assertRedirect();
    $this->get(route('cases.trail', $story))->assertOk()->assertSee($related->title)->assertDontSee($hidden->title);
    expect(app(CaseFile::class)->parts($story))->not->toBeEmpty();
});
