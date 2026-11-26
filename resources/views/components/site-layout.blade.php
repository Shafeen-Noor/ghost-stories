@props(['title' => 'Ghost Stories'])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} | Ghost Stories</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="site-header">
        <a class="brand" href="{{ route('welcome') }}"><svg aria-hidden="true" viewBox="0 0 30 38" fill="none"><path d="M3 34V15a12 12 0 0 1 24 0v19l-6-4-6 4-6-4z" stroke="currentColor" stroke-width="1.5"/><circle cx="11" cy="16" r="1.4" fill="currentColor"/><circle cx="19" cy="16" r="1.4" fill="currentColor"/></svg>Ghost Stories</a>
        <nav aria-label="Main navigation">
            <a href="{{ route('locations.index') }}">Places</a>
            @can('admin')<a href="{{ route('admin.dashboard') }}">Moderation</a>@endcan
            @auth
                <a href="{{ route('stories.index') }}">The archive</a>
                <a href="{{ route('dashboard') }}">My campfire</a>
                <a href="{{ route('userzone.stories.create') }}">Tell a story ↗</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-button" type="submit">Log out</button>
                </form>
            @else
                <a href="{{ route('stories.index') }}">The archive</a>
                <a href="{{ route('login') }}">Log in</a>
                <a href="{{ route('register') }}">Register</a>
            @endauth
        </nav>
    </header>
    <main>
        @if(session('status'))
            <p class="notice" role="status">{{ match(session('status')) { 'profile-updated' => 'Your account details have been saved.', 'password-updated' => 'Your password has been updated.', default => session('status') } }}</p>
        @endif
        {{ $slot }}
    </main>
    <footer><span>For the things we can't quite explain.</span><small>Read slowly. Leave a light on.</small></footer>
</body>
</html>
