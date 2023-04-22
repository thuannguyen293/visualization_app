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
                        <a href="{{route("stocking_time_series")}}" class="btn btn-basic w-100">{{ __('app.time_series')}}</a>
                        <a href="{{route("stocking_regional_comparison")}}" class="btn btn-basic w-100 active">{{ __('app.regional_comparison')}}</a>
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
            <h4 class="filter-information">{{ __('app.stocking')}}, Zeitreihe {{date("Y")-1}}, ganzer Kanton, Fliessgewässer, Alle Arten</h4>
            <div class="btn-group action-btn-group">
                <button class="btn-show-chart btn btn-basic w-100 active" onclick="showChartArea();">{{ __('app.chart')}}</button>
                <button class="btn-show-table btn btn-basic w-100" onclick="showTableArea();">{{ __('app.table')}}</button>
                <button class="btn btn-basic w-100" onclick="downloadImage();">{{ __('app.download')}}</button>
            </div>
        </div>
        <div class="col-md-12 detail-option-area">
            <div class="btn-group action-btn-group">
                <button class="btn btn-basic w-100 btn-type active" data-type="lake">{{ __('app.lake')}}</button>
                <button class="btn btn-basic w-100 btn-type" data-type="stream">{{ __('app.stream')}}</button>
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
            <table>
                <thead>
                    <tr>
                        <th>{{ __('app.region')}}</th>
                        <th>{{ __('app.total')}}</th>
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
    <script src="{{ asset('libs/nouislider/nouislider.js') }}"></script>

    <script src="{{ asset('libs/dom-to-image.min.js') }}"></script>
    <script src="{{ asset('libs/file-save.min.js') }}"></script>

    <script src="{{ asset('app/js/ob_geo_chart.js') }}" defer></script>
    <script src="{{ asset('app/js/polygon_data.js') }}" defer></script>
    <script src="{{ asset('libs/jquery.datatables/jquery.datatables.min.js') }}"></script>
    <script>
        var mySlider = document.getElementById('slider-range');
        var data = [];
        var geo_chart;
        var chart_data;
        var current_area_type = 'lake';
        $(document).ready(function () {
            $.extend( $.fn.dataTable.defaults, {
                searching: false,
                ordering: true,
                paging: false,
                info: false
            });
            var mySlider = document.getElementById('slider-range');
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
                UpdateChart(current_area_type);
            });

            $('select[name="fishtype_filter"], select[name="waterbody_type"]').on('change', function () {
                UpdateChart(current_area_type);
            });
            $('.detail-option-area .btn-type').on('click', function () {
                $('.detail-option-area .btn-type').removeClass('active');
                $(this).addClass('active');
                current_area_type = $(this).data('type');
                drawMap(data, current_area_type);
                UpdateTable(data);
            });

            UpdateChart(current_area_type);
        });

        function init(){
            // UpdateChart();
        }

        function drawMap(data, type = 'lake') {
            var main_data = [];
            var stocking = data.stocking[type];
            main_data.push(["Area", "Total","Name"]);
            $.each(stocking, function(i, item){
                main_data.push([item.region_code, item.total, item.region_name]);
            });
            geo_chart = new OBGeoChart.getInstance({
                "canvas_id": "geo_canvas",
                "data": main_data,
                "type": "stocking"
            });
            geo_chart.Init();
        }

        function UpdateChart(type = 'lake'){
            var waterbody_type = $('select[name="waterbody_type"]').children("option:selected").val();
            if (waterbody_type == "") waterbody_type = "all";
            var year = mySlider.noUiSlider.get();
            ShowLoading();
            $.ajax({
                url: '/api/stocking/regional_comparison/chart',
                type: "get",
                data: {
                    waterbody_type: waterbody_type,
                    year: year
                },
                success: function(result) {
                    HideLoading();
                    data = result.data;
                    drawMap(result.data, type);
                    UpdateTable(result.data);
                    chart_data = result.data;
                },
                error: function(){
                    HideLoading();
                }
            });

            UpdateFilterInformation('{{ __('app.stocking')}}', false);
        }

        function UpdateTable(data) {
            var container = $('.detail-table-area table tbody');
            var html = '';
            var type =  $('.detail-option-area .btn-type.active').data('type');
            var stocking = data.stocking[type];
            $.each(stocking, function(i, item){
                var fish_total = (item.total) ? item.total:0;
                html += '<tr>';
                html += '   <td>'+item.region_name+'</td>';
                html += '   <td>'+numberWithCommas(fish_total)+'</td>';
                html += '</tr>';
            });
            container.html(html);
            $('.detail-table-area table').DataTable();
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
                // console.log('Download chart image');
                domtoimage.toJpeg(document.getElementById('ob_geo_chart'))
                    .then(function (dataUrl) {
                        var pdf = new jsPDF('l', 'mm', [750, 800]);
                        pdf.text(30, 10, $('.filter-information').html());
                        pdf.addImage(dataUrl, 'JPEG', 10, 15);
                        pdf.save("stocking_regional_comparison.pdf");
                    })
                    .catch(function (error) {
                        console.error('oops, something went wrong!', error);
                    });

            } else {
                // console.log('Download table file');
                var type =  $('.detail-option-area .btn-type.active').data('type');
                var data_post = {
                    "title": $('.filter-information').html(),
                    "header": [],
                    "body": []
                };
                $(".detail-table-area table thead tr th").each(function () {
                    data_post["header"].push($(this).text());
                });

                var temp_data = chart_data.stocking[type];
                var temp;
                $.each(temp_data, function (i, item) {
                    temp = [];
                    temp.push(item["region_name"]);
                    temp.push(item["total"]);
                    data_post["body"].push(temp);
                });

                DownloadExcelData(data_post, 'stocking_regional_comparison.xlsx');
            }
        }
    </script>
@endsection
