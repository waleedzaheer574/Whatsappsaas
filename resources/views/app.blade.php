<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="ChatFlow AI automates WhatsApp replies, lead capture, broadcasts, CRM and analytics for modern teams.">
        <title inertia>{{ config('app.name', 'ChatFlow AI') }}</title>
        <link rel="icon" type="image/svg+xml" href="/chatflow-favicon.svg">
        <link rel="shortcut icon" href="/chatflow-favicon.svg">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />
        @vite(['resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
