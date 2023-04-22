<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Imports\CatchesImport;
use App\Imports\LicensesImport;
use App\Imports\LicensesTypeImport;
use App\Imports\StockingImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Models\Catches;
use Carbon\Carbon;
use DB, Validator, Log;

class ImportController extends Controller
{

    public function import(Request $request)
    {
        $file = $request->file('file');
        $type = $request->get('type');
        if ($type == 'catches') {
            Log::info('Importing Catches');
            Excel::import(new CatchesImport, $file);
            return redirect()->route('import')->with('success', 'Catches good!');
        }else if ($type == 'licenses') {
            Log::info('Importing Licenses');
            Excel::import(new LicensesImport, $file);
            return redirect()->route('import')->with('success', 'Licenses good!');
        } else if ($type == 'stocking') {
            Log::info('Importing Stocking');
            Excel::import(new StockingImport, $file);

            return redirect()->route('import')->with('success', 'Stocking good!');
        }
    }
}
