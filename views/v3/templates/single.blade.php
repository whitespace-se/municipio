@extends('templates.master')

@section('before-layout')
@stop

@section('above')
    <div class="nav-helper">
        @breadcrumb([
            'list' => \Municipio\Theme\Navigation::breadcrumbData()
        ])
        @endbreadcrumb
        @includeIf('partials.navigation.accessibility')
    </div>
@stop


<!-- >SIDEBAR LEFT  -->
{{-- @section('sidebar-left')
    <div class="sidebar sidebar-left">
        @if (get_field('nav_sub_enable', 'option'))
            {!! $navigation['sidebarMenu'] !!}
        @endif

        @include('partials.sidebar', ['id' => 'left-sidebar'])
        @include('partials.sidebar', ['id' => 'right-sidebar', 'classes' => 'hidden-lg'])
        @include('partials.sidebar', ['id' => 'left-sidebar-bottom'])
    </div>
@stop
--}}

<!-- CONTENT  AREA -->
@section('content')

    @grid([
        "col" => [
            "xs" => [$template['xs'][0],$template['xs'][1]],
            "sm" => [$template['sm'][0],$template['xs'][1]],
            "md" => [$template['md'][0],$template['xs'][1]],
            "lg" => [$template['lg'][0],$template['xs'][1]],
            "xl" => [$template['xl'][0],$template['xs'][1]]
        ],
        "row" => [
            "xs" => [1,2],
            "sm" => [1,2],
            "md" => [1,2],
            "lg" => [1,2],
            "xl" => [1,2]
        ],
        "classList" => ['content']
    ])

        <!-- CONTENT AREA TOP -->
        @include('partials.sidebar', ['id' => 'content-area-top'])

        <!-- >CONTENT ARTICLE -->
        @section('loop')
            @if($post)
                @include('partials.article', (array) $post)
            @endif
        @show

        <!-- CONTENT AREA  -->
        @include('partials.sidebar', ['id' => 'content-area'])

    @endgrid
@stop

<!-- RIGHT SIDEBAR -->
@if ($template['sidebar'])
    @section('sidebar-right')
        @grid([
            "col" => [
                "xs" => [10,13],
                "sm" => [10,13],
                "md" => [10,13],
                "lg" => [10,13],
                "xl" => [10,13]
            ],
            "row" => [
                "xs" => [1,2],
                "sm" => [1,2],
                "md" => [1,2],
                "lg" => [1,2],
                "xl" => [1,2]
            ],
            "classList" => ['sidebar', 'sidebar-right']
        ])
            Höger Sidebar
            @include('partials.sidebar', ['id' => 'right-sidebar'])

        @endgrid
    @stop
@endif


@section('below')
    @include('partials.sidebar', ['id' => 'content-area-bottom'])
@stop
