@props(['title' => 'The journal'])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} | Unexplained</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
    <script src="{{ asset('js/editorial.js') }}?v={{ filemtime(public_path('js/editorial.js')) }}" defer></script>
</head>
@php($isWriting = request()->routeIs('userzone.stories.create', 'userzone.stories.edit', 'admin.stories.create'))
@php($isAdmin = auth()->check() && auth()->user()->is_admin)
<body @class(['is-writing' => $isWriting, 'inner-page' => !$isWriting && !request()->routeIs('stories.index'), 'admin-shell' => $isAdmin])>
    <a class="skip-link" href="#main-content">Skip to content</a>
    <header class="publication-header">
        <div class="navigation-row">
            @if($isAdmin)
                <a class="admin-brand" href="{{ route('admin.dashboard') }}">Unexplained <span>Admin</span></a>
                <nav aria-label="Admin navigation">
                    @foreach(['stories' => 'Stories', 'mystery' => 'Featured mystery', 'comments' => 'Comments', 'users' => 'Users', 'places' => 'Places', 'tags' => 'Tags'] as $section => $label)
                        <a href="{{ route('admin.dashboard', ['section' => $section]) }}" @if(request()->routeIs('admin.dashboard') && request('section', 'stories') === $section) aria-current="page" @endif>{{ $label }}</a>
                    @endforeach
                </nav>
                <div class="navigation-actions"><a href="{{ route('stories.index') }}">View website</a><form method="POST" action="{{ route('logout') }}">@csrf<button class="text-button">Log out</button></form></div>
            @else
                <nav aria-label="Main navigation">
                    <a href="{{ route('stories.index') }}" @if(request()->routeIs('stories.index', 'stories.show')) aria-current="page" @endif>Stories</a>
                    <a href="{{ route('discovery.map') }}" @if(request()->routeIs('discovery.map')) aria-current="page" @endif>Map</a>
                    <a href="{{ route('cases.tonight') }}">Featured mystery</a>
                    <a href="{{ route('discovery.random') }}">Surprise me</a>
                    @auth
                        <a href="{{ route('collections.index') }}" @if(request()->routeIs('collections.*')) aria-current="page" @endif>Saved</a>
                        <a href="{{ route('userzone.stories.index') }}" @if(request()->routeIs('userzone.stories.index')) aria-current="page" @endif>My stories</a>
                    @endauth
                </nav>
                <div class="navigation-actions">
                    @auth
                        <a class="write-link" href="{{ route('userzone.stories.create') }}">Write a story</a>
                        <details class="account-menu"><summary>Account</summary><div class="account-dropdown"><strong>{{ auth()->user()->name }}</strong><a href="{{ route('dashboard') }}">Reading activity</a><a href="{{ route('userzone.trash.index') }}">Trash</a><a href="{{ route('profile.edit') }}">Settings</a><form method="POST" action="{{ route('logout') }}">@csrf<button class="text-button">Log out</button></form></div></details>
                    @else
                        <a href="{{ route('login') }}">Log in</a><a class="write-link" href="{{ route('register') }}">Sign up</a>
                    @endauth
                </div>
            @endif
        </div>
        @unless($isAdmin || $isWriting)
            <div class="masthead"><a href="{{ route('stories.index') }}" aria-label="Unexplained home">The Unexplained</a></div>
        @endunless
    </header>
    <main id="main-content">
        @if(session('status'))<p class="notice" role="status"><span aria-hidden="true">✓</span> {{ match(session('status')) { 'profile-updated' => 'Your account details have been saved.', 'password-updated' => 'Your password has been updated.', default => session('status') } }}</p>@endif
        {{ $slot }}
    </main>
    <footer class="publication-footer compact-footer"><span>© {{ now()->year }} The Unexplained</span><a href="https://github.com/Shafeen-Noor/ghost-stories">Source code ↗</a></footer>
</body>
</html>
