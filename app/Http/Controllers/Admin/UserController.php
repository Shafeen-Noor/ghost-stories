<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is_admin || $user->is($request->user()), 403);
        $validated = $request->validate(['is_banned' => ['required', 'boolean']]);
        $user->is_banned = $validated['is_banned'];
        $user->save();

        return back()->with('status', $user->is_banned ? 'User suspended.' : 'User access restored.');
    }
}
