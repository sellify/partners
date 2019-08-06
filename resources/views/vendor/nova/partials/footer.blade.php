<p class="mt-8 text-center text-xs text-80">
    <a href="https://sellify.dev" target="_blank" class="text-primary dim no-underline">Sellify</a>
    <span class="px-1">&middot;</span>
    &copy; {{ date('Y') }} Sellify - By Nivesh Saharan
    <span class="px-1">&middot;</span>
    v{{ Laravel\Nova\Nova::version() }}
</p>
<style>
    .min-w-site {
        min-width: 0 !important;
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

@if(config('analytics.gtm.id'))
    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id={{config('analytics.gtm.id')}}"
                height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->
@endif