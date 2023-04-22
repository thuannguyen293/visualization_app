@extends('app.layouts.master.app')

@section('styles')
    <link href="{{ asset('libs/nouislider/nouislider.css') }}" rel="stylesheet" type="text/css">
    @parent


@endsection

@section('content')
    <div class="row filter-area">
        <div class="col-md-12 filter-item">
            <label>{{ __('app.select')}}</label>
            <div class="btn-group">
                <button class="btn btn-basic w-100 active">{{ __('app.time_series')}}</button>
                <button class="btn btn-basic w-100">{{ __('app.season')}}</button>
                <button class="btn btn-basic w-100">{{ __('app.regional_comparison')}}</button>
                <button class="btn btn-basic w-100">{{ __('app.fishtype_comparison')}}</button>
            </div>
        </div>
        <div class="col-md-12 filter-item">
            <label>{{ __('app.filter')}}</label>
            <div class="btn-group">
                <select name="geographic_filter" class="js-select2-active w-100">
                    <option value="all">{{ __('app.geographic_filter')}}</option>
                    <option value="option 1">option 1</option>
                    <option value="option 1">option 2</option>
                    <option value="option 1">option 3</option>
                    <option value="option 1">option 4</option>
                </select>
                <select name="fishtype_filter" class="js-select2-active w-100">
                    <option value="all">{{ __('app.fishtype_filter')}}</option>
                    @foreach($fish_types as $fish_type)
                    <option value="{{$fish_type->code}}">{{$fish_type->name}}</option>
                    @endforeach
                </select>
                <select name="waterbody_type" class="js-select2-active w-100">
                    <option value="all">{!!htmlentities(__('app.waterbody_type'))!!}</option>
                    @foreach($water_body_types as $waterbody_type)
                    <option value="{{$waterbody_type->code}}">{{$waterbody_type->name}}</option>
                    @endforeach
                </select>

            </div>
        </div>
        <div class="col-md-12 filter-item">
            <label class="label-slider">{{ __('app.year')}}</label>
            <div class="slider-area">
                <div id="slider-range"></div>
            </div>
        </div>
    </div>
    <div class="row detail-area">
        <div class="col-md-12 detail-info-area">
            <h4>Zeitreihe 2002 – 2018, ganzer Kanton, Fliessgewässer, Alle Arten</h4>
            <div class="btn-group action-btn-group">
                <button class="btn btn-basic w-100 active">{{ __('app.chart')}}</button>
                <button class="btn btn-basic w-100">{{ __('app.table')}}</button>
                <button class="btn btn-basic w-100" onclick="downloadImage();">{{ __('app.download')}}</button>
            </div>
        </div>
        <div class="col-md-12 detail-chart-area">
            <div id="main_chart" style="width: 100%; height: 450px;"></div>
        </div>
    </div>


@endsection

@section('script_footer')
    @parent
    {{--    <script src="{{ asset('libs/jqueryui.v1.12.1/jquery-ui.js') }}"></script>--}}
    <script src="{{ asset('libs/nouislider/nouislider.js') }}"></script>

    <script src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
        $(document).ready(function () {
            var mySlider = document.getElementById('slider-range');

            noUiSlider.create(mySlider, {

                start: [2010, 2020],
                range: {
                    'min': [1970],
                    'max': [2020]
                },
                step: 1,
                tooltips: true,
                connect: [false, true, false],

            });

        });


        google.charts.load('current', {'packages': ['corechart', 'bar']});
        google.charts.setOnLoadCallback(drawVisualization);

        var current_chart;

        function drawVisualization() {

            var main_data = google.visualization.arrayToDataTable([
                ['Year', 'Sessions', { role: 'style' }, 'Catches', { role: 'style' }, 'CPUE', { role: 'style' }],
                ['2010', 34, "color: #3365cc", 345, "color: #dc3913;", 872.6, ""],
                ['2011', 165, "color: #3365cc", 938, "color: #dc3913;", 614.6, ""],
                ['2012', 135, "color: #3365cc", 1120, "color: #dc3913;", 682, ""],
                ['2013', 157, "color: #3365cc", 1167, "color: #dc3913;", 623, ""],
                ['2014', 139, "color: #3365cc", 1110, "color: #dc3913;", 609.4, ""],
                ['2015', 112, "color: #3365cc", 1212, "color: #dc3913;", 664.2, ""],
                ['2016', 154, "color: #3365cc", 1054, "color: #dc3913;", 569.6, ""],
                ['2017', 123, "color: #3365cc", 1147, "color: #dc3913;", 345.6, ""],
                ['2018', 157, "color: #3365cc", 1287, "color: #dc3913;", 567.6, ""],
                ['2019', 178, "color: #3365cc", 1299, "color: #dc3913;", 324.6, ""],
                ['2020', 129, "color: #3365cc", 1212, "color: #dc3913;", 654.6, ""],
                ['', 0, "color: #3365cc", 0, "color: #dc3913;", 0, "stroke-width: 0;fill-opacity:0;"],
                ['10YA', 154, "stroke-color: #3365cc; stroke-width: 2;fill-color:#3365cc;fill-opacity: 0.2;", 909, "stroke-color: #dc3913; stroke-width: 2;fill-color:#dc3913;fill-opacity: 0.2;", 624.6,  "stroke-width: 0;"]
            ]);

            var main_options = {
                // title: 'Monthly Coffee Production by Country',
                vAxis: {title: 'Anzahl'},
                // hAxis: {title: 'Year'},
                seriesType: 'bars',
                series: {
                    2: {
                        type: 'line',
                        11: {
                            lineWidth: 5,
                            lineDashStyle: [1, 1]
                        }
                    }
                },
                legend: {position: 'bottom'},
                pointSize: 5,

                // yAxis:
            };

            var main_div =  document.getElementById('main_chart');
            var main_chart = new google.visualization.ComboChart(main_div);
            main_chart.draw(main_data, main_options);

            current_chart = main_chart;

            // var average_data = google.visualization.arrayToDataTable([
            //     ['Year', 'Sessions', 'Catches', 'CPUE'],
            //     ['2020', 165, 938, 614.6]
            // ]);
            //
            // var average_options = {
            //     // title: 'Monthly Coffee Production by Country',
            //     // vAxis: {title: 'Anzahl'},
            //     // hAxis: {title: 'Year'},
            //     seriesType: 'bars',
            //     series: {2: {type: 'line'}},
            //     legend: {position: 'bottom'},
            //     pointSize: 5,
            //     vAxis: {
            //         // title: 'Population (millions)',
            //         // scaleType: 'log',
            //         ticks: [1500]
            //     }
            // };
            //
            // var average_chart = new google.visualization.ComboChart(document.getElementById('average_chart'));
            // average_chart.draw(average_data, average_options);
        }



        function downloadImage() {
            var imgUri = current_chart.getImageURI();
            console.log(imgUri);

        }
    </script>
@endsection
