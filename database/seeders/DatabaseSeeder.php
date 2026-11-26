<?php

namespace Database\Seeders;

use App\Models\CaseOpinion;
use App\Models\Comment;
use App\Models\Location;
use App\Models\ReadingCollection;
use App\Models\Story;
use App\Models\Tag;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstWhere('email', 'admin@admin.com') ?? User::factory()->admin()->create([
            'name' => 'Demo Administrator', 'email' => 'admin@admin.com', 'password' => 'password',
        ]);
        $admin->forceFill(['is_admin' => true, 'is_banned' => false, 'password' => 'password'])->save();
        $readers = collect(range(1, 3))->map(fn (int $number): User => User::firstWhere('email', 'reader'.$number.'@example.test') ?? User::factory()->create([
            'name' => 'Campfire Reader '.$number, 'email' => 'reader'.$number.'@example.test',
        ])
        );
        $tags = collect(['Haunted', 'Abandoned', 'Night journeys', 'Unexplained'])->map(fn (string $name): Tag => Tag::firstWhere('name', $name) ?? Tag::factory()->create(['name' => $name, 'slug' => (string) str($name)->slug()])
        );

        $author = User::firstOrCreate(['email' => 'nightwatcher@example.test'], [
            'name' => 'Night Watcher',
            'password' => Str::random(40),
        ]);

        $stories = [
            [
                'location' => 'Bellweather House',
                'description' => 'A fictional empty house at the end of a quiet country lane.',
                'title' => 'Someone is still upstairs',
                'category' => 'ghost',
                'body' => 'My grandmother always said the empty house at the end of the lane was not really empty. We used to laugh about it.

Last October, a storm knocked a branch through its front window. I went over to cover it before the rain got in. The hallway smelled of damp wood and old wallpaper.

Then I heard someone walking upstairs. Three slow steps, a pause, then three more. I called out. The steps stopped.

I shone my torch up the staircase. Halfway up, the stairs ended. The entire upper floor had collapsed years ago.

I left the board by the door. When I looked back from the gate, the upstairs light came on.',
            ],
            [
                'location' => 'Ashwick Station',
                'description' => 'A fictional railway platform closed since the winter of 1962.',
                'title' => 'The last train never left',
                'category' => 'urban_legend',
                'body' => 'There is no track at Ashwick anymore. Just a platform, a broken clock and a bench that nobody sits on.

The clock stopped at 11:47. Locals say that is when the last train was supposed to arrive.

One evening I was walking my dog past the station when she froze. Somewhere beyond the trees, a whistle sounded. The platform lights flickered, one after another.

A woman was sitting on the bench, holding a small suitcase. She asked if I knew when the train was coming.

I blinked and she was gone. The clock had moved forward by one minute.',
            ],
            [
                'location' => 'Greywater Pier',
                'description' => 'A fictional wooden pier on a foggy stretch of coast.',
                'title' => 'A light beneath the water',
                'category' => 'ghost',
                'body' => 'The fishermen call it the lantern. A pale green light that drifts beneath the pier when the fog comes in.

I thought it was a reflection until the night I watched it move against the tide. It stopped directly below me.

For a moment, the water was perfectly clear. I could see a hand holding the light. Then another hand reached up, palm open, as if asking me to help.

I ran back along the pier. Behind me, wet footsteps followed for exactly seven paces.

In the morning, there were seven dark marks in the wood. They have never dried.',
            ],
        ];

        $stories[] = [
            'location' => 'The north stairwell',
            'description' => 'A fictional stairwell in an old apartment building. Demo data for the project.',
            'title' => 'The floor above the top floor', 'category' => 'conspiracy',
            'body' => 'The lift has six buttons, but last night a seventh lit up. I watched it climb past my floor. When the doors opened again, someone had left a key on the floor. Its label read: Do not go upstairs.',
        ];

        $collection = $admin->readingCollections()->firstWhere('name', 'Investigate later') ?? ReadingCollection::factory()->create(['user_id' => $admin->id, 'name' => 'Investigate later']);

        foreach ($stories as $index => $entry) {
            $location = Location::firstWhere('name', $entry['location']) ?? Location::factory()->create([
                'name' => $entry['location'], 'description' => $entry['description'],
            ]);
            if ($location->latitude === null) {
                $coordinates = [[55.9533, -3.1883], [51.5074, -0.1278], [38.7223, -9.1393], [41.3874, 2.1686]][$index];
                $location->update(['latitude' => $coordinates[0], 'longitude' => $coordinates[1]]);
            }
            $owner = $index === 3 ? $admin : $author;
            $story = Story::where('title', $entry['title'])->where('author_id', $owner->id)->first()
                ?? Story::factory()->create([
                    'title' => $entry['title'], 'body' => $entry['body'], 'category' => $entry['category'],
                    'author_id' => $owner->id, 'location_id' => $location->id,
                ]);
            $collection->stories()->syncWithoutDetaching([$story->id]);
            foreach ($readers as $readerIndex => $reader) {
                if (! $story->caseOpinions()->where('user_id', $reader->id)->exists()) {
                    CaseOpinion::factory()->create(['story_id' => $story->id, 'user_id' => $reader->id, 'choice' => array_keys(CaseOpinion::CHOICES)[$readerIndex], 'revision' => sha1($story->body)]);
                }
            }
            $story->tags()->syncWithoutDetaching([$tags[$index % $tags->count()]->id]);

            foreach ($readers as $readerIndex => $reader) {
                if (! $story->votes()->where('user_id', $reader->id)->exists()) {
                    Vote::factory()->create(['story_id' => $story->id, 'user_id' => $reader->id, 'value' => $readerIndex === 2 ? -1 : 1]);
                }
            }
            foreach (['explanation' => 'Could this be an ordinary sound travelling through the building? A recording at the same time each evening might help.', 'experience' => 'I had a similar experience on a late walk. I cannot explain it, but the sudden silence is what I remember.'] as $kind => $body) {
                if (! $story->comments()->where('kind', $kind)->exists()) {
                    $thread = Comment::factory()->create(['story_id' => $story->id, 'author_id' => $readers[1]->id, 'kind' => $kind, 'body' => $body]);
                    Comment::factory()->create(['story_id' => $story->id, 'author_id' => $readers[2]->id, 'parent_id' => $thread->id, 'body' => 'That is an interesting detail. Was anyone else there at the time?']);
                }
            }
            if (! $story->comments()->where('author_id', $readers[0]->id)->exists()) {
                Comment::factory()->create([
                    'story_id' => $story->id, 'author_id' => $readers[0]->id,
                    'body' => 'I would like to hear more about this place. Has anyone else had a similar experience?',
                ]);
            }
        }
    }
}
