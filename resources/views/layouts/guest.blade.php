<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <!-- Tailwind CSS & Alpine.js via CDN -->
            <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
            <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.15.1/dist/cdn.min.js"></script>
        @endif
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    </head>
    <body>
        <div class="auth-screen">
            <a class="brand" href="{{ route('stories.index') }}">Ghost Stories</a>
            <div class="auth-box">
                <p class="eyebrow">A circle of storytellers</p>
                <h1>Gather around.</h1>
                {{ $slot }}
            </div>
            <a href="{{ route('stories.index') }}">← Back to the stories</a>
        </div>
    </body>
</html>
