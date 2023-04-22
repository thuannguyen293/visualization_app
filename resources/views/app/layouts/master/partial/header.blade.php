<h1>{{ __('app.data_visualization_temporary')}}</h1>
<ul class="data-selection-menu">
    <li @if(strpos(Route::currentRouteName(), 'catches') !== false)class="active"@endif><a href="{{route('catches_time_series')}}"><em class="ob-icon ob-icon-catches"></em>{!! htmlentities(__('app.catches')) !!}</a></li>
    <li @if(strpos(Route::currentRouteName(), 'stocking') !== false)class="active"@endif><a href="{{route('stocking_time_series')}}"><em class="ob-icon ob-icon-stocking"></em>{!! htmlentities(__('app.stocking')) !!}</a></li>
    <li @if(strpos(Route::currentRouteName(), 'licenses') !== false)class="active"@endif><a href="{{route('licenses')}}"><em class="ob-icon ob-icon-licenses"></em>{!! htmlentities(__('app.licenses')) !!}</a></li>
{{--    <li @if(strpos(Route::currentRouteName(), 'annual_report') !== false)class="active"@endif><a ><i class="grch-file-icon grch-pdf"></i>{{ __('app.annual_report')}}</a></li>--}}
</ul>