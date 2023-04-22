$(document).ready(function () {
    $('.js-select2-active').each(function (index) {
        var placeholders = $(this).data('placeholders');
        $(this).select2({
            width: 'style',
            placeholder: placeholders
            // allowClear: true
        });
        $(this).on('change', function () {
            var change_value = $(this).val();
            if(change_value == "all"){
                $(this).find("option[value='all']").remove();
            }else{
                if($(this).find("option[value='all']").length == 0){
                    $(this).prepend('<option value="all">'+placeholders+'</option>');
                }

            }
        });
    });

    $(window).on('resize', function () {
        // var win = $(this); //this = window
        // if (win.width() >= 992) {
        //     $('#waterbodytype_tabs').show();
        //     $('#waterbodytype_tab_content').show();
        //     $('#waterbodytype_tabs_mobile').hide();
        // } else {
        //     $('#waterbodytype_tabs').hide();
        //     $('#waterbodytype_tab_content').hide();
        //     $('#waterbodytype_tabs_mobile').show();
        // }
    });
    $(window).trigger('resize');

    $("#waterbodytype_tabs_mobile").find(".btn").click(function () {
        var stt = $(this).data('stt');
        setTimeout(function () {
            var obj = $("#waterbodytype_tabs").find(".nav-link");
            if (!obj.eq(stt - 1).hasClass("active")) {
                obj.eq(stt - 1).trigger('click');
            }
        }, 100);
    });

    // $("#waterbodytype_tabs").find(".nav-link").click(function () {
    //     var stt = $(this).data('stt');
    //     setTimeout(function () {
    //         var obj = $("#waterbodytype_tabs_mobile").find(".btn");
    //         if (obj.eq(stt - 1).hasClass("collapsed")) {
    //             obj.eq(stt - 1).trigger('click');
    //         }
    //     }, 100);
    // });

    $("button").on('click',function (e) {
        e.preventDefault();
    });
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
    sorting_data(data, column, type);
    return false;
});
function DownloadImagePDFCallback(img_base64_jpeg, file_name, title) {
    // var pdf = new jsPDF();
    var pdf = new jsPDF('l', 'mm', [650, 700]);
    pdf.text(20, 10, title);
    pdf.addImage(img_base64_jpeg, 'JPEG', 3, 22);
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
        url: $('#main_body').data('url') + '/api/export',
        type: "POST",
        data: data_post,
        dataType: 'binary',
        success: function (result) {
            var url = URL.createObjectURL(result);
            $('<a />', {
                'href': url,
                'download': file_name
            }).hide().appendTo("body")[0].click();
            setTimeout(function () {
                URL.revokeObjectURL(url);
            }, 10000);
        }
    });
    return false;
}

function UpdateFilterInformation(screen_text, is_two_year = true) {
    var time_range = mySlider.noUiSlider.get();

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

    // var value_fishtype_filter = $('select[name="fishtype_filter"]').children("option:selected").val();
    // if (value_fishtype_filter == '') {
    //     text_filter_information += ', ' + $('select[name="fishtype_filter"]').data('placeholders');
    // } else {
    //     text_filter_information += ', ' + $('select[name="fishtype_filter"]').children("option:selected").text();
    // }

    $('.filter-information').html(text_filter_information);
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
    $('.detail-area').LoadingOverlay("show");
}
function HideLoading() {
    $('.detail-area').LoadingOverlay("hide");
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