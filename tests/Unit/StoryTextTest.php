<?php

use App\StoryText;

it('creates a readable excerpt without Markdown formatting', function () {
    $text = new StoryText;

    expect($text->excerpt('**A strange sound** near the station.'))->toBe('A strange sound near the station.');
});
