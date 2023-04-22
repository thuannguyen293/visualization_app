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
                        <a href="{{route("catches_season")}}" class="btn btn-basic w-100">{{ __('app.season')}}</a>
                        <a href="{{route("catches_regional_comparison")}}" class="btn btn-basic w-100">{{ __('app.regional_comparison')}}</a>
                        <a href="{{route("catches_fishtype_comparison")}}" class="btn btn-basic w-100 active">{{ __('app.fishtype_comparison')}}</a>
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
            <h2 class="filter-information">{!!htmlentities(__('app.catches'))!!}, Zeitreihe {{date("Y")-10}} - {{date("Y") - 1}}, ganzer Kanton, Fliessgewässer, Alle Arten</h2>
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
    <script src="{{ asset('libs/chartjs/Chart.min.js') }}"></script>
    <script src="{{ asset('libs/nouislider/nouislider.js') }}"></script>
    <script src="{{ asset('libs/pagination/pagination.min.js') }}"></script>

    <script>
        var mySlider = document.getElementById('slider-range');
        var color_list = ['#005780', '#D45087', '#FFA600', '#009966', '#F69F6A', '#595959', '#00AEFF'];
        var min_year = 2011;
        var max_year = new Date().getFullYear() - 1;
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

            $('select[name="geographic_filter"], select[name="waterbody_type"]').on('change', function () {
                UpdateChart();
            });

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

            UpdateChart();
        });

        var current_chart;
        var chart_data;
        var table_column_number = 5;

        function DrawMyChart(data){
            $('#my_chart').remove(); // this is my <canvas> element
            $('.detail-chart-area').prepend('<canvas id="my_chart" width="900" height="500"></canvas>');
            current_chart = document.getElementById('my_chart');

            if (data.length == 0 || data["fishtypes"].length == 0) {
                $(".detail-area").addClass('no-data');
                return;
            }else{
                $(".detail-area").removeClass('no-data');
            }
            var time_range = mySlider.noUiSlider.get();
            var ctx_chart = document.getElementById('my_chart').getContext('2d');
            var labels = [];
            var datasets = {};
            var temp_data = data.catches;
            var avg = data.average;
            var last = Object.keys(temp_data).length;
            var color = Chart.helpers.color;
            $.each(data.fishtypes, function(k, fishtype){
                //var value = (item[fishtype.fishtype_code]) ? parseInt(item[fishtype.fishtype_code]) : 0;
                datasets[fishtype.name] = [];
            });

            $.each(temp_data, function (i, item) {
                if (i !== 'average') {
                    labels.push(item.year);

                    $.each(data.fishtypes, function(k, fishtype){
                        var value = (item[fishtype.fishtype_code]) ? parseInt(item[fishtype.fishtype_code]) : 0;
                        datasets[fishtype.name].push(value);
                    });
                }else{
                    if(parseInt(time_range[1]) >= min_year && parseInt(time_range[1]) <= max_year){
                        labels.push("");
                        labels.push(ConvertToHTML('{!!htmlentities(__('app.10year_avg'))!!}'));

                        $.each(data.fishtypes, function(k, fishtype){
                            var value = (item[fishtype.fishtype_code]) ? parseFloat(item[fishtype.fishtype_code]) : 0;
                            datasets[fishtype.name].push(null);
                            datasets[fishtype.name].push(value);
                        });
                    }
                }
            });

            var chart_render_data = {
                labels: labels,
                datasets: []
            };

            var count = 0;
            $.each(datasets, function(name,value){
                var _color = color_list[count];
                chart_render_data.datasets.push({
                    label: name,
                    data: value,
                    backgroundColor: _color
                });

                if( count < 6) count++;
            });


            var my_chart = new Chart(ctx_chart, {
                type: 'bar',
                data: chart_render_data,
                options: {
                    responsive: false,
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
                    scales: {
                        xAxes: [{
                            stacked: true,
                        }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                callback: function(value, index, values) {
                                    return numberWithCommas(value);
                                }
                            },
                            stacked: true,
                            scaleLabel: {
                                display: true,
                                labelString: '{{__('app.number')}}'
                            }
                        }]
                    }
                }
            });

        }


        function UpdateChart(){
            var geographic_id = $('select[name="geographic_filter"]').children("option:selected").val();
            var waterbody_type = $('select[name="waterbody_type"]').children("option:selected").val();
            if (geographic_id == "") geographic_id = "all";
            if (waterbody_type == "") waterbody_type = "all";
            var time_range = mySlider.noUiSlider.get();
            ShowLoading();
            $.ajax({
                url: '{{route('home')}}/api/catches/fishtype_comparison/chart',
                type: "get",
                data: {
                    geographic_id: geographic_id,
                    waterbody_type: waterbody_type,
                    time_range: time_range
                },
                success: function(result) {
                    HideLoading();
                    chart_data = result.data;
                    DrawMyChart(result.data);
                    UpdateTable(Object.values(result.data.catches));
                },
                error: function(){
                    HideLoading();
                }
            });

            UpdateFilterInformation('{!!htmlentities(__('app.catches'))!!}', true);
        }

        function UpdateTable(data, column = '', type = 'sorting_asc') {
            var time_range = mySlider.noUiSlider.get();
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
                var _element = ['{{__('app.genre')}}'];
                $.each(chart_data.fishtypes, function(i, item){
                    if (i >= j*col && i < col*(j+1) ) {
                        _element.push(item.name);
                    }
                });
                data_arr.push(_element);
                $.each(data, function(i, item){
                    var _element;
                    if (item.year == 'average') {
                        if(parseInt(time_range[1]) >= min_year && parseInt(time_range[1]) <= max_year){
                            _element = ['{!!htmlentities(__('app.10year_avg'))!!}'];
                        }
                    }else{
                        _element = [item.year];
                    }
                    $.each(chart_data.fishtypes, function(k, fishtype){
                        if (k >= j*col && k < col*(j+1) ) {
                            value = (item[fishtype.fishtype_code]) ? parseInt(item[fishtype.fishtype_code]) : 0;
                            if (item.year == 'average') {
                                if(parseInt(time_range[1]) >= min_year && parseInt(time_range[1]) <= max_year){
                                    value = (item[fishtype.fishtype_code]) ? parseFloat(item[fishtype.fishtype_code]) : 0;
                                    _element.push(value);
                                }
                            }else{
                                _element.push(value);
                            }
                            
                        }
                    });
                    data_arr.push(_element);
                });
            }
            console.log(data_arr);
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
            var _data = Object.values(data.catches);
            var _col;
            if (column == 0) {
                _col = 'year';
            }else{
                _col = chart_data.fishtypes[column-1].fishtype_code;
            }
            if(column == 0){
                if (type == 'sorting_asc') {
                    _data.sort(function(a, b) {
                        var regionA = String(a[_col]); // ignore upper and lowercase
                        var regionB = String(b[_col]); // ignore upper and lowercase
                        if (regionA < regionB) {
                            return -1;
                        }
                        if (regionA > regionB) {
                            return 1;
                        }
                        // names must be equal
                        return 0;
                    });
                }else{
                    _data.sort(function(a, b) {
                        var regionA = String(a[_col]); // ignore upper and lowercase
                        var regionB = String(b[_col]); // ignore upper and lowercase
                        if (regionA > regionB) {
                            return -1;
                        }
                        if (regionA < regionB) {
                            return 1;
                        }
                        // names must be equal
                        return 0;
                    });
                }
            }else{
                if (type == 'sorting_asc') {
                    _data.sort(function(a, b) {
                        return parseFloat(a[_col]) - parseFloat(b[_col]);
                    });
                }else{
                    _data.sort(function(a, b) {
                        return parseFloat(b[_col]) - parseFloat(a[_col]);
                    });
                }
            }
            UpdateTable(_data, column, type);
        }

        function downloadImage() {
            if ($('.detail-chart-area').hasClass('active')){
                var canvas_temp = ResizeCanvasImageData(current_chart, $('#my_chart').width(), $('#my_chart').height());
                DownloadImagePDFCallback(canvas_temp, "catches_fishtype_comparison.pdf", $('.filter-information').html());
            } else {
                // console.log('Download table file');
                var data_post = {
                    "title": $('.filter-information').html(),
                    "header": [],
                    "body": []
                };
                data_post["header"] = ['{{__('app.genre')}}'];
                $.each(chart_data.fishtypes, function(k, fishtype){
                    data_post["header"].push(fishtype.name);
                });

                var temp_data = chart_data.catches;
                var avg = chart_data.average;
                var temp;
                $.each(temp_data, function (i, item) {
                    temp = [];
                    if (item.year == 'average') {
                        temp.push('{{__('app.10year_avg')}}');
                    }else{
                        temp.push(item.year);
                    }
                    $.each(chart_data.fishtypes, function(j, fishtype){
                        value = (item[fishtype.fishtype_code]) ? parseInt(item[fishtype.fishtype_code]) : 0;
                        if (item.year == 'average') {
                            value = (item[fishtype.fishtype_code]) ? parseFloat(item[fishtype.fishtype_code]) : 0;
                        }
                        temp.push(value);
                    });
                    data_post["body"].push(temp);
                });

                DownloadExcelData(data_post, 'catches_fishtype_comparison.xlsx');
            }
            return false;
        }

        function showTableArea(){
            $('.detail-chart-area').removeClass('active');
            $('.detail-table-area').addClass('active');

            $('.btn-show-chart').removeClass('active');
            $('.btn-show-table').addClass('active');
            return false;

        }

        function showChartArea(){
            $('.detail-chart-area').addClass('active');
            $('.detail-table-area').removeClass('active');

            $('.btn-show-chart').addClass('active');
            $('.btn-show-table').removeClass('active');
            return false;
        }
    </script>
@endsection
