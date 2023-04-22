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
    @include('single_app.partial.styles')
@show
<!-- END STYLES -->
    <script>
        {{--var app_url = '{{route('home')}}/';--}}
    </script>
</head>
<body>
<div class="body-container" id="main_body" data-url="{{route('home')}}/">
    <div class="body-row">
        <div class="main-column">
            <!-- HEADER -->
        @section('header')
            @include('single_app.partial.header')
        @show
        <!-- END HEADER -->

            <!-- CONTENT -->
        <div class="row filter-area"></div>
        <div class="row detail-area">
            <div class="col-md-12 detail-info-area">
                <div class="btn-group action-btn-group">
                    <button class="btn-show-chart btn btn-basic w-100 active" onclick="ShowChartArea();" type="button"><em class="ob-icon ob-icon-chart"></em>{{ __('app.chart')}}</button>
                    <button class="btn-show-table btn btn-basic w-100" onclick="ShowTableArea();" type="button"><em class="ob-icon ob-icon-table"></em>{{ __('app.table')}}</button>
                    <button class="btn btn-basic w-100" onclick="DownloadImage();" type="button"><em class="ob-icon ob-icon-download"></em>{{ __('app.download')}}</button>
                </div>
                <h2 class="filter-information">{!!htmlentities(__('app.catches'))!!}, Zeitreihe {{date("Y")-10}} - {{date("Y")-1}}, ganzer Kanton, Fliessgewässer, Alle Arten</h2>
            </div>
            <div class="col-md-12 detail-option-area" style="display: none;">
            </div>
            <div class="col-md-12 detail-chart-area active" id="ob_geo_chart">
                <p class="action-tip">{!! htmlentities(__('app.action_tip'))!!}</p>
            </div>
            <div class="col-md-12 detail-table-area">
            </div>
            <div class="col-md-12 no-data-area">
                <img src="{{ asset('images/streamline-icon-no-data.png') }}" alt="chart don't have data">
                <p>{{__('app.content_no_data')}}</p>
            </div>
        </div>
        <!-- END CONTENT -->

        </div>
    </div>
    <div class="static_data">
        <div id="app_url" data-value="{{route('home')}}"></div>
        <div id="time_range" data-value="{{$time_range}}"></div>
        <div id="time_year" data-value="{{$year}}"></div>
        <div id="region_data" data-value="{{$regions}}"></div>
        <div id="waterbody_type_data" data-value="{{$water_body_types}}"></div>
    </div>
</div>
@section('modal')
    @if (isset($waterbody))
        @include('single_app.partial.modal')
    @endif
@show
<!-- SCRIPT FOOTER -->
@section('script_footer')
    @include('single_app.partial.script_footer')
@show
<!-- END SCRIPT FOOTER -->
</body>
</html>
