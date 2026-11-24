<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Story;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
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
                'body' => "My grandmother always said the empty house at the end of the lane was not really empty. We used to laugh about it.

Last October, a storm knocked a branch through its front window. I went over to cover it before the rain got in. The hallway smelled of damp wood and old wallpaper.

Then I heard someone walking upstairs. Three slow steps, a pause, then three more. I called out. The steps stopped.

I shone my torch up the staircase. Halfway up, the stairs ended. The entire upper floor had collapsed years ago.

I left the board by the door. When I looked back from the gate, the upstairs light came on.",
            ],
            [
                'location' => 'Ashwick Station',
                'description' => 'A fictional railway platform closed since the winter of 1962.',
                'title' => 'The last train never left',
                'category' => 'urban_legend',
                'body' => "There is no track at Ashwick anymore. Just a platform, a broken clock and a bench that nobody sits on.

The clock stopped at 11:47. Locals say that is when the last train was supposed to arrive.

One evening I was walking my dog past the station when she froze. Somewhere beyond the trees, a whistle sounded. The platform lights flickered, one after another.

A woman was sitting on the bench, holding a small suitcase. She asked if I knew when the train was coming.

I blinked and she was gone. The clock had moved forward by one minute.",
            ],
            [
                'location' => 'Greywater Pier',
                'description' => 'A fictional wooden pier on a foggy stretch of coast.',
                'title' => 'A light beneath the water',
                'category' => 'ghost',
                'body' => "The fishermen call it the lantern. A pale green light that drifts beneath the pier when the fog comes in.

I thought it was a reflection until the night I watched it move against the tide. It stopped directly below me.

For a moment, the water was perfectly clear. I could see a hand holding the light. Then another hand reached up, palm open, as if asking me to help.

I ran back along the pier. Behind me, wet footsteps followed for exactly seven paces.

In the morning, there were seven dark marks in the wood. They have never dried.",
            ],
        ];

        foreach ($stories as $story) {
            $location = Location::firstOrCreate(['name' => $story['location']], [
                'description' => $story['description'],
            ]);

            Story::firstOrCreate(['title' => $story['title'], 'author_id' => $author->id], [
                'body' => $story['body'],
                'category' => $story['category'],
                'location_id' => $location->id,
            ]);
        }
    }
}
