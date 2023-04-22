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
                    <div class="slider-area">
                        <div id="slider-range"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row detail-area">
        <div class="col-md-12 detail-info-area">
            <h4 class="filter-information">{{ __('app.catches')}}, Zeitreihe {{date("Y") - 1}}, ganzer Kanton, Fliessgewässer, Alle Arten</h4>
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
            var style_1 = "color: #003f5c";
            var style_2 = "color: #ef5675";
            var style_3 = "color: #7a519a";
            var style_4 = "color: #00cfe3";
            var style_5 = "color: #ff6e54;";
            var style_6 = "color: #ffa600;";

            $.each(data, function (i, item) {
                var row = [
                    item.month,
                    (item.avg_sessions) ? parseFloat(item.avg_sessions):0, style_1, '<div class="tooltip-c"><p>{{ __('app.month')}}: <b>'+ item.month +'</b></p><p>{{ __('app.sessions_10')}}: <b>'+numberWithCommas(parseFloat(item.avg_sessions))+'</b></p></div>',
                    (item.avg_catches) ? parseFloat(item.avg_catches):0, style_2, '<div class="tooltip-c"><p>{{ __('app.month')}}: <b>'+ item.month +'</b></p><p>{{ __('app.catches_10')}}: <b>'+numberWithCommas(parseFloat(item.avg_catches))+'</b></p></div>',
                    (item.sessions) ? parseFloat(item.sessions):0, style_3, '<div class="tooltip-c"><p>{{ __('app.month')}}: <b>'+ item.month +'</b></p><p>{{ __('app.sessions')}}: <b>'+numberWithCommas(parseFloat(item.sessions))+'</b></p></div>',
                    (item.catches) ? parseFloat(item.catches):0, style_4, '<div class="tooltip-c"><p>{{ __('app.month')}}: <b>'+ item.month +'</b></p><p>{{ __('app.catches')}}: <b>'+numberWithCommas(parseFloat(item.catches))+'</b></p></div>',
                    (item.avg_CPUE) ? parseFloat(item.avg_CPUE):0, style_5, '<div class="tooltip-c"><p>{{ __('app.month')}}: <b>'+ item.month+'</b></p><p>{{ __('app.cpue_10')}}: <b>'+numberWithCommas(parseFloat(item.avg_CPUE))+'</b></p></div>',
                    (item.CPUE) ? parseFloat(item.CPUE):0, style_6, '<div class="tooltip-c"><p>{{ __('app.month')}}: <b>'+ item.month +'</b></p><p>CPUE: <b>'+numberWithCommas(parseFloat(item.CPUE))+'</b></p></div>'
                ];
                dataset.push(row);
            });

            dataset.unshift([
                "{{ __('app.month')}}'",
                "{{ __('app.sessions_10')}}", {role: 'style'}, {role: 'tooltip', p: {html: true}},
                "{{ __('app.catches_10')}}", {role: 'style'}, {role: 'tooltip', p: {html: true}},
                "{{ __('app.sessions')}}", {role: 'style'}, {role: 'tooltip', p: {html: true}},
                "{{ __('app.catches')}}", {role: 'style'}, {role: 'tooltip', p: {html: true}},
                "{{ __('app.cpue_10')}}", {role: 'style'}, {role: 'tooltip', p: {html: true}},
                "{{ __('app.cpue')}}", {role: 'style'}, {role: 'tooltip', p: {html: true}}
            ]);


            var main_data = google.visualization.arrayToDataTable(dataset);

            var main_options = {
                vAxis: [
                    {title: 'Anzahl'},
                    {title: 'CPUE'}
                ],
                // hAxis: {title: 'Year'},
                seriesType: 'bars',
                series: {
                    0: {color: '#003f5c'},
                    1: {color: '#ef5675'},
                    2: {color: '#7a519a'},
                    3: {color: '#00cfe3'},
                    4: {color: '#ff6e54', type: 'line', targetAxisIndex: 1},
                    5: {color: '#ffa600', type: 'line', targetAxisIndex: 1}
                },
                legend: {position: 'top', maxLines: 2},
                pointSize: 5,
                tooltip: { isHtml: true }

                // yAxis:
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
                var color = ['#003f5c', '#ef5675', '#7a519a', '#00cfe3', '#ffa600', '#ffa600'];
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
                                }
                            };
                            // grey out the legend entry
                            col = Math.floor(col/3);
                            main_options.series[col]['color'] = '#CCCCCC';
                        }else {
                            // show the data series
                            columns[col] = col;
                            col = Math.floor(col/3);
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
            // console.log("UpdateChart");
            var geographic_id = $('select[name="geographic_filter"]').children("option:selected").val();
            var fishtype_code = $('select[name="fishtype_filter"]').children("option:selected").val();
            var waterbody_type = $('select[name="waterbody_type"]').children("option:selected").val();
            if (geographic_id == "") geographic_id = "all";
            if (fishtype_code == "") fishtype_code = "all";
            if (waterbody_type == "") waterbody_type = "all";
            var year = mySlider.noUiSlider.get();
            ShowLoading();
            $.ajax({
                url: '/api/catches/season/chart',
                type: "get",
                data: {
                    geographic_id: geographic_id,
                    fishtype_code: fishtype_code,
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

            UpdateFilterInformation('{{ __('app.catches')}}', false);
        }

        function UpdateTable(data, column = '', type = 'sorting_asc') {
            var container = $('.detail-table-area table tbody');
            var html = '';
            var data_header = ['{{ __('app.sessions')}}', '{{ __('app.catches')}}', '{{ __('app.cpue')}}', '{{ __('app.sessions_10')}}', '{{ __('app.catches_10')}}', '{{ __('app.cpue_10')}}'];
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
                // console.log('Download chart image');
                var img_uri = current_chart.getImageURI();
                ConvertToDataJPGE(img_uri, $('#main_chart').width(), $('#main_chart').height(), "catches_season.pdf", $('.filter-information').html());
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
