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
                            <li>A1 - Vorderrhein oberhalb Ilanz</li>
                            <li>A2 - Val Lumnezia/Valsertal</li>
                            <li>A3 - Vorderrhein unterhalb Ilanz inkl. Safiental</li>
                            <li>B1 - Rheinwald</li>
                            <li>B2 - Avers</li>
                            <li>B3 - Schams</li>
                            <li>B4 - Albula-/Landwassertal</li>
                            <li>B5 - Surses/Oberhalbstein</li>
                            <li>B6 - Lenzerheide/Schin/Heinzenberg/Domleschg</li>
                            <li>C1 - Churer Rheintal</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="region-list right">
                            <li>C2 - Schanfigg</li>
                            <li>C3 - Prättigau</li>
                            <li>D1 - Oberengadin</li>
                            <li>D2 - Unterengadin</li>
                            <li>E - Münstertal</li>
                            <li>F - Puschlav</li>
                            <li>G - Bergell</li>
                            <li>H1 - Misox</li>
                            <li>H2 - Calancatal</li>
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
                        <input class="form-control waterbody-search" placeholder="{!!htmlentities(__('app.search_waterbody'))!!}" onkeyup="SearchWaterBody(this)" id="search_waterbody">
                        <button class="clear-search" onclick="ClearWaterbodySearch()">&times;</button>
                    </div>
                    <div class="col-xl-12">
                        <div id="waterbodytype_tabs_mobile">
                            <?php $stt = 1; ?>
                            @foreach($group_waterbody as $waterbody)
                                <div class="card">
                                    <div class="card-header" id="heading_{{$stt}}">
                                        <button class="btn collapsed" data-toggle="collapse" data-target="#waterbodytype_mobile_{{$stt}}" data-stt="{{$stt}}" aria-expanded="true">{!!htmlentities($waterbody['name'])!!} <em class="grch-arrow-icon grch-single-arrow-down"></em>
                                        </button>
                                    </div>
                                    <div id="waterbodytype_mobile_{{$stt}}" class="area-collapse collapse">
                                        <div class="card-body">
                                            <ul class="waterbody-list">
                                                @foreach ($waterbody['waterbody'] as $element)
                                                    <li class="waterbody-list-item" data-content="{!! strtolower($element['region_code'].': '.$element['name']) !!}">{!! $element['region_code'].': '.htmlentities($element['name']) !!}</li>
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