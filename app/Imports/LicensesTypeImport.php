<?php 

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\Importable;

class LicensesTypeImport implements ToCollection, WithStartRow
{
	use Importable;

	public function collection(Collection $rows)
	{	
		return $rows;
	}

	public function startRow(): int
	{
		return 2;
	}
}
 ?>