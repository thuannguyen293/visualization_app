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
        @include('app.layouts.partial.styles')
    @show
    <!-- END STYLES -->

    <!-- SCRIPT HEADER -->
    @section('script_header')
        @include('app.layouts.partial.script_header')
    @show
    <!-- END SCRIPT HEADER -->
</head>
<body>
<div id="app">
    <!-- HEADER -->
    @section('header')
        @include('app.layouts.partial.header')
    @show
    <!-- END HEADER -->

    <!-- CONTENT -->
    <main class="py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    @yield('content')
                </div>
                <div class="col-md-4">
                    @section('right_part')
                        @include('app.layouts.partial.right_part')
                    @show
                </div>
            </div>
        </div>
    </main>
    <!-- END CONTENT -->

    <!-- FOOTER -->
    @section('footer')
        {{--@include('app.layouts.partial.footer')--}}
    @show
    <!-- END FOOTER -->
</div>
<!-- SCRIPT FOOTER -->
@section('script_footer')
    @include('app.layouts.partial.script_footer')
@show
<!-- END SCRIPT FOOTER -->
</body>
</html>
