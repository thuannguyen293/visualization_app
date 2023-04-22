<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



Route::get('language/{locale}', 'LanguageController@index')->name('language');

//Route::get('welcome', function () {
//    return view('welcome');
//})->name('welcome');

Route::get('main_app','App\SinglePageController@main_app')->name('main_app');
Route::get('app_jquery','App\SinglePageController@app_jquery')->name('app_jquery');

Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    return "Cache is cleared";
});

Route::get('under_construction', function () {
    return view('under_construction');
})->name('under_construction');

Route::get('import', function () {
    return view('import');
});
Route::post('import', 'App\ImportController@import')->name('import');

Route::get('','App\SinglePageController@home')->name('home');
//
Route::get('statistics','App\SinglePageController@statistics')->name('statistics');
//Route::get('statistics/catches','App\SinglePageController@statistics')->name('catches');

Route::get('catches/time_series','App\SinglePageController@CatchesTimeSeries')->name('catches_time_series');
Route::get('catches/season','App\SinglePageController@CatchesSeason')->name('catches_season');
Route::get('catches/regional_comparison','App\SinglePageController@CatchesRegionalComparison')->name('catches_regional_comparison');
Route::get('catches/fishtype_comparison','App\SinglePageController@CatchesFishtypeComparison')->name('catches_fishtype_comparison');

Route::get('stocking/time_series','App\SinglePageController@StockingTimeSeries')->name('stocking_time_series');
Route::get('stocking/regional_comparison','App\SinglePageController@StockingRegionalComparison')->name('stocking_regional_comparison');

Route::get('licenses','App\SinglePageController@Licenses')->name('licenses');


Route::get('catches/demo_time_series','App\SinglePageController@DemoCatchesTimeSeries')->name('catches_demo_chart');
Route::get('catches/demo_season','App\SinglePageController@DemoCatchesSeason')->name('catches_demo_season');
Route::get('catches/demo_fishtype_comparison','App\SinglePageController@DemoCatchesFishtypeComparison')->name('catches_demo_chart_2');
Route::get('stocking/demo_time_series','App\SinglePageController@DemoStockingTimeSeries')->name('demo_stocking_time_series');
Route::get('licenses/demo','App\SinglePageController@DemoLicenses')->name('demo_licenses');

//Route::get('stocking','App\SinglePageController@statistics')->name('stocking');
// Route::get('licenses','App\SinglePageController@statistics')->name('licenses');
//Route::get('annual_report','App\SinglePageController@statistics')->name('annual_report');

//API

Route::group(['prefix' => 'api'], function () {
    //Catches
    Route::get('catches/time_series/chart', 'Api\CatchesController@GetChartTimeSeriesData');
    Route::get('catches/season/chart', 'Api\CatchesController@GetChartSeasonData');
    Route::get('catches/regional_comparison/chart', 'Api\CatchesController@GetChartRegionalComparisonData');
    Route::get('catches/fishtype_comparison/chart', 'Api\CatchesController@GetChartFishtypeComparisonData');
    //Stocking
    Route::get('stocking/time_series/chart', 'Api\StockingController@GetChartTimeSeriesData');
    Route::get('stocking/regional_comparison/chart', 'Api\StockingController@GetChartRegionalComparisonData');
    //Licenses
    Route::get('licenses/chart', 'Api\LicensesController@GetChartData');

    Route::get('image', 'Api\CatchesController@DownloadImage');
    Route::post('image', 'Api\CatchesController@DownloadImage');
    Route::post('export', 'Api\CatchesController@DownloadExcel');

});


Auth::routes();


Route::group(['prefix' => '{lang}'], function() {
//    Auth::routes();
//    Route::get('welcome', function () {
//        return view('welcome');
//    })->name('welcome');
//
//    Route::get('','App\SinglePageController@home')->name('home');

//    Route::get('statistics','App\SinglePageController@statistics')->name('statistics');
//    Route::get('contact','App\SinglePageController@contact')->name('contact');

//    Route::get('catches/time_series','App\SinglePageController@CatchesTimeSeries')->name('catches_time_series');
//    Route::get('catches/season','App\SinglePageController@CatchesSeason')->name('catches_season');
//    Route::get('catches/regional_comparison','App\SinglePageController@CatchesRegionalComparison')->name('catches_regional_comparison');
//    Route::get('catches/fishtype_comparison','App\SinglePageController@CatchesFishtypeComparison')->name('catches_fishtype_comparison');


    // API

//    Route::group(['prefix' => 'api'], function () {
//        Route::get('catches/chart', 'Api\CatchesController@GetChartData');
//    });
});


