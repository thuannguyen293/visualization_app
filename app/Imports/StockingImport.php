<?php

namespace App\Imports;

use App\Models\Stocking;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\Importable;

class StockingImport implements ToCollection, WithStartRow
{
    use Importable;

    public function collection(Collection $rows)
    {
        $arr_fishtype = [
            4 => "BF",
            5 => "RF",
            6 => "NC",
            7 => "AA",
        ];

        foreach ($rows as $row) {
            //Lake

//            for ($i = 4; $i < 8; $i++) {
//                if (!empty($row[$i])) {
//                    Stocking::create([
//                        'year' => $row[3],
//                        'waterbody_id' => intval($row[0]),
//                        'region_code' => $row[2],
//                        'fishtype_code' => $arr_fishtype[$i],
//                        'fish_total' => intval($row[$i]),
//                    ]);
//                }
//            }


            // Stream
            if (!empty($row[4])) {
                Stocking::create([
                    'year' => $row[3],
                    'waterbody_id' => intval($row[0]),
                    'region_code' => $row[2],
                    'fishtype_code' => "BF_SF",
                    'fish_total' => intval($row[4]),
                ]);
            }
        }
    }

    public function startRow(): int
    {
        return 3;
    }
}