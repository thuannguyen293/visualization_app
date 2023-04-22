var LoadStockingPage = function () {
    console.log("LoadStockingPage");
    var html = "";
    html += '<div class="col-md-12 filter-item" style="display: none;">';
    html += '   <div class="row">';
    html += '       <div class="col-xl-12">';
    html += '           <div class="btn-group group-filter-1">';
    html += '               <button data-sub_page="time_series" class="btn btn-basic w-100 menu-two active" type="button">Zeitreihe</button>';
    html += '               <button data-sub_page="regional_comparison" class="btn btn-basic w-100 menu-two" type="button">Regionaler Vergleich</button>';
    html += '           </div>';
    html += '       </div>';
    html += '   </div>';
    html += '</div>';


    html += '<div class="filter-sub-area">';
    html += '</div>';
    $('.filter-area').append(html);
    LoadStockingSubPage();
};

function LoadStockingSubPage() {
    console.log("LoadStockingSubPage", current_sub_page);
    $('#my_chart').remove();
    $('.detail-table-area').html("");

    LoadStockingTimeSeries();
    DownloadImage = DownloadStockingTimeSeries;
    SortData = SortDataStockingTimeSeries;
}

function LoadStockingTimeSeries() {
    console.log("LoadStockingTimeSeries");
    var html = "";
    html += '<div class="col-md-12 filter-item">';
    html += '   <div class="row">';
    html += '       <div class="col-xl-12">';
    html += '           <div class="btn-group element-2">';
    html += '               <div class="select-group">';
    html += '                   <label for="geographic_filter">Geographic Filter</label>';
    html += '                   <select name="geographic_filter" class="js-select2-active w-100" data-placeholders="Ganzer Kanton" id="geographic_filter">';
    html += '                       <option></option>';
    $.each(regions, function (i, region) {
        html += '                   <option value="' + region['region_code'] + '">' + region['name_DE'] + '</option>';
    });
    html += '                   </select>';
    html += '                   <button class="btn btn-info-select" data-toggle="modal" data-target="#geographic_modal" type="button">i</button>';
    html += '               </div>';

    html += '               <div class="select-group">';
    html += '                   <label for="waterbody_type">Waterbody Type</label>';
    html += '                   <select name="waterbody_type" class="js-select2-active w-100" data-placeholders="Alle Gewässer" id="waterbody_type">';
    html += '                       <option></option>';
    html += '                       <option value="AS">Alle Seen</option>';
    html += '                       <option value="FG">Fliessgewässer</option>';
    html += '                   </select>';
    html += '                   <button class="btn btn-info-select" data-toggle="modal" data-target="#waterbodytype_modal" type="button">i</button>';
    html += '               </div>';

    html += '           </div>';
    html += '       </div>';
    html += '   </div>';
    html += '</div>';


    html += '<div class="col-md-12 filter-item">';
    html += '   <div class="row">';
    html += '       <div class="col-xl-12">';
    html += '           <div class="btn-group">';
    html += '               <div class="slider-area">';
    html += '                   <div id="slider-range"></div>';
    html += '               </div>';
    html += '           </div>';
    html += '       </div>';
    html += '   </div>';
    html += '</div>';


    $('.filter-sub-area').append(html);

    // draw table
    var html_table = "";
    html_table += '<table>';
    html_table += ' <thead>';
    html_table += ' </thead>';
    html_table += ' <tbody>';
    html_table += ' </tbody>';
    html_table += '</table>';
    html_table += '<div id="pagination-container"></div>';

    $('.detail-table-area').html(html_table);

    my_slider = document.getElementById('slider-range');

    $('.js-select2-active').each(function (index) {
        var placeholders = $(this).data('placeholders');
        $(this).select2({
            width: 'style',
            placeholder: placeholders
            // allowClear: true
        });
        $(this).on('change', function () {
            var change_value = $(this).val();
            if (change_value == "all") {
                $(this).find("option[value='all']").remove();
            } else {
                if ($(this).find("option[value='all']").length == 0) {
                    $(this).prepend('<option value="all">' + placeholders + '</option>');
                }
            }
            UpdateChartStockingTimeSeries();
        });
    });

    noUiSlider.create(my_slider, {
        start: [time_range[0], time_range[1]],
        range: {
            'min': [2002],
            'max': [max_year]
        },
        step: 1,
        tooltips: true,
        connect: [false, true, false],
    });

    my_slider.noUiSlider.on('change.one', function () {
        UpdateChartStockingTimeSeries();
    });
    UpdateChartStockingTimeSeries();
}

function UpdateChartStockingTimeSeries() {
    var geographic_id = $('select[name="geographic_filter"]').children("option:selected").val();
    var waterbody_type = $('select[name="waterbody_type"]').children("option:selected").val();
    if (geographic_id == "") geographic_id = "all";
    if (waterbody_type == "") waterbody_type = "all";
    time_range = my_slider.noUiSlider.get();
    ShowLoading();
    $.ajax({
        url: app_url + '/api/stocking/time_series/chart',
        type: "get",
        data: {
            geographic_id: geographic_id,
            waterbody_type: waterbody_type,
            time_range: time_range
        },
        success: function (result) {
            HideLoading();
            chart_data = result.data;
            DrawChartStockingTimeSeries(result.data);
            var stocking_value = Object.keys(result.data.stocking).map(function(key) {
                return result.data.stocking[key];
            });
            UpdateTableStockingTimeSeries(stocking_value, '', 'sorting_asc');
        },
        error: function () {
            HideLoading();
        }
    });

    ReloadFilterInformation('Fischbesatz', true);
}

function DrawChartStockingTimeSeries(data) {
    $('.action-tip').show();
    $('#my_chart').remove(); // this is my <canvas> element
    $('.detail-chart-area').prepend('<canvas id="my_chart" width="900" height="500"></canvas>');
    current_chart = document.getElementById('my_chart');

    if (data.length == 0 || data["fishtypes"].length == 0) {
        $(".detail-area").addClass('no-data');
        return;
    } else {
        $(".detail-area").removeClass('no-data');
    }

    var ctx_chart = current_chart.getContext('2d');
    var labels = [];
    var datasets = {};
    var temp_data = data.stocking;
    // var color_list = ["#005780","#D45087","#FFA600","#009966"];
    $.each(data.fishtypes, function (k, fishtype) {
        datasets[fishtype.name] = [];
    });

    $.each(temp_data, function (i, item) {
        labels.push(String(i));
        $.each(data.fishtypes, function (k, type) {
            value = (item[type.code]) ? parseInt(item[type.code]) : 0;
            datasets[type.name].push(value);
        });
    });

    var chart_render_data = {
        labels: labels,
        datasets: []
    };
    var count = 0;
    $.each(datasets, function (name, value) {
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
            responsive: false,
            legend: {position: 'bottom'},
            tooltips: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function (tooltipItem, data) {
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
                    stacked: true
                }],
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function (value, index, values) {
                            return numberWithCommas(value);
                        }
                    },
                    stacked: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Anzahl'
                    }
                }]
            }
        }
    });
}

function UpdateTableStockingTimeSeries(data, column, type) {
    // console.log(data);
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
        var _element = ['Jahr'];
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

function DownloadStockingTimeSeries() {
    if ($('.detail-chart-area').hasClass('active')){
        DownloadImagePDFCallback(current_chart.toDataURL("image/png", 1.0), "Fischbesatz_Zeitreihe.pdf", $('.filter-information').html());
    } else {
        // console.log('Download table file');
        var data_post = {
            "title": $('.filter-information').html(),
            "header": [],
            "body": []
        };
        data_post["header"] = ['Jahr'];
        $.each(chart_data.fishtypes, function(k, fishtype){
            data_post["header"].push(fishtype.name);
        });

        var temp_data = chart_data.stocking;
        var temp;
        $.each(temp_data, function (i, item) {
            temp = [];
            temp.push(item.year);
            $.each(chart_data.fishtypes, function(j, type){
                var value = (item[type.code]) ? parseInt(item[type.code]) : 0;
                temp.push(value);
            });
            data_post["body"].push(temp);
        });

        DownloadExcelData(data_post, 'Fischbesatz_Zeitreihe.xlsx');
    }
    return false;
}

function SortDataStockingTimeSeries(data, column, type){
    var _data = Object.keys(data.stocking).map(function(key) {
        return data.stocking[key];
    });
    var _col;
    if (column == 0) {
        _col = 'year';
    }else{
        _col = chart_data.fishtypes[column-1].code;
    }
    if (type == 'sorting_asc') {
        _data.sort(function(a, b) {
            var val_a = (a[_col] != undefined ? parseInt(a[_col]) : 0);
            var val_b = (b[_col] != undefined ? parseInt(b[_col]) : 0);
            return val_a - val_b;
        });
    }else{
        _data.sort(function(a, b) {
            var val_a = (a[_col] != undefined ? parseInt(a[_col]) : 0);
            var val_b = (b[_col] != undefined ? parseInt(b[_col]) : 0);
            return val_b - val_a
        });
    }
    UpdateTableStockingTimeSeries(_data, column, type);
}