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
        <a class="brand" href="{{ route('welcome') }}">Ghost Stories</a>
        <nav aria-label="Main navigation">
            @auth
                <a href="{{ route('stories.index') }}">The archive</a>
                <a href="{{ route('userzone.stories.index') }}">My stories</a>
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
            <p class="notice" role="status">{{ session('status') }}</p>
        @endif
        {{ $slot }}
    </main>
    <footer>A collection of stories from places that stay with us.</footer>
</body>
</html>
