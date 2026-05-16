<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, height=device-height, viewport-fit=cover">
        <meta name="description" content="Eventicks ticketscanner">
        <meta name="keywords" content="Eventicks">
        <meta name="author" content="Eventicks">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#EF7900">

        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

        <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96">
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        <link rel="shortcut icon" href="/favicon.ico">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">

        <title>@yield('title', config('app.name'))</title>

        <script src="https://kit.fontawesome.com/4baa8ea5d9.js" crossorigin="anonymous" defer></script>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">

        @notifyCss

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @yield('css_after')

        @laravelPWA
    </head>
    <body>
        @unless (request()->routeIs('scan.result'))
            @include('shared.navbar')
        @endunless

        @yield('main', 'Page under construction ...')

        @yield('script_after')

        <x-notify::notify />
        @notifyJs
    </body>
</html>
