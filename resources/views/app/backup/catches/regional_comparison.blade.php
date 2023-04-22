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
                        <a href="{{route("catches_regional_comparison")}}" class="btn btn-basic w-100 active">{{ __('app.regional_comparison')}}</a>
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
                            <select name="geographic_filter" class="js-select2-active w-100" data-placeholders="{{ __('app.geographic_filter')}}" disabled>
                                <option></option>
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
        <div class="col-md-12 detail-option-area">
            <div class="btn-group action-btn-group">
                <button class="btn btn-basic w-100 btn-type active" data-type="sessions">{{ __('app.sessions')}}</button>
                <button class="btn btn-basic w-100 btn-type" data-type="catches">{{ __('app.catches')}}</button>
                <button class="btn btn-basic w-100 btn-type" data-type="CPUE">CPUE</button>
            </div>
        </div>
        <div class="col-md-12 detail-chart-area active" id="ob_geo_chart">
            <div class="ob-geo-chart">
                <div class="chart">
                    <canvas id="geo_canvas" width="800" height="600"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-12 detail-table-area">
            <table class="max_width140">
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

    <script src="{{ asset('libs/dom-to-image.min.js') }}"></script>
    <script src="{{ asset('libs/file-save.min.js') }}"></script>
    <script src="{{ asset('libs/jspdf/jspdf.min.js') }}"></script>

    <script src="{{ asset('app/js/ob_geo_chart.js') }}" defer></script>
    <script src="{{ asset('app/js/polygon_data.js') }}" defer></script>
    <script src="{{ asset('libs/pagination/pagination.min.js') }}"></script>
    <script>
        var mySlider = document.getElementById('slider-range');
        var data = [];
        var geo_chart;
        var chart_data;
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

            init();

            mySlider.noUiSlider.on('change.one', function () {
                var current_type =  $('.detail-option-area .btn-type.active').data('type');
                UpdateChart(current_type);
            });
            $('select[name="fishtype_filter"], select[name="waterbody_type"]').on('change', function () {
                var current_type =  $('.detail-option-area .btn-type.active').data('type');
                UpdateChart(current_type);
            });
            $('.detail-option-area .btn-type').on('click', function () {
                $('.detail-option-area .btn-type').removeClass('active');
                $(this).addClass('active');
                drawMap(data, $(this).data('type'));
            });

            UpdateChart('sessions');

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

        function init(){
            // UpdateChart('sessions');
        }

        function drawMap(data, type = 'sessions') {
            var main_data = [];
            main_data.push(["Area", "Sessions", "Name"]);
            $.each(data, function(i, item){
                if (type === 'CPUE') {
                    main_data.push([i, parseFloat(item[type]), i]);
                }else{
                    main_data.push([i, item[type], i]);
                }
            });
            geo_chart = new OBGeoChart.getInstance({
                "canvas_id": "geo_canvas",
                "data": main_data,
                "type": type,
            });
            geo_chart.Init();
        }

        function UpdateChart(type = 'sessions'){
            // console.log("UpdateChart");
            var fishtype_code = $('select[name="fishtype_filter"]').children("option:selected").val();
            var waterbody_type = $('select[name="waterbody_type"]').children("option:selected").val();
            if (fishtype_code == "") fishtype_code = "all";
            if (waterbody_type == "") waterbody_type = "all";
            var year = mySlider.noUiSlider.get();
            ShowLoading();
            $.ajax({
                url: '/api/catches/regional_comparison/chart',
                type: "get",
                data: {
                    fishtype_code: fishtype_code,
                    waterbody_type: waterbody_type,
                    year: year
                },
                success: function(result) {
                    HideLoading();
                    data = result.data;
                    drawMap(result.data, type);
                    UpdateTable(Object.values(result.data));
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
            var data_header = ['{{ __('app.catches')}}', '{{ __('app.sessions')}}', '{{ __('app.cpue')}}', '{{ __('app.catches_10')}}', '{{ __('app.sessions_10')}}', '{{ __('app.cpue_10')}}'];
            var col = table_column_number;
            var total = data_header.length;
            var page = Math.floor(total/col);

            if (total%col > 0) {
                page = page + 1;
            }

            var data_arr = [];
            for (var j = 0; j < page; j++) {
                var _element = ['{{ __('app.region')}}'];
                $.each(data_header, function(i, item){
                    if (i >= j*col && i < col*(j+1) ) {
                        _element.push(item);
                    }
                });
                data_arr.push(_element);
                $.each(data, function(i, item){
                    _element = [item.region_name];
                    $.each(data_header, function(k, header){
                        if (k >= j*col && k < col*(j+1) ) {
                            switch(k) {
                                case 0:
                                    value = item.catches;
                                    break;
                                case 1:
                                    value = item.sessions;
                                    break;
                                case 2:
                                    value = item.CPUE;
                                    break;
                                case 3:
                                    value = parseFloat(item.avg_catches);
                                    break;
                                case 4:
                                    value = parseFloat(item.avg_sessions);
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
        }
        
        function sorting_data(data, column, type){
            var data = Object.values(data);
            var _col;
            switch(column) {
                case 0:
                    _col = 'region_name';
                    break;
                case 1:
                    _col = 'catches';
                    break;
                case 2:
                    _col = 'sessions';
                    break;
                case 3:
                    _col = 'CPUE';
                    break;
                case 4:
                    _col = 'avg_catches';
                    break;
                case 5:
                    _col = 'avg_sessions';
                    break;
                case 6:
                    _col = 'avg_CPUE';
                    break;
            }
            if(column == 0){
                if (type == 'sorting_asc') {
                    data.sort(function(a, b) {
                        var regionA = a[_col].toUpperCase(); // ignore upper and lowercase
                        var regionB = b[_col].toUpperCase(); // ignore upper and lowercase
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
                    data.sort(function(a, b) {
                        var regionA = a[_col].toUpperCase(); // ignore upper and lowercase
                        var regionB = b[_col].toUpperCase(); // ignore upper and lowercase
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
            }else if (column == 1 || column == 2){
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
        function downloadImage() {
            if ($('.detail-chart-area').hasClass('active')){
                domtoimage.toJpeg(document.getElementById('ob_geo_chart'))
                    .then(function (dataUrl) {
                        var pdf = new jsPDF('l', 'mm', [750, 800]);
                        pdf.text(30, 10, $('.filter-information').html());
                        pdf.addImage(dataUrl, 'JPEG', 10, 15);
                        pdf.save("ob_geo_chart.pdf");
                    })
                    .catch(function (error) {
                        console.error('oops, something went wrong!', error);
                    });
            } else {
                var data_post = {
                    "title": $('.filter-information').html(),
                    "header": [],
                    "body": []
                };

                data_post["header"] = ['{{ __('app.region')}}', '{{ __('app.catches')}}', '{{ __('app.sessions')}}', '{{ __('app.cpue')}}', '{{ __('app.catches_10')}}', '{{ __('app.sessions_10')}}', '{{ __('app.cpue_10')}}'];

                var temp_data = chart_data;
                var temp;
                $.each(temp_data, function (i, item) {
                    temp = [];
                    temp.push(item["region_name"]);
                    temp.push(item["catches"]);
                    temp.push(item["sessions"]);
                    temp.push(item["CPUE"]);
                    temp.push(item["avg_catches"]);
                    temp.push(item["avg_sessions"]);
                    temp.push(item["avg_CPUE"]);
                    data_post["body"].push(temp);
                });

                DownloadExcelData(data_post, 'catches_regional_comparison.xlsx');
            }
        }
    </script>
@endsection
