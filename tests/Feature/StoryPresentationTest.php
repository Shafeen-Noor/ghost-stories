<?php

use App\Models\Story;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function storyTestImage(string $name = 'image.png'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aDWQAAAAASUVORK5CYII='));
}

it('stores cover background and gallery images with presentation choices', function () {
    Storage::fake('local');
    $story = Story::factory()->create();

    $this->actingAs($story->author)->patch(route('userzone.stories.update', $story), [
        'title' => $story->title, 'body' => $story->body, 'category' => 'ghost', 'location_id' => $story->location_id,
        'cover_image' => storyTestImage(), 'background_image' => storyTestImage('background.png'),
        'gallery' => [storyTestImage('gallery.png')], 'reading_theme' => 'dark', 'cover_layout' => 'full',
    ])->assertSessionHasNoErrors();

    $story->refresh();
    Storage::disk('local')->assertExists([$story->cover_path, $story->background_path, $story->gallery_paths[0]]);
    expect($story->reading_theme)->toBe('dark');
    $this->get(route('stories.media', [$story, 'cover']))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
});

it('replaces cover images and removes selected gallery images', function () {
    Storage::fake('local');
    Storage::disk('local')->put('story-images/old.png', 'old image');
    Storage::disk('local')->put('story-images/gallery.png', 'gallery');
    $story = Story::factory()->create(['cover_path' => 'story-images/old.png', 'gallery_paths' => ['story-images/gallery.png']]);

    $this->actingAs($story->author)->patch(route('userzone.stories.update', $story), [
        'title' => $story->title, 'body' => $story->body, 'category' => 'ghost', 'location_id' => $story->location_id,
        'cover_image' => storyTestImage(), 'remove_gallery' => [0],
    ])->assertSessionHasNoErrors();

    Storage::disk('local')->assertMissing(['story-images/old.png', 'story-images/gallery.png']);
    expect($story->fresh()->gallery_paths)->toBe([]);
});

it('rejects fake images unsupported types and arbitrary presentation values', function () {
    $story = Story::factory()->create();

    $this->actingAs($story->author)->patch(route('userzone.stories.update', $story), [
        'title' => $story->title, 'body' => $story->body, 'category' => 'ghost', 'location_id' => $story->location_id,
        'cover_image' => UploadedFile::fake()->createWithContent('photo.jpg', '<?php echo "unsafe";'),
        'background_image' => UploadedFile::fake()->createWithContent('photo.svg', '<svg></svg>'),
        'reading_theme' => 'javascript', 'cover_preset' => '../../file',
    ])->assertSessionHasErrors(['cover_image', 'background_image', 'reading_theme', 'cover_preset']);
    expect($story->fresh()->cover_path)->toBeNull();
});

it('never accepts another users upload or a client supplied path', function () {
    Storage::fake('local');
    $story = Story::factory()->create();
    $payload = ['title' => $story->title, 'body' => $story->body, 'category' => 'ghost', 'location_id' => $story->location_id, 'cover_image' => storyTestImage()];

    $this->actingAs(User::factory()->create())->patch(route('userzone.stories.update', $story), $payload)->assertForbidden();
    expect(Storage::disk('local')->allFiles())->toBe([]);
    unset($payload['cover_image']);
    $this->actingAs($story->author)->patch(route('userzone.stories.update', $story), $payload + ['cover_path' => '../../private'])->assertSessionHasNoErrors();
    expect($story->fresh()->cover_path)->toBeNull();
});

it('limits the total gallery size when adding to existing images', function () {
    $story = Story::factory()->create(['gallery_paths' => array_fill(0, 6, 'image.png')]);

    $this->actingAs($story->author)->patch(route('userzone.stories.update', $story), [
        'title' => $story->title, 'body' => $story->body, 'category' => 'ghost', 'location_id' => $story->location_id,
        'gallery' => [storyTestImage()],
    ])->assertSessionHasErrors('gallery');
});

it('hides image assets with their story and retains them through soft deletion', function () {
    Storage::fake('local');
    Storage::disk('local')->put('story-images/cover.png', 'image');
    $story = Story::factory()->create(['status' => 'hidden', 'cover_path' => 'story-images/cover.png']);

    $this->get(route('stories.media', [$story, 'cover']))->assertNotFound();
    $this->actingAs($story->author)->get(route('stories.media', [$story, 'cover']))->assertOk();
    $story->delete();
    $this->get(route('stories.media', [$story, 'cover']))->assertNotFound();
    Storage::disk('local')->assertExists('story-images/cover.png');
    $story->forceDelete();
    Storage::disk('local')->assertMissing('story-images/cover.png');
});

it('previews formatted writing without allowing executable markup', function () {
    $this->actingAs(User::factory()->create())->postJson(route('stories.preview'), [
        'body' => '**A bold opening** and <script>alert(1)</script> [unsafe](javascript:alert(1))',
    ])->assertOk()->assertJsonPath('html', fn (string $html): bool => str_contains($html, '<strong>A bold opening</strong>') && ! str_contains($html, '<script>') && ! str_contains($html, 'href="javascript:'));
});

it('requires authentication and validates editor previews', function () {
    $this->postJson(route('stories.preview'), ['body' => 'Text'])->assertUnauthorized();
    $this->actingAs(User::factory()->create())->postJson(route('stories.preview'), ['body' => str_repeat('a', 30001)])->assertUnprocessable();
});

it('renders formatted story content on its public page', function () {
    $story = Story::factory()->create(['body' => "## The hallway\n\n**The knocking never stopped.**\n\n<script>alert(1)</script>"]);

    $this->get(route('stories.show', $story))->assertOk()
        ->assertSee('<h2>The hallway</h2>', false)
        ->assertSee('<strong>The knocking never stopped.</strong>', false)
        ->assertDontSee('<script>alert(1)</script>', false);
});
