<header class="site-header {{isset($classnames) ? is_array($classnames) ? implode(' ', $classnames) : $classnames : ''}}" id="site-header">
    {{-- Search Form --}}
    @section('search-form')
        {{-- TODO: Implement search form --}}
    @show

    {{-- Navbars --}}
    @section('navigation')

        {{-- Top Navigation --}}
        @yield('top-navigation')
        
        {{-- Primary Navigation --}}
        @yield('primary-navigation')
        
        {{-- Secondary Navigation --}}
        @yield('secondary-navigation')

    @show

    {{-- Mobile Navigation --}}
    @section('mobile-navigation')
        @sidebar([
                'logo'          => $logotype->standard['url'],
                'items'         => $primaryMenuItems,
                'pageId'        => $pageID,
                'classList'     => [
                    'l-docs--sidebar',
                    'c-sidebar--fixed',
                    'u-visibility--hidden@md',
                    'u-visibility--hidden@lg',
                    'u-visibility--hidden@xl'
                ],
                
                'attributeList' => [
                    'js-toggle-item'    => 'js-mobile-sidebar',
                    'js-toggle-class'   => 'c-sidebar--collapsed'
                ],
                'endpoints'     => [
                    'children'          => $homeUrlPath . '/wp-json/municipio/v1/navigation/children',
                    'active'            => $homeUrlPath . '/wp-json/municipio/v1/navigation/active'
                ],
            ])
        @endsidebar
    @show

    @section('helper-navigation')
        @includeIf('partials.navigation.helper')
    @show
</header>

@includeIf('partials.hero')
@includeIf('partials.sidebar', ['id' => 'top-sidebar'])
