<?php

namespace App\Policies;

use App\Models\Story;
use App\Models\User;

class StoryPolicy
{
    public function vote(User $user, Story $story): bool
    {
        return ! $story->trashed() && $story->status === 'visible' && $user->id !== $story->author_id;
    }

    public function update(User $user, Story $story): bool
    {
        return $user->id === $story->author_id;
    }

    public function delete(User $user, Story $story): bool
    {
        return $user->id === $story->author_id;
    }

    public function restore(User $user, Story $story): bool
    {
        return $user->id === $story->author_id;
    }
}
