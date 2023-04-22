<?php 

namespace App\Imports;

use App\Models\Catches;
use App\Jobs\CatchesImportJob;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Contracts\Queue\ShouldQueue;
use Carbon\Carbon;
use Log;
		
class CatchesImport	implements ToCollection, ShouldQueue, WithChunkReading, WithStartRow, WithBatchInserts
{
	public function collection(Collection $rows)
	{	
		foreach ($rows as $row) {
			dispatch(new CatchesImportJob($row));
			// Catches::create([
			// 	'fishing_session_date' => Carbon::parse(($row[0] - 25569)*24*60*60)->format('Y-m-d'),
			// 	'waterbody_id' => $row[1],
			// 	'fishtype_code' => $row[2],
			// 	'bis22' => $row[3],
			// 	'bis24' => $row[4],
			// 	'bis28' => $row[5],
			// 	'bis34' => $row[6],
			// 	'ab34' => $row[7],
			// 	'fish_total' => $row[8],
			// 	'fish_boat' => $row[9],
			// ]);
		}
	}

	public function startRow(): int
	{
		return 3;
	}

	public function batchSize(): int
	{
		return 500;
	}

	public function chunkSize(): int
	{
		return 500;
	}
}
 ?>