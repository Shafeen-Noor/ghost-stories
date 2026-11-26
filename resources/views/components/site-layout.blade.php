@props(['title' => 'The journal'])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} | Unexplained</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
    <script src="{{ asset('js/editorial.js') }}?v={{ filemtime(public_path('js/editorial.js')) }}" defer></script>
</head>
<body @if(request()->routeIs('userzone.stories.create', 'userzone.stories.edit')) class="is-writing" @endif>
    <a class="skip-link" href="#main-content">Skip to content</a>
    <header class="publication-header">
        <div class="navigation-row">
            <nav aria-label="Main navigation"><a href="{{ route('cases.tonight') }}">Tonight’s case</a><a href="{{ route('stories.index') }}" @if(request()->routeIs('stories.*')) aria-current="page" @endif>Stories</a><a href="{{ route('discovery.map') }}" @if(request()->routeIs('discovery.map')) aria-current="page" @endif>Mystery map</a><a href="{{ route('locations.index') }}" @if(request()->routeIs('locations.*')) aria-current="page" @endif>Places</a><a href="{{ route('discovery.random') }}">Surprise me ↗</a></nav>
            <div class="navigation-actions">
                @auth
                    <a class="write-link" href="{{ route('userzone.stories.create') }}">Write a story <span aria-hidden="true">↗</span></a>
                    <details class="account-menu"><summary><span class="avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><span class="account-label">My account</span><span aria-hidden="true">⌄</span></summary><div class="account-dropdown"><strong>{{ auth()->user()->name }}</strong><a href="{{ route('dashboard') }}">Investigation desk</a><a href="{{ route('collections.index') }}">Saved collections</a><a href="{{ route('userzone.stories.index') }}">My stories</a><a href="{{ route('userzone.trash.index') }}">Trash</a><a href="{{ route('profile.edit') }}">Account settings</a>@can('admin')<a href="{{ route('admin.dashboard') }}">Moderation</a>@endcan<form method="POST" action="{{ route('logout') }}">@csrf<button class="text-button">Log out</button></form></div></details>
                @else
                    <a href="{{ route('login') }}">Log in</a><a class="write-link" href="{{ route('register') }}">Join the journal ↗</a>
                @endauth
            </div>
        </div>
        <div class="utility-bar"><span><i class="live-dot" aria-hidden="true"></i> AN INDEPENDENT JOURNAL OF THE UNEXPLAINED</span><span>{{ now()->format('l, F j, Y') }}</span></div>
        <div class="masthead"><a href="{{ route('stories.index') }}" aria-label="Unexplained home">The Unexplained<span class="masthead-star" aria-hidden="true">✳</span></a></div>
    </header>
    <main id="main-content">
        @if(session('status'))<p class="notice" role="status"><span aria-hidden="true">✓</span> {{ match(session('status')) { 'profile-updated' => 'Your account details have been saved.', 'password-updated' => 'Your password has been updated.', default => session('status') } }}</p>@endif
        {{ $slot }}
    </main>
    <footer class="publication-footer"><div><a class="footer-brand" href="{{ route('stories.index') }}">UNEX<br>PLAINED.</a><p>Some stories stay with you.<br>These are the ones worth sharing.</p></div><div class="footer-links"><a href="{{ route('stories.index') }}">Read the journal</a><a href="{{ route('locations.index') }}">Explore places</a><a href="{{ route('userzone.stories.create') }}">Share your story</a><small>Independent stories. Open minds.<br>© {{ now()->year }} Unexplained</small></div></footer>
</body>
</html>
