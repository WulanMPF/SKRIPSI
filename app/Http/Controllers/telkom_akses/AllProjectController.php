<?php

namespace App\Http\Controllers\telkom_akses;

use App\Http\Controllers\Controller;
use Google\Cloud\Firestore\FirestoreClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;
use Google\Cloud\Core\Timestamp as FireTimestamp;
use Cloudinary\Cloudinary;

class AllProjectController extends Controller
{
    private function getFirestore()
    {
        return new FirestoreClient([
            'projectId' => env('FIREBASE_PROJECT_ID'),
            'keyFilePath' => storage_path('app/firebase/luwina-381dd-firebase-adminsdk-fbsvc-d4615d8138.json'),
        ]);
    }

    private function fetchFotoData()
    {
        $foto_collection = $this->getFirestore()->collection('Foto_Evident')->documents();
        $foto_doc = [];

        foreach ($foto_collection as $docf) {
            if ($docf->exists()) {
                $foto_doc[] = [
                    'id' => $docf->id(),
                    'foto' => $docf->data()['foto_path'],
                ];
            }
        }

        usort($foto_doc, fn($c, $d) => (int)$c['id'] <=> (int)$d['id']);
        return $foto_doc;
    }

    private function fetchPendingData()
    {
        $pending_collection = $this->getFirestore()->collection('Pending')->documents();
        $pending_doc = [];

        foreach ($pending_collection as $docpe) {
            if ($docpe->exists()) {
                $pending_doc[] = [
                    'id' => $docpe->id(),
                    'keterangan' => $docpe->data()['pending_keterangan'],
                    'waktu' => $docpe->data()['pending_waktu'],
                ];
            }
        }

        usort($pending_doc, fn($e, $f) => (int)$e['id'] <=> (int)$f['id']);
        return $pending_doc;
    }

    private function fetchQEData()
    {
        $qe_collection = $this->getFirestore()->collection('QE')->documents();
        $qe_doc = [];

        foreach ($qe_collection as $docq) {
            if ($docq->exists()) {
                $qe_doc[] = [
                    'id' => $docq->id(),
                    'qe' => $docq->data()['type'],
                ];
            }
        }

        usort($qe_doc, fn($g, $h) => (int)$g['id'] <=> (int)$h['id']);
        return $qe_doc;
    }

    private function fetchAllProjects($start = null, $end = null)
    {
        $project_collection = $this->getFirestore()->collection('All_Project_TA')->documents();
        $project_doc = [];
        $tot = 0;

        foreach ($project_collection as $docp) {
            if ($docp->exists()) {
                $data = $docp->data();

                // Ambil timestamp mentah
                $rawUpload = $data['ta_project_waktu_upload'] ?? null;
                $rawPengerjaan = $data['ta_project_waktu_pengerjaan'] ?? null;
                $rawSelesai = $data['ta_project_waktu_selesai'] ?? null;

                // Konversi ke string tanggal untuk tampilan
                $tglUpload = $this->formatDate($rawUpload);
                $tglPengerjaan = $this->formatDate($rawPengerjaan);
                $tglSelesai = $this->formatDate($rawSelesai);

                $totalValue = (float) ($data['ta_project_total'] ?? 0);

                // 🚨 FILTER disini (gunakan rawUpload langsung)
                if ($start && $end && $rawUpload) {
                    if (method_exists($rawUpload, 'get')) {
                        $uploadDate = Carbon::instance($rawUpload->get());
                    } else {
                        $uploadDate = Carbon::parse($rawUpload);
                    }

                    $startDate = Carbon::parse($start)->startOfDay();
                    $endDate   = Carbon::parse($end)->endOfDay();

                    if (!($uploadDate->between($startDate, $endDate))) {
                        continue; // skip kalau di luar range
                    }
                }

                $projectQERef = $data['ta_project_qe_id'];
                $qeData = $this->getReferenceData($projectQERef);

                $project_doc[] = [
                    'id' => $docp->id(),
                    'nama_project' => $data['ta_project_pekerjaan'],
                    'deskripsi_project' => $data['ta_project_deskripsi'],
                    'qe' => $qeData ? $qeData['type'] : null,
                    'tgl_upload' => $tglUpload,
                    'tgl_pengerjaan' => $tglPengerjaan,
                    'tgl_selesai' => $tglSelesai,
                    'status' => $data['ta_project_status'],
                    'total' => $totalValue,
                    'total_formatted' => number_format($totalValue, 0, ',', '.'),
                ];

                $tot += $totalValue;
            }
        }

        return [$project_doc, number_format($tot, 0, ',', '.')];
    }

    private function fetchProjectTaData()
    {
        return Cache::remember('project_ta_doc', 3600, function () {
            $project_ta_collection = $this->getFirestore()->collection('Data_Project_TA')->documents();

            $project_ta_doc = [];
            $uraianOptions = [];
            foreach ($project_ta_collection as $docd) {
                if ($docd->exists()) {
                    $project_ta_doc[] = [
                        'id' => $docd->id(),
                        'designator' => $docd->data()['ta_designator'],
                        'uraian' => $docd->data()['ta_uraian_pekerjaan'],
                        'satuan' => $docd->data()['ta_satuan'],
                        'harga_material' => $docd->data()['ta_harga_material'],
                        'harga_jasa' => $docd->data()['ta_harga_jasa'],
                    ];
                    $uraianOptions[] = $docd->data()['ta_uraian_pekerjaan'];
                }
            }

            $uraianOptions = array_values(array_unique($uraianOptions));
            sort($uraianOptions);
            usort($project_ta_doc, fn($c, $d) => (int)$c['id'] <=> (int)$d['id']);

            return [$project_ta_doc, $uraianOptions];
        });
    }

    private function getReferenceData($ref)
    {
        if ($ref && method_exists($ref, 'snapshot')) {
            $doc = $ref->snapshot();
            return $doc->exists() ? $doc->data() : null;
        }
        return null;
    }

    public function index(Request $request)
    {
        // Fetch data using separate functions
        $foto_doc = $this->fetchFotoData();
        $pending_doc = $this->fetchPendingData();
        $qe_doc = $this->fetchQEData();

        // ambil query param dari URL (?start=...&end=...)
        $start = $request->query('start');
        $end   = $request->query('end');

        list($project_doc, $grandTotal) = $this->fetchAllProjects($start, $end);

        // Prepare data for charts
        $totalProject = count($project_doc);
        $totalRevenue = array_sum(array_column($project_doc, 'total'));

        // Data per month for the current year
        $dataPerBulan = array_fill(1, 12, 0);
        foreach ($project_doc as $project) {
            if (!empty($project['tgl_upload'])) {
                $bulan = (int) date('n', strtotime($project['tgl_upload']));
                $tahun = (int) date('Y', strtotime($project['tgl_upload']));
                if ($tahun == date('Y')) {
                    $dataPerBulan[$bulan]++;
                }
            }
        }
        $chartTotalProjectData = array_values($dataPerBulan);

        // Chart data for QE
        $chartQEData = [];
        foreach ($project_doc as $project) {
            if (!empty($project['tgl_upload'])) {
                $tahun = (int) date('Y', strtotime($project['tgl_upload']));
                if ($tahun == date('Y')) {
                    $qe = $project['qe'] ?? 'UNKNOWN';
                    if (!isset($chartQEData[$qe])) {
                        $chartQEData[$qe] = 0;
                    }
                    $chartQEData[$qe]++;
                }
            }
        }

        // Chart data for project status
        $chartPieData = [];
        foreach ($project_doc as $project) {
            if (!empty($project['tgl_upload'])) {
                $tahun = (int) date('Y', strtotime($project['tgl_upload']));
                if ($tahun == date('Y')) {
                    $status = $project['status'] ?? 'UNKNOWN';
                    if (!isset($chartPieData[$status])) {
                        $chartPieData[$status] = 0;
                    }
                    $chartPieData[$status]++;
                }
            }
        }

        return view('telkom_akses.allproject.allproject_telkomakses', compact(
            'project_doc',
            'grandTotal',
            'chartTotalProjectData',
            'chartQEData',
            'chartPieData',
            'qe_doc'
        ));
    }

    private function hitungTotal($detailDocs)
    {
        $totalMaterial = 0;
        $totalJasa     = 0;

        foreach ($detailDocs as $d) {
            if (!$d->exists()) continue;

            $row = $d->data();
            $designatorData = $row['ta_detail_ta_id']->snapshot()->data();
            $volume         = $row['ta_detail_volume'] ?? 0;

            $totalMaterial += ($designatorData['ta_harga_material'] ?? 0) * $volume;
            $totalJasa     += ($designatorData['ta_harga_jasa'] ?? 0) * $volume;
        }

        $total = $totalMaterial + $totalJasa;
        $ppn   = $total * 0.11;
        $grand = $total + $ppn;

        return [
            'material' => $totalMaterial,
            'jasa'     => $totalJasa,
            'total'    => $total,
            'ppn'      => $ppn,
            'grand'    => $grand,
        ];
    }

    private function formatDate($timestamp)
    {
        if (!$timestamp) {
            return "-";
        };

        if (is_object($timestamp) && method_exists($timestamp, 'get')) {
            $timestamp = $timestamp->get()->format('Y-m-d');
        } else {
            $timestamp = Carbon::parse($timestamp)->format('Y-m-d');
        }

        return $timestamp;
    }

    public function downloadPDF(Request $request)
    {
        $start = $request->query('start');
        $end   = $request->query('end');

        list($project_doc, $grandTotal) = $this->fetchAllProjects($start, $end);

        // 🔧 Gunakan format parser yang konsisten
        if ($start && $end) {
            $startFormatted = Carbon::createFromFormat('Y-m-d', $start)->translatedFormat('j M Y');
            $endFormatted   = Carbon::createFromFormat('Y-m-d', $end)->translatedFormat('j M Y');
            $title = "All Project TA ({$startFormatted} - {$endFormatted})";
        } else {
            $title = "All Project TA - " . now()->translatedFormat('j M Y');
        }

        $pdf = Pdf::loadView('telkom_akses.allproject.download_telkomakses', [
            'project_doc' => $project_doc,
            'grandTotal' => $grandTotal,
            'title' => $title,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('All_Project_' . now()->format('Y-m-d') . '.pdf');
    }

    public function detailProcess($id)
    {
        $firestore = $this->getFirestore();
        $docRef = $firestore->collection('All_Project_TA')->document($id);
        $doc = $docRef->snapshot();

        if (!$doc->exists()) {
            return redirect()->route('telkomakses.allproject')->with('error', 'Data project tidak ditemukan');
        }

        $data = $doc->data();

        // --- Ambil data project utama pakai getReferenceData() ---
        $fotoData = $data['ta_project_foto'] ?? [];
        $pendingData = $this->getReferenceData($data['ta_project_pending_id'] ?? null);
        $qeData      = $this->getReferenceData($data['ta_project_qe_id'] ?? null);

        $tglUpload     = $this->formatDate($data['ta_project_waktu_upload'] ?? null);
        $tglPengerjaan = $this->formatDate($data['ta_project_waktu_pengerjaan'] ?? null);
        $tglSelesai    = $this->formatDate($data['ta_project_waktu_selesai'] ?? null);

        // --- Ambil detail ---
        $detailDocs = $firestore->collection('Detail_Project_TA')
            ->where('ta_detail_all_id', '=', $docRef)
            ->documents();

        $detail = [];
        $totalMaterial = 0;
        $totalJasa = 0;

        foreach ($detailDocs as $d) {
            if (!$d->exists()) continue;

            $row = $d->data();

            // Fetch data from Data_Project_TA
            $designatorRef = $row['ta_detail_ta_id'];
            $designatorData = $this->getReferenceData($designatorRef);

            $hargaMaterial = $designatorData['ta_harga_material'] ?? 0;
            $hargaJasa = $designatorData['ta_harga_jasa'] ?? 0;
            $volume = $row['ta_detail_volume'] ?? 0;

            $totalM = $hargaMaterial * $volume;
            $totalJ = $hargaJasa * $volume;

            $totalMaterial += $totalM;
            $totalJasa += $totalJ;

            $detail[] = (object)[
                'id' => $d->id(),
                'designator' => $designatorData['ta_designator'] ?? '',
                'uraian' => $designatorData['ta_uraian_pekerjaan'] ?? '',
                'satuan' => $designatorData['ta_satuan'] ?? '',
                'harga_material' => $hargaMaterial,
                'harga_jasa' => $hargaJasa,
                'volume' => $volume,
                'total_material' => $totalM,
                'total_jasa' => $totalJ,
            ];
        }

        $total = $totalMaterial + $totalJasa;
        $ppn = $total * 0.11;
        $grand = $total + $ppn;

        // Update project total in Firestore
        $docRef->update([
            ['path' => 'ta_project_total', 'value' => $grand],
        ]);

        $totals = [
            'material' => $totalMaterial,
            'jasa' => $totalJasa,
            'total' => $total,
            'ppn' => $ppn,
            'grand' => $grand,
        ];

        return view('telkom_akses.allproject.process.detail_process', [
            'process' => [
                'id'               => $id,
                'nama_project'     => $data['ta_project_pekerjaan'],
                'deskripsi_project' => $data['ta_project_deskripsi'],
                'qe'               => $qeData['type'] ?? null,
                'foto'             => $fotoData,
                'pending'          => $pendingData,
                'tgl_upload'       => $tglUpload,
                'tgl_pengerjaan'   => $tglPengerjaan,
                'tgl_selesai'      => $tglSelesai,
                'status'           => $data['ta_project_status'],
                'total'            => $data['ta_project_total'],
                'detail'           => $detail,
            ],
            'totals' => $totals,
        ]);
    }

    public function accProcess($id)
    {
        $firestore = $this->getFirestore();
        $docRef = $firestore->collection('All_Project_TA')->document($id);

        $doc = $docRef->snapshot();
        if (!$doc->exists()) {
            return redirect()->route('telkomakses.allproject')->with('error', 'Project tidak ditemukan');
        }

        // Update status jadi ACC
        $docRef->update([
            ['path' => 'ta_project_status', 'value' => 'ACC'],
        ]);

        return redirect()->route('telkomakses.allproject_process.acc')->with('success', 'Project berhasil di-ACC');
    }

    public function rejectProcess($id)
    {
        $firestore = $this->getFirestore();
        $docRef = $firestore->collection('All_Project_TA')->document($id);

        $doc = $docRef->snapshot();
        if (!$doc->exists()) {
            return redirect()->route('telkomakses.process')->with('error', 'Project tidak ditemukan');
        }

        // Update status jadi REJECT
        $docRef->update([
            ['path' => 'ta_project_status', 'value' => 'REJECT'],
        ]);

        return redirect()->route('telkomakses.allproject_process.reject')->with('success', 'Project berhasil di-Reject');
    }

    public function detailAcc($id)
    {
        $firestore = $this->getFirestore();
        $docRef = $firestore->collection('All_Project_TA')->document($id);
        $doc = $docRef->snapshot();

        if (!$doc->exists()) {
            return redirect()->route('telkomakses.allproject')->with('error', 'Data project tidak ditemukan');
        }

        $data = $doc->data();

        // --- Foto evident (ambil semua dokumen by project_id)
        $fotoDocs = $firestore->collection('Foto_Evident')
            ->where('project_id', '=', $id)
            ->documents();

        $fotoData = [
            'sebelum' => [],
            'proses' => [],
            'sesudah' => [],
        ];

        foreach ($fotoDocs as $docFoto) {
            if ($docFoto->exists()) {
                $dataFoto = $docFoto->data()['foto_path'] ?? [];

                if (is_object($dataFoto)) {
                    $dataFoto = json_decode(json_encode($dataFoto), true);
                }

                foreach (['sebelum', 'proses', 'sesudah'] as $step) {
                    if (!empty($dataFoto[$step])) {
                        $fotoData[$step] = array_merge($fotoData[$step], $dataFoto[$step]);
                    }
                }
            }
            // dd($docFoto->data());
        }

        $acc['foto'] = $fotoData;

        // --- Pending (ambil semua dokumen by project_id)
        $pendingDocs = $firestore->collection('Pending')
            ->where('project_id', '=', $id)->documents();
        $pendingData = [];
        foreach ($pendingDocs as $pd) {
            if (!$pd->exists()) continue;
            $dataPd = $pd->data();
            $kets = $dataPd['pending_keterangan'] ?? null;
            $waktus = $dataPd['pending_waktu'] ?? null;

            if (is_array($kets)) {
                foreach ($kets as $i => $ket) {
                    $pendingData[] = [
                        'tgl_pending' => is_array($waktus) ? ($waktus[$i] ?? $waktus[0] ?? '-') : ($waktus ?? '-'),
                        'keterangan'  => $ket ?? '-',
                    ];
                }
            } else {
                $pendingData[] = [
                    'tgl_pending' => $waktus ?? '-',
                    'keterangan'  => $kets ?? '-',
                ];
            }
        }

        // Fetch detail from Detail_Project_TA
        $detailDocs = $firestore->collection('Detail_Project_TA')
            ->where('ta_detail_all_id', '=', $docRef) // filter by project reference
            ->documents();

        $detail = [];
        $totalMaterial = 0;
        $totalJasa = 0;

        foreach ($detailDocs as $d) {
            if (!$d->exists()) continue;

            $row = $d->data();

            // Fetch data from Data_Project_TA
            $designatorRef = $row['ta_detail_ta_id'];
            $designatorData = $this->getReferenceData($designatorRef);

            $hargaMaterial = $designatorData['ta_harga_material'] ?? 0;
            $hargaJasa = $designatorData['ta_harga_jasa'] ?? 0;
            $volume = $row['ta_detail_volume'] ?? 0;

            $totalM = $hargaMaterial * $volume;
            $totalJ = $hargaJasa * $volume;

            $totalMaterial += $totalM;
            $totalJasa += $totalJ;

            $detail[] = (object)[
                'id' => $d->id(),
                'designator' => $designatorData['ta_designator'] ?? '',
                'uraian' => $designatorData['ta_uraian_pekerjaan'] ?? '',
                'satuan' => $designatorData['ta_satuan'] ?? '',
                'harga_material' => $hargaMaterial,
                'harga_jasa' => $hargaJasa,
                'volume' => $volume,
                'total_material' => $totalM,
                'total_jasa' => $totalJ,
            ];
        }

        $total = $totalMaterial + $totalJasa;
        $ppn = $total * 0.11;
        $grand = $total + $ppn;

        // Update project total in Firestore
        $docRef->update([
            ['path' => 'ta_project_total', 'value' => $grand],
        ]);

        $totals = [
            'material' => $totalMaterial,
            'jasa' => $totalJasa,
            'total' => $total,
            'ppn' => $ppn,
            'grand' => $grand,
        ];

        return view('telkom_akses.allproject.acc.detail_acc', [
            'acc' => [
                'id'              => $id,
                'nama_project'    => $data['ta_project_pekerjaan'],
                'deskripsi_project' => $data['ta_project_deskripsi'],
                'qe'              => $data['ta_project_qe_id'] ?? null,
                'foto'            => $fotoData,
                'foto_project' => $data['ta_project_foto'] ?? [],
                'pending'         => $pendingData,
                'tgl_upload'      => $this->formatDate($data['ta_project_waktu_upload'] ?? null),
                'tgl_pengerjaan'  => $this->formatDate($data['ta_project_waktu_pengerjaan'] ?? null),
                'tgl_selesai'     => $this->formatDate($data['ta_project_waktu_selesai'] ?? null),
                'status'          => $data['ta_project_status'],
                'total'           => $data['ta_project_total'],
                'detail'          => $detail,
            ],
            'totals' => $totals,
        ]);
    }

    public function detailReject($id)
    {
        $firestore = $this->getFirestore();
        $docRef = $firestore->collection('All_Project_TA')->document($id);
        $doc = $docRef->snapshot();

        if (!$doc->exists()) {
            return redirect()->route('telkomakses.allproject')->with('error', 'Data project tidak ditemukan');
        }

        $data = $doc->data();
        $fotoData = $data['ta_project_foto'] ?? [];
        $pendingData = $this->getReferenceData($data['ta_project_pending_id'] ?? null);
        $qeData = $this->getReferenceData($data['ta_project_qe_id'] ?? null);

        $tglUpload = $this->formatDate($data['ta_project_waktu_upload'] ?? null);
        $tglPengerjaan = $this->formatDate($data['ta_project_waktu_pengerjaan'] ?? null);
        $tglSelesai = $this->formatDate($data['ta_project_waktu_selesai'] ?? null);

        $detailDocs = $firestore->collection('Detail_Project_TA')
            ->where('ta_detail_all_id', '=', $docRef) // filter by project reference
            ->documents();

        $detail = [];
        $totalMaterial = 0;
        $totalJasa = 0;

        foreach ($detailDocs as $d) {
            if (!$d->exists()) continue;

            $row = $d->data();

            // Fetch data from Data_Project_TA
            $designatorRef = $row['ta_detail_ta_id'];
            $designatorData = $this->getReferenceData($designatorRef);

            $hargaMaterial = $designatorData['ta_harga_material'] ?? 0;
            $hargaJasa = $designatorData['ta_harga_jasa'] ?? 0;
            $volume = $row['ta_detail_volume'] ?? 0;

            $totalM = $hargaMaterial * $volume;
            $totalJ = $hargaJasa * $volume;

            $totalMaterial += $totalM;
            $totalJasa += $totalJ;

            $detail[] = (object)[
                'id' => $d->id(),
                'designator' => $designatorData['ta_designator'] ?? '',
                'uraian' => $designatorData['ta_uraian_pekerjaan'] ?? '',
                'satuan' => $designatorData['ta_satuan'] ?? '',
                'harga_material' => $hargaMaterial,
                'harga_jasa' => $hargaJasa,
                'volume' => $volume,
                'total_material' => $totalM,
                'total_jasa' => $totalJ,
            ];
        }

        $total = $totalMaterial + $totalJasa;
        $ppn = $total * 0.11;
        $grand = $total + $ppn;

        // Update project total in Firestore
        $docRef->update([
            ['path' => 'ta_project_total', 'value' => $grand],
        ]);

        $totals = [
            'material' => $totalMaterial,
            'jasa' => $totalJasa,
            'total' => $total,
            'ppn' => $ppn,
            'grand' => $grand,
        ];

        return view('telkom_akses.allproject.reject.detail_reject', [
            'reject' => [
                'id' => $id,
                'nama_project' => $data['ta_project_pekerjaan'],
                'deskripsi_project' => $data['ta_project_deskripsi'],
                'qe' => $qeData['type'] ?? null,
                'foto' => $fotoData,
                'pending' => $pendingData,
                'tgl_upload' => $tglUpload,
                'tgl_pengerjaan' => $tglPengerjaan,
                'tgl_selesai' => $tglSelesai,
                'status' => $data['ta_project_status'],
                'total' => $data['ta_project_total'],
                'detail' => $detail,
            ],
            'totals' => $totals,
        ]);
    }
}
