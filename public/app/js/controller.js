var current_page = "catches";
var current_sub_page = "time_series";

var min_year = 2011;
// var max_year = new Date().getFullYear() - 1;
var max_year = 2020;
var color_list = ['#005780', '#D45087', '#FFA600', '#009966', '#F69F6A', '#595959', '#00AEFF'];
var table_column_number = 5;

var app_url = $("#app_url").data('value');
var time_range = $("#time_range").data('value');
var time_year = $("#time_year").data('value');
var regions = $("#region_data").data('value');
var waterbody_types = $("#waterbody_type_data").data('value');

var current_chart;
var chart_data;
var my_slider;

// function common
var OnLoadPage = LoadCatchesPage;
var DownloadImage;
var SortData;


$(document).ready(function () {
    $('.data-selection-menu .menu-one').on('click', function (obj) {
        $(".menu-one").removeClass('active');
        $(obj.target).addClass('active');
        var new_page = $(obj.target).data("page");
        var new_sub_page = $(obj.target).data("sub_page");
        if (new_page != current_page || new_sub_page != current_sub_page) {
            current_page = new_page;
            current_sub_page = new_sub_page;
            // console.log(current_page, current_sub_page);
            ResetPage();
        }
    });
    OnLoadPage();

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


    $(document).on('click', 'table tbody .heading th', function(){
        var class_current = $(this).attr('class');
        var data = chart_data;
        var column = $(this).data('index');
        var type = '';
        $(this).removeClass(class_current);
        if (class_current == 'sorting_asc') {
            $(this).addClass('sorting_desc');
            type = 'sorting_desc';
        }
        if (class_current == 'sorting_desc') {
            $(this).addClass('sorting_asc');
            type = 'sorting_asc';
        }
        if (class_current == 'sorting') {
            $('table tbody .heading th').removeClass();
            $(this).addClass('sorting_asc');
            $('table tbody .heading th').not(this).addClass('sorting');
            type = 'sorting_asc';
        }
        SortData(data, column, type);
        return false;
    });

    $("#waterbodytype_tabs_mobile").find(".btn").click(function () {
        var stt = $(this).data('stt');
        setTimeout(function () {
            var obj = $("#waterbodytype_tabs").find(".nav-link");
            if (!obj.eq(stt - 1).hasClass("active")) {
                obj.eq(stt - 1).trigger('click');
            }
        }, 100);
    });
});


function ResetPage() {
    $('.filter-area').html('');
    $('.detail-option-area').html('');
    $('.detail-option-area').hide();
    $('.ob-geo-chart').remove();
    $('.detail-option-area').html('');
    $('.detail-option-area').hide();
    $('.action-tip').hide();
    switch (current_page) {
        case "stocking":
            OnLoadPage = LoadStockingPage;
            break;
        case "licenses":
            OnLoadPage = LoadLicensesPage;
            break;
        default:
            //catches
            OnLoadPage = LoadCatchesPage;
            break;
    }
    OnLoadPage();
}


function ShowChartArea() {
    $('.detail-chart-area').addClass('active');
    $('.detail-table-area').removeClass('active');

    $('.btn-show-chart').addClass('active');
    $('.btn-show-table').removeClass('active');
    return false;
}


function ShowTableArea() {
    $('.detail-chart-area').removeClass('active');
    $('.detail-table-area').addClass('active');

    $('.btn-show-chart').removeClass('active');
    $('.btn-show-table').addClass('active');
    return false;
}

function ReloadFilterInformation(screen_text, is_two_year) {
    var time_range = my_slider.noUiSlider.get();

    //console.log("UpdateFilterInformation", geographic_id, fishtype_code, waterbody_type, time_range);
    var text_filter_information = screen_text + ', ' + $('.group-filter-1').find(".active").html();
    if (is_two_year) {
        text_filter_information += ' ' + Math.round(time_range[0]) + ' - ' + Math.round(time_range[1]);
    } else {
        text_filter_information += ' ' + Math.round(time_range);
    }

    var value_geographic_filter = $('select[name="geographic_filter"]').children("option:selected").val();
    if (value_geographic_filter == '') {
        text_filter_information += ', ' + $('select[name="geographic_filter"]').data('placeholders');
    } else {
        text_filter_information += ', ' + $('select[name="geographic_filter"]').children("option:selected").text();
    }

    var value_waterbody_type = $('select[name="waterbody_type"]').children("option:selected").val();
    if (value_waterbody_type== '') {
        text_filter_information += ', ' + $('select[name="waterbody_type"]').data('placeholders');
    } else {
        text_filter_information += ', ' + $('select[name="waterbody_type"]').children("option:selected").text();
    }

    $('.filter-information').html(text_filter_information);
    return false;
}

function DownloadImagePDFCallback(img_base64_jpeg, file_name, title) {
    // var pdf = new jsPDF('l', 'mm', [650, 700]);
    var pdf = new jsPDF('landscape');
    pdf.text(20, 10, title);
    pdf.addImage(img_base64_jpeg, 'JPEG', 3, 22, 290, 160);
    pdf.save(file_name);
    return false;
}

// function Resize
function ResizeCanvasImageData(canvas_obj, width, height){
    var resizedCanvas = document.createElement("canvas");
    var resizedContext = resizedCanvas.getContext("2d");
    resizedCanvas.height = height;
    resizedCanvas.width = width;
    resizedContext.fillStyle = "white";
    resizedContext.fillRect(0, 0, width, height);
    resizedContext.drawImage(canvas_obj, 0, 0, width, height);
    return resizedCanvas.toDataURL();

}

function ConvertToDataJPGE(png_base64, width, height, file_name, title) {

    var image = new Image();
    image.onload = function () {
        var canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        canvas.getContext('2d').drawImage(image, 0, 0);
        try {
            DownloadImagePDFCallback(canvas.toDataURL("image/jpeg", 1.0), file_name, title);
        } catch (e) {
            console.log("ConvertToDataJPGE", e);
        }

    };
    image.src = png_base64;
    return false;
}

function DownloadExcelData(data_post, file_name) {
    $.ajax({
        url: app_url + '/api/export',
        type: "POST",
        data: data_post,
        dataType: 'binary',
        success: function (result, status, xhr) {
            var url = URL.createObjectURL(result);
            if (window.navigator.msSaveOrOpenBlob) {
                // Internet Explorer
                var contentType = xhr.getResponseHeader("content-type");
                window.navigator.msSaveOrOpenBlob(new Blob([result], {type: contentType}), file_name);
            } else{
                $('<a />', {'href': url, 'download': file_name}).hide().appendTo("body")[0].click();
                setTimeout(function () {
                    URL.revokeObjectURL(url);
                }, 1000);
            }
        }
    });
    return false;
}

function SearchWaterBody(obj) {
    var value = $(obj).val();
    $('#filter_style').remove();
    $('#waterbodytype_modal').addClass('seaching');
    $('.clear-search').hide();
    if (value === "") {
        $('#waterbodytype_tabs_mobile .area-collapse').collapse('hide');
        $('#waterbodytype_modal').removeClass('seaching');
        return;
    }
    $('.clear-search').show();
    $(".waterbody-list-item[data-content*=\""+value.toLowerCase()+"\"]").closest(".collapse").collapse('show');
    $("<style id='filter_style' type='text/css'>.waterbody-list-item:not([data-content*=\"" + value.toLowerCase() + "\"]) { display: none;} </style>").appendTo("head");
    return false;
}

function ClearWaterbodySearch(){
    $('.waterbody-search').val("");
    SearchWaterBody($('.waterbody-search'));
    return false;
}

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "'");
}

function ShowLoading() {
    $('body').LoadingOverlay("show");
}
function HideLoading() {
    $('body').LoadingOverlay("hide");
}

function ConvertToHTML(text){
    var parser = new DOMParser;
    var dom = parser.parseFromString(
        '<!doctype html><body>' + text,
        'text/html');
    var decodedString = dom.body.textContent;
    return decodedString;
}

/**
 *
 * jquery.binarytransport.js
 *
 * @description. jQuery ajax transport for making binary data type requests.
 * @version 1.0
 *
 */

// use this transport for "binary" data type
$.ajaxTransport("+binary", function (options, originalOptions, jqXHR) {
    // check for conditions and support for blob / arraybuffer response type
    if (window.FormData && ((options.dataType && (options.dataType == 'binary')) || (options.data && ((window.ArrayBuffer && options.data instanceof ArrayBuffer) || (window.Blob && options.data instanceof Blob))))) {
        return {
            // create new XMLHttpRequest
            send: function (_, callback) {
                // setup all variables
                var xhr = new XMLHttpRequest(),
                    url = options.url,
                    type = options.type,
                    // blob or arraybuffer. Default is blob
                    dataType = options.responseType || "blob",
                    data = options.data || null;

                xhr.addEventListener('load', function () {
                    var data = {};
                    data[options.dataType] = xhr.response;
                    // make callback and send data
                    callback(xhr.status, xhr.statusText, data, xhr.getAllResponseHeaders());
                });

                xhr.open(type, url, true);
                xhr.responseType = dataType;
                xhr.send(data);
            },
            abort: function () {
                jqXHR.abort();
            }
        };
    }
});