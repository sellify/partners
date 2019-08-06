<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full font-sans antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ Nova::name() }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,800,800i,900,900i" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ mix('app.css', 'vendor/nova') }}">
    <style>
        .min-w-site{
            min-width: 0;
        }
    </style>

    @if(config('analytics.gtm.id'))
    <!-- Google Tag Manager -->
    <script>(function( w, d, s, l, i ){
            w[l] = w[l] || [];
            w[l].push( {
                'gtm.start':
                    new Date().getTime(), event: 'gtm.js'
            } );
            var f = d.getElementsByTagName( s )[0],
                j = d.createElement( s ), dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore( j, f );
        })( window, document, 'script', 'dataLayer', '{{config('analytics.gtm.id')}}' );</script>
    <!-- End Google Tag Manager -->
    @endif
</head>
<body class="bg-40 text-black h-full">
    @if(config('analytics.gtm.id'))
    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id={{config('analytics.gtm.id')}}"
                height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->
    @endif
    <div class="h-full">
        <div class="px-view py-view mx-auto">
            @yield('content')
        </div>
    </div>
</body>
</html>
