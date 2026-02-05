<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, height=device-height, viewport-fit=cover">
        <meta name="description" content="">
        <meta name="keywords" content="Lichtstoet Rozenberg, Rozenberg, Lichtstoet, Lightshow, Oudste lichtstoet" />
        <meta name="author" content="Tomas Marlein">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="manifest" href="/manifest.webmanifest">
        <meta name="theme-color" content="#EF7900">

        <!-- iOS PWA support -->
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <link rel="apple-touch-icon" href="/icons/icon-192.png">

        <title>@yield('title', env('APP_NAME'))</title>

        {{--  Font awesome  --}}
        <script src="https://kit.fontawesome.com/4baa8ea5d9.js" crossorigin="anonymous"></script>

        {{--  Google fonts  --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">

        @notifyCss

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orestbida/cookieconsent@3.0.1/dist/cookieconsent.css">

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

        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(console.error);
                });
            }
        </script>
    </body>
</html>
