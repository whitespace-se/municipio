<?php
$get_theme = wp_get_theme();
$active_theme = $get_theme->get( 'TextDomain' ) ;
?>

<div class="mobile-header">
    <div class="mobile-header__logotype">
        {!! municipio_get_logotype(get_field('header_logotype', 'option'), get_field('logotype_tooltip', 'option'), false, get_field('header_tagline_enable', 'option')) !!}
    </div>
    <button aria-expanded="false" aria-controls="site-header-container" class="hidden-print js-menu-toggle">
        <span class="button-container">
            <svg class="icon-default" width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v-2z" fill-rule="evenodd"/></svg>
            <svg  class="icon-close"  width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M20 2l-2-2-8 8-8-8-2 2 8 8-8 8 2 2 8-8 8 8 2-2-8-8z" fill-rule="evenodd"/></svg>
            <span><?php _e('Menu', $active_theme); ?></span>
        </span>
    </button>
</div>


<div id="site-header-container" class="site-header-container hidden-print">
    <div class="container">
        <div class="grid">
            <div class="grid-xs-12 grid-lg-3 site-header__logotype">
                {!! municipio_get_logotype(get_field('header_logotype', 'option'), get_field('logotype_tooltip', 'option'), false, get_field('header_tagline_enable', 'option')) !!}
            </div>
            <div class="grid-xs-12 grid-lg-9 hidden-print site-header__nav-section">
                <div class="help-menu-container">
                    <nav class="menu-help" aria-label="Hjälpnavigation">
                        <ul class="nav nav-help nav-horizontal">
                            <li class="help-menu-top__wrapper">
                                {!!
                                    wp_nav_menu(array(
                                        'theme_location' => 'help-menu',
                                        'menu_id' => 'help-menu-top',
                                        'echo' => 'echo',
                                        'before' => '',
                                        'after' => '',
                                        'link_before' => '',
                                        'link_after' => '',
                                        'depth' => 1,
                                        'fallback_cb' => '__return_false',
                                        'container' => 'li'
                                ));
                                !!}
                            </li>
                        </ul>
                    </nav>
                </div>

                <div class="search-wrapper">
                    @include('partials.search.top-search', array(
                        'isTopNav' => true
                    ))
                </div>
            </div>
        </div>
    </div>

    @if (get_field('nav_primary_enable', 'option') === true)
        <nav class="primary-navigation hidden-print">
            <div id="main-menu" class="primary-navigation__container clearfix">
                {!! $navigation['mainMenu'] !!}
            </div>
        </nav>
        <nav class="secondary-navigation hidden-print">
            <div id="secondary-menu" class="secondary-navigation__container clearfix">
                @include('partials.mobile-menu')
            </div>
        </nav>

    @endif
</div>
