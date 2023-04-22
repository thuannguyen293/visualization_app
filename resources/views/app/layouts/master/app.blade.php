<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta name="robots" content="noindex,nofollow">

    {{--<link rel="shortcut icon" href="/favicon.ico">--}}

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@if(isset($title)) {{$title}} @else {{config('app.name', 'Visualization App')}} @endif</title>

    <!-- STYLES -->
    @section('styles')
        @include('app.layouts.master.partial.styles')
    @show
    <!-- END STYLES -->
    <script>
        var app_url = '{{route('home')}}/';
    </script>
</head>
<body>
<div class="body-container" id="main_body" data-url="{{route('home')}}/">
    <div class="body-row">
        <div class="main-column">
            <!-- HEADER -->
            @section('header')
                @include('app.layouts.master.partial.header')
            @show
            <!-- END HEADER -->

            <!-- CONTENT -->
            @yield('content')
            <!-- END CONTENT -->

        </div>
    </div>
</div>
@section('modal')
    @if (isset($waterbody))
    @include('app.layouts.master.partial.modal')
    @endif
@show
    <!-- SCRIPT FOOTER -->
    @section('script_footer')
        @include('app.layouts.master.partial.script_footer')
    @show
    <!-- END SCRIPT FOOTER -->
</body>
</html>
