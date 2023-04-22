@extends('app.layouts.master.app')

@section('styles')
    <link href="{{ asset('libs/nouislider/nouislider.css') }}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
    @parent
@endsection

@section('content')
    <div class="row filter-area">
        <div class="col-md-12 filter-item">
            <div class="row">
                <div class="col-xl-12">
                    <div class="btn-group group-filter-1">
                        <a href="{{route("catches_time_series")}}" class="btn btn-basic w-100 active">{{ __('app.time_series')}}</a>
                        <a href="{{route("catches_season")}}" class="btn btn-basic w-100">{{ __('app.season')}}</a>
                        <a href="{{route("catches_regional_comparison")}}" class="btn btn-basic w-100">{{ __('app.regional_comparison')}}</a>
                        <a href="{{route("catches_fishtype_comparison")}}" class="btn btn-basic w-100">{{ __('app.fishtype_comparison')}}</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 filter-item">
            <div class="row">
                <div class="col-xl-12">
                    <div class="btn-group element-3">
                        <div class="select-group">
                            <select name="geographic_filter" class="js-select2-active w-100" data-placeholders="{{ __('app.geographic_filter')}}">
                                <option></option>
                                @foreach($regions as $region)
                                    <option value="{{$region->region_code}}">{{$region->name_DE}}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-info-select" data-toggle="modal" data-target="#geographic_modal">i</button>
                        </div>
                        <div class="select-group">
                            <select name="fishtype_filter" class="js-select2-active w-100" data-placeholders="{{ __('app.fishtype_filter')}}">
                                <option></option>
                                @foreach($fish_types as $fish_type)
                                    <option value="{{$fish_type->code}}">{{$fish_type->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="select-group">
                            <select name="waterbody_type" class="js-select2-active w-100" data-placeholders="{{ __('app.waterbody_type')}}">
                                <option></option>
                                @foreach($water_body_types as $waterbody_type)
                                    <option value="{{$waterbody_type->code}}">{{$waterbody_type->name}}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-info-select" data-toggle="modal" data-target="#waterbodytype_modal">i</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-md-12 filter-item">
            <div class="row">
                <div class="col-xl-12">
                    <div class="btn-group">
                        <div class="slider-area">
                            <div id="slider-range"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row detail-area">
        <div class="col-md-12 detail-info-area">
            <h4 class="filter-information">{{ __('app.catches')}}, Zeitreihe {{date("Y")-10}} - {{date("Y")-1}}, ganzer Kanton, Fliessgewässer, Alle Arten</h4>
            <div class="btn-group action-btn-group">
                <button class="btn-show-chart btn btn-basic w-100 active" onclick="showChartArea();">{{ __('app.chart')}}</button>
                <button class="btn-show-table btn btn-basic w-100" onclick="showTableArea();">{{ __('app.table')}}</button>
                <button class="btn btn-basic w-100" onclick="downloadImage();">{{ __('app.download')}}</button>
            </div>
        </div>
        <div class="col-md-12 detail-chart-area active">
            <div id="main_chart" class="chart-legend-bottom" style="width: 100%; height: 500px;"></div>
        </div>
        <div class="col-md-12 detail-table-area">
            <table>
                <thead>
                <tr>
                    <th>{{ __('app.year')}}</th>
                    <th>{{ __('app.sessions')}}</th>
                    <th>{{ __('app.catches')}}</th>
                    <th>{{ __('app.cpue')}}</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>


@endsection

@section('script_footer')
    @parent
    {{--    <script src="{{ asset('libs/jqueryui.v1.12.1/jquery-ui.js') }}"></script>--}}
    <script src="{{ asset('libs/nouislider/nouislider.js') }}"></script>

    {{--<script src="https://www.gstatic.com/charts/loader.js"></script>--}}
    <script src="{{ asset('libs/jquery.datatables/jquery.datatables.min.js') }}"></script>
    <script>
        var mySlider = document.getElementById('slider-range');

        $(document).ready(function () {
            $.extend( $.fn.dataTable.defaults, {
                searching: false,
                ordering: true,
                paging: false,
                info: false
            });
            noUiSlider.create(mySlider, {
                start: [{{$time_range[0]}}, {{$time_range[1]}}],
                range: {
                    'min': [2002],
                    'max': [new Date().getFullYear() - 1]
                },
                step: 1,
                tooltips: true,
                connect: [false, true, false],

            });

            mySlider.noUiSlider.on('change.one', function () {
                UpdateChart();
            });

            $('select[name="geographic_filter"], select[name="fishtype_filter"], select[name="waterbody_type"]').on('change', function () {
                UpdateChart();
            });

            UpdateChart();
        });

        function init() {
            // UpdateChart();
        }

        google.charts.load('current', {'packages': ['corechart', 'bar']});
        google.charts.setOnLoadCallback(init);

        var current_chart;
        var chart_data;

        function drawVisualization(data) {
            if (data.length == 0) {
                $('#main_chart').html("");
                return;
            }
            var dataset = [];
            var temp_data = data.catches;
            var avg = data.average;
            $.each(temp_data, function (i, item) {
                var row = [
                    String(item.year),
                    parseInt(item.sessions), "", '<div class="tooltip-c"><p>{{__('app.year')}}: <b>'+String(item.year)+'</b></p><p>{{__('app.sessions')}}: <b>'+numberWithCommas(item.sessions)+'</b></p></div>',
                    parseInt(item.catches), "", '<div class="tooltip-c"><p>{{__('app.year')}}: <b>'+String(item.year)+'</b></p><p>{{__('app.catches')}}: <b>'+numberWithCommas(parseInt(item.catches))+'</b></p></div>',
                    parseFloat(item.CPUE), "", '<div class="tooltip-c"><p>{{__('app.year')}}: <b>'+String(item.year)+'</b></p><p>CPUE: <b>'+numberWithCommas(parseFloat(item.CPUE))+'</b></p></div>'
                ];
                dataset.push(row);
            });
            //Add 10ya
            dataset.push(
                ['', undefined, "", '', undefined, "", '', undefined, "", ''],
                ["{{__('app.10year_avg')}}", parseFloat(avg.sessions), "stroke-color: #7a519a; stroke-width: 2;fill-color:#7a519a;fill-opacity: 0.2;", '<div class="tooltip-c"><p>10 Year Average</p><p>Sessions: <b>'+numberWithCommas(parseFloat(avg.sessions))+'</b></p></div>', parseFloat(avg.catches), "stroke-color: #00cfe3; stroke-width: 2;fill-color:#00cfe3;fill-opacity: 0.2;", '<div class="tooltip-c"><p>10 Year Average</p><p>Catches: <b>'+numberWithCommas(parseFloat(avg.catches))+'</b></p></div>', parseFloat(avg.CPUE), "stroke-width: 0;", '<div class="tooltip-c"><p>10 Year Average</p><p>CPUE: <b>'+numberWithCommas(parseFloat(avg.CPUE))+'</b></p></div>']
            );
            dataset.unshift([
                "{{__('app.year')}}",
                "{{__('app.sessions')}}", {role: 'style'}, {role: 'tooltip', p: {html: true}},
                "{{__('app.catches')}}", {role: 'style'}, {role: 'tooltip', p: {html: true}},
                "{{__('app.cpue')}}", {role: 'style'}, {role: 'tooltip', p: {html: true}}
            ]);
            var main_data = google.visualization.arrayToDataTable(dataset);
            var main_options = {
                // vAxis: {title: 'Anzahl'},
                // hAxis: {title: 'Year'},
                seriesType: 'bars',
                series: {
                    0: {
                        color: '#7a519a',
                    },
                    1: {
                        color: '#00cfe3',
                    },
                    2: {
                        color: '#ffa600',
                        type: 'line',
                        strokeWidth: 0,
                        areaOpacity: 0,
                        targetAxisIndex: 1
                    }
                },
                legend: {position: 'bottom'},
                pointSize: 5,
                tooltip: { isHtml: true }
            };

            var main_div = document.getElementById('main_chart');
            var main_chart = new google.visualization.ComboChart(main_div);
            // main_chart.draw(main_data, main_options);

            current_chart = main_chart;
            var view = new google.visualization.DataView(main_data);
            main_data = view.toDataTable();
            main_chart.draw(main_data, main_options);
            var columns = [];
            var series = {};
            for (var i = 0; i < main_data.getNumberOfColumns(); i++) {
                columns.push(i);
                if (i > 0) {
                  series[i - 1] = {};
                }
            }
            google.visualization.events.addListener(main_chart, 'select', function () {
                var sel = main_chart.getSelection();
                var color = ['#7a519a', '#00cfe3', '#ffa600'];
                // if selection length is 0, we deselected an element
                if (sel.length > 0) {
                    // if row is undefined, we clicked on the legend
                    if (sel[0].row === null) {
                        var col = sel[0].column;
                        if (columns[col] == col) {
                            // hide the data series
                            columns[col] = {
                                label: main_data.getColumnLabel(col),
                                type: main_data.getColumnType(col),
                                calc: function () {
                                    return null;
                                },
                            };
                            // grey out the legend entry
                            if (col == 1) {
                                col = 0;
                            }else{
                                col = Math.floor(col/2) -1;
                            }
                            main_options.series[col]['color'] = '#CCCCCC';
                        }else {
                            columns[col] = col;
                            if (col == 1) {
                                col = 0;
                            }else{
                                col = Math.floor(col/2) -1;
                            }
                            _color = color[col];
                            main_options.series[col]['color'] = _color;
                        }
                        var view = new google.visualization.DataView(main_data);
                        view.setColumns(columns);
                        main_chart.draw(view, main_options);
                    }
                }
            });
        }

        function UpdateChart() {
            // Get filter value and call API get data to update chart

            var geographic_id = $('select[name="geographic_filter"]').children("option:selected").val();
            var fishtype_code = $('select[name="fishtype_filter"]').children("option:selected").val();
            var waterbody_type = $('select[name="waterbody_type"]').children("option:selected").val();
            if (geographic_id == "") geographic_id = "all";
            if (fishtype_code == "") fishtype_code = "all";
            if (waterbody_type == "") waterbody_type = "all";

            var time_range = mySlider.noUiSlider.get();
            ShowLoading();
            $.ajax({
                url: '/api/catches/time_series/chart',
                type: "get",
                data: {
                    geographic_id: geographic_id,
                    fishtype_code: fishtype_code,
                    waterbody_type: waterbody_type,
                    time_range: time_range
                },
                success: function (result) {
                    HideLoading();
                    drawVisualization(result.data);
                    UpdateTable(result.data);
                    chart_data = result.data;
                },
                error: function(){
                    HideLoading();
                }
            });

            UpdateFilterInformation('{{ __('app.catches')}}', true);
        }

        function UpdateTable(data) {
            var temp_data = data.catches;
            var avg = data.average;
            var container = $('.detail-table-area table tbody');
            var html = '';
            $.each(temp_data, function (i, item) {
                html += '<tr>';
                html += '   <td>' + item.year + '</td>';
                html += '   <td>' + numberWithCommas(item.catches) + '</td>';
                html += '   <td>' + numberWithCommas(item.sessions) + '</td>';
                html += '   <td>' + numberWithCommas(item.CPUE) + '</td>';
                html += '</tr>';
            });
            html += '<tr>';
            html += '   <td>{{__('app.10year_avg')}}</td>';
            html += '   <td>' + numberWithCommas(parseFloat(avg.catches)) + '</td>';
            html += '   <td>' + numberWithCommas(parseFloat(avg.sessions)) + '</td>';
            html += '   <td>' + numberWithCommas(parseFloat(avg.CPUE)) + '</td>';
            html += '</tr>';
            container.html(html);
            $('.detail-table-area table').DataTable();
        }

        function downloadImage() {

            if ($('.detail-chart-area').hasClass('active')) {
                // console.log('Download chart image');
                var img_uri = current_chart.getImageURI();
                ConvertToDataJPGE(img_uri, $('#main_chart').width(), $('#main_chart').height(), "catches_time_series.pdf", $('.filter-information').html());
            } else {
                // console.log('Download table file');

                var data_post = {
                    "title": $('.filter-information').html(),
                    "header": [],
                    "body": []
                };
                $(".detail-table-area table thead tr th").each(function () {
                    data_post["header"].push($(this).text());
                });

                var temp_data = chart_data.catches;
                var avg = chart_data.average;
                var temp;
                $.each(temp_data, function (i, item) {
                    temp = [];
                    temp.push(item.year);
                    temp.push(item.catches);
                    temp.push(item.sessions);
                    temp.push(item.CPUE);
                    data_post["body"].push(temp);
                });
                temp = [];
                temp.push("{{__('app.10year_avg')}}");
                temp.push(avg.catches);
                temp.push(avg.sessions);
                temp.push(avg.CPUE);
                data_post["body"].push(temp);

                DownloadExcelData(data_post, 'catches_time_series.xlsx');
            }
        }

        function showTableArea() {
            $('.detail-chart-area').removeClass('active');
            $('.detail-table-area').addClass('active');

            $('.btn-show-chart').removeClass('active');
            $('.btn-show-table').addClass('active');
        }

        function showChartArea() {
            $('.detail-chart-area').addClass('active');
            $('.detail-table-area').removeClass('active');

            $('.btn-show-chart').addClass('active');
            $('.btn-show-table').removeClass('active');
        }
    </script>
@endsection
