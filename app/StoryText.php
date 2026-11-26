<?php

namespace App;

use Illuminate\Support\Str;

class StoryText
{
    public function render(string $body): string
    {
        return Str::markdown($body, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 20,
        ]);
    }

    public function excerpt(string $body, int $length = 160): string
    {
        return Str::limit(trim(html_entity_decode(strip_tags($this->render($body)), ENT_QUOTES | ENT_HTML5, 'UTF-8')), $length);
    }
}
