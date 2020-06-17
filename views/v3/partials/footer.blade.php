
@if (is_active_sidebar('bottom-sidebar'))
    @includeIf('partials.sidebar', ['id' => 'bottom-sidebar'])
@endif

@footer([
    'logotype' => $logotype->negative['url'],
    'logotypeHref' => $homeUrl
])
@endfooter