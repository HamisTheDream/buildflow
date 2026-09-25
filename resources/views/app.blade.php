<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js'])
    @inertiaHead

    @php
    $site_icon = cache()->remember('site_icon_favicon', 3600, function () {
    return \App\Models\Setting::where('key', 'site_icon')->value('value');
    });
    @endphp
    @if($site_icon)
    <link rel="icon" href="{{ \Illuminate\Support\Facades\Storage::disk(config('filesystems.uploads_disk'))->url($site_icon) }}">
    @else
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>