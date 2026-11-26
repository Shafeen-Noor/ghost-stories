<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
    </head>
    <body>
        <div class="auth-screen">
            <a class="brand" href="{{ route('stories.index') }}">Unexplained</a>
            <div class="auth-box">
                <p class="eyebrow">A circle of storytellers</p>
                <h1>Gather around.</h1>
                {{ $slot }}
            </div>
            <a href="{{ route('stories.index') }}">← Back to the stories</a>
        </div>
    </body>
</html>
