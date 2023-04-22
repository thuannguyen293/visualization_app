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
                        <p class="btn btn-basic w-100 active" data-buyer="1">{{ __('app.local_buyers')}}</p>
                        <p class="btn btn-basic w-100" data-buyer="2">{{ __('app.foreign_buyers')}}</p>
                        <p class="btn btn-basic w-100" data-buyer="all">{{ __('app.all_licenses')}}</p>
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
            <h2 class="filter-information">{!!htmlentities(__('app.licenses'))!!}, {{date("Y")-10}} - {{date("Y")-1}}, Local Buyers</h2>
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
    {{--<script src="https://www.gstatic.com/charts/loader.js"></script>--}}
    <script src="{{ asset('libs/chartjs/Chart.min.js') }}"></script>
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
                UpdateChart($('.btn-group p.active').data('buyer'));
            });

            $('.btn-group p').on('click', function () {
                $('.btn-group p').removeClass('active');
                $(this).addClass('active');
                UpdateChart($(this).data('buyer'));
            });

            UpdateChart(1);
        });

        function init() {
            //UpdateChart(1); // 1 is local_buyer
        }

        var current_chart;
        var chart_data;

        function drawVisualization(data) {
            $('#my_chart').remove(); // this is my <canvas> element
            $('.detail-chart-area').prepend('<canvas id="my_chart" width="900" height="500"></canvas>');
            current_chart = document.getElementById('my_chart');

            if (data.length == 0 || data["average"].length == 0) {
                $(".detail-area").addClass('no-data');
                return;
            }else{
                $(".detail-area").removeClass('no-data');
            }

            var time_range = mySlider.noUiSlider.get();
            var ctx_chart = document.getElementById('my_chart').getContext('2d');
            var labels = [];
            var datasets = {};
            var temp_data = data.licenses;
            var avg = data.average;
            var types = data.license_types.filter(function(value, index, arr){ return index !== 2;});//Remove Other License - index = 2;
            var color_list = ['#005780', '#D45087', '#FFA600'];

            $.each(types, function(k, license_type){
                datasets[license_type] = [];
            });
            $.each(temp_data, function (i, item) {
                labels.push(String(i));
                $.each(types, function(k, license_type){
                    var element = item.find(el => el.license_type === license_type);
                    var value = 0;
                    if (element) {
                        value = element.licenses;
                    }
                    datasets[license_type].push(value);
                });
            });
            if(parseInt(time_range[1]) >= min_year && parseInt(time_range[1]) <= max_year){
                labels.push("");
                labels.push(ConvertToHTML('{!!htmlentities(__('app.10year_avg'))!!}'));
                $.each(types, function(k, license_type){
                    var element = avg.find(el => el.license_type === license_type);
                    var value = 0;
                    if (element) {
                        value = parseFloat(element.average);
                    }
                    datasets[license_type].push(null);
                    datasets[license_type].push(value);
                });
            }

            var chart_render_data = {
                labels: labels,
                datasets: []
            };
            var count = 0;
            $.each(datasets, function(name,value){
                var _color = color_list[count];
                if (name !== 'Jugendpatent') {
                    chart_render_data.datasets.push({
                        label: name,
                        data: value,
                        yAxisID: 'y-axis-1',
                        order: 1,
                        backgroundColor: _color
                    });
                }else{
                    chart_render_data.datasets.push({
                        type: 'line',
                        label: name,
                        borderColor: _color,
                        fill: false,
                        data: value,
                        yAxisID: 'y-axis-1',
                        lineTension: 0,
                        order: 0
                    });
                }

                count++;
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
                            stacked: false,
                        }],
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
                            stacked: false,
                            scaleLabel: {
                                display: true,
                                labelString: '{{__('app.number')}}'
                            }
                        }]
                    }
                }
            });
        }

        function UpdateChart(buyer_type) {
            var time_range = mySlider.noUiSlider.get();
            ShowLoading();
            $.ajax({
                url: '{{route('home')}}/api/licenses/chart',
                type: "get",
                data: {
                    buyer_type: buyer_type,
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


            var time_range = mySlider.noUiSlider.get();
            var text_filter_information = '{!!htmlentities(__('app.licenses'))!!}, ';
            text_filter_information += $('.btn-group p.active').text();
            text_filter_information += ' ' + Math.round(time_range[0]) + ' - ' + Math.round(time_range[1]);

            $('.filter-information').html(text_filter_information);
        }

        function UpdateTable(data) {
            if ( $.fn.dataTable.isDataTable('.detail-table-area table') ) {
                $('.detail-table-area table').DataTable().destroy();
            }
            var time_range = mySlider.noUiSlider.get();
            var header_container = $('.detail-table-area table thead');
            var container = $('.detail-table-area table tbody');
            var header_html = '';
            var container_html = '';
            var types = data.license_types.filter(function(value, index, arr){ return index !== 3;});//Remove Jugendpatent - index = 3;
            header_html = '<tr>';
            header_html += '<th>{{__('app.year')}}</th>';
            $.each(types, function(i, license_type){
                if (license_type == 'other') {
                    header_html += '<th>{!!htmlentities(__('app.other_licenses'))!!}</th>';
                }else{
                    header_html += '<th>'+license_type+'</th>';
                }
            });
            header_html += '</tr>';
            header_container.html(header_html);
            $.each(data.licenses, function(i, item){
                container_html += '<tr>';
                container_html += '   <td>'+i+'</td>';
                $.each(types, function(k, licensetype){
                    element = item.find(el => el.license_type === licensetype);
                    if (element) {
                        value = element.licenses;
                    }else{
                        value = 0;
                    }
                    container_html += '   <td>'+numberWithCommas(value)+'</td>';
                });
                container_html += '</tr>';
            });
            if(parseInt(time_range[1]) >= min_year && parseInt(time_range[1]) <= max_year){
                container_html += '<tr>';
                container_html += '   <td>{!!htmlentities(__('app.10year_avg'))!!}</td>';
                $.each(types, function(k, licensetype){
                    element = data.average.find(el => el.license_type === licensetype);
                    if (element) {
                        value = parseFloat(element.average);
                    }else{
                        value = 0;
                    }
                    container_html += '   <td>'+numberWithCommas(value)+'</td>';
                });
                container_html += '</tr>';
            }
            container.html(container_html);
            $('.detail-table-area table').DataTable();
        }

        function downloadImage() {
            if ($('.detail-chart-area').hasClass('active')){
                var canvas_temp = ResizeCanvasImageData(current_chart, $('#my_chart').width(), $('#my_chart').height());
                DownloadImagePDFCallback(canvas_temp, "licenses_chart.pdf", $('.filter-information').html());
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

                var temp_data = chart_data.licenses;
                var avg = chart_data.average;
                var temp;
                var types = chart_data.license_types.filter(function(value, index, arr){ return index !== 3;});//Remove Jugendpatent - index = 3;
                $.each(temp_data, function (i, item) {
                    temp = [];
                    temp.push(i);
                    $.each(types, function(k, licensetype){
                        var value;
                        var element = item.find(el => el.license_type === licensetype);
                        if (element) {
                            value = element.licenses;
                        }else{
                            value = 0;
                        }
                        temp.push(value);
                    });
                    data_post["body"].push(temp);
                });
                temp = [];
                temp.push("10 Year Average");
                $.each(types, function(k, licensetype){
                    var value;
                    var element = avg.find(el => el.license_type === licensetype);
                    if (element) {
                        value = parseFloat(element.average);
                    }else{
                        value = 0;
                    }
                    temp.push(value);
                });
                data_post["body"].push(temp);

                DownloadExcelData(data_post, 'licenses.xlsx');
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