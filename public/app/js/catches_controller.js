var current_geo_chart;
var LoadCatchesPage = function () {
    // console.log("LoadCatchesPage");
    var html = "";
    // Render Sub Page
    html += '<div class="col-md-12 filter-item">';
    html += '   <div class="row">';
    html += '       <div class="col-xl-12">';
    html += '           <div class="btn-group group-filter-1">';
    html += '               <button data-sub_page="time_series" class="btn btn-basic w-100 menu-two active" type="button">Zeitreihe</button>';
    html += '               <button data-sub_page="season" class="btn btn-basic w-100 menu-two" type="button">Saison</button>';
    html += '               <button data-sub_page="regional_comparison" class="btn btn-basic w-100 menu-two" type="button">Regionaler Vergleich</button>';
    html += '               <button data-sub_page="fishtype_comparison" class="btn btn-basic w-100 menu-two" type="button">Fischarten Vergleich</button>';
    html += '           </div>';
    html += '       </div>';
    html += '   </div>';
    html += '</div>';

    html += '<div class="filter-sub-area">';
    html += '</div>';
    $('.filter-area').append(html);

    $('.filter-item .menu-two').on('click', function (obj) {
        $(".menu-two").removeClass('active');
        $(obj.target).addClass('active');
        var new_sub_page = $(obj.target).data("sub_page");
        if (new_sub_page != current_sub_page) {
            current_sub_page = new_sub_page;
            console.log(current_page, current_sub_page);
            $(".filter-sub-area").html("");
            LoadCatchesSubPage();
        }
    });
    LoadCatchesSubPage();
};


function LoadCatchesSubPage() {
    // console.log("LoadCatchesSubPage", current_sub_page);
    $('#my_chart').remove();
    $('.detail-table-area').html("");
    $('.ob-geo-chart').remove();
    $('.detail-option-area').html('');
    $('.detail-option-area').hide();
    $('.action-tip').hide();
    DownloadImage = null;
    switch (current_sub_page) {
        case "time_series":
            LoadCatchesTimeSeries();
            DownloadImage = DownloadImageCatchesTimeSeries;
            break;
        case "season":
            LoadCatchesSeason();
            DownloadImage = DownloadImageCatchesSeason;
            SortData = SortDataCatchesSeason;
            break;
        case "regional_comparison":
            LoadCatchesRegionalComparison();
            DownloadImage = DownloadImageCatchesRegionalComparison;
            SortData = SortDataCatchesRegionalComparison;
            break;
        case "fishtype_comparison":
            LoadCatchesFishtypeComparison();
            DownloadImage = DownloadImageCatchesFishtypeComparison;
            SortData = SortDataCatchesFishtypeComparison;
            break;
        default:
            LoadCatchesTimeSeries();
            break;
    }

}

//* CATCHES TIME SERIES ******************************
function LoadCatchesTimeSeries() {
    // console.log("LoadCatchesTimeSeries");
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
    $.each(waterbody_types, function (i, waterbody_type) {
        html += '                   <option value="' + waterbody_type['code'] + '">' + waterbody_type['name'] + '</option>';
    });
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
    html_table += '     <tr>';
    html_table += '     <th>Jahr</th>';
    html_table += '     <th>Fänge</th>';
    html_table += '     <th>Ereignisse</th>';
    html_table += '     <th>CPUE</th>';
    html_table += '     </tr>';
    html_table += ' </thead>';
    html_table += ' <tbody>';
    html_table += ' </tbody>';
    html_table += '</table>';

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
            UpdateChartCatchesTimeSeries();
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
        connect: [false, true, false]
    });

    my_slider.noUiSlider.on('change.one', function () {
        UpdateChartCatchesTimeSeries();
    });
    UpdateChartCatchesTimeSeries();
}

function UpdateChartCatchesTimeSeries() {
    // console.log("UpdateChartCatchesTimeSeries");

    var geographic_id = $('select[name="geographic_filter"]').children("option:selected").val();
    var waterbody_type = $('select[name="waterbody_type"]').children("option:selected").val();
    if (geographic_id == "") geographic_id = "all";
    if (waterbody_type == "") waterbody_type = "all";

    time_range = my_slider.noUiSlider.get();
    ShowLoading();
    $.ajax({
        url: app_url + '/api/catches/time_series/chart',
        type: "get",
        data: {
            geographic_id: geographic_id,
            waterbody_type: waterbody_type,
            time_range: time_range
        },
        success: function (result) {
            HideLoading();
            DrawChartCatchesTimeSeries(result.data);

            UpdateTableCatchesTimeSeries(result.data);
            chart_data = result.data;
        },
        error: function () {
            HideLoading();
        }
    });

    ReloadFilterInformation('Fänge', true);
}

function DrawChartCatchesTimeSeries(data) {
    $('.action-tip').show();
    $('#my_chart').remove(); // this is my <canvas> element
    $('.detail-chart-area').prepend('<canvas id="my_chart" width="900" height="500"></canvas>');
    current_chart = document.getElementById('my_chart');

    if (data.length == 0 || data["catches"].length == 0) {
        $(".detail-area").addClass('no-data');
        return;
    } else {
        $(".detail-area").removeClass('no-data');
    }

    var time_range = my_slider.noUiSlider.get();
    var ctx_chart = current_chart.getContext('2d');
    var labels = [];
    var datasets = {
        'sessions': [],
        'catches': [],
        'CPUE': []
    };
    var temp_data = data.catches;
    var avg = data.average;
    // var last = temp_data.length + 1;
    // var color = Chart.helpers.color;
    $.each(temp_data, function (i, item) {
        labels.push(item.year);
        datasets['sessions'].push(parseInt(item.sessions));
        datasets['catches'].push(parseInt(item.catches));
        datasets['CPUE'].push(parseFloat(item.CPUE));
    });
    if (parseInt(time_range[1]) >= min_year && parseInt(time_range[1]) <= max_year) {
        labels.push("");
        labels.push(ConvertToHTML('Ø 10 Jahre'));
        datasets['sessions'].push(null);
        datasets['sessions'].push(avg.sessions);
        datasets['catches'].push(null);
        datasets['catches'].push(avg.catches);
        datasets['CPUE'].push(null);
        datasets['CPUE'].push(avg.CPUE);
    }

    var chart_render_data = {
        labels: labels,
        datasets: [
            {
                type: 'line',
                label: 'CPUE',
                borderColor: '#ff0000',
                fill: false,
                data: datasets["CPUE"],
                yAxisID: 'y-axis-2',
                lineTension: 0
            },
            {
                type: 'bar',
                label: "Ereignisse",
                data: datasets['sessions'],
                yAxisID: 'y-axis-1',
                backgroundColor: '#fcd5b5',
                borderColor: "#7a3a05",
                borderWidth: 1
            }, {
                type: 'bar',
                label: ConvertToHTML('Fänge'),
                data: datasets['catches'],
                yAxisID: 'y-axis-1',
                backgroundColor: '#b7dee8',
                borderColor: "#1f5461",
                borderWidth: 1

            }
        ]
    };
    var my_chart = new Chart(ctx_chart, {
        type: 'bar',
        data: chart_render_data,
        options: {
            responsive: false,
            legend: {position: 'bottom'},
            tooltips: {
                mode: 'index',
                intersect: true,
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
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function (value, index, values) {
                            return numberWithCommas(value);
                        }
                    },
                    type: 'linear',
                    display: true,
                    position: 'left',
                    id: 'y-axis-1',
                    scaleLabel: {
                        display: true,
                        labelString: 'Anzahl'
                    }
                }, {
                    ticks: {
                        beginAtZero: true
                    },
                    type: 'linear',
                    display: true,
                    position: 'right',
                    id: 'y-axis-2',
                    gridLines: {drawOnChartArea: false},
                    scaleLabel: {
                        display: true,
                        labelString: 'CPUE',
                        rotate: true
                    }
                }]
            }
        }
    });
}

function UpdateTableCatchesTimeSeries(data) {
    // console.log("UpdateTableCatchesTimeSeries");
    if ($.fn.dataTable.isDataTable('.detail-table-area table')) {
        $('.detail-table-area table').DataTable().destroy();
    }
    var time_range = my_slider.noUiSlider.get();
    var temp_data = data.catches;
    var avg = data.average;
    var container = $('.detail-table-area table tbody');
    var html = '';
    $.each(temp_data, function (i, item) {
        html += '<tr>';
        html += '   <td>' + item.year + '</td>';
        html += '   <td>' + numberWithCommas(item.catches) + '</td>';
        html += '   <td>' + numberWithCommas(item.sessions) + '</td>';
        html += '   <td>' + numberWithCommas(item.CPUE) + '</td>';
        html += '</tr>';
    });
    if (parseInt(time_range[1]) >= min_year && parseInt(time_range[1]) <= max_year) {
        html += '<tr>';
        html += '   <td>Ø 10 Jahre</td>';
        html += '   <td>' + numberWithCommas(parseFloat(avg.catches)) + '</td>';
        html += '   <td>' + numberWithCommas(parseFloat(avg.sessions)) + '</td>';
        html += '   <td>' + numberWithCommas(parseFloat(avg.CPUE)) + '</td>';
        html += '</tr>';
    }
    container.html(html);
    $('.detail-table-area table').DataTable({
        paging: false,
        select: true
    });
}

function DownloadImageCatchesTimeSeries() {
    // console.log("DownloadImageCatchesTimeSeries");
    if ($('.detail-chart-area').hasClass('active')) {
        DownloadImagePDFCallback(current_chart.toDataURL("image/png", 1.0), "Fänge_Zeitreihe.pdf", $('.filter-information').html());
    } else {
        var data_post = {
            "title": $('.filter-information').html(),
            "header": [],
            "body": []
        };
        $(".detail-table-area table thead tr th").each(function () {
            data_post["header"].push($(this).text());
        });

        var temp_data = chart_data.catches;
        var avg = chart_data.average;
        var temp;
        $.each(temp_data, function (i, item) {
            temp = [];
            temp.push(item.year);
            temp.push(item.catches);
            temp.push(item.sessions);
            temp.push(item.CPUE);
            data_post["body"].push(temp);
        });
        temp = [];
        temp.push("Ø 10 Jahre");
        temp.push(avg.catches);
        temp.push(avg.sessions);
        temp.push(avg.CPUE);
        data_post["body"].push(temp);

        DownloadExcelData(data_post, 'Fänge_Zeitreihe.xlsx');
    }
    return false;
}

//* END CATCHES TIME SERIES **************************

//* CATCHES SEASON **********************************
function LoadCatchesSeason() {
    // console.log("LoadCatchesSeason");
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
    $.each(waterbody_types, function (i, waterbody_type) {
        html += '                   <option value="' + waterbody_type['code'] + '">' + waterbody_type['name'] + '</option>';
    });
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
            UpdateChartCatchesSeason();
        });
    });

    noUiSlider.create(my_slider, {
        start: [time_year],
        range: {
            'min': 2002,
            'max': max_year,
        },
        step: 1,
        tooltips: true,
    });

    my_slider.noUiSlider.on('change.one', function () {
        UpdateChartCatchesSeason();
    });
    UpdateChartCatchesSeason();
}

function UpdateChartCatchesSeason() {
    // console.log("UpdateChartCatchesSeason");

    var geographic_id = $('select[name="geographic_filter"]').children("option:selected").val();
    var waterbody_type = $('select[name="waterbody_type"]').children("option:selected").val();
    if (geographic_id == "") geographic_id = "all";
    if (waterbody_type == "") waterbody_type = "all";
    time_year = my_slider.noUiSlider.get();
    ShowLoading();
    $.ajax({
        url: app_url + '/api/catches/season/chart',
        type: "get",
        data: {
            geographic_id: geographic_id,
            waterbody_type: waterbody_type,
            year: time_year
        },
        success: function (result) {
            HideLoading();
            DrawChartCatchesSeason(result.data);
            UpdateTableCatchesSeason(result.data, '', 'sorting_asc');
            chart_data = result.data;
        },
        error: function () {
            HideLoading();
        }
    });

    ReloadFilterInformation('Fänge', false);
}

function DrawChartCatchesSeason(data) {
    $('.action-tip').show();
    $('#my_chart').remove(); // this is my <canvas> element
    $('.detail-chart-area').prepend('<canvas id="my_chart" width="900" height="500"></canvas>');
    current_chart = document.getElementById('my_chart');
    if (data.length == 0) {
        $(".detail-area").addClass('no-data');
        return;
    } else {
        $(".detail-area").removeClass('no-data');
    }

    // time_year = my_slider.noUiSlider.get();
    var ctx_chart = current_chart.getContext('2d');
    var labels = [];
    if (parseInt(time_year) >= min_year && parseInt(time_year) <= max_year) {
        var datasets = {
            'sessions_10': [],
            'catches_10': [],
            'sessions': [],
            'catches': [],
            'cpue_10': [],
            'cpue': []
        };
    } else {
        var datasets = {
            'sessions': [],
            'catches': [],
            'cpue': []
        };
    }

    var temp_data = data;
    var last = temp_data.length + 1;
    var color = Chart.helpers.color;
    $.each(temp_data, function (i, item) {
        labels.push(item.month);
        if (parseInt(time_year) >= min_year && parseInt(time_year) <= max_year) {
            datasets['sessions_10'].push(parseInt(item.avg_sessions));
            datasets['catches_10'].push(parseInt(item.avg_catches));
        }
        datasets['sessions'].push(parseFloat(item.sessions));
        datasets['catches'].push(parseInt(item.catches));
        if (parseInt(time_year) >= min_year && parseInt(time_year) <= max_year) {
            datasets['cpue_10'].push(parseFloat(item.avg_CPUE));
        }
        datasets['cpue'].push(parseFloat(item.CPUE));
    });
    var _datasets = [];
    if (parseInt(time_year) >= min_year && parseInt(time_year) <= max_year) {
        _datasets = [
            {
                type: 'bar',
                label: ConvertToHTML('Ereignisse Ø 10J.'),
                backgroundColor: color('#fcd5b5').alpha(0.3).rgbString(),
                fill: false,
                data: datasets["sessions_10"],
                yAxisID: 'y-axis-1',
                order: 1,
                borderColor: '#fcd5b5',
                borderWidth: 2,
            }, {
                type: 'bar',
                label: "Ereignisse",
                backgroundColor: '#fcd5b5',
                borderColor: "#7a3a05",
                borderWidth: 1,
                data: datasets['sessions'],
                yAxisID: 'y-axis-1',
                order: 1
            },
            {
                type: 'bar',
                label: ConvertToHTML('Fänge Ø 10J.'),
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
                label: ConvertToHTML('Fänge'),
                backgroundColor: '#b7dee8',
                borderColor: "#1f5461",
                borderWidth: 1,
                data: datasets['catches'],
                yAxisID: 'y-axis-1',
                order: 1
            },
            {
                type: 'line',
                label: ConvertToHTML('CPUE Ø 10J.'),
                borderColor: '#ff6e54',
                fill: false,
                data: datasets["cpue_10"],
                yAxisID: 'y-axis-2',
                lineTension: 0,
                order: 0,
            },
            {
                type: 'line',
                label: "CPUE",
                borderColor: '#ff0000',
                fill: false,
                data: datasets["cpue"],
                yAxisID: 'y-axis-2',
                lineTension: 0,
                order: 0
            }
        ]
    } else {
        _datasets = [
            {
                type: 'bar',
                label: "Ereignisse",
                backgroundColor: '#7a519a',
                data: datasets['sessions'],
                yAxisID: 'y-axis-1',
                order: 1,
            },
            {
                type: 'bar',
                label: ConvertToHTML('Fänge'),
                backgroundColor: '#00cfe3',
                data: datasets['catches'],
                yAxisID: 'y-axis-1',
                order: 1
            },
            {
                type: 'line',
                label: "CPUE",
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
            legend: {position: 'bottom'},
            tooltips: {
                mode: 'index',
                intersect: true,
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
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function (value, index, values) {
                            return numberWithCommas(value);
                        }
                    },
                    type: 'linear',
                    display: true,
                    position: 'left',
                    id: 'y-axis-1',
                    scaleLabel: {
                        display: true,
                        labelString: 'Anzahl'
                    }
                }, {
                    ticks: {
                        beginAtZero: true
                    },
                    type: 'linear',
                    display: true,
                    position: 'right',
                    id: 'y-axis-2',
                    gridLines: {drawOnChartArea: false},
                    scaleLabel: {
                        display: true,
                        labelString: 'CPUE',
                        rotate: true
                    }
                }]
            }
        }
    });
}

function UpdateTableCatchesSeason(data, column, type) {
    // var time_range = my_slider.noUiSlider.get();
    var container = $('.detail-table-area table tbody');

    var data_header = ['Ereignisse', 'Fänge', 'CPUE'];
    if (parseInt(time_year) >= min_year && parseInt(time_year) <= max_year) {
        data_header = ['Ereignisse', 'Fänge', 'CPUE', 'Ereignisse Ø 10J.', 'Fänge Ø 10J.', 'CPUE Ø 10J.'];
    }
    var col = table_column_number;
    var total = data_header.length;
    var page = Math.floor(total / col);

    if (total % col > 0) {
        page = page + 1;
    }

    var data_arr = [];

    for (var j = 0; j < page; j++) {
        var _element = ['Monat'];
        $.each(data_header, function (i, item) {
            if (i >= j * col && i < col * (j + 1)) {
                _element.push(item);
            }
        });
        data_arr.push(_element);
        $.each(data, function (i, item) {
            _element = [item.month];
            $.each(data_header, function (k, header) {
                if (k >= j * col && k < col * (j + 1)) {
                    switch (k) {
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
    var pageSize = data.length + 1;
    $('#pagination-container').pagination({
        dataSource: data_arr,
        pageSize: pageSize,
        callback: function (data, pagination) {
            var container_html = '';
            $.each(data, function (index, item) {
                if (index == 0) {
                    container_html += '<tr class="heading">';
                    $.each(item, function (i, el) {
                        if (column != '' || column == 0) {
                            if (i == column) {
                                container_html += '<th class="' + type + '" data-index=' + i + '>' + el + '</th>';
                            } else {
                                container_html += '<th class="sorting" data-index=' + i + '>' + el + '</th>';
                            }
                        } else {
                            if (i == 0) {
                                container_html += '<th class="sorting_asc" data-index=' + i + '>' + el + '</th>';
                            } else {
                                container_html += '<th class="sorting" data-index=' + i + '>' + el + '</th>';
                            }
                        }
                    });
                } else {
                    container_html += '<tr>';
                    $.each(item, function (i, el) {
                        if (i != 0) {
                            container_html += '<td>' + numberWithCommas(el) + '</td>';
                        } else {
                            container_html += '<td>' + el + '</td>';
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
    } else {
        $('#pagination-container').show();
    }
}

function SortDataCatchesSeason(data, column, type) {
    var _col;
    switch (column) {
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
    if (column == 0 || column == 1 || column == 2) {
        if (type == 'sorting_asc') {
            data.sort(function (a, b) {
                var val_a = (a[_col] != undefined ? parseInt(a[_col]) : 0);
                var val_b = (b[_col] != undefined ? parseInt(b[_col]) : 0);
                return val_a - val_b;
            });
        } else {
            data.sort(function (a, b) {
                var val_a = (a[_col] != undefined ? parseInt(a[_col]) : 0);
                var val_b = (b[_col] != undefined ? parseInt(b[_col]) : 0);
                return val_b - val_a;
            });
        }
    } else {
        if (type == 'sorting_asc') {
            data.sort(function (a, b) {
                var val_a = (a[_col] != undefined ? parseFloat(a[_col]) : 0);
                var val_b = (b[_col] != undefined ? parseFloat(b[_col]) : 0);
                return val_a - val_b;
            });
        } else {
            data.sort(function (a, b) {
                var val_a = (a[_col] != undefined ? parseFloat(a[_col]) : 0);
                var val_b = (b[_col] != undefined ? parseFloat(b[_col]) : 0);
                return val_b - val_a;
            });
        }
    }
    UpdateTableCatchesSeason(data, column, type);
}

function DownloadImageCatchesSeason() {
    if ($('.detail-chart-area').hasClass('active')) {
        DownloadImagePDFCallback(current_chart.toDataURL("image/png", 1.0), "Fänge_Saison.pdf", $('.filter-information').html());
    } else {
        var data_post = {
            "title": $('.filter-information').html(),
            "header": [],
            "body": []
        };
        data_post["header"] = ['Monat', 'Ereignisse', 'Fänge', 'CPUE'];

        if (parseInt(time_year) >= min_year && parseInt(time_year) <= max_year) {
            data_post["header"] = ['Monat', 'Ereignisse', 'Fänge', 'CPUE', 'Ereignisse Ø 10J.', 'Fänge Ø 10J.', 'CPUE Ø 10J.'];
        }

        var temp_data = chart_data;
        var temp;
        $.each(temp_data, function (i, item) {
            temp = [];
            temp.push(item["month"]);
            temp.push(item["sessions"]);
            temp.push(item["catches"]);
            temp.push(item["CPUE"]);
            if (data_post["header"].length > 4) {
                temp.push(item["avg_sessions"]);
                temp.push(item["avg_catches"]);
                temp.push(item["avg_CPUE"]);
            }
            data_post["body"].push(temp);
        });

        DownloadExcelData(data_post, 'Fänge_Saison.xlsx');
    }
    return false;
}

//* END CATCHES SEASON ******************************

//* CATCHES REGIONAL COMPARISON ******************************
function LoadCatchesRegionalComparison() {
    // console.log("LoadCatchesRegionalComparison");

    var html = "";
    html += '<div class="col-md-12 filter-item">';
    html += '   <div class="row">';
    html += '       <div class="col-xl-12">';
    html += '           <div class="btn-group element-2">';
    html += '               <div class="select-group">';
    html += '                   <label for="geographic_filter">Geographic Filter</label>';
    html += '                   <select name="geographic_filter" class="js-select2-active w-100" data-placeholders="Ganzer Kanton" id="geographic_filter" disabled>';
    html += '                       <option></option>';
    html += '                   </select>';
    html += '                   <button class="btn btn-info-select" data-toggle="modal" data-target="#geographic_modal" type="button">i</button>';
    html += '               </div>';

    html += '               <div class="select-group">';
    html += '                   <label for="waterbody_type">Waterbody Type</label>';
    html += '                   <select name="waterbody_type" class="js-select2-active w-100" data-placeholders="Alle Gewässer" id="waterbody_type">';
    html += '                       <option></option>';
    $.each(waterbody_types, function (i, waterbody_type) {
        html += '                   <option value="' + waterbody_type['code'] + '">' + waterbody_type['name'] + '</option>';
    });
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
    html_table += '<table class="max_width140">';
    html_table += ' <thead>';
    html_table += ' </thead>';
    html_table += ' <tbody>';
    html_table += ' </tbody>';
    html_table += '</table>';
    html_table += '<div id="pagination-container"></div>';

    $('.detail-table-area').html(html_table);


    $('.detail-option-area').show();
    // draw detail option area
    var html_detail_option = "";
    html_detail_option += '<div class="btn-group action-btn-group">';
    html_detail_option += ' <button class="btn btn-basic w-100 btn-type active" data-type="sessions" type="button">Ereignisse</button>';
    html_detail_option += ' <button class="btn btn-basic w-100 btn-type" data-type="catches" type="button">' + ConvertToHTML('Fänge') + '</button>';
    html_detail_option += ' <button class="btn btn-basic w-100 btn-type" data-type="CPUE" type="button">CPUE</button>';
    html_detail_option += '</div>';
    $('.detail-option-area').append(html_detail_option);

    my_slider = document.getElementById('slider-range');

    noUiSlider.create(my_slider, {
        start: [time_year],
        range: {
            'min': 2002,
                'max': max_year
        },
        step: 1,
        tooltips: true
    });

    $('.js-select2-active').each(function (index) {
        var placeholders = $(this).data('placeholders');
        $(this).select2({
            width: 'style',
            placeholder: placeholders
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
        });
    });

    my_slider.noUiSlider.on('change.one', function () {
        var current_type = $('.detail-option-area .btn-type.active').data('type');
        UpdateChartCatchesRegionalComparison(current_type);
    });
    $('select[name="waterbody_type"]').on('change', function () {
        var current_type = $('.detail-option-area .btn-type.active').data('type');
        UpdateChartCatchesRegionalComparison(current_type);
    });
    $('.detail-option-area .btn-type').on('click', function () {
        $('.detail-option-area .btn-type').removeClass('active');
        $(this).addClass('active');
        DrawChartCatchesRegionalComparison(chart_data, $(this).data('type'));
        ReloadFilterInformation($(this).html(), false);
        // domtoimage.toJpeg(document.getElementById('ob_geo_chart')).then(function (dataUrl) {});
    });

    UpdateChartCatchesRegionalComparison('sessions');

}

function UpdateChartCatchesRegionalComparison(type) {
    // console.log("UpdateChart");
    var waterbody_type = $('select[name="waterbody_type"]').children("option:selected").val();
    if (waterbody_type == "") waterbody_type = "all";
    time_year = my_slider.noUiSlider.get();
    ShowLoading();
    $.ajax({
        url: app_url + '/api/catches/regional_comparison/chart',
        type: "get",
        data: {
            waterbody_type: waterbody_type,
            year: time_year
        },
        success: function (result) {
            HideLoading();
            DrawChartCatchesRegionalComparison(result.data, type);
            var data_value = Object.keys(result.data).map(function (key) {
                return result.data[key];
            });
            UpdateTableCatchesRegionalComparison(data_value, '', 'sorting_asc');
            chart_data = result.data;
            // domtoimage.toJpeg(document.getElementById('ob_geo_chart')).then(function (dataUrl) {});
        },
        error: function () {
            HideLoading();
        }
    });
    var current_text_type = $('.detail-option-area .btn-type.active').html();
    ReloadFilterInformation(current_text_type, false);
}

function DrawChartCatchesRegionalComparison(data, type) {
    $('.ob-geo-chart').remove();
    var html_chart = "";
    html_chart += '<div class="ob-geo-chart">';
    html_chart += '<div class="chart" id="ob_canvas"></div>';
    html_chart += '</div>';
    $('.detail-chart-area').prepend(html_chart);

    // current_chart = document.getElementById('my_chart');

    if (data.length == 0) {
        $(".detail-area").addClass('no-data');
        return;
    } else {
        $(".detail-area").removeClass('no-data');
    }

    var main_data = [];
    main_data.push(["Area", "Sessions", "Name"]);
    $.each(data, function (i, item) {
        if (type === 'CPUE') {
            main_data.push([i, parseFloat(item[type]), i]);
        } else {
            main_data.push([i, item[type], i]);
        }
    });
    var geo_chart = new OBGeoChart.getInstance({
        "canvas_id": "ob_canvas",
        "data": main_data,
        "type": type,
        "width": 800,
        "height": 600,
        "text": {
            "no_data": "Keine Daten",
            "region": "Region",
            "type": $(".detail-option-area").find(".btn.active").html(),
        }
    });
    geo_chart.Init();
    current_geo_chart = geo_chart;
}

function UpdateTableCatchesRegionalComparison(data, column, type) {
    var container = $('.detail-table-area table tbody');
    var data_header = ['Fänge', 'Ereignisse', 'CPUE'];

    if (parseInt(time_year) >= min_year && parseInt(time_year) <= max_year) {
        data_header = ['Fänge', 'Ereignisse', 'CPUE', 'Ereignisse Ø 10J.', 'Fänge Ø 10J.', 'CPUE Ø 10J.'];
    }


    var col = table_column_number;
    var total = data_header.length;
    var page = Math.floor(total / col);

    if (total % col > 0) {
        page = page + 1;
    }

    var data_arr = [];
    for (var j = 0; j < page; j++) {
        var _element = ['Region'];
        $.each(data_header, function (i, item) {
            if (i >= j * col && i < col * (j + 1)) {
                _element.push(item);
            }
        });
        data_arr.push(_element);
        $.each(data, function (i, item) {
            _element = [item.region_name];
            $.each(data_header, function (k, header) {
                if (k >= j * col && k < col * (j + 1)) {
                    switch (k) {
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
    var pageSize = data.length + 1;
    $('#pagination-container').pagination({
        dataSource: data_arr,
        pageSize: pageSize,
        callback: function (data, pagination) {
            var container_html = '';
            $.each(data, function (index, item) {
                if (index == 0) {
                    container_html += '<tr class="heading">';
                    $.each(item, function (i, el) {
                        if (column != '' || column == 0) {
                            if (i == column) {
                                container_html += '<th class="' + type + '" data-index=' + i + '>' + el + '</th>';
                            } else {
                                container_html += '<th class="sorting" data-index=' + i + '>' + el + '</th>';
                            }
                        } else {
                            if (i == 0) {
                                container_html += '<th class="sorting_asc" data-index=' + i + '>' + el + '</th>';
                            } else {
                                container_html += '<th class="sorting" data-index=' + i + '>' + el + '</th>';
                            }
                        }
                    });
                } else {
                    container_html += '<tr>';
                    $.each(item, function (i, el) {
                        if (i != 0) {
                            container_html += '<td>' + numberWithCommas(el) + '</td>';
                        } else {
                            container_html += '<td>' + el + '</td>';
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
    } else {
        $('#pagination-container').show();
    }
}

function SortDataCatchesRegionalComparison(data, column, type) {
    var data = Object.keys(data).map(function (key) {
        return data[key];
    });

    var _col;
    switch (column) {
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
    if (column == 0) {
        if (type == 'sorting_asc') {
            data.sort(function (a, b) {
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
        } else {
            data.sort(function (a, b) {
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
    } else if (column == 1 || column == 2) {
        if (type == 'sorting_asc') {
            data.sort(function (a, b) {
                var val_a = (a[_col] != undefined ? parseInt(a[_col]) : 0);
                var val_b = (b[_col] != undefined ? parseInt(b[_col]) : 0);
                return val_a - val_b;
            });
        } else {
            data.sort(function (a, b) {
                var val_a = (a[_col] != undefined ? parseInt(a[_col]) : 0);
                var val_b = (b[_col] != undefined ? parseInt(b[_col]) : 0);
                return val_b - val_a;
            });
        }
    } else {
        if (type == 'sorting_asc') {
            data.sort(function (a, b) {
                var val_a = (a[_col] != undefined ? parseFloat(a[_col]) : 0);
                var val_b = (b[_col] != undefined ? parseFloat(b[_col]) : 0);
                return val_a - val_b;
            });
        } else {
            data.sort(function (a, b) {
                var val_a = (a[_col] != undefined ? parseFloat(a[_col]) : 0);
                var val_b = (b[_col] != undefined ? parseFloat(b[_col]) : 0);
                return val_b - val_a;
            });
        }
    }
    UpdateTableCatchesRegionalComparison(data, column, type);
}

function DownloadImageCatchesRegionalComparison() {
    if ($('.detail-chart-area').hasClass('active')) {
        // domtoimage.toJpeg(document.getElementById('ob_geo_chart'))
        //     .then(function (dataUrl) {
        //         var pdf = new jsPDF('l', 'mm', [750, 800]);
        //         pdf.addImage(dataUrl, 'JPEG', 20, 20);
        //         pdf.text(30, 10, $('.filter-information').html());
        //         pdf.save("Fänge_Regionaler_Vergleich.pdf");
        //     })
        //     .catch(function (error) {
                var dataImage = current_geo_chart.GetImageDataJPEG();
                var pdf = new jsPDF('l', 'mm', [750, 800]);
                pdf.addImage(dataImage, 'JPEG', 20, 20);
                pdf.text(30, 10, $('.filter-information').html());
                pdf.save("Fänge_Regionaler_Vergleich.pdf");
                // console.error('oops, something went wrong!', error);
            // });
    } else {
        var data_post = {
            "title": $('.filter-information').html(),
            "header": [],
            "body": []
        };
        data_post["header"] = ['Region', 'Fänge', 'Ereignisse', 'CPUE'];

        if (parseInt(time_year) >= min_year && parseInt(time_year) <= max_year) {
            data_post["header"] = ['Region', 'Fänge', 'Ereignisse', 'CPUE', 'Fänge Ø 10J.', 'Ereignisse Ø 10J.', 'CPUE Ø 10J.'];
        }

        var temp_data = chart_data;
        var temp;
        $.each(temp_data, function (i, item) {
            temp = [];
            temp.push(item["region_name"]);
            temp.push(item["catches"]);
            temp.push(item["sessions"]);
            temp.push(item["CPUE"]);
            if (data_post["header"].length > 4) {
                temp.push(item["avg_catches"]);
                temp.push(item["avg_sessions"]);
                temp.push(item["avg_CPUE"]);
            }
            data_post["body"].push(temp);
        });

        DownloadExcelData(data_post, 'Fänge_Regionaler_Vergleich.xlsx');
    }
    return false;
}

//* END CATCHES REGIONAL COMPARISON ******************************

//* CATCHES FISHTYPE COMPARISON ******************************
function LoadCatchesFishtypeComparison() {
    // console.log("LoadCatchesFishtypeComparison");
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
    $.each(waterbody_types, function (i, waterbody_type) {
        html += '                   <option value="' + waterbody_type['code'] + '">' + waterbody_type['name'] + '</option>';
    });
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
            UpdateChartCatchesFishtypeComparison();
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
        connect: [false, true, false]
    });

    my_slider.noUiSlider.on('change.one', function () {
        UpdateChartCatchesFishtypeComparison();
    });
    UpdateChartCatchesFishtypeComparison();
}

function UpdateChartCatchesFishtypeComparison() {
    // console.log("UpdateChartCatchesFishtypeComparison");

    var geographic_id = $('select[name="geographic_filter"]').children("option:selected").val();
    var waterbody_type = $('select[name="waterbody_type"]').children("option:selected").val();
    if (geographic_id == "") geographic_id = "all";
    if (waterbody_type == "") waterbody_type = "all";

    time_range = my_slider.noUiSlider.get();
    ShowLoading();
    $.ajax({
        url: app_url + '/api/catches/fishtype_comparison/chart',
        type: "get",
        data: {
            geographic_id: geographic_id,
            waterbody_type: waterbody_type,
            time_range: time_range
        },
        success: function (result) {
            HideLoading();
            chart_data = result.data;
            DrawChartCatchesFishtypeComparison(result.data);
            var catches_value = Object.keys(result.data.catches).map(function (key) {
                return result.data.catches[key];
            });
            UpdateTableCatchesFishtypeComparison(catches_value, '', 'sorting_asc');
            // UpdateTableCatchesFishtypeComparison(result.data);

        },
        error: function () {
            HideLoading();
        }
    });

    ReloadFilterInformation('Fänge', true);
}

function DrawChartCatchesFishtypeComparison(data) {
    $('.action-tip').show();
    // console.log("DrawChartCatchesFishtypeComparison");
    $('#my_chart').remove();
    $('.detail-chart-area').prepend('<canvas id="my_chart" width="900" height="500"></canvas>');
    current_chart = document.getElementById('my_chart');

    if (data.length == 0 || data["fishtypes"].length == 0) {
        $(".detail-area").addClass('no-data');
        return;
    } else {
        $(".detail-area").removeClass('no-data');
    }
    var time_range = my_slider.noUiSlider.get();
    var ctx_chart = document.getElementById('my_chart').getContext('2d');
    var labels = [];
    var datasets = {};
    var temp_data = data.catches;
    $.each(data.fishtypes, function (k, fishtype) {
        datasets[fishtype.name] = [];
    });

    $.each(temp_data, function (i, item) {
        if (i !== 'average') {
            labels.push(item.year);

            $.each(data.fishtypes, function (k, fishtype) {
                var value = (item[fishtype.fishtype_code]) ? parseInt(item[fishtype.fishtype_code]) : 0;
                datasets[fishtype.name].push(value);
            });
        } else {
            if (parseInt(time_range[1]) >= min_year && parseInt(time_range[1]) <= max_year) {
                labels.push("");
                labels.push(ConvertToHTML('Ø 10 Jahre'));

                $.each(data.fishtypes, function (k, fishtype) {
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
    $.each(datasets, function (name, value) {
        var _color = color_list[count];
        chart_render_data.datasets.push({
            label: name,
            data: value,
            backgroundColor: _color
        });

        if (count < 6) count++;
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
                    stacked: true,
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

function UpdateTableCatchesFishtypeComparison(data, column, type) {
    // console.log("UpdateTableCatchesFishtypeComparison");
    var time_range = my_slider.noUiSlider.get();
    var container = $('.detail-table-area table tbody');
    var col = table_column_number;
    var total = chart_data.fishtypes.length;
    var page = Math.floor(total / col);
    if (total % col > 0) {
        page = page + 1;
    }

    var data_arr = [];
    for (var j = 0; j < page; j++) {
        var _element = ['Jahr'];
        $.each(chart_data.fishtypes, function (i, item) {
            if (i >= j * col && i < col * (j + 1)) {
                _element.push(item.name);
            }
        });
        data_arr.push(_element);
        $.each(data, function (i, item) {
            var _element;
            if (item.year == 'average') {
                if (parseInt(time_range[1]) >= min_year && parseInt(time_range[1]) <= max_year) {
                    _element = ['Ø 10 Jahre'];
                }
            } else {
                _element = [item.year];
            }
            $.each(chart_data.fishtypes, function (k, fishtype) {
                if (k >= j * col && k < col * (j + 1)) {
                    value = (item[fishtype.fishtype_code]) ? parseInt(item[fishtype.fishtype_code]) : 0;
                    if (item.year == 'average') {
                        if (parseInt(time_range[1]) >= min_year && parseInt(time_range[1]) <= max_year) {
                            value = (item[fishtype.fishtype_code]) ? parseFloat(item[fishtype.fishtype_code]) : 0;
                            _element.push(value);
                        }
                    } else {
                        _element.push(value);
                    }

                }
            });
            data_arr.push(_element);
        });
    }
    // console.log(data_arr);
    var pageSize = data.length + 1;
    $('#pagination-container').pagination({
        dataSource: data_arr,
        pageSize: pageSize,
        callback: function (data, pagination) {
            var container_html = '';
            $.each(data, function (index, item) {
                if (index == 0) {
                    container_html += '<tr class="heading">';
                    $.each(item, function (i, el) {
                        if (column != '' || column == 0) {
                            if (i == column) {
                                container_html += '<th class="' + type + '" data-index=' + i + '>' + el + '</th>';
                            } else {
                                container_html += '<th class="sorting" data-index=' + i + '>' + el + '</th>';
                            }
                        } else {
                            if (i == 0) {
                                container_html += '<th class="sorting_asc" data-index=' + i + '>' + el + '</th>';
                            } else {
                                container_html += '<th class="sorting" data-index=' + i + '>' + el + '</th>';
                            }
                        }
                    });
                } else {
                    container_html += '<tr>';
                    $.each(item, function (i, el) {
                        if (i != 0) {
                            container_html += '<td>' + numberWithCommas(el) + '</td>';
                        } else {
                            container_html += '<td>' + el + '</td>';
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

function DownloadImageCatchesFishtypeComparison() {
    // console.log("DownloadImageCatchesFishtypeComparison");
    if ($('.detail-chart-area').hasClass('active')) {
        DownloadImagePDFCallback(current_chart.toDataURL("image/png", 1.0), "Fänge_Fischarten_Vergleich.pdf", $('.filter-information').html());
    } else {
        // console.log('Download table file');
        var data_post = {
            "title": $('.filter-information').html(),
            "header": [],
            "body": []
        };
        data_post["header"] = ['Jahr'];
        $.each(chart_data.fishtypes, function (k, fishtype) {
            data_post["header"].push(fishtype.name);
        });

        var temp_data = chart_data.catches;
        var avg = chart_data.average;
        var temp;
        $.each(temp_data, function (i, item) {
            temp = [];
            if (item.year == 'average') {
                temp.push('Ø 10 Jahre');
            } else {
                temp.push(item.year);
            }
            $.each(chart_data.fishtypes, function (j, fishtype) {
                value = (item[fishtype.fishtype_code]) ? parseInt(item[fishtype.fishtype_code]) : 0;
                if (item.year == 'average') {
                    value = (item[fishtype.fishtype_code]) ? parseFloat(item[fishtype.fishtype_code]) : 0;
                }
                temp.push(value);
            });
            data_post["body"].push(temp);
        });

        DownloadExcelData(data_post, 'Fänge_Fischarten_Vergleich.xlsx');
    }
    return false;
}

function SortDataCatchesFishtypeComparison(data, column, type) {
    var _data = Object.keys(data.catches).map(function (key) {
        return data.catches[key];
    });

    var _col;
    if (column == 0) {
        _col = 'year';
    } else {
        _col = chart_data.fishtypes[column - 1].fishtype_code;
    }
    if (column == 0) {
        if (type == 'sorting_asc') {
            _data.sort(function (a, b) {
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
        } else {
            _data.sort(function (a, b) {
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
    } else {
        if (type == 'sorting_asc') {
            _data.sort(function (a, b) {
                var val_a = (a[_col] != undefined ? parseFloat(a[_col]) : 0);
                var val_b = (b[_col] != undefined ? parseFloat(b[_col]) : 0);
                return val_a - val_b;
            });
        } else {
            _data.sort(function (a, b) {
                var val_a = (a[_col] != undefined ? parseFloat(a[_col]) : 0);
                var val_b = (b[_col] != undefined ? parseFloat(b[_col]) : 0);
                return val_b - val_a;
            });
        }
    }
    UpdateTableCatchesFishtypeComparison(_data, column, type);
}

//* END CATCHES FISHTYPE COMPARISON
