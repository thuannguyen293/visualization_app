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
                    <div class="slider-area">
                        <div id="slider-range"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row detail-area">
        <div class="col-md-12 detail-info-area">
            <h4 class="filter-information">{{ __('app.catches')}}, Zeitreihe {{date("Y")-10}} - {{date("Y") - 1}}, ganzer Kanton, Fliessgewässer, Alle Arten</h4>
            <div class="btn-group action-btn-group">
                <button class="btn-show-chart btn btn-basic w-100 active" onclick="showChartArea();">{{ __('app.chart')}}</button>
                <button class="btn-show-table btn btn-basic w-100" onclick="showTableArea();">{{ __('app.table')}}</button>
                <button class="btn btn-basic w-100" onclick="downloadImage();">{{ __('app.download')}}</button>
            </div>
        </div>
        <div class="col-md-12 detail-chart-area active">
            <div id="main_chart" style="width: 100%; height: 450px;"></div>
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
    <script src="{{ asset('libs/nouislider/nouislider.js') }}"></script>

    <script src="https://www.gstatic.com/charts/loader.js"></script>
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

        function init(){
            // UpdateChart();
        }

        google.charts.load('current', {'packages': ['corechart']});
        google.charts.setOnLoadCallback(init);

        var current_chart;
        var chart_data;
        var table_column_number = 5;

        function drawVisualization(data) {
            if (data.catches.length == 0) {
                $('#main_chart').html("");
                return;
            }
            var dataset = [];
            var fishtypes = ['Genre'];
            var temp_data = data.catches;
            var avg = data.average;
            $.each(temp_data, function(i, item){
                if (i !== 'average') {
                    var row = [String(i)];
                    $.each(data.fishtypes, function(k, fishtype){
                        value = (item[fishtype.fishtype_code]) ? parseInt(item[fishtype.fishtype_code]) : 0;
                        row.push(value);
                        row.push('<div class="tooltip-c"><p>{{__('app.year')}}: <b>'+String(i)+'</b></p><p>'+fishtype.name+': <b>'+numberWithCommas(value)+'</b></p></div>');
                    });
                    dataset.push(row);
                }else{
                    var row = [''];
                    $.each(data.fishtypes, function(k, fishtype){
                        row.push(undefined);
                        row.push('');
                    });
                    dataset.push(row);

                    var row = ["{{__('app.10year_avg')}}"];
                    $.each(data.fishtypes, function(k, fishtype){
                        value = (item[fishtype.fishtype_code]) ? parseInt(item[fishtype.fishtype_code]) : 0;
                        row.push(value);
                        row.push('<div class="tooltip-c"><p>{{__('app.year')}}: <b>{{__('app.10year_avg')}}</b></p><p>'+fishtype.name+': <b>'+numberWithCommas(value)+'</b></p></div>');
                    });
                    dataset.push(row);
                }
            });

            $.each(data.fishtypes, function(i, item){
                fishtypes.push(item.name);
                fishtypes.push({role: 'tooltip', p: {html: true}});
            });
            dataset.unshift(fishtypes);
            var main_data = google.visualization.arrayToDataTable(dataset);

            var main_options = {
                legend: { position: 'top', maxLines: 3 },
                bar: { groupWidth: '75%' },
                isStacked: true,
                tooltip: { isHtml: true }
            };
            var main_div =  document.getElementById('main_chart');
            var main_chart = new google.visualization.ColumnChart(main_div);
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
                            col = Math.floor(col/2);
                            series[col]['color'] = '#CCCCCC';
                            main_options['series'] = series; 
                        }else {
                            // show the data series
                            columns[col] = col;
                            col = Math.floor(col/2);
                            series[col]['color'] = null;
                            main_options['series'] = series; 
                        }
                        var view = new google.visualization.DataView(main_data);
                        view.setColumns(columns);
                        main_chart.draw(view, main_options);
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
                url: '/api/catches/fishtype_comparison/chart',
                type: "get",
                data: {
                    geographic_id: geographic_id,
                    waterbody_type: waterbody_type,
                    time_range: time_range
                },
                success: function(result) {
                    HideLoading();
                    chart_data = result.data;
                    drawVisualization(result.data);
                    UpdateTable(Object.values(result.data.catches));
                },
                error: function(){
                    HideLoading();
                }
            });

            UpdateFilterInformation('{{ __('app.catches')}}', true);
        }

        function UpdateTable(data, column = '', type = 'sorting_asc') {
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
                    if (item.year == 'average') {
                        _element = ['{{__('app.10year_avg')}}'];
                    }else{
                        _element = [item.year];
                    }
                    $.each(chart_data.fishtypes, function(k, fishtype){
                        if (k >= j*col && k < col*(j+1) ) {
                            value = (item[fishtype.fishtype_code]) ? parseInt(item[fishtype.fishtype_code]) : 0;
                            if (item.year == 'average') {
                                value = (item[fishtype.fishtype_code]) ? parseFloat(item[fishtype.fishtype_code]) : 0;
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
                // console.log('Download chart image');
                var img_uri = current_chart.getImageURI();
                ConvertToDataJPGE(img_uri, $('#main_chart').width(), $('#main_chart').height(), "catches_fishtype_comparison.pdf", $('.filter-information').html());;
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
        }

        function showTableArea(){
            $('.detail-chart-area').removeClass('active');
            $('.detail-table-area').addClass('active');

            $('.btn-show-chart').removeClass('active');
            $('.btn-show-table').addClass('active');


        }

        function showChartArea(){
            $('.detail-chart-area').addClass('active');
            $('.detail-table-area').removeClass('active');

            $('.btn-show-chart').addClass('active');
            $('.btn-show-table').removeClass('active');
        }
    </script>
@endsection
