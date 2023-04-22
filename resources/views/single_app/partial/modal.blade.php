<!-- Modal Geographic-->
<?php $group_waterbody = $waterbody['group_waterbody'];  ?>
<div class="modal fade" id="geographic_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">{{__('app.information_geographic_filter')}}</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <img class="image-geomap" src="{{asset("/images/geomap.png")}}" alt="map">
                    </div>
                    <div class="col-md-6">
                        <ul class="region-list left">
                            <li>{!!htmlentities('A1 - Vorderrhein oberhalb Ilanz')!!}</li>
                            <li>{!!htmlentities('A2 - Val Lumnezia/Valsertal')!!}</li>
                            <li>{!!htmlentities('A3 - Vorderrhein unterhalb Ilanz inkl. Safiental')!!}</li>
                            <li>{!!htmlentities('B1 - Rheinwald')!!}</li>
                            <li>{!!htmlentities('B2 - Avers')!!}</li>
                            <li>{!!htmlentities('B3 - Schams')!!}</li>
                            <li>{!!htmlentities('B4 - Albula-/Landwassertal')!!}</li>
                            <li>{!!htmlentities('B5 - Surses/Oberhalbstein')!!}</li>
                            <li>{!!htmlentities('B6 - Lenzerheide/Schin/Heinzenberg/Domleschg')!!}</li>
                            <li>{!!htmlentities('C1 - Churer Rheintal')!!}</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="region-list right">
                            <li>{!!htmlentities('C2 - Schanfigg')!!}</li>
                            <li>{!!htmlentities('C3 - Prättigau')!!}</li>
                            <li>{!!htmlentities('D1 - Oberengadin')!!}</li>
                            <li>{!!htmlentities('D2 - Unterengadin')!!}</li>
                            <li>{!!htmlentities('E - Münstertal')!!}</li>
                            <li>{!!htmlentities('F - Puschlav')!!}</li>
                            <li>{!!htmlentities('G - Bergell')!!}</li>
                            <li>{!!htmlentities('H1 - Misox')!!}</li>
                            <li>{!!htmlentities('H2 - Calancatal')!!}</li>
                        </ul>

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('app.ok')}}</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Geographic-->
<div class="modal fade" id="waterbodytype_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">{!!htmlentities(__('app.information_waterbody_type'))!!}</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 search-button-area">
                        <label for="search_waterbody" class="lb_search_waterbody">{{__('app.filter')}}</label>
                        <input class="form-control waterbody-search" placeholder="{!!htmlentities(__('app.search_waterbody'))!!}" onkeyup="SearchWaterBody(this)" id="search_waterbody" type="text">
                        <button type="button" class="clear-search" onclick="ClearWaterbodySearch()">&times;</button>
                    </div>
                    <div class="col-xl-12">
                        <div id="waterbodytype_tabs_mobile">
                            <?php $stt = 1; ?>
                            @foreach($group_waterbody as $waterbody)
                                <div class="card">
                                    <div class="card-header" id="heading_{{$stt}}">
                                        <button type="button" class="btn collapsed" data-toggle="collapse" data-target="#waterbodytype_mobile_{{$stt}}" data-stt="1" aria-expanded="true">
                                            {!!htmlentities($waterbody['name'])!!} <em class="grch-arrow-icon grch-single-arrow-down"></em>
                                        </button>
                                    </div>
                                    <div id="waterbodytype_mobile_{{$stt}}" class="area-collapse collapse">
                                        <div class="card-body">
                                            <ul class="waterbody-list">
                                                @foreach ($waterbody['waterbody'] as $element)
                                                    <li class="waterbody-list-item" data-content="{!! htmlentities(strtolower($element['region_code'].': '.$element['name'])) !!}">{!! htmlentities($element['region_code'].': '.$element['name']) !!}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <?php $stt++; ?>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('app.ok')}}</button>
            </div>
        </div>
    </div>
</div>