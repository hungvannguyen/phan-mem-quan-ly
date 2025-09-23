<!DOCTYPE html>
<html class="no-js" dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ env('APP_NAME') }}</title>

    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- <link rel="icon" href=""> -->
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/style.css', 'resources/js/app.js'])
    @yield('partials.head')
</head>

<body>
    @include('partials.navbar')

    <!-- Page wrapper-->

    @yield('content')

    @include('partials.footer')
    @include('partials.foot')
</body>

</html>
