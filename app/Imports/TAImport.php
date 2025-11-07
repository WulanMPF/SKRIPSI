<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class TAImport implements ToCollection
{
    public $headerData = [];
    public $detailData = [];

    public function collection(Collection $rows)
    {
        // dd($rows);
        // --- HEADER DATA ---
        $this->headerData = [
            'ta_project_pekerjaan' => $rows[1][3] ?? '',
            'ta_project_khs'       => $rows[2][3] ?? '',
            'ta_project_pelaksana' => isset($rows[3][3]) ? str_replace(':', '', trim($rows[3][3])) : '',
            'ta_project_witel'     => isset($rows[4][3]) ? str_replace(':', '', trim($rows[4][3])) : '',
        ];

        // --- DETAIL DATA (mulai dari baris ke-11 / index 11) ---
        $this->detailData = [];
        for ($i = 11; $i < count($rows); $i++) {
            $row = $rows[$i];
            $designator = $row[2] ?? '';
            $volume = $row[7] ?? '';

            // Hanya masukkan baris jika designator ada dan volume tidak kosong
            if (!empty($designator) && $volume !== '' && $volume !== null) {
                $this->detailData[] = [
                    'designator' => trim($designator),
                    'volume' => is_numeric($volume) ? (float) $volume : $volume,
                ];
            }
        }

        // Tes hasil
        // dd([
        //     'headerData'   => $this->headerData,
        //     'detailCount'  => count($this->detailData),
        //     'detailSample' => array_slice($this->detailData, 0, 20),
        // ]);
    }
}
