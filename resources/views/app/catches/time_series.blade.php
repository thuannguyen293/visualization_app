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
                    <div class="btn-group element-2">
                        <div class="select-group">
                            <label for="geographic_filter">{{__('app.geographic_filter')}}</label>
                            <select name="geographic_filter" class="js-select2-active w-100" data-placeholders="{{ __('app.filter_all_geographic')}}" id="geographic_filter">
                                <option></option>
                                @foreach($regions as $region)
                                    <option value="{{$region->region_code}}">{!!htmlentities($region->name_DE)!!}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-info-select" data-toggle="modal" data-target="#geographic_modal">i</button>
                        </div>
                        <div class="select-group">
                            <label for="waterbody_type">{!!htmlentities(__('app.waterbody_type'))!!}</label>
                            <select name="waterbody_type" class="js-select2-active w-100" data-placeholders="{!!htmlentities(__('app.filter_all_waterbody_type'))!!}" id="waterbody_type">
                                <option></option>
                                @foreach($water_body_types as $waterbody_type)
                                    <option value="{{$waterbody_type->code}}">{!!htmlentities($waterbody_type->name)!!}</option>
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
            <h2 class="filter-information">{!!htmlentities(__('app.catches'))!!}, Zeitreihe {{date("Y")-10}} - {{date("Y")-1}}, ganzer Kanton, Fliessgewässer, Alle Arten</h2>
            <div class="btn-group action-btn-group">
                <button class="btn-show-chart btn btn-basic w-100 active" onclick="showChartArea();"><em class="ob-icon ob-icon-chart"></em>{{ __('app.chart')}}</button>
                <button class="btn-show-table btn btn-basic w-100" onclick="showTableArea();"><em class="ob-icon ob-icon-table"></em>{{ __('app.table')}}</button>
                <button class="btn btn-basic w-100" onclick="downloadImage();"><em class="ob-icon ob-icon-download"></em>{{ __('app.download')}}</button>
            </div>
        </div>
        <div class="col-md-12 detail-chart-area active">
            <p class="action-tip">{!! htmlentities(__('app.action_tip'))!!}</p>
        </div>
        <div class="col-md-12 detail-table-area">
            <table>
                <thead>
                <tr>
                    <th>{{ __('app.year')}}</th>
                    <th>{!!htmlentities(__('app.catches'))!!}</th>
                    <th>{{ __('app.sessions')}}</th>
                    <th>{{ __('app.cpue')}}</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div class="col-md-12 no-data-area">
            <img src="{{ asset('images/streamline-icon-no-data.png') }}" alt="chart don't have data">
            <p>{{__('app.content_no_data')}}</p>
        </div>
    </div>


@endsection

@section('script_footer')
    @parent
    <script src="{{ asset('libs/chartjs/Chart.min.js') }}"></script>
    <script src="{{ asset('libs/nouislider/nouislider.js') }}"></script>
    <script src="{{ asset('libs/jquery.datatables/jquery.datatables.min.js') }}"></script>

    <script>
        var mySlider = document.getElementById('slider-range');
        var min_year = 2011;
        var max_year = new Date().getFullYear() - 1;
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

            $('select[name="geographic_filter"], select[name="waterbody_type"]').on('change', function () {
                UpdateChart();
            });

            UpdateChart();

        });

        var current_chart;
        var chart_data;

        function DrawMyChart(data){
            $('#my_chart').remove(); // this is my <canvas> element
            $('.detail-chart-area').prepend('<canvas id="my_chart" width="900" height="500"></canvas>');
            current_chart = document.getElementById('my_chart');

            if (data.length == 0 || data["catches"].length == 0) {
                $(".detail-area").addClass('no-data');
                return;
            }else{
                $(".detail-area").removeClass('no-data');
            }

            var time_range = mySlider.noUiSlider.get();
            var ctx_chart = current_chart.getContext('2d');
            var labels = [];
            var datasets = {
                'sessions' : [],
                'catches' : [],
                'CPUE' : []
            };
            var temp_data = data.catches;
            var avg = data.average;
            var last = temp_data.length + 1;
            var color = Chart.helpers.color;
            $.each(temp_data, function (i, item) {
                labels.push(item.year);
                datasets['sessions'].push(parseInt(item.sessions));
                datasets['catches'].push(parseInt(item.catches));
                datasets['CPUE'].push(parseFloat(item.CPUE));
            });
            if(parseInt(time_range[1]) >= min_year && parseInt(time_range[1]) <= max_year){
                labels.push("");
                labels.push(ConvertToHTML('{!!htmlentities(__('app.10year_avg'))!!}'));
                datasets['sessions'].push(null);
                datasets['sessions'].push(avg.sessions);
                datasets['catches'].push(null);
                datasets['catches'].push(avg.catches);
                datasets['CPUE'].push(null);
                datasets['CPUE'].push(avg.CPUE);
            }
                
            var chart_render_data = {
                labels: labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'CPUE',
                        borderColor: '#ff0000',
                        fill: false,
                        data: datasets["CPUE"],
                        yAxisID: 'y-axis-2',
                        lineTension: 0
                    },
                    {
                        type: 'bar',
                        label: "{{__('app.sessions')}}",
                        data: datasets['sessions'],
                        yAxisID: 'y-axis-1',
                        backgroundColor: '#fcd5b5',
                        borderColor: "#7a3a05",
                        borderWidth: 1,
                    }, {
                        type: 'bar',
                        label: ConvertToHTML('{!!htmlentities(__('app.catches'))!!}'),
                        data: datasets['catches'],
                        yAxisID: 'y-axis-1',
                        backgroundColor: '#b7dee8',
                        borderColor: "#1f5461",
                        borderWidth: 1

                    }
                ]
            };
            var my_chart = new Chart(ctx_chart, {
                type: 'bar',
                data: chart_render_data,
                options: {
                    responsive: false,
                    legend: { position: 'bottom' },
                    tooltips: {
                        mode: 'index',
                        intersect: true,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var label = data.datasets[tooltipItem.datasetIndex].label || '';

                                if (label) {
                                    label += ': ';
                                }
                                label += numberWithCommas(tooltipItem.yLabel);
                                return label;
                            }
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                callback: function(value, index, values) {
                                    return numberWithCommas(value);
                                }
                            },
                            type: 'linear',
                            display: true,
                            position: 'left',
                            id: 'y-axis-1',
                            scaleLabel: {
                                display: true,
                                labelString: '{{__('app.number')}}'
                            }
                        }, {
                            ticks: {
                                beginAtZero: true
                            },
                            type: 'linear',
                            display: true,
                            position: 'right',
                            id: 'y-axis-2',
                            gridLines: { drawOnChartArea: false },
                            scaleLabel: {
                                display: true,
                                labelString: '{{__('app.cpue')}}',
                                rotate: true
                            }
                        }]
                    }
                }
            });
        }

        function UpdateChart() {
            // Get filter value and call API get data to update chart

            var geographic_id = $('select[name="geographic_filter"]').children("option:selected").val();
            var waterbody_type = $('select[name="waterbody_type"]').children("option:selected").val();
            if (geographic_id == "") geographic_id = "all";
            if (waterbody_type == "") waterbody_type = "all";

            var time_range = mySlider.noUiSlider.get();
            ShowLoading();
            $.ajax({
                url:  '{{route('home')}}/api/catches/time_series/chart',
                type: "get",
                data: {
                    geographic_id: geographic_id,
                    waterbody_type: waterbody_type,
                    time_range: time_range
                },
                success: function (result) {
                    HideLoading();
                    DrawMyChart(result.data);

                    UpdateTable(result.data);
                    chart_data = result.data;
                },
                error: function(){
                    HideLoading();
                }
            });

            UpdateFilterInformation('{!!htmlentities(__('app.catches'))!!}', true);
        }

        function UpdateTable(data) {
            if ( $.fn.dataTable.isDataTable('.detail-table-area table') ) {
                $('.detail-table-area table').DataTable().destroy();
            }
            var time_range = mySlider.noUiSlider.get();
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
            if(parseInt(time_range[1]) >= min_year && parseInt(time_range[1]) <= max_year){
                html += '<tr>';
                html += '   <td>{!!htmlentities(__('app.10year_avg'))!!}</td>';
                html += '   <td>' + numberWithCommas(parseFloat(avg.catches)) + '</td>';
                html += '   <td>' + numberWithCommas(parseFloat(avg.sessions)) + '</td>';
                html += '   <td>' + numberWithCommas(parseFloat(avg.CPUE)) + '</td>';
                html += '</tr>';
            }
            container.html(html);
            $('.detail-table-area table').DataTable();
        }

        function downloadImage() {
            if ($('.detail-chart-area').hasClass('active')) {
                var canvas_temp = ResizeCanvasImageData(current_chart, $('#my_chart').width(), $('#my_chart').height());
                DownloadImagePDFCallback(canvas_temp, "catches_time_series.pdf", $('.filter-information').html());
            } else {
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
            return false;
        }

        function showTableArea() {
            $('.detail-chart-area').removeClass('active');
            $('.detail-table-area').addClass('active');

            $('.btn-show-chart').removeClass('active');
            $('.btn-show-table').addClass('active');
            return false;
        }

        function showChartArea() {
            $('.detail-chart-area').addClass('active');
            $('.detail-table-area').removeClass('active');

            $('.btn-show-chart').addClass('active');
            $('.btn-show-table').removeClass('active');
            return false;
        }
    </script>
@endsection
