<?php

namespace App\Http\Controllers\super_admin;

use App\Http\Controllers\Controller;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Core\Timestamp as FireTimestamp;
use Carbon\Carbon;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Cloudinary\Cloudinary;
// use Google\Cloud\Firestore\DocumentSnapshot;
// use Illuminate\Support\Str;
use App\Models\PertMasterTask;
use App\Models\PertInput;
use App\Models\PertResult;
use App\Models\PertPath;
use App\Models\PertMasterDependency;

class AccController extends Controller
{
    private function getFirestore()
    {
        return new FirestoreClient([
            'projectId' => env('FIREBASE_PROJECT_ID'),
            'keyFilePath' => storage_path('app/firebase/luwina-381dd-firebase-adminsdk-fbsvc-d4615d8138.json'),
        ]);
    }

    public function index(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');

        $foto_doc = $this->fetchFotoData();
        $pending_doc = $this->fetchPendingData();
        $qe_doc = $this->fetchQEData();
        list($acc_doc, $grandTotal) = $this->fetchAccProjects($start, $end);

        return view('super_admin.acc.acc_superadmin', compact('acc_doc', 'grandTotal'));
    }

    private function fetchFotoData()
    {
        $foto_collection = $this->getFirestore()->collection('Foto_Evident')->documents();
        $foto_doc = [];

        foreach ($foto_collection as $docf) {
            if ($docf->exists()) {
                $paths = $docf->data()['foto_path'] ?? [];

                if (!is_array($paths)) {
                    $paths = [$paths];
                }

                foreach ($paths as $path) {
                    $foto_doc[] = [
                        'id' => $docf->id(),
                        'foto' => $path,
                    ];
                }
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

    private function fetchAccProjects($start = null, $end = null)
    {
        $acc_collection = $this->getFirestore()->collection('All_Project_TA')->documents();
        $acc_doc = [];
        $tot = 0;

        foreach ($acc_collection as $doca) {
            if ($doca->exists()) {
                $data = $doca->data();

                if (($data['ta_project_status'] ?? '') !== 'ACC') {
                    continue;
                }

                // Ambil tanggal upload
                $tglUpload = $this->formatDate($data['ta_project_waktu_upload'] ?? null);

                // Jika user memilih rentang tanggal → filter
                if ($start && $end) {
                    try {
                        $uploadDate = Carbon::parse($tglUpload);
                        $startDate = Carbon::parse($start);
                        $endDate = Carbon::parse($end);

                        // Jika tglUpload di luar rentang, skip
                        if ($uploadDate->lt($startDate) || $uploadDate->gt($endDate)) {
                            continue;
                        }
                    } catch (\Exception $e) {
                        continue; // kalau parsing gagal, skip aja
                    }
                }

                // $accFotoRef = $data['ta_project_foto_id'];
                // $accPendingRef = $data['ta_project_pending_id'];
                $accQERef = $data['ta_project_qe_id'];

                // $fotoData = $this->getReferenceData($accFotoRef);
                // $pendingData = $this->getReferenceData($accPendingRef);
                $qeData = $this->getReferenceData($accQERef);

                $tglUpload = $this->formatDate($data['ta_project_waktu_upload'] ?? null);
                $tglPengerjaan = $this->formatDate($data['ta_project_waktu_pengerjaan'] ?? null);
                $tglSelesai = $this->formatDate($data['ta_project_waktu_selesai'] ?? null);
                $totalValue = (float) ($data['ta_project_total'] ?? 0);

                $acc_doc[] = [
                    'id' => $doca->id(),
                    'nama_project' => $data['ta_project_pekerjaan'],
                    'deskripsi_project' => $data['ta_project_deskripsi'],
                    'qe' => $qeData ? $qeData['type'] : null,
                    'tgl_upload' => $tglUpload,
                    'tgl_pengerjaan' => $tglPengerjaan,
                    'tgl_selesai' => $tglSelesai,
                    'status' => $data['ta_project_status'],
                    'total' => number_format($totalValue, 0, ',', '.'),
                ];

                $tot += $totalValue;
            }
        }

        return [$acc_doc, number_format($tot, 0, ',', '.')];
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

    public function detail($id)
    {
        $firestore = $this->getFirestore();
        $docRef = $firestore->collection('All_Project_TA')->document($id);
        $doc = $docRef->snapshot();

        // dd($id);

        if (!$doc->exists()) {
            return redirect()->route('superadmin.acc')->with('error', 'Data project tidak ditemukan');
        }

        $data = $doc->data();
        $qeRef = $data['ta_project_qe_id'] ?? null;

        $qe = '';

        if ($qeRef) {

            $qeSnapshot = $qeRef->snapshot();

            if ($qeSnapshot->exists()) {

                $qeData = $qeSnapshot->data();

                $qe = strtolower(trim($qeData['type'] ?? ''));
            }
        }

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

        // PENAMBAHAN MODUL PERT
        $pertTasks = [];
        $pertResult = [];
        $criticalPath = [];

        // hanya jalan kalau project punya waktu pengerjaan
        if (!empty($data['ta_project_waktu_pengerjaan'])) {

            // cek apakah sudah ada input PERT
            $hasInput = PertInput::where('project_id', $id)->exists();

            // =========================
            // 🔹 KONDISI SUDAH INPUT
            // =========================
            if ($hasInput) {

                $pertCollection = PertInput::with('masterTask')
                    ->where('project_id', $id)
                    ->get();

                // ambil critical path
                $criticalPath = $this->getCriticalPath($id);

                foreach ($pertCollection as $item) {

                    $kode = $item->masterTask->kode ?? null;

                    // untuk tabel utama
                    $pertTasks[] = [
                        'id_master' => $item->id_master,
                        'kode' => $kode,
                        'nama_pekerjaan' => $item->masterTask->nama_pekerjaan ?? '-',
                        'realistis' => $item->masterTask->realistis ?? 0,
                        'optimis' => $item->optimis ?? 0,
                        'pesimis' => $item->pesimis ?? 0,
                        'time_expected' => $item->time_expected ?? 0,
                    ];

                    // untuk hasil (critical path)
                    if (in_array($kode, $criticalPath)) {
                        $pertResult[] = [
                            'kode' => $kode,
                            'nama_pekerjaan' => $item->masterTask->nama_pekerjaan ?? '-',
                            'realistis' => $item->masterTask->realistis ?? 0,
                            'optimis' => $item->optimis ?? 0,
                            'pesimis' => $item->pesimis ?? 0,
                            'time_expected' => $item->time_expected ?? 0,
                        ];
                    }
                }

                // kalau belum ada hasil critical path → fallback tampil semua
                if (empty($pertResult)) {
                    $pertResult = $pertTasks;
                }
            }
            // =========================
            // 🔹 KONDISI BELUM INPUT
            // =========================
            else {

                $qeRef = $data['ta_project_qe_id'] ?? null;

                $qe = '';

                if ($qeRef) {

                    $qeSnapshot = $qeRef->snapshot();

                    if ($qeSnapshot->exists()) {

                        $qeData = $qeSnapshot->data();

                        $qe = strtolower(trim($qeData['type'] ?? ''));
                    }
                }

                if (str_contains($qe, 'material')) {

                    $pertCollection = PertMasterTask::whereBetween('id_master', [1, 15])->get();
                } elseif (str_contains($qe, 'preventive')) {

                    $pertCollection = PertMasterTask::whereBetween('id_master', [16, 29])->get();
                } elseif (str_contains($qe, 'relokasi')) {

                    $pertCollection = PertMasterTask::whereBetween('id_master', [30, 44])->get();
                } elseif (str_contains($qe, 'recovery')) {

                    $pertCollection = PertMasterTask::whereBetween('id_master', [45, 60])->get();
                } else {

                    $pertCollection = collect();
                }

                foreach ($pertCollection as $task) {

                    $pertTasks[] = [
                        'id_master' => $task->id_master,
                        'kode' => $task->kode,
                        'nama_pekerjaan' => $task->nama_pekerjaan,
                        'realistis' => $task->realistis,
                        'optimis' => null,
                        'pesimis' => null,
                        'time_expected' => null,
                    ];
                }
            }
        }

        // tentukan range berdasarkan QE
        $range = [];

        if (str_contains($qe, 'material')) {
            $range = [1, 15];
        } elseif (str_contains($qe, 'preventive')) {
            $range = [16, 29];
        } elseif (str_contains($qe, 'relokasi')) {
            $range = [30, 44];
        } elseif (str_contains($qe, 'recovery')) {
            $range = [45, 60];
        }

        // ambil model sesuai range
        $allModels = PertResult::with([
            'paths' => function ($q) use ($range) {
                $q->whereBetween('id_master', $range)
                    ->orderBy('urutan');
            },
            'paths.masterTask'
        ])
            ->where('project_id', $id)
            ->get();

        // filter model kosong
        $allModels = $allModels->filter(function ($model) {
            return $model->paths->count() > 0;
        });

        // reset index collection
        $allModels = $allModels->values();

        return view('super_admin.acc.detail_acc', [
            'acc' => [
                'id'              => $id,
                'nama_project'    => $data['ta_project_pekerjaan'],
                'deskripsi_project' => $data['ta_project_deskripsi'],
                'qe'              => $data['ta_project_qe_id'] ?? null,
                'foto'            => $fotoData,
                'foto_project'    => $data['ta_project_foto'] ?? [],
                'pending'         => $pendingData,
                'tgl_upload'      => $this->formatDate($data['ta_project_waktu_upload'] ?? null),
                'tgl_pengerjaan'  => $this->formatDate($data['ta_project_waktu_pengerjaan'] ?? null),
                'tgl_selesai'     => $this->formatDate($data['ta_project_waktu_selesai'] ?? null),
                'status'          => $data['ta_project_status'],
                'total'           => $data['ta_project_total'],

                // detail
                'detail'          => $detail,

                // PERT
                'pertTasks'       => $pertTasks,
                'pertResult'      => $pertResult,
                'criticalPath'    => $criticalPath,
                'allModels'       => $allModels,
            ],

            'totals' => $totals,
        ]);
    }

    public function edit($id)
    {
        $firestore = $this->getFirestore();
        $docRef = $firestore->collection('All_Project_TA')->document($id);
        $doc = $docRef->snapshot();

        if (!$doc->exists()) {
            return redirect()->route('superadmin.acc')->with('error', 'Data project tidak ditemukan');
        }

        $data = $doc->data();

        // --- Ambil detail project ---
        $detailDocs = $firestore->collection('Detail_Project_TA')
            ->where('ta_detail_all_id', '=', $docRef)
            ->documents();

        $detail = [];
        foreach ($detailDocs as $d) {
            if (!$d->exists()) continue;

            $row = $d->data();
            $designatorData = $row['ta_detail_ta_id']->snapshot()->data();

            $hargaMaterial = $designatorData['ta_harga_material'] ?? 0;
            $hargaJasa     = $designatorData['ta_harga_jasa'] ?? 0;
            $volume        = $row['ta_detail_volume'] ?? 0;

            $detail[] = (object)[
                'id'             => $d->id(),
                'designator'     => $designatorData['ta_designator'] ?? '',
                'uraian'         => $designatorData['ta_uraian_pekerjaan'] ?? '',
                'satuan'         => $designatorData['ta_satuan'] ?? '',
                'harga_material' => $hargaMaterial,
                'harga_jasa'     => $hargaJasa,
                'volume'         => $volume,
                'total_material' => $hargaMaterial * $volume,
                'total_jasa'     => $hargaJasa * $volume,
            ];
        }

        $totals = $this->hitungTotal($detailDocs);

        // --- Ambil data referensi designator pakai helper ---
        [$project_ta_doc, $uraianOptions] = $this->fetchProjectTaData();

        return view('super_admin.acc.edit_acc', [
            'acc' => [
                'id'               => $id,
                'nama_project'     => $data['ta_project_pekerjaan'],
                'deskripsi_project' => $data['ta_project_deskripsi'],
                'detail'           => $detail,
            ],
            'totals'         => $totals,
            'project_ta_doc' => $project_ta_doc,
        ]);
    }

    public function update(Request $request, $id)
    {
        $firestore = $this->getFirestore();
        $docRef = $firestore->collection('All_Project_TA')->document($id);
        $doc = $docRef->snapshot();

        if (!$doc->exists()) {
            return redirect()->route('superadmin.acc')->with('error', 'Project tidak ditemukan');
        }

        // Update project name
        $docRef->update([
            ['path' => 'ta_project_pekerjaan', 'value' => $request->nama_project],
        ]);

        // Existing details
        $existingDetails = $firestore->collection('Detail_Project_TA')
            ->where('ta_detail_all_id', '=', $docRef)
            ->documents();

        // Map for existing details
        $existingMap = [];
        foreach ($existingDetails as $detail) {
            $existingMap[$detail->id()] = $detail; // Using document ID as the key
        }

        // Data from the form
        $designators = $request->input('designator', []);
        $volumes = $request->input('volume', []);
        $detailIds = $request->input('detail_id', []); // Associated detail IDs

        foreach ($designators as $index => $dsg) {
            $volume = (int)($volumes[$index] ?? 0);
            $detailId = $detailIds[$index] ?? null;

            // Fetch the designator reference based on user input
            $designatorDoc = $firestore->collection('Data_Project_TA')->where('ta_designator', '=', $dsg)->documents()->rows();

            if ($dsg && $volume > 0) {
                if ($detailId && isset($existingMap[$detailId])) {
                    // Update existing detail
                    $detailRef = $existingMap[$detailId];

                    // Update volume
                    $detailRef->reference()->update([
                        ['path' => 'ta_detail_volume', 'value' => $volume],
                    ]);

                    // Update designator if it has changed
                    if (count($designatorDoc) > 0) {
                        $detailRef->reference()->update([
                            ['path' => 'ta_detail_ta_id', 'value' => $designatorDoc[0]->reference()], // Save as reference
                        ]);
                    }
                } else {
                    // Add new detail if not exists
                    if (count($designatorDoc) > 0) {
                        $firestore->collection('Detail_Project_TA')->add([
                            'ta_detail_all_id' => $docRef,
                            'ta_detail_ta_id' => $designatorDoc[0]->reference(), // Save as reference
                            'ta_detail_volume' => $volume,
                        ]);
                    }
                }
            }
        }

        // Update total after changes
        $detailDocs = $firestore->collection('Detail_Project_TA')
            ->where('ta_detail_all_id', '=', $docRef)
            ->documents();
        $totals = $this->hitungTotal($detailDocs);
        $docRef->update([['path' => 'ta_project_total', 'value' => $totals['grand']]]);

        return redirect()
            ->route('superadmin.acc_detail', $id)
            ->with('success', 'Project berhasil diperbarui');
    }

    public function destroy($id, $detailId)
    {
        $firestore = $this->getFirestore();

        // Referensi ke dokumen Detail_Project_TA yang ingin dihapus
        $detailRef = $firestore->collection('Detail_Project_TA')->document($detailId);
        $detailDoc = $detailRef->snapshot();

        if (!$detailDoc->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Data detail tidak ditemukan.'
            ], 404);
        }

        // Hapus dokumen dari Firestore
        $detailRef->delete();

        // Hitung ulang total project setelah penghapusan
        $docRef = $firestore->collection('All_Project_TA')->document($id);
        $detailDocs = $firestore->collection('Detail_Project_TA')
            ->where('ta_detail_all_id', '=', $docRef)
            ->documents();

        $totals = $this->hitungTotal($detailDocs);

        // Update total di dokumen induk
        $docRef->update([
            ['path' => 'ta_project_total', 'value' => $totals['grand']]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Material berhasil dihapus.'
        ]);
    }

    public function destroyProject($id)
    {
        $firestore = $this->getFirestore();

        // Referensi ke dokumen project utama
        $projectRef = $firestore->collection('All_Project_TA')->document($id);
        $projectSnap = $projectRef->snapshot();

        if (!$projectSnap->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Data project tidak ditemukan.'
            ], 404);
        }

        // Ambil semua detail project yang terhubung
        $detailDocs = $firestore->collection('Detail_Project_TA')
            ->where('ta_detail_all_id', '=', $projectRef)
            ->documents();

        // Hapus semua detail project
        foreach ($detailDocs as $detail) {
            if ($detail->exists()) {
                $firestore->collection('Detail_Project_TA')->document($detail->id())->delete();
            }
        }

        // Hapus data project utama
        $projectRef->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data project dan seluruh material berhasil dihapus.'
        ]);
    }

    public function kerjakan($id)
    {
        $firestore = $this->getFirestore();
        $docRef = $firestore->collection('All_Project_TA')->document($id);

        // cek apakah dokumen ada
        $doc = $docRef->snapshot();
        if (!$doc->exists()) {
            return redirect()->route('superadmin.acc')
                ->with('error', 'Project tidak ditemukan');
        }

        // gunakan Firestore Timestamp agar konsisten dengan data Firestore
        $now = new FireTimestamp(new \DateTime());

        $docRef->update([
            ['path' => 'ta_project_waktu_pengerjaan', 'value' => $now],
        ]);

        return redirect()->route('superadmin.acc_detail', $id)
            ->with('success', 'Tanggal pengerjaan berhasil diset.');
    }

    public function storeFoto(Request $request, $id)
    {
        // dd($request->file('foto_sebelum'), $request->file('foto_sesudah'));

        try {
            $firestore = $this->getFirestore();

            $docRef = $firestore
                ->collection('acc')
                ->document($id);

            if (empty($request->file('foto_sebelum')) && empty($request->file('foto_sesudah'))) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada file yang diupload.'
                ], 400);
            }

            $cloudinary = new Cloudinary(config('cloudinary.url'));

            $uploaded = [
                'sebelum' => [],
                'sesudah' => [],
            ];

            // ================================
            // LOOP FOTO SEBELUM PER DESIGNATOR
            // ================================
            if (!empty($request->file('foto_sebelum'))) {
                foreach ($request->file('foto_sebelum') as $designator => $files) {
                    foreach ($files as $file) {
                        if (!$file->isValid()) continue;

                        $upload = $cloudinary->uploadApi()->upload(
                            $file->getRealPath(),
                            [
                                'folder' => "new_evident_foto/sebelum/$designator"
                            ]
                        );

                        $uploaded['sebelum'][$designator][] = $upload['secure_url'];
                    }
                }
            }

            // ================================
            // LOOP FOTO SESUDAH PER DESIGNATOR
            // ================================
            if (!empty($request->file('foto_sesudah'))) {
                foreach ($request->file('foto_sesudah') as $designator => $files) {
                    foreach ($files as $file) {
                        if (!$file->isValid()) continue;

                        $upload = $cloudinary->uploadApi()->upload(
                            $file->getRealPath(),
                            [
                                'folder' => "new_evident_foto/sesudah/$designator"
                            ]
                        );

                        $uploaded['sesudah'][$designator][] = $upload['secure_url'];
                    }
                }
            }

            // ================================
            // CARI / BUAT DOKUMEN FOTO_EVIDENT
            // ================================
            $fotoDocs = $firestore->collection('Foto_Evident')
                ->where('project_id', '=', $id)
                ->documents();

            $docRef = null;
            foreach ($fotoDocs as $d) {
                if ($d->exists()) {
                    $docRef = $firestore
                        ->collection('Foto_Evident')
                        ->document($d->id());
                    break;
                }
            }

            if (!$docRef) {
                $newDoc = $firestore->collection('Foto_Evident')->add([
                    'project_id' => $id,
                    'foto_path' => [
                        'sebelum' => [],
                        'sesudah' => [],
                    ],
                    'uploaded_at' => new FireTimestamp(new \DateTime()),
                ]);

                $docRef = $firestore
                    ->collection('Foto_Evident')
                    ->document($newDoc->id());
            }

            // ================================
            // AMBIL DATA EXISTING
            // ================================
            $snapshot = $docRef->snapshot()->data() ?? [];

            $existing = $snapshot['foto_path'] ?? [
                'sebelum' => [],
                'sesudah' => [],
            ];

            // ================================
            // MERGE PER DESIGNATOR
            // ================================
            foreach ($uploaded['sebelum'] as $dsg => $urls) {
                $existing['sebelum'][$dsg] =
                    array_merge($existing['sebelum'][$dsg] ?? [], $urls);
            }

            foreach ($uploaded['sesudah'] as $dsg => $urls) {
                $existing['sesudah'][$dsg] =
                    array_merge($existing['sesudah'][$dsg] ?? [], $urls);
            }

            // ================================
            // SIMPAN KE FIRESTORE
            // ================================
            $docRef->set([
                'project_id' => $id,
                'foto_path' => $existing,
                'uploaded_at' => new FireTimestamp(new \DateTime()),
            ], ['merge' => true]);

            // =====================================
            // UPDATE ta_project_waktu_selesai
            // =====================================
            $projectRef = $firestore
                ->collection('All_Project_TA') // pastikan nama collection benar
                ->document($id);

            $projectSnapshot = $projectRef->snapshot();

            if ($projectSnapshot->exists()) {
                $projectData = $projectSnapshot->data();

                // hanya set jika belum ada
                if (empty($projectData['ta_project_waktu_selesai'])) {
                    $projectRef->update([
                        [
                            'path'  => 'ta_project_waktu_selesai',
                            'value' => new FireTimestamp(new \DateTime()),
                        ],
                    ]);
                }
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Foto evident berhasil diupload.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function pending(Request $request, $id)
    {
        $request->validate([
            'tgl_pending'   => 'required|array|min:1',
            'tgl_pending.*' => 'required|date',
            'keterangan'    => 'required|array|min:1',
            'keterangan.*'  => 'required|string|max:255',
        ]);

        $firestore = $this->getFirestore();

        foreach ($request->keterangan as $i => $ket) {
            $tgl = $request->tgl_pending[$i] ?? $request->tgl_pending[0] ?? now()->format('Y-m-d');

            $pendingRef = $firestore->collection('Pending')->add([
                'pending_keterangan' => $ket,
                'pending_waktu'      => $tgl,
                'project_id'         => $id,
                'created_at'         => new FireTimestamp(new \DateTime())
            ]);
        }

        return back()->with('success', 'Project berhasil dipending');
    }

    private function formatDate($timestamp)
    {
        // Jika null, kosong, atau tidak valid
        if (empty($timestamp) || $timestamp === '0000-00-00') {
            return '-';
        }

        try {
            // Firestore Timestamp
            if ($timestamp instanceof \Google\Cloud\Core\Timestamp) {
                return $timestamp->get()->format('Y-m-d');
            }

            // DateTime / Carbon instance
            if ($timestamp instanceof \DateTimeInterface) {
                return Carbon::instance($timestamp)->format('Y-m-d');
            }

            // String valid (cek parseable)
            $date = Carbon::parse($timestamp);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            // Kalau parsing gagal, tampilkan "-"
            return '-';
        }
    }



    // ===================== //
    // PENAMBAHAN MODUL PERT //
    // ===================== //

    // INPUT OPTIMIS & PESIMIS
    public function storePert(Request $request, $projectId)
    {
        // dump($request->all());

        foreach ($request->id_master as $i => $idMaster) {

            // ambil data master task (untuk realistis)
            $task = PertMasterTask::find($idMaster);

            // dump([
            //     'id_master' => $idMaster,
            //     'task' => $task
            // ]);

            if (!$task) continue;

            $optimis   = (float) $request->optimis[$i];
            $pesimis   = (float) $request->pesimis[$i];

            // ambil realistis dari pert_master_task
            $realistis = (float) $task->realistis;

            // rumus PERT
            $timeExpected = ($optimis + (4 * $realistis) + $pesimis) / 6;

            // dump([
            //     'kode' => $task->kode,
            //     'optimis' => $optimis,
            //     'realistis' => $realistis,
            //     'pesimis' => $pesimis,
            //     'time_expected' => $timeExpected
            // ]);

            // simpan ke pert_input
            PertInput::create([
                'id_master'     => $idMaster,
                'project_id'    => $projectId,
                'optimis'       => $optimis,
                'pesimis'       => $pesimis,
                'time_expected' => $timeExpected
            ]);

            // dump($input);
        }

        // Generate model PERT
        $this->generatePertModel($projectId);
        // dd(PertResult::where('project_id', $projectId)->get());

        return back()->with('success', 'Data PERT berhasil disimpan');
    }

    private function generatePertModel($projectId)
    {
        // =========================
        // 1. HAPUS DATA LAMA
        // =========================
        $oldResults = PertResult::where('project_id', $projectId)->get();

        foreach ($oldResults as $old) {
            PertPath::where('id_result', $old->id_result)->delete();
        }

        PertResult::where('project_id', $projectId)->delete();


        // =========================
        // 2. AMBIL INPUT
        // =========================
        $inputs = PertInput::with('masterTask')
            ->where('project_id', $projectId)
            ->get();

        $taskIds = $inputs->pluck('id_master')->toArray();

        $expectedMap = [];
        foreach ($inputs as $input) {
            $expectedMap[$input->masterTask->id_master] = $input->time_expected;
        }

        // dd($expectedMap);

        // =========================
        // 3. GRAPH DEPENDENCY
        // =========================
        $dependencies = PertMasterDependency::whereIn('id_master', $taskIds)
            ->get()
            ->filter(function ($dep) use ($taskIds) {
                return $dep->ketergantungan === null
                    || in_array($dep->ketergantungan, $taskIds);
            });

        $graph = [];
        foreach ($dependencies as $dep) {
            if ($dep->ketergantungan !== null) {
                $graph[$dep->ketergantungan][] = $dep->id_master;
            }
        }


        // =========================
        // 4. CARI START NODE
        // =========================
        $allTasks = $taskIds;

        $startNodes = [];

        foreach ($allTasks as $task) {
            $isStart = true;

            foreach ($dependencies as $dep) {
                if ($dep->id_master == $task && $dep->ketergantungan !== null) {
                    $isStart = false;
                    break;
                }
            }

            if ($isStart) {
                $startNodes[] = $task;
            }
        }


        // =========================
        // 5. BUILD PATH
        // =========================
        $paths = [];

        function buildPath($current, $graph, $path = [])
        {
            $path[] = $current;

            if (!isset($graph[$current])) {
                return [$path];
            }

            $allPaths = [];

            foreach ($graph[$current] as $next) {
                $subPaths = buildPath($next, $graph, $path);

                foreach ($subPaths as $sp) {
                    $allPaths[] = $sp;
                }
            }

            return $allPaths;
        }

        foreach ($startNodes as $start) {
            $paths = array_merge($paths, buildPath($start, $graph));
        }


        // =========================
        // 6. SIMPAN + HITUNG
        // =========================
        $minDurasi = null;
        $criticalId = null;

        // dd($paths);

        foreach ($paths as $path) {

            $durasi = 0;

            foreach ($path as $taskId) {
                $durasi += $expectedMap[$taskId] ?? 0;
            }

            // simpan ke pert_result
            $result = PertResult::create([
                'project_id' => $projectId,
                'tot_durasi' => $durasi,
                'is_critical' => 0
            ]);

            // simpan ke pert_path
            foreach ($path as $i => $taskId) {

                PertPath::create([
                    'id_result' => $result->id_result,
                    'id_master' => $taskId,
                    'urutan'    => $i + 1
                ]);
            }

            // cek max
            // if ($midurasi > $maxDurasi) {
            //     $maxDurasi = $durasi;
            //     $criticalId = $result->id_result;
            // }

            // cek min
            if ($minDurasi === null || $durasi < $minDurasi) {
                $minDurasi = $durasi;
                $criticalId = $result->id_result;
            }
        }


        // =========================
        // 7. SET CRITICAL PATH
        // =========================
        if ($criticalId) {
            PertResult::where('id_result', $criticalId)
                ->update(['is_critical' => 1]);
        }
    }

    // GENERATE MODEL PATH (BEFORE-AFTER)
    // private function generatePertModel($projectId)
    // {
    //     // 1. HAPUS DATA LAMA
    //     PertResult::where('project_id', $projectId)->delete();

    //     // 2. AMBIL INPUT
    //     $inputs = PertInput::with('masterTask')
    //         ->where('project_id', $projectId)
    //         ->get();

    //     // dump($inputs);

    //     $expectedMap = [];

    //     foreach ($inputs as $input) {
    //         $expectedMap[$input->masterTask->kode] = $input->time_expected;
    //     }

    //     // dump($expectedMap);

    //     // 3. AMBIL DEPENDENCY DARI DATABASE
    //     $dependencies = PertMasterDependency::all();

    //     // mapping: parent -> children
    //     $graph = [];

    //     foreach ($dependencies as $dep) {
    //         $parent = $dep->ketergantungan; // sebelumnya
    //         $child  = $dep->id_master;      // sesudahnya

    //         if ($parent !== null) {
    //             $graph[$parent][] = $child;
    //         }
    //     }

    //     // 4. CARI START NODE (yang tidak punya dependency)
    //     $allTasks = PertMasterTask::pluck('id_master')->toArray();
    //     $hasParent = $dependencies->pluck('id_master')->toArray();

    //     $startNodes = [];

    //     foreach ($allTasks as $task) {
    //         $isStart = true;

    //         foreach ($dependencies as $dep) {
    //             if ($dep->id_master == $task && $dep->ketergantungan !== null) {
    //                 $isStart = false;
    //                 break;
    //             }
    //         }

    //         if ($isStart) {
    //             $startNodes[] = $task;
    //         }
    //     }

    //     // 5. TRACING PATH (BUKAN DFS, TAPI RUNUT)
    //     $paths = [];

    //     function buildPath($current, $graph, $path = [])
    //     {
    //         $path[] = $current;

    //         if (!isset($graph[$current])) {
    //             return [$path]; // end node
    //         }

    //         $allPaths = [];

    //         foreach ($graph[$current] as $next) {
    //             $subPaths = buildPath($next, $graph, $path);

    //             foreach ($subPaths as $sp) {
    //                 $allPaths[] = $sp;
    //             }
    //         }

    //         return $allPaths;
    //     }

    //     // generate semua path dari start node
    //     foreach ($startNodes as $start) {
    //         $paths = array_merge($paths, buildPath($start, $graph));
    //     }

    //     // DEBUG HASIL PATH
    //     // dd([
    //     //     'graph' => $graph,
    //     //     'startNodes' => $startNodes,
    //     //     'paths' => $paths
    //     // ]);
    // }

    private function getCriticalPath($projectId)
    {
        // Ambil result yang critical
        $result = PertResult::where('project_id', $projectId)
            ->where('is_critical', true)
            ->first();

        // dump($result);

        if (!$result) {
            return [];
        }

        // Ambil path berdasarkan urutan
        $paths = PertPath::with('masterTask')
            ->where('id_result', $result->id_result)
            ->orderBy('urutan')
            ->get();

        // dump($paths);

        // Ambil kode (lebih aman pakai mapping)
        return $paths->map(function ($item) {
            return optional($item->masterTask)->kode;
        })->filter()->values()->toArray();

        // dd($paths->map(function ($item) {
        //     return optional($item->masterTask)->kode;
        // }));
    }
}
// ===================== //
    // PENAMBAHAN MODUL PERT //
    // ===================== //

    // FETCH PERT MASTER TASK
    // private function fetchPertMasterTask()
    // {
    //     $firestore = $this->getFirestore();

    //     $tasks = $firestore->collection('PERT_Master_Task')->documents();

    //     $data = [];

    //     foreach ($tasks as $task) {
    //         if ($task->exists()) {

    //             $row = $task->data();

    //             $data[] = [
    //                 'id' => $task->id(),
    //                 'kode' => $row['kode'],
    //                 'nama_pekerjaan' => $row['nama_pekerjaan'],
    //                 'realistis' => $row['realistis'],
    //                 'ketergantungan' => $row['ketergantungan']
    //             ];
    //         }
    //     }

    //     usort($data, fn($a, $b) => strcmp($a['kode'], $b['kode']));

    //     return $data;
    // }

    // private function fetchPertInput($projectRef)
    // {
    //     $firestore = $this->getFirestore();

    //     $docs = $firestore
    //         ->collection('PERT_Input')
    //         ->where('project_id', '=', $projectRef)
    //         ->documents();

    //     $pertInput = [];

    //     foreach ($docs as $doc) {

    //         if (!$doc->exists()) continue;

    //         $data = $doc->data();

    //         $kode = $data['kode'] ?? null;
    //         $te = $data['time_expected'] ?? 0;

    //         if ($kode) {
    //             $pertInput[$kode] = $te;
    //         }
    //     }

    //     return $pertInput;
    // }

    // // INPUT OPTIMIS & PESIMIS
    // public function storePert(Request $request, $projectId)
    // {
    //     // dump('CEK INPUTAN FORM');
    //     // dump($request->all());
    //     $firestore = $this->getFirestore();

    //     $projectRef = $firestore
    //         ->collection('All_Project_TA')
    //         ->document($projectId);

    //     foreach ($request->kode as $i => $kode) {

    //         $optimis   = (float) $request->optimis[$i];
    //         $realistis = (float) $request->realistis[$i];
    //         $pesimis   = (float) $request->pesimis[$i];

    //         $timeExpected =
    //             ($optimis + (4 * $realistis) + $pesimis) / 6;

    //         // dump('CEK TIME EXPECTED');
    //         // dump([
    //         //     'kode' => $kode,
    //         //     'optimis' => $optimis,
    //         //     'realistis' => $realistis,
    //         //     'pesimis' => $pesimis,
    //         //     'time_expected' => $timeExpected
    //         // ]);

    //         // dump('CEK DATA YANG AKAN DISIMPAN');
    //         // dump([
    //         //     'project_id' => $projectId,
    //         //     'kode' => $kode,
    //         //     'optimis' => $optimis,
    //         //     'realistis' => $realistis,
    //         //     'pesimis' => $pesimis,
    //         //     'time_expected' => $timeExpected
    //         // ]);

    //         $firestore->collection('PERT_Input')->add([
    //             'project_id' => $projectRef,
    //             'kode' => $kode,
    //             'optimis' => $optimis,
    //             'realistis' => $realistis,
    //             'pesimis' => $pesimis,
    //             'time_expected' => $timeExpected
    //         ]);
    //     }

    //     // dump('CEK DATA COLLECTION PERT_Input');

    //     $checkInput = $firestore
    //         ->collection('PERT_Input')
    //         ->where('project_id', '=', $projectRef)
    //         ->documents();

    //     $dataCheck = [];

    //     foreach ($checkInput as $doc) {
    //         if ($doc->exists()) {
    //             $dataCheck[] = $doc->data();
    //         }
    //     }

    //     // dump($dataCheck);

    //     $this->generatePertModel($projectId);

    //     // return back()->with('success', 'Perhitungan PERT berhasil disimpan');
    //     // return redirect()
    //     //     ->route('superadmin.acc_detail', $projectId)
    //     //     ->with('success', 'Perhitungan PERT berhasil disimpan');
    // }

    // // GENERATE MODEL PATH (BEFORE-AFTER)
    // private function generatePertModel($projectId)
    // {
    //     $firestore = $this->getFirestore();

    //     $projectRef = $firestore
    //         ->collection('All_Project_TA')
    //         ->document($projectId);

    //     dump('Project ID:', $projectId);

    //     // =========================
    //     // 1. HAPUS DATA LAMA
    //     // =========================
    //     // dump('=== Hapus data lama ===');

    //     $old = $firestore
    //         ->collection('PERT_Model_Result')
    //         ->where('project_id', '=', $projectRef)
    //         ->documents();

    //     foreach ($old as $doc) {
    //         if ($doc->exists()) {
    //             // dump('Hapus doc:', $doc->id());

    //             $firestore->collection('PERT_Model_Result')
    //                 ->document($doc->id())
    //                 ->delete();
    //         }
    //     }

    //     // =========================
    //     // 2. AMBIL TIME EXPECTED
    //     // =========================
    //     dump('== Ambil time expected ===');

    //     $inputDocs = $firestore
    //         ->collection('PERT_Input')
    //         ->where('project_id', '=', $projectRef)
    //         ->documents();

    //     $expectedMap = [];

    //     foreach ($inputDocs as $doc) {

    //         if (!$doc->exists()) continue;

    //         $row = $doc->data();

    //         $expectedMap[$row['kode']] = $row['time_expected'];

    //         dump('Data PERT_Input:', [
    //             'kode' => $row['kode'],
    //             'time_expected' => $row['time_expected']
    //         ]);
    //     }

    //     dump('Expected Map:', $expectedMap);

    //     // =========================
    //     // 3. PATH ATAU JALUR
    //     // =========================
    //     dump('=== Path atau jalur ===');

    //     $paths = [
    //         ['A', 'B', 'C', 'D', 'F', 'G', 'I', 'J'],
    //         ['A', 'B', 'C', 'D', 'F', 'H', 'I', 'J'],
    //         ['A', 'B', 'E', 'F', 'G', 'I', 'J'],
    //         ['A', 'B', 'E', 'F', 'H', 'I', 'J'],
    //         ['A', 'B', 'C', 'D', 'F', 'G', 'J'],
    //         ['A', 'B', 'E', 'F', 'G', 'J'],
    //     ];

    //     dump('Daftar Path:', $paths);

    //     // =========================
    //     // 4. HITUNG TOTAL DURASI
    //     // =========================
    //     dump('=== Hitung durasi tiap path ===');

    //     $results = [];

    //     foreach ($paths as $index => $path) {

    //         $total = 0;

    //         dump("Path ke-" . ($index + 1), $path);

    //         foreach ($path as $kode) {

    //             $durasi = $expectedMap[$kode] ?? 0;

    //             dump("Kode: $kode, Durasi:", $durasi);

    //             $total += $durasi;
    //         }

    //         dump('Total durasi path:', $total);

    //         $results[] = [
    //             'jalur' => $path,
    //             'tot_durasi' => $total
    //         ];
    //     }

    //     dump('Semua hasil path:', $results);

    //     // =========================
    //     // 5. CARI CRITICAL PATH
    //     // =========================
    //     dump('=== Cari durasi maksimum (Jalur Kritis) ===');

    //     $maxDurasi = max(array_column($results, 'tot_durasi'));

    //     dump('Durasi maksimum:', $maxDurasi);

    //     // =========================
    //     // 6. SIMPAN KE FIRESTORE
    //     // =========================
    //     dump('=== Simpan ke Firestore ===');

    //     foreach ($results as $model) {

    //         $isCritical = $model['tot_durasi'] == $maxDurasi;

    //         dump('Simpan model:', [
    //             'jalur' => $model['jalur'],
    //             'total' => $model['tot_durasi'],
    //             'is_critical' => $isCritical
    //         ]);

    //         $firestore
    //             ->collection('PERT_Model_Result')
    //             ->add([
    //                 'project_id' => $projectRef,
    //                 'jalur' => $model['jalur'],
    //                 'tot_durasi' => $model['tot_durasi'],
    //                 'is_critical' => $isCritical
    //             ]);
    //     }
    // }

    // private function getCriticalPath($projectId)
    // {
    //     $firestore = $this->getFirestore();

    //     $projectRef = $firestore
    //         ->collection('All_Project_TA')
    //         ->document($projectId);

    //     $docs = $firestore
    //         ->collection('PERT_Model_Result')
    //         ->where('project_id', '=', $projectRef)
    //         ->where('is_critical', '=', true)
    //         ->documents();

    //     foreach ($docs as $doc) {
    //         if ($doc->exists()) {
    //             return $doc->data()['jalur'];
    //         }
    //     }

    //     return [];
    // }

// GENERATE MODEL PATH (DFS)
    // private function generatePertModel($projectId)
    // {
    //     // dump('CEK PANGGIL FUNCTION generatePertModel');
    //     // dump($projectId);

    //     $firestore = $this->getFirestore();

    //     $projectRef = $firestore
    //         ->collection('All_Project_TA')
    //         ->document($projectId);

    //     $old = $firestore
    //         ->collection('PERT_Model_Result')
    //         ->where('project_id', '=', $projectRef)
    //         ->documents();

    //     foreach ($old as $doc) {
    //         if ($doc->exists()) {
    //             $firestore->collection('PERT_Model_Result')
    //                 ->document($doc->id())
    //                 ->delete();
    //         }
    //     }
    //     /*
    //     =================================
    //     AMBIL MASTER TASK
    //     =================================
    //     */

    //     $masterDocs = $firestore
    //         ->collection('PERT_Master_Task')
    //         ->documents();

    //     $tasks = [];
    //     $ketergantungan = [];

    //     foreach ($masterDocs as $doc) {

    //         if (!$doc->exists()) continue;

    //         $row = $doc->data();

    //         $kode = $row['kode'];

    //         $tasks[$kode] = $row;

    //         $ketergantungan[$kode] = $row['ketergantungan'] ?? [];
    //     }

    //     // dump('CEK ISI MASTER TASK');
    //     // dump($tasks);
    //     // dump($ketergantungan);

    //     /*
    //     =================================
    //     AMBIL EXPECTED TIME
    //     =================================
    //     */

    //     $inputDocs = $firestore
    //         ->collection('PERT_Input')
    //         ->where('project_id', '=', $projectRef)
    //         ->documents();

    //     $expectedMap = [];

    //     foreach ($inputDocs as $doc) {

    //         if (!$doc->exists()) continue;

    //         $row = $doc->data();

    //         $expectedMap[$row['kode']] =
    //             $row['time_expected'];
    //     }

    //     // dump('CEK EXPECTED TIME TIAP KODE');
    //     // dump($expectedMap);

    //     /*
    //     =================================
    //     BANGUN GRAPH
    //     =================================
    //     */

    //     $graph = [];

    //     foreach ($ketergantungan as $task => $deps) {

    //         foreach ($deps as $dep) {

    //             $graph[$dep][] = $task;
    //         }
    //     }

    //     // dump('CEK GRAPH DEPENDENCY');
    //     // dump($graph);

    //     /*
    //     =================================
    //     CARI START NODE
    //     =================================
    //     */

    //     $startNodes = [];

    //     foreach ($tasks as $kode => $task) {

    //         if (empty($ketergantungan[$kode])) {
    //             $startNodes[] = $kode;
    //         }
    //     }

    //     // dump('CEK START NODE');
    //     // dump($startNodes);

    //     /*
    //     =================================
    //     DFS UNTUK SEMUA PATH
    //     =================================
    //     */

    //     $paths = [];

    //     $dfs = function ($node, $path) use (&$dfs, &$graph, &$paths) {

    //         $path[] = $node;

    //         if (!isset($graph[$node])) {

    //             $paths[] = $path;
    //             return;
    //         }

    //         foreach ($graph[$node] as $next) {

    //             $dfs($next, $path);
    //         }
    //     };

    //     foreach ($startNodes as $start) {

    //         $dfs($start, []);
    //     }

    //     // dump('CEK SEMUA JALUR TIAP MODEL');
    //     // dump($paths);

    //     /*
    //     =================================
    //     HITUNG TOTAL DURASI & CARI MAX DURASI
    //     =================================
    //     */

    //     $results = [];

    //     foreach ($paths as $path) {

    //         $total = 0;

    //         foreach ($path as $kode) {

    //             $total += $expectedMap[$kode] ?? 0;
    //         }

    //         $results[] = [
    //             'jalur' => $path,
    //             'tot_durasi' => $total
    //         ];
    //     }

    //     // dump('CEK TOTAL DURASI TIAP MODEL');
    //     // dump($results);

    //     $maxDurasi = max(array_column($results, 'tot_durasi'));

    //     // dump('CEK DURASI TERBESAR (JALUR KRITIS)');
    //     // dump($maxDurasi);

    //     /*
    //     =================================
    //     SIMPAN SEMUA MODEL
    //     =================================
    //     */

    //     foreach ($results as $model) {

    //         $isCritical = $model['tot_durasi'] == $maxDurasi;

    //         // dump('DATA MODEL YANG AKAN DISIMPAN');
    //         // dump([
    //         //     'jalur' => $model['jalur'],
    //         //     'tot_durasi' => $model['tot_durasi'],
    //         //     'is_critical' => $isCritical
    //         // ]);

    //         $firestore
    //             ->collection('PERT_Model_Result')
    //             ->add([
    //                 'project_id' => $projectRef,
    //                 'jalur' => $model['jalur'],
    //                 'tot_durasi' => $model['tot_durasi'],
    //                 'is_critical' => $isCritical
    //             ]);
    //     }
    // }