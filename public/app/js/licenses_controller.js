
var licenses_buyer_type = {
    "local_buyers" : 1,
    "foreign_buyers" : 2,
    "all_licenses" : "all"
}
var LoadLicensesPage = function () {
    console.log("LoadLicensesPage");
    var html = "";
    html += '<div class="col-md-12 filter-item">';
    html += '   <div class="row">';
    html += '       <div class="col-xl-12">';
    html += '           <div class="btn-group group-filter-1">';
    html += '               <button data-sub_page="local_buyers" class="btn btn-basic w-100 menu-two active" type="button">Einheimische Fischer</button>';
    html += '               <button data-sub_page="foreign_buyers" class="btn btn-basic w-100 menu-two" type="button">Ausserkantonale Fischer</button>';
    html += '               <button data-sub_page="all_licenses" class="btn btn-basic w-100 menu-two" type="button">Alle Fischer</button>';
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
            UpdateChartLicenses();
        }
    });


    LoadLicensesSubPage();
};

function LoadLicensesSubPage() {
    console.log("LoadLicensesSubPage", current_sub_page);
    $('#my_chart').remove();
    $('.detail-table-area').html("");

    DownloadImage = DownloadLicenses;

    var html = "";
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

    $('.detail-table-area').html(html_table);

    my_slider = document.getElementById('slider-range');

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
        UpdateChartLicenses();
    });
    UpdateChartLicenses();
}

function UpdateChartLicenses() {
    time_range = my_slider.noUiSlider.get();
    ShowLoading();
    $.ajax({

        url: app_url + '/api/licenses/chart',
        type: "get",
        data: {
            buyer_type: licenses_buyer_type[current_sub_page],
            time_range: time_range
        },
        success: function (result) {
            HideLoading();
            DrawChartLicense(result.data);
            UpdateTableLicense(result.data);
            chart_data = result.data;
        },
        error: function(){
            HideLoading();
        }
    });

    var text_filter_information = 'Patentverkäufe, ';
    text_filter_information += $('.filter-item .menu-two.active').text();
    text_filter_information += ' ' + Math.round(time_range[0]) + ' - ' + Math.round(time_range[1]);

    $('.filter-information').html(text_filter_information);
}

function DrawChartLicense(data) {
    $('.action-tip').show();
    $('#my_chart').remove(); // this is my <canvas> element
    $('.detail-chart-area').prepend('<canvas id="my_chart" width="900" height="500"></canvas>');
    current_chart = document.getElementById('my_chart');

    if (data.length == 0 || data["average"].length == 0) {
        $(".detail-area").addClass('no-data');
        return;
    }else{
        $(".detail-area").removeClass('no-data');
    }

    var time_range = my_slider.noUiSlider.get();
    var ctx_chart = document.getElementById('my_chart').getContext('2d');
    var labels = [];
    var datasets = {};
    var temp_data = data.licenses;
    var avg = data.average;
    var types = data.license_types.filter(function(value, index, arr){ return index !== 2;});//Remove Other License - index = 2;
    // var color_list = ['#005780', '#D45087', '#FFA600'];

    $.each(types, function(k, license_type){
        datasets[license_type] = [];
    });
    $.each(temp_data, function (i, item) {
        labels.push(String(i));
        $.each(types, function(k, license_type){
            var value = 0;
            $.each(item, function(k, v){
                if (v["license_type"] === license_type) {
                    value = v["licenses"];
                }
            });

            // var element = item.find(el => el.license_type === license_type);
            // var value = 0;
            // if (element) {
            //     value = element.licenses;
            // }
            datasets[license_type].push(value);
        });
    });
    if(parseInt(time_range[1]) >= min_year && parseInt(time_range[1]) <= max_year){
        labels.push("");
        labels.push(ConvertToHTML('Ø 10 Jahre'));
        $.each(types, function(k, license_type){

            var value = 0;
            $.each(avg, function(k, v){
                if (v["license_type"] === license_type) {
                    value = parseFloat(v["average"]);
                }
            });

            // var element = avg.find(el => el.license_type === license_type);
            // var value = 0;
            // if (element) {
            //     value = parseFloat(element.average);
            // }
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
                        labelString: 'Anzahl'
                     }
                }]
            }
        }
    });
}

function UpdateTableLicense(data) {
    if ( $.fn.dataTable.isDataTable('.detail-table-area table') ) {
        $('.detail-table-area table').DataTable().destroy();
    }
    var time_range = my_slider.noUiSlider.get();
    var header_container = $('.detail-table-area table thead');
    var container = $('.detail-table-area table tbody');
    var header_html = '';
    var container_html = '';
    var types = data.license_types.filter(function(value, index, arr){ return index !== 3;});//Remove Jugendpatent - index = 3;
    header_html = '<tr>';
    header_html += '<th>Jahr</th>';
    $.each(types, function(i, license_type){
        if (license_type == 'other') {
            header_html += '<th>Übrige Patente</th>';
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
            var value = 0;
            $.each(item, function(k, v){
                if (v["license_type"] === licensetype) {
                    value = v["licenses"];
                }
            });


            // element = item.find(el => el.license_type === licensetype);
            // if (element) {
            //     value = element.licenses;
            // }else{
            //     value = 0;
            // }
            container_html += '   <td>'+numberWithCommas(value)+'</td>';
        });
        container_html += '</tr>';
    });
    if(parseInt(time_range[1]) >= min_year && parseInt(time_range[1]) <= max_year){
        container_html += '<tr>';
        container_html += '   <td>Ø 10 Jahre</td>';
        $.each(types, function(k, licensetype){

            var value = 0;
            $.each(data.average, function(k, v){
                if (v["license_type"] === licensetype) {
                    value = parseFloat(v["average"]);
                }
            });


            // element = data.average.find(el => el.license_type === licensetype);
            // if (element) {
            //     value = parseFloat(element.average);
            // }else{
            //     value = 0;
            // }
            container_html += '   <td>'+numberWithCommas(value)+'</td>';
        });
        container_html += '</tr>';
    }
    container.html(container_html);
    $('.detail-table-area table').DataTable({
        paging: false,
        select: true
    });
}

function DownloadLicenses() {
    if ($('.detail-chart-area').hasClass('active')){
        DownloadImagePDFCallback(current_chart.toDataURL("image/png", 1.0), "Patentverkäufe_Zeitreihe.pdf", $('.filter-information').html());
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

                var value = 0;
                $.each(item, function(k, v){
                    if (v["license_type"] === licensetype) {
                        value = v["licenses"];
                    }
                });

                // var value;
                // var element = item.find(el => el.license_type === licensetype);
                // if (element) {
                //     value = element.licenses;
                // }else{
                //     value = 0;
                // }
                temp.push(value);
            });
            data_post["body"].push(temp);
        });
        temp = [];
        temp.push("Ø 10 Jahre");
        $.each(types, function(k, licensetype){

            var value = 0;
            $.each(avg, function(k, v){
                if (v["license_type"] === licensetype) {
                    value = parseFloat(v["average"]);
                }
            });

            // var value;
            // var element = avg.find(el => el.license_type === licensetype);
            // if (element) {
            //     value = parseFloat(element.average);
            // }else{
            //     value = 0;
            // }
            temp.push(value);
        });
        data_post["body"].push(temp);

        DownloadExcelData(data_post, 'Patentverkäufe_Zeitreihe.xlsx');
    }
    return false;
}