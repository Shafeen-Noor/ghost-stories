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
    </header>
    <main>{{ $slot }}</main>
    <footer>A collection of stories from places that stay with us.</footer>
</body>
</html>
