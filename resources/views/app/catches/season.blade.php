@extends('app.layouts.master.app')

@section('styles')
    <link href="{{ asset('libs/nouislider/nouislider.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('libs/pagination/pagination.css') }}" rel="stylesheet" type="text/css">
    @parent
@endsection

@section('content')
    <div class="row filter-area">
        <div class="col-md-12 filter-item">
            <div class="row">
                <div class="col-xl-12">
                    <div class="btn-group group-filter-1">
                        <a href="{{route("catches_time_series")}}" class="btn btn-basic w-100">{{ __('app.time_series')}}</a>
                        <a href="{{route("catches_season")}}" class="btn btn-basic w-100 active">{{ __('app.season')}}</a>
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
                    <div class="slider-area">
                        <div id="slider-range"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row detail-area">
        <div class="col-md-12 detail-info-area">
            <h2 class="filter-information">{!!htmlentities(__('app.catches'))!!}, Zeitreihe {{date("Y") - 1}}, ganzer Kanton, Fliessgewässer, Alle Arten</h2>
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
                </thead>
                <tbody>
                </tbody>
            </table>
            <div id="pagination-container"></div>
        </div>
        <div class="col-md-12 no-data-area">
            <img src="{{ asset('images/streamline-icon-no-data.png') }}" alt="chart don't have data">
            <p>{{__('app.content_no_data')}}</p>
        </div>
    </div>


@endsection

@section('script_footer')
    @parent
    <script src="{{ asset('libs/nouislider/nouislider.js') }}"></script>
    <script src="{{ asset('libs/chartjs/Chart.min.js') }}"></script>
    <script src="{{ asset('libs/pagination/pagination.min.js') }}"></script>
    <script>
        var mySlider = document.getElementById('slider-range');
        var min_year = 2011;
        var max_year = new Date().getFullYear() - 1;
        $(document).ready(function () {
            noUiSlider.create(mySlider, {
                start: parseInt({{$year}}),
                range: {
                    'min': 2002,
                    'max': (new Date().getFullYear() - 1)
                },
                step: 1,
                tooltips: true
            });

            mySlider.noUiSlider.on('change.one', function () {
                UpdateChart();
            });

            $('select[name="geographic_filter"], select[name="waterbody_type"]').on('change', function () {
                UpdateChart();
            });
            UpdateChart();

            $(window).on('resize', function () {
                var win = $(this); //this = window
                if (win.width() >= 650) {
                    table_column_number = 5;
                } else if (win.width() >= 550) {
                    table_column_number = 4;
                } else if (win.width() >= 450) {
                    table_column_number = 3;
                } else{
                    table_column_number = 2;
                }
            });
        });

        function init() {
            // UpdateChart();
        }

        var current_chart;
        var chart_data;

        function drawVisualization(data) {
            $('#my_chart').remove(); // this is my <canvas> element
            $('.detail-chart-area').prepend('<canvas id="my_chart" width="900" height="500"></canvas>');
            current_chart = document.getElementById('my_chart');
            if (data.length == 0) {
                $(".detail-area").addClass('no-data');
                return;
            }else{
                $(".detail-area").removeClass('no-data');
            }

            var time_range = mySlider.noUiSlider.get();
            var ctx_chart = current_chart.getContext('2d');
            var labels = [];
            if(parseInt(time_range) >= min_year && parseInt(time_range) <= max_year){
                var datasets = {
                    'sessions_10' : [],
                    'catches_10' : [],
                    'sessions' : [],
                    'catches' : [],
                    'cpue_10' : [],
                    'cpue' : []
                };
            }else{
                var datasets = {
                    'sessions' : [],
                    'catches' : [],
                    'cpue' : []
                };
            }

            var temp_data = data;
            var last = temp_data.length + 1;
            var color = Chart.helpers.color;
            $.each(temp_data, function (i, item) {
                labels.push(item.month);
                if(parseInt(time_range) >= min_year && parseInt(time_range) <= max_year){
                    datasets['sessions_10'].push(parseInt(item.avg_sessions));
                    datasets['catches_10'].push(parseInt(item.avg_catches));
                }
                datasets['sessions'].push(parseFloat(item.sessions));
                datasets['catches'].push(parseInt(item.catches));
                if(parseInt(time_range) >= min_year && parseInt(time_range) <= max_year){
                    datasets['cpue_10'].push(parseFloat(item.avg_CPUE));
                }
                datasets['cpue'].push(parseFloat(item.CPUE));
            });
            var _datasets = [];
            if(parseInt(time_range) >= min_year && parseInt(time_range) <= max_year){
                _datasets = [
                    {
                        type: 'bar',
                        label: ConvertToHTML('{!!htmlentities(__('app.sessions_10'))!!}'),
                        backgroundColor: color('#fcd5b5').alpha(0.3).rgbString(),
                        fill: false,
                        data: datasets["sessions_10"],
                        yAxisID: 'y-axis-1',
                        order: 1,
                        borderColor: '#fcd5b5',
                        borderWidth: 2,
                    },{
                        type: 'bar',
                        label: "{{__('app.sessions')}}",
                        backgroundColor: '#fcd5b5',
                        borderColor: "#7a3a05",
                        borderWidth: 1,
                        data: datasets['sessions'],
                        yAxisID: 'y-axis-1',
                        order: 1,
                    },
                    {
                        type: 'bar',
                        label: ConvertToHTML('{!!htmlentities(__('app.catches_10'))!!}'),
                        backgroundColor: color('#b7dee8').alpha(0.3).rgbString(),
                        fill: false,
                        data: datasets["catches_10"],
                        yAxisID: 'y-axis-1',
                        order: 1,
                        borderColor: '#b7dee8',
                        borderWidth: 2
                    },
                    {
                        type: 'bar',
                        label: ConvertToHTML('{!!htmlentities(__('app.catches'))!!}'),
                        backgroundColor: '#b7dee8',
                        borderColor: "#1f5461",
                        borderWidth: 1,
                        data: datasets['catches'],
                        yAxisID: 'y-axis-1',
                        order: 1
                    },
                    {
                        type: 'line',
                        label: ConvertToHTML('{!!htmlentities(__('app.cpue_10'))!!}'),
                        borderColor: '#ff6e54',
                        fill: false,
                        data: datasets["cpue_10"],
                        yAxisID: 'y-axis-2',
                        lineTension: 0,
                        order: 0,
                    },
                    {
                        type: 'line',
                        label: "{{ __('app.cpue')}}",
                        borderColor: '#ff0000',
                        fill: false,
                        data: datasets["cpue"],
                        yAxisID: 'y-axis-2',
                        lineTension: 0,
                        order: 0
                    },
                ]
            }else{
                _datasets =  [
                    {
                        type: 'bar',
                        label: "{{__('app.sessions')}}",
                        backgroundColor: '#7a519a',
                        data: datasets['sessions'],
                        yAxisID: 'y-axis-1',
                        order: 1,
                    },
                    {
                        type: 'bar',
                        label: ConvertToHTML('{!!htmlentities(__('app.catches'))!!}'),
                        backgroundColor: '#00cfe3',
                        data: datasets['catches'],
                        yAxisID: 'y-axis-1',
                        order: 1
                    },
                    {
                        type: 'line',
                        label: "{{ __('app.cpue')}}",
                        borderColor: '#ffa600',
                        fill: false,
                        data: datasets["cpue"],
                        yAxisID: 'y-axis-2',
                        lineTension: 0,
                        order: 0
                    }
                ];
            }

            var chart_render_data = {
                labels: labels,
                datasets: _datasets
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
            // console.log("UpdateChart");
            var geographic_id = $('select[name="geographic_filter"]').children("option:selected").val();
            var waterbody_type = $('select[name="waterbody_type"]').children("option:selected").val();
            if (geographic_id == "") geographic_id = "all";
            if (waterbody_type == "") waterbody_type = "all";
            var year = mySlider.noUiSlider.get();
            ShowLoading();
            $.ajax({
                url: '{{route('home')}}/api/catches/season/chart',
                type: "get",
                data: {
                    geographic_id: geographic_id,
                    waterbody_type: waterbody_type,
                    year: year
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

            UpdateFilterInformation('{!!htmlentities(__('app.catches'))!!}', false);
        }

        function UpdateTable(data, column = '', type = 'sorting_asc') {
            var time_range = mySlider.noUiSlider.get();
            var container = $('.detail-table-area table tbody');
            var html = '';
            if(parseInt(time_range) >= min_year && parseInt(time_range) <= max_year){
                var data_header = ['{{ __('app.sessions')}}', '{!!htmlentities(__('app.catches'))!!}', '{{ __('app.cpue')}}', '{!!htmlentities(__('app.sessions_10'))!!}', '{!!htmlentities(__('app.catches_10'))!!}', '{!!htmlentities(__('app.cpue_10'))!!}'];
            }else{
                var data_header = ['{{ __('app.sessions')}}', '{!!htmlentities(__('app.catches'))!!}', '{{ __('app.cpue')}}'];
            }
            var col = table_column_number;
            var total = data_header.length;
            var page = Math.floor(total/col);

            if (total%col > 0) {
                page = page + 1;
            }

            var data_arr = [];

            for (var j = 0; j < page; j++) {
                var _element = ['{{ __('app.month')}}'];
                $.each(data_header, function(i, item){
                    if (i >= j*col && i < col*(j+1) ) {
                        _element.push(item);
                    }
                });
                data_arr.push(_element);
                $.each(data, function(i, item){
                    _element = [item.month];
                    $.each(data_header, function(k, header){
                        if (k >= j*col && k < col*(j+1) ) {
                            switch(k) {
                                case 0:
                                    value = item.sessions;
                                    break;
                                case 1:
                                    value = item.catches;
                                    break;
                                case 2:
                                    value = item.CPUE;
                                    break;
                                case 3:
                                    value = parseFloat(item.avg_sessions);
                                    break;
                                case 4:
                                    value = parseFloat(item.avg_catches);
                                    break;
                                case 5:
                                    value = parseFloat(item.avg_CPUE);
                                    break;
                            }
                            _element.push(value);
                        }
                    });
                    data_arr.push(_element);
                });
            }
            var pageSize = data.length+1;
            $('#pagination-container').pagination({
                dataSource: data_arr,
                pageSize: pageSize,
                callback: function(data, pagination) {
                    var container_html = '';
                    $.each(data, function(index, item){
                        if (index == 0) {
                            container_html += '<tr class="heading">';
                            $.each(item, function(i, el){
                                if (column != '' || column == 0) {
                                    if (i == column) {
                                        container_html += '<th class="'+type+'" data-index='+i+'>'+el+'</th>';
                                    }else{
                                        container_html += '<th class="sorting" data-index='+i+'>'+el+'</th>';
                                    }
                                }else{
                                    if (i == 0) {
                                        container_html += '<th class="sorting_asc" data-index='+i+'>'+el+'</th>';
                                    }else{
                                        container_html += '<th class="sorting" data-index='+i+'>'+ el +'</th>';
                                    }
                                }
                            });
                        }else{
                            container_html += '<tr>';
                            $.each(item, function(i, el){
                                if (i != 0) {
                                    container_html += '<td>'+ numberWithCommas(el) +'</td>';
                                }else{
                                    container_html += '<td>'+ el +'</td>';
                                }
                            });
                        }
                        container_html += '</tr>';
                    });
                    container.html(container_html);
                }
            })
            if ($('#pagination-container').pagination('getTotalPage') == 1) {
                $('#pagination-container').hide();
            }
        }

        function sorting_data(data, column, type){
            var _col;
            switch(column) {
                case 0:
                    _col = 'm';
                    break;
                case 1:
                    _col = 'sessions';
                    break;
                case 2:
                    _col = 'catches';
                    break;
                case 3:
                    _col = 'CPUE';
                    break;
                case 4:
                    _col = 'avg_sessions';
                    break;
                case 5:
                    _col = 'avg_catches';
                    break;
                case 6:
                    _col = 'avg_CPUE';
                    break;
            }
            if (column == 0 || column == 1 || column == 2){
                if (type == 'sorting_asc') {
                    data.sort(function(a, b) {
                        return parseInt(a[_col]) - parseInt(b[_col]);
                    });
                }else{
                    data.sort(function(a, b) {
                        return parseInt(b[_col]) - parseInt(a[_col]);
                    });
                }
            }else{
                if (type == 'sorting_asc') {
                    data.sort(function(a, b) {
                        return parseFloat(a[_col]) - parseFloat(b[_col]);
                    });
                }else{
                    data.sort(function(a, b) {
                        return parseFloat(b[_col]) - parseFloat(a[_col]);
                    });
                }
            }
            UpdateTable(data, column, type);
        }

        function downloadImage() {
            if ($('.detail-chart-area').hasClass('active')) {
                var canvas_temp = ResizeCanvasImageData(current_chart, $('#my_chart').width(), $('#my_chart').height());
                DownloadImagePDFCallback(canvas_temp, "catches_season.pdf", $('.filter-information').html());
            } else {
                var data_post = {
                    "title": $('.filter-information').html(),
                    "header": [],
                    "body": []
                };
                data_post["header"] = ['{{ __('app.month')}}', '{{ __('app.sessions')}}', '{{ __('app.catches')}}', '{{ __('app.cpue')}}', '{{ __('app.sessions_10')}}', '{{ __('app.catches_10')}}', '{{ __('app.cpue_10')}}'];

                var temp_data = chart_data;
                var temp;
                $.each(temp_data, function (i, item) {
                    temp = [];
                    temp.push(item["month"]);
                    temp.push(item["sessions"]);
                    temp.push(item["catches"]);
                    temp.push(item["CPUE"]);
                    temp.push(item["avg_sessions"]);
                    temp.push(item["avg_catches"]);
                    temp.push(item["avg_CPUE"]);
                    data_post["body"].push(temp);
                });

                DownloadExcelData(data_post, 'catches_season.xlsx');
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
