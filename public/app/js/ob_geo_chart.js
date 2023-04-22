(function (window) {
    var OBGeoChart = function (options) {
        this.scale = 180;
        this.delta_x = 2692000;
        this.delta_y = 1111000;
        this.colors = ["#09008c", "#0051a3", "#01b7b6", "#01ce66", "#fbfa01", "#fe8814"];
        this.color_box = {
            "sessions": [[[254, 241, 231], 0], [[250, 185, 133], 1], [[247, 144, 59], 2], [[196, 93, 8], 3], [[98, 46, 4], 4]],
            "catches": [[[236, 246, 249], 0], [[177, 220, 231], 1], [[119, 194, 212], 2], [[55, 151, 174], 3], [[24, 67, 78], 4]],
            "CPUE": [[[255, 230, 230], 0], [[255, 128, 128], 1], [[255, 51, 51], 2], [[204, 0, 0], 3], [[102, 0, 0], 4]],
            "stocking": [[[255, 255, 204], 0], [[194, 230, 153], 1], [[120, 198, 121], 2], [[49, 163, 84], 3], [[0, 104, 5], 4]],
        };

        this.type = (options['type'] != undefined) ? options['type'] : "sessions";
        this.color_type = this.color_box[this.type];

        var width = options["width"];
        var height = options["height"];

        this.width = (width != undefined) ? width : 800;
        this.height = (height != undefined) ? height : 600;

        this.canvas_id = "obj_canvas";
        var ob_canvas = document.createElement("canvas");
        ob_canvas.id = this.canvas_id;
        ob_canvas.height = height;
        ob_canvas.width = width;
        $("#" + this.canvas_id).remove();
        $("#" + options["canvas_id"]).append(ob_canvas);
        this.canvas = ob_canvas;
        //document.body.appendChild(ob_canvas);
        // this.canvas = document.getElementById(options["canvas_id"]);


        this.context = this.canvas.getContext('2d');
        this.context.font = "15px Arial";
        // this.width = (width != undefined) ? width : this.canvas.width;
        // this.height = (height != undefined) ? height : this.canvas.height;

        this.width = (width != undefined) ? width : 800;
        this.height = (height != undefined) ? height : 600;

        this.data = options["data"];
        this.area_list = {};
        this.disable_area_list = {};
        var arr_point = [];
        var arr_value = [];
        var i, j;
        for (i = 1; i < this.data.length; i++) {
            this.area_list[this.data[i][0]] = [];
            var area_info = this.GetPolygonData(this.data[i][0]);
            arr_point = [];
            for (j = 0; j < area_info["polygon"].length; j++) {
                arr_point.push([this.FormatX(area_info["polygon"][j][0]), this.FormatY(area_info["polygon"][j][1])]);
            }
            this.area_list[this.data[i][0]] = arr_point;

            arr_value.push(parseFloat(this.data[i][1]));
        }

        // value data
        var min_value = 0;
        var max_value = 0;
        if (arr_value.length > 0) {
            max_value = min_value = arr_value[0];
            for (var l = 1; l < arr_value.length; l++) {
                if (arr_value[l] > max_value) max_value = arr_value[l];
                if (arr_value[l] < min_value) min_value = arr_value[l];
            }
        }

        $('.ob-legend').remove();
        // Add legend
        var html_legend = "<div class='ob-legend'>";
        html_legend += "<span class='ob-legend-value'>" + numberWithCommas(min_value) + "</span>";
        html_legend += "<div class='color-legend' style='background-image: linear-gradient(to right";
        for (i = 0; i < this.color_type.length; i++) {
            html_legend += ", rgb(" + this.color_type[i][0].join() + ")";
        }
        html_legend += ")'></div>";
        html_legend += "<span class='ob-legend-value'>" + numberWithCommas(max_value) + "</span>";
        html_legend += "<div class='ob-triangle-down'></div>";
        html_legend += "</div>";
        if (min_value != 0 && max_value != 0) {
            $('.ob-geo-chart').append(html_legend);
        }


        for (var item in polygon_data) {
            if (!polygon_data.hasOwnProperty(item)) continue;
            if (this.area_list[item] == undefined) {
                this.disable_area_list[item] = [];
                var disable_area_info = this.GetPolygonData(item);
                arr_point = [];
                for (var k = 0; k < disable_area_info["polygon"].length; k++) {
                    arr_point.push([this.FormatX(disable_area_info["polygon"][k][0]), this.FormatY(disable_area_info["polygon"][k][1])]);

                }
                this.disable_area_list[item] = arr_point;
            }
        }


        this.options = options;
        this.screen_rate_scale = 1;
        this.offset_x = 0;
        this.offset_y = 0;
        this.max_value = max_value;
        this.min_value = min_value;

        this.hover_area = "";
        this.hover_area_name = "";
        this.last_hover_area = "";

        this.ResetOffset();

        // Text
        if (this.options.text) {
            if (this.options.text["no_data"] == undefined)
                this.options.text["no_data"] = "No data";
        } else {
            this.options.text = {"no_data": "No data"};
        }

    };
    //Singleton object
    window.OBGeoChart = OBGeoChart;
    OBGeoChart.instance = null;
    OBGeoChart.getInstance = function (options) {
        // if (OBGeoChart.instance == null) {
        if (options["canvas_id"] != undefined)
            OBGeoChart.instance = new OBGeoChart(options);
        else
            return null;
        // }
        return OBGeoChart.instance;
    };
    OBGeoChart.prototype = {
        Init: function () {
            //var _game = this;
            setInterval(function () {
                // _game.Update();
            }, 1000 / 60);
            this.Draw();
            var geo_chart_lib = this;
            // $("#" + this.options["canvas_id"] ).bind('mousemove touchmove', function (e) {
            $("#" + this.canvas_id).bind('mousemove touchmove', function (e) {
                var bounding_rect = this.getBoundingClientRect();
                var offset_x = bounding_rect.left;
                var offset_y = bounding_rect.top;

                var parent_width = $(this).parent().width();
                var parent_height = $(this).parent().height();

                var mouse_x = (e.clientX - offset_x) * this.width / parent_width;
                var mouse_y = (e.clientY - offset_y) * this.height / parent_height;

                geo_chart_lib.offset_x = mouse_x;
                geo_chart_lib.offset_y = mouse_y;

                geo_chart_lib.hover_area = "";
                geo_chart_lib.hover_area_name = "";

                $(".ob-triangle-down").css("left", -2020);
                for (var item in geo_chart_lib.area_list) {
                    if (geo_chart_lib.Inside([mouse_x, mouse_y], geo_chart_lib.area_list[item])) {
                        geo_chart_lib.hover_area = item;
                        geo_chart_lib.hover_area_name = item;
                        if (geo_chart_lib.hover_area != geo_chart_lib.last_hover_area) {
                            geo_chart_lib.last_hover_area = geo_chart_lib.hover_area;
                            // geo_chart_lib.Draw();
                        }
                        break;
                    }
                }
                if (geo_chart_lib.hover_area == "") {
                    for (var item in geo_chart_lib.disable_area_list) {
                        if (geo_chart_lib.Inside([mouse_x, mouse_y], geo_chart_lib.disable_area_list[item])) {
                            geo_chart_lib.hover_area = item;
                            if (geo_chart_lib.hover_area != geo_chart_lib.last_hover_area) {
                                geo_chart_lib.last_hover_area = geo_chart_lib.hover_area;
                            }
                            break;
                        }
                    }
                }
                if (geo_chart_lib.hover_area == "") {
                    if (geo_chart_lib.last_hover_area != "") {
                        geo_chart_lib.last_hover_area = "";
                        geo_chart_lib.Draw();
                    }
                }

                if (geo_chart_lib.last_hover_area != "") {
                    geo_chart_lib.Draw();
                }

                // geo_chart_lib.Draw();
                e.preventDefault();
            }).bind('mouseleave', function (e) {
                // console.log("mouseleave");
                geo_chart_lib.last_hover_area == "";
            });
        },
        Update: function () {
            //this.Draw();
        },
        Draw: function () {
            this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
            this.context.fillStyle = "#ffffff";
            this.context.fillRect(0, 0, this.canvas.width, this.canvas.height);
            this.DrawPolygonDisable();
            this.DrawPolygonEnable();
            if (this.hover_area != "") {
                this.DrawPopUp();
            }
            //this.DrawLegend();
        },
        DrawPolygonEnable: function () {
            var flagHoverArea = false;
            for (var item in this.area_list) {
                // Fill color
                var area_data = this.GetDataChartByArea(item);
                if (area_data != null) {
                    // this.context.fillStyle = this.colors[this.color_area[item]];
                    this.context.fillStyle = this.GetColorByPercent(area_data[1] / this.max_value);
                    // console.log(item, area_data[1], this.max_value, this.GetColorByPercent(area_data[1] / this.max_value));
                    this.context.beginPath();
                    for (var j = 0; j < this.area_list[item].length; j++) {
                        this.context.lineTo(this.area_list[item][j][0], this.area_list[item][j][1]);
                    }
                    this.context.closePath();
                    this.context.fill();
                }

                // Stock
                this.context.beginPath();
                this.context.lineWidth = 0.5;
                if (this.hover_area == item) {
                    flagHoverArea = true;
                    this.context.lineWidth = 2;
                }
                // this.context.strokeStyle = '#0b6712';
                this.context.strokeStyle = '#333';
                // this.context.moveTo(0, 0);
                for (var k = 0; k < this.area_list[item].length; k++) {
                    this.context.lineTo(this.area_list[item][k][0], this.area_list[item][k][1]);
                }
                this.context.closePath();
                this.context.stroke();
            }

            if (flagHoverArea) {
                // this.DrawPopUp();
                this.DrawRatingColorLegend();
            }

        },
        DrawPolygonDisable: function () {
            var area_list = this.disable_area_list;
            // var flagHoverArea = false;
            for (var item in area_list) {
                // Fill color
                this.context.fillStyle = '#f5f5f5';
                this.context.beginPath();
                for (var j = 0; j < area_list[item].length; j++) {
                    this.context.lineTo(area_list[item][j][0], area_list[item][j][1]);
                }
                this.context.closePath();
                this.context.fill();

                // Stock
                this.context.beginPath();
                this.context.lineWidth = 1;
                if (this.hover_area == item) {
                    // flagHoverArea = true;
                    this.context.lineWidth = 2;
                }
                this.context.strokeStyle = '#000';
                for (var k = 0; k < area_list[item].length; k++) {
                    this.context.lineTo(area_list[item][k][0], area_list[item][k][1]);
                }
                this.context.closePath();
                this.context.stroke();
            }
        },
        DrawPopUp: function () {
            this.context.font = "15px Arial";
            var rect_left = this.offset_x;
            var rect_top = this.offset_y - 30;
            var height_rect = 52;
            var text_title = this.options.text["region"] + ": " + this.GetPolygonName(this.hover_area);
            var text_info = this.options.text["type"] + ": " + this.options.text["no_data"];
            var measure_text_title = this.context.measureText(text_title);
            var measure_text_info = this.context.measureText(text_info);//Keine Daten
            var width_rect = measure_text_info.width + 25;

            if (measure_text_title.width > measure_text_info.width)
                width_rect = measure_text_title.width + 25;

            var flagHoverActiveArea = false;
            var flagHoverDisableArea = false;
            for (var item in this.area_list) {
                if (this.hover_area == item) {
                    flagHoverActiveArea = true;
                }
            }
            for (var item in this.disable_area_list) {
                if (this.hover_area == item) {
                    flagHoverDisableArea = true;
                }
            }
            if (flagHoverActiveArea) {
                var info_chart = this.GetInformationChart();
                //text_info = this.hover_area + ": " + numberWithCommas(info_chart[1]);
                if (info_chart != null) {
                    console.log(info_chart);
                    text_info = this.options.text["type"] + ": " + numberWithCommas(info_chart[1]);
                    measure_text_info = this.context.measureText(text_info);
                    if (measure_text_info.width > measure_text_title.width)
                        width_rect = measure_text_info.width + 25;
                }
            }

            if (rect_left > this.canvas.width * 1 / 2) {
                rect_left -= width_rect;
            }

            if (flagHoverActiveArea || flagHoverDisableArea) {
                this.context.fillStyle = '#fff';
                this.context.fillRect(rect_left, rect_top, width_rect, height_rect);
                this.context.lineWidth = 1;
                this.context.strokeStyle = '#333333';
                this.context.strokeRect(rect_left, rect_top, width_rect, height_rect);
                this.context.fillStyle = "#000";
                this.context.fillText(text_title, rect_left + 10, rect_top + 20);
                this.context.fillText(text_info, rect_left + 10, rect_top + 40);
            }
        },
        DrawLegend: function () {

            this.context.fillStyle = "#000";
            var min_value = numberWithCommas(this.min_value);
            var max_value = numberWithCommas(this.max_value);
            var measure_text = this.context.measureText(min_value);
            this.context.fillText(min_value, 0, 599);

            var grd = this.context.createLinearGradient(0, 0, 200, 0);
            grd.addColorStop(0, "rgb(" + this.color_type[0][0].join() + ")");
            for (var i = 1; i < this.color_type.length - 1; i++) {
                grd.addColorStop(i / (this.color_type.length - 1), "rgb(" + this.color_type[i][0].join() + ")");
            }

            grd.addColorStop(1, "rgb(" + this.color_type[this.color_type.length - 1][0].join() + ")");

            this.context.fillStyle = grd;
            this.context.fillRect(measure_text.width + 5, 587, 200, 13);

            this.context.fillStyle = "#000";
            this.context.fillText(max_value, measure_text.width + 5 + 200 + 5, 599);


        },
        DrawRatingColorLegend: function () {
            var info_chart = this.GetInformationChart();
            if (info_chart != null) {
                var rate_legend = (info_chart[1] - this.min_value) / (this.max_value - this.min_value);
                $(".ob-triangle-down").css("left", $(".color-legend").position()["left"] + rate_legend * $(".color-legend").width());
            }
        },
        GetPolygonData: function (area_name) {
            return polygon_data[area_name];
        },
        GetPolygonName: function (area_name) {
            return polygon_data[area_name]['name'];
        },
        FormatX: function (location) {
            return (location - this.delta_x) / this.scale;
        },
        FormatY: function (location) {
            var convert_location = (location - this.delta_y) / this.scale;
            return this.ConvertY(convert_location);
        },
        ConvertY: function (location) {
            const line_height = this.height / 2;
            if (location > line_height) {
                location = line_height - (location - line_height);
            } else {
                location = line_height + (line_height - location);
            }
            return location;
        },
        ResetOffset: function () {
            var BB = this.canvas.getBoundingClientRect();
            // console.log(this.canvas);
            // console.log("ResetOffset", BB.left, BB.top);
            this.offset_x = BB.left;
            this.offset_y = BB.top;
        },
        Inside: function (point, vs) {
            // ray-casting algorithm based on
            // http://www.ecse.rpi.edu/Homepages/wrf/Research/Short_Notes/pnpoly.html

            var x = point[0], y = point[1];

            var inside = false;
            for (var i = 0, j = vs.length - 1; i < vs.length; j = i++) {
                var xi = vs[i][0], yi = vs[i][1];
                var xj = vs[j][0], yj = vs[j][1];

                var intersect = ((yi > y) != (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
                if (intersect) inside = !inside;
            }

            return inside;
        },
        GetInformationChart: function () {
            for (var i = 1; i < this.data.length; i++) {
                if (this.data[i][0] == this.hover_area) {
                    return this.data[i];
                }
            }
            return null;
        },
        GetDataChartByArea: function (area) {
            for (var i = 1; i < this.data.length; i++) {
                if (this.data[i][0] == area) {
                    return this.data[i];
                }
            }
            return null;
        },
        GetImageData: function () {
            return this.canvas.toDataURL();
        },
        GetImageDataJPEG: function () {
            this.hover_area = "";
            this.Draw();
            this.DrawLegend();
            var image_data_result = this.canvas.toDataURL("image/jpeg", 1.0);
            this.Draw();
            return image_data_result;
        },
        GetColorByPercent: function (percent) {
            var colorRange = [0, 1];
            if (percent > 0) {
                for (var i = 0; i < this.color_type.length; i++) {
                    if (percent <= i / (this.color_type.length - 1)) {
                        colorRange = [i - 1, i];
                        break;
                    }
                }
            }

            //Get the two closest colors
            var firstcolor = this.color_type[colorRange[0]][0];
            var secondcolor = this.color_type[colorRange[1]][0];

            var sliderWidth = $(".color-legend").width();

            //Calculate ratio between the two closest colors
            var firstcolor_x = sliderWidth * (this.color_type[colorRange[0]][1] / (this.color_type.length - 1));
            var secondcolor_x = sliderWidth * (this.color_type[colorRange[1]][1] / (this.color_type.length - 1)) - firstcolor_x;

            var slider_x = sliderWidth * (percent) - firstcolor_x;
            var ratio = slider_x / secondcolor_x;
            // console.log(firstcolor,secondcolor,ratio);
            //Get the color with pickHex(thx, less.js's mix function!)
            var result = this.pickHex(secondcolor, firstcolor, ratio);

            // $('.test-color').css("background-color", 'rgb('+result.join()+')');
            return 'rgb(' + result.join() + ')';
        },
        pickHex: function (color1, color2, weight) {
            var p = weight;
            var w = p * 2 - 1;
            var w1 = (w / 1 + 1) / 2;
            var w2 = 1 - w1;
            var rgb = [Math.round(color1[0] * w1 + color2[0] * w2),
                Math.round(color1[1] * w1 + color2[1] * w2),
                Math.round(color1[2] * w1 + color2[2] * w2)];
            return rgb;
        }
    };
})
(window);