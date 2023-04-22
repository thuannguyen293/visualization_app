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
            <h4 class="filter-information">{{__('app.licenses')}}, {{date("Y")-10}} - {{date("Y")-1}}, Local Buyers</h4>
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
        </div>
    </div>


@endsection

@section('script_footer')
    @parent
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

        google.charts.load('current', {'packages': ['corechart']});
        google.charts.setOnLoadCallback(init);

        var current_chart;
        var chart_data;

        function drawVisualization(data) {
            var dataset = [];
            var license_types = ['Genre'];
            var temp_data = data.licenses;
            var avg = data.average;
            var types = data.license_types.filter(function(value, index, arr){ return index !== 2;});//Remove Other License - index = 2;
            $.each(temp_data, function (i, item) {
                var row = [String(i)];
                $.each(types, function(k, licensetype){
                    element = item.find(el => el.license_type === licensetype);
                    if (element) {
                        value = element.licenses;
                    }else{
                        value = 0;
                    }
                    row.push(value);
                    row.push('<div class="tooltip-c"><p>{{__('app.year')}}: <b>'+String(i)+'</b></p><p>'+licensetype+': <b>'+numberWithCommas(value)+'</b></p></div>');
                });
                dataset.push(row);
            });

            var row = [''];
            $.each(types, function(k, licensetype){
                row.push(undefined);
                row.push('');
            });
            dataset.push(row);

            //Add 10ya
            var row = ["{{ __('app.cpue_10')}}"];
            $.each(types, function(k, licensetype){
                element = avg.find(el => el.license_type === licensetype);
                if (element) {
                    value = parseFloat(element.average);
                }else{
                    value = 0;
                }
                row.push(value);
                row.push('<div class="tooltip-c"><p>{{ __('app.cpue_10')}}</p><p>'+licensetype+': <b>'+numberWithCommas(value)+'</b></p></div>');
            });
            dataset.push(row);

            $.each(types, function(i, licensetype){
                license_types.push(licensetype);
                license_types.push({role: 'tooltip', p: {html: true}});
            });
            dataset.unshift(license_types);
            
            var main_data = google.visualization.arrayToDataTable(dataset);
            var main_options = {
                legend: {position: 'bottom'},
                bar: { groupWidth: '75%' },
                isStacked: true,
                tooltip: { isHtml: true },
                series: {
                    0: {
                        color: '#003f5c',
                    },
                    1: {
                        color: '#7a5195',
                    },
                    2: {
                        color: '#ef5675',
                        type: 'line',
                        targetAxisIndex: 1
                    }
                },
                pointSize: 5,
            };

            var main_div = document.getElementById('main_chart');
            var main_chart = new google.visualization.ColumnChart(main_div);
            main_chart.draw(main_data, main_options);

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
                var color = ['#003f5c', '#7a5195', '#ef5675'];
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
                            main_options.series[col]['color'] = '#CCCCCC';
                        }else {
                            // show the data series
                            columns[col] = col;
                            col = Math.floor(col/2);
                            var temp_color = color[col];
                            main_options.series[col]['color'] = temp_color;
                        }
                        var view = new google.visualization.DataView(main_data);
                        view.setColumns(columns);
                        main_chart.draw(view, main_options);
                    }
                }
            });
        }

        function UpdateChart(buyer_type) {
            var time_range = mySlider.noUiSlider.get();
            ShowLoading();
            $.ajax({
                url: '/api/licenses/chart',
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
            var text_filter_information = '{{ __('app.licenses')}}, ';
            text_filter_information += $('.btn-group p.active').text();
            text_filter_information += ' ' + Math.round(time_range[0]) + ' - ' + Math.round(time_range[1]);

            $('.filter-information').html(text_filter_information);
        }

        function UpdateTable(data) {
            if ( $.fn.dataTable.isDataTable('.detail-table-area table') ) {
                $('.detail-table-area table').DataTable().destroy();
            }
            var header_container = $('.detail-table-area table thead');
            var container = $('.detail-table-area table tbody');
            var header_html = '';
            var container_html = '';
            var types = data.license_types.filter(function(value, index, arr){ return index !== 3;});//Remove Jugendpatent - index = 3;
            header_html = '<tr>';
            header_html += '<th>{{__('app.year')}}</th>';
            $.each(types, function(i, license_type){
                if (license_type == 'other') {
                    header_html += '<th>{{__('app.other_licenses')}}</th>';
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
            container_html += '<tr>';
            container_html += '   <td>{{__('app.10year_avg')}}</td>';
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
            container.html(container_html);
            $('.detail-table-area table').DataTable();
        }

        function downloadImage() {
            if ($('.detail-chart-area').hasClass('active')){
                // console.log('Download chart image');
                var img_uri = current_chart.getImageURI();
                ConvertToDataJPGE(img_uri, $('#main_chart').width(), $('#main_chart').height(), "licenses_chart.pdf", $('.filter-information').html());
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