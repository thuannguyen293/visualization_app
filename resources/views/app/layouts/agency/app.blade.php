<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">

{{--<link rel="shortcut icon" href="/favicon.ico">--}}

<!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@if(isset($title)) {{$title}} @else {{config('app.name', 'Visualization App')}} @endif</title>

    <!-- STYLES -->
@section('styles')
    @include('app.layouts.agency.partial.styles')
@show
<!-- END STYLES -->

    <!-- SCRIPT HEADER -->
@section('script_header')
    @include('app.layouts.agency.partial.script_header')
@show
<!-- END SCRIPT HEADER -->
</head>
<body>
<div id="app">
    <!-- HEADER -->
@section('header')
    @include('app.layouts.agency.partial.header')
@show
<!-- END HEADER -->

    <!-- CONTENT -->
    @yield('content')
    <!-- END CONTENT -->

    <!-- FOOTER -->
@section('footer')
    @include('app.layouts.agency.partial.footer')
@show
<!-- END FOOTER -->
</div>
<!-- SCRIPT FOOTER -->
@section('script_footer')
    @include('app.layouts.agency.partial.script_footer')
@show
<!-- END SCRIPT FOOTER -->
</body>
</html>
