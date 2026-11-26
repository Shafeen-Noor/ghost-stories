<?php

namespace App\Http\Controllers\Userzone;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $published = $user->stories()->visible()->count();
        $trashed = $user->stories()->onlyTrashed()->count();
        $stories = $user->stories()->withVotingFor($user)->with(['author', 'location', 'tags'])->latest()->take(3)->get();

        return view('userzone.dashboard', compact('user', 'published', 'trashed', 'stories'));
    }
}
