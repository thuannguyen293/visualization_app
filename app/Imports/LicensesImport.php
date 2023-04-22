<?php 

namespace App\Imports;

use App\Models\Licenses;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\Importable;
use App\Imports\LicensesTypeImport;
		
class LicensesImport implements ToCollection, WithStartRow
{
	use Importable;

	public function collection(Collection $rows)
	{	
		$license_type = public_path().'/License Types_Updated.xlsx';
        $type = (new LicensesTypeImport)->toArray($license_type)[0];
		foreach ($rows as $row) {
			$key = array_search($row[0], array_column($type, 0));
			Licenses::create([
				'year' => $row[2],
				'license_code' => $row[0],
				'license_total' => $row[1],
				'license_type' => $row[3],
				'license_name' => $row[4],
				'license_category' => $row[5],
				'buyer' => ($type[$key][4] == 'Einheimische') ? 1:(($type[$key][4] == 'Ausserkantonale') ? 2:0),
				'is_young' => ($type[$key][5] == 'Yes') ? 1:0,
			]);
		}
	}

	public function startRow(): int
	{
		return 2;
	}
}
 ?>