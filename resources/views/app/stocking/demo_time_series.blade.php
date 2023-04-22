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
                        <a href="{{route("stocking_time_series")}}" class="btn btn-basic w-100 active">{{ __('app.time_series')}}</a>
                        <a href="{{route("stocking_regional_comparison")}}" class="btn btn-basic w-100">{{ __('app.regional_comparison')}}</a>
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
                            <select name="fishtype_filter" class="js-select2-active w-100" data-placeholders="{{ __('app.fishtype_filter')}}" disabled>
                                <option></option>
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
            <h4 class="filter-information">{{ __('app.stocking')}}, Zeitreihe {{date("Y")-10}} - {{date("Y")-1}}, ganzer Kanton, Fliessgewässer, Alle Arten</h4>
            <div class="btn-group action-btn-group">
                <button class="btn-show-chart btn btn-basic w-100 active" onclick="showChartArea();">{{ __('app.chart')}}</button>
                <button class="btn-show-table btn btn-basic w-100" onclick="showTableArea();">{{ __('app.table')}}</button>
                <button class="btn btn-basic w-100" onclick="downloadImage();">{{ __('app.download')}}</button>
            </div>
        </div>
        <div class="col-md-12 detail-chart-area active">
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
    </div>


@endsection

@section('script_footer')
    @parent
    {{--    <script src="{{ asset('libs/jqueryui.v1.12.1/jquery-ui.js') }}"></script>--}}
    <script src="{{ asset('libs/nouislider/nouislider.js') }}"></script>
    <script src="{{ asset('libs/chartjs/Chart.min.js') }}"></script>
    <script src="{{ asset('libs/pagination/pagination.min.js') }}"></script>
    <script>
        var mySlider = document.getElementById('slider-range');

        $(document).ready(function () {
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
            $('.detail-chart-area').append('<canvas id="my_chart" width="900" height="500"></canvas>');
            current_chart = document.getElementById('my_chart');

            if (data.length == 0) {
                return;
            }

            var ctx_chart = current_chart.getContext('2d');
            var labels = [];
            var datasets = {};
            var temp_data = data.stocking;
            var color_list = ["#003f5c","#ef5675","#7a519a","#00cfe3"];
            $.each(data.fishtypes, function(k, fishtype){
                datasets[fishtype.name] = [];
            });

            $.each(temp_data, function (i, item) {
                labels.push(String(i));
                $.each(data.fishtypes, function(k, type){
                    value = (item[type.code]) ? parseInt(item[type.code]) : 0;
                    datasets[type.name].push(value);
                });
            });

            var chart_render_data = {
                labels: labels,
                datasets: []
            };
            var count = 0;
            $.each(datasets, function(name,value){
                chart_render_data.datasets.push({
                    label: name,
                    backgroundColor: color_list[count],
                    data: value
                });
                count++;
            });

            var my_chart = new Chart(ctx_chart, {
                type: 'bar',
                data: chart_render_data,
                options: {
                    title: {display: false},
                    legend: {position: 'bottom'},
                    tooltips: {
                        mode: 'index',
                        intersect: false,
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
                    responsive: true,
                    scales: {
                        xAxes: [{
                            stacked: true,
                        }],
                        yAxes: [{
                            stacked: true
                        }]
                    }
                }
            });
        }

        function UpdateChart() {
            var geographic_id = $('select[name="geographic_filter"]').children("option:selected").val();
            var fishtype_code = $('select[name="fishtype_filter"]').children("option:selected").val();
            var waterbody_type = $('select[name="waterbody_type"]').children("option:selected").val();
            if (geographic_id == "") geographic_id = "all";
            if (fishtype_code == "") fishtype_code = "all";
            if (waterbody_type == "") waterbody_type = "all";
            var time_range = mySlider.noUiSlider.get();
            ShowLoading();
            $.ajax({
                url: '/api/stocking/time_series/chart',
                type: "get",
                data: {
                    geographic_id: geographic_id,
                    fishtype_code: fishtype_code,
                    waterbody_type: waterbody_type,
                    time_range: time_range
                },
                success: function (result) {
                    HideLoading();
                    chart_data = result.data;
                    drawVisualization(result.data);
                    UpdateTable(Object.values(result.data.stocking));
                },
                error: function(){
                    HideLoading();
                }
            });

            UpdateFilterInformation('{{ __('app.stocking')}}', true);
        }

        function UpdateTable(data, column = '', type = 'sorting_asc') {
            console.log(data);
            var container = $('.detail-table-area table tbody');
            var header_html = '';
            var container_html = '';
            var col = table_column_number;
            var total = chart_data.fishtypes.length;
            var page = Math.floor(total/col);
            if (total%col > 0) {
                page = page + 1;
            }

            var data_arr = [];
            for (var j = 0; j < page; j++) {
                var _element = ['{{__('app.year')}}'];
                $.each(chart_data.fishtypes, function(i, item){
                    if (i >= j*col && i < col*(j+1) ) {
                        _element.push(item.name);
                    }
                });
                data_arr.push(_element);
                $.each(data, function(i, item){
                    _element = [String(item.year)];
                    $.each(chart_data.fishtypes, function(k, fishtype){
                        if (k >= j*col && k < col*(j+1) ) {
                            value = (item[fishtype.code]) ? parseInt(item[fishtype.code]) : 0;
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
            });
            if ($('#pagination-container').pagination('getTotalPage') == 1) {
                $('#pagination-container').hide();
            }
        }

        function sorting_data(data, column, type){
            var _data = Object.values(data.stocking);
            var _col;
            if (column == 0) {
                _col = 'year';
            }else{
                _col = chart_data.fishtypes[column-1].code;
            }
            if (type == 'sorting_asc') {
                _data.sort(function(a, b) {
                    return parseInt(a[_col]) - parseInt(b[_col]);
                });
            }else{
                _data.sort(function(a, b) {
                    return parseInt(b[_col]) - parseInt(a[_col]);
                });
            }
            UpdateTable(_data, column, type);
        }

        function downloadImage() {

            if ($('.detail-chart-area').hasClass('active')){
                // console.log('Download chart image');
                var img_uri = current_chart.getImageURI();
                ConvertToDataJPGE(img_uri, $('#main_chart').width(), $('#main_chart').height(), "stocking_time_series.pdf", $('.filter-information').html());;
            } else {
                // console.log('Download table file');
                var data_post = {
                    "title": $('.filter-information').html(),
                    "header": [],
                    "body": []
                };
                data_post["header"] = ['{{__('app.year')}}'];
                $.each(chart_data.fishtypes, function(k, fishtype){
                    data_post["header"].push(fishtype.name);
                });

                var temp_data = chart_data.stocking;
                var temp;
                $.each(temp_data, function (i, item) {
                    temp = [];
                    temp.push(item.year);
                    $.each(chart_data.fishtypes, function(j, type){
                        value = (item[type.code]) ? parseInt(item[type.code]) : 0;
                        temp.push(value);
                    });
                    data_post["body"].push(temp);
                });

                DownloadExcelData(data_post, 'stocking_time_series.xlsx');
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
