<!DOCTYPE html>
<html class="no-js" dir="ltr" lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>{{ env('APP_NAME') }}</title>

	<meta name="viewport"
					content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover">
	@vite(['resources/scss/style.scss', 'resources/js/app.js'])
	@yield('head')
</head>

<body>

<!-- Page wrapper-->
<main class="page-wrapper">

	@yield('content')

</main>

@yield('foot')
</body>

</html>