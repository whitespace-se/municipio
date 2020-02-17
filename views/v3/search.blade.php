@extends('templates.master')
@section('layout')
    @switch($activeSearchEngine)
        @case("google")
            @includeIf('partials.search.google')
            @break
        @case("algolia")
            @includeIf('partials.search.algolia')
            @break
        @default
            @includeIf('partials.search.wp')
    @endswitch
@stop
