<?php

namespace App\Http\Controllers\super_admin;

use App\Http\Controllers\Controller;
use Google\Cloud\Firestore\FirestoreClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Imports\TaImport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AllProjectController extends Controller
{
    private function getFirestore()
    {
        return new FirestoreClient([
            'projectId' => env('FIREBASE_PROJECT_ID'),
            'keyFilePath' => base_path(env('FIREBASE_CREDENTIALS')),
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

        return view('super_admin.allproject.allproject_superadmin', compact(
            'project_doc',
            'grandTotal',
            'chartTotalProjectData',
            'chartQEData',
            'chartPieData',
            'qe_doc'
        ));
    }

    public function create(Request $request)
    {
        try {
            $firestore = $this->getFirestore();

            // 🔒 Validasi input
            $request->validate([
                'file' => 'required|mimes:xlsx,xls',
                'qe' => 'required|string',
                'status' => 'required|string',
                'deskripsi' => 'required|string',
            ]);

            // 1️⃣ Jalankan import Excel
            $rows = Excel::toCollection(new TAImport, $request->file('file'))[0];
            $import = new TAImport();
            $import->collection($rows);

            // dd([
            //     'headerData' => $import->headerData,
            //     'detailData' => $import->detailData,
            // ]);

            $header = $import->headerData;
            $details = $import->detailData;

            // 2️⃣ Ambil reference QE dari Firestore
            $qe_collection = $firestore->collection('QE');
            $qe_doc = $qe_collection->where('type', '=', $request->qe)->documents();

            $qe_ref = null;
            foreach ($qe_doc as $doc) {
                if ($doc->exists()) {
                    $qe_ref = $qe_collection->document($doc->id());
                    break;
                }
            }

            if (!$qe_ref) {
                return back()->with('error', 'QE tidak ditemukan.');
            }

            // 3️⃣ Simpan ke All_Project_TA
            $allProjectRef = $firestore->collection('All_Project_TA')->add([
                'ta_project_qe_id'        => $qe_ref,
                'ta_project_pekerjaan'    => $header['ta_project_pekerjaan'],
                'ta_project_deskripsi'    => $request->deskripsi,
                'ta_project_khs'          => $header['ta_project_khs'],
                'ta_project_pelaksana'    => $header['ta_project_pelaksana'],
                'ta_project_witel'        => $header['ta_project_witel'],
                'ta_project_foto_id'      => null,
                'ta_project_pending_id'   => null,
                'ta_project_status'       => $request->status,
                'ta_project_total'        => 0,
                'ta_project_waktu_pengerjaan' => null,
                'ta_project_waktu_selesai'    => null,
                'ta_project_waktu_upload'     => Carbon::now(),
            ]);

            // 4️⃣ Simpan ke Detail_Project_TA (hanya baris valid sesuai TAImport)
            $dataProjectCollection = $firestore->collection('Data_Project_TA');
            foreach ($details as $detail) {
                $designator = $detail['designator'];
                $volume = $detail['volume'];

                // 🔍 Cari dokumen designator di Data_Project_TA
                $dataTA = $dataProjectCollection->where('ta_designator', '=', $designator)->documents();
                $dataRef = null;
                foreach ($dataTA as $d) {
                    if ($d->exists()) {
                        $dataRef = $dataProjectCollection->document($d->id());
                        break;
                    }
                }

                // Lewati kalau tidak ditemukan
                if (!$dataRef) continue;

                // 🔹 Tambahkan ke Detail_Project_TA
                $firestore->collection('Detail_Project_TA')->add([
                    'ta_detail_all_id' => $allProjectRef,
                    'ta_detail_ta_id'  => $dataRef,
                    'ta_detail_volume' => $volume,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data project berhasil diupload!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function detail($id)
    {
        $firestore = $this->getFirestore();
        $docRef = $firestore->collection('All_Project_TA')->document($id);
        $doc = $docRef->snapshot();

        if (!$doc->exists()) {
            return redirect()->route('superadmin.allproject')->with('error', 'Data project tidak ditemukan');
        }

        $data = $doc->data();

        // Fetch related data
        $fotoData = $this->getReferenceData($data['ta_project_foto_id'] ?? null);
        $pendingData = $this->getReferenceData($data['ta_project_pending_id'] ?? null);
        $qeData = $this->getReferenceData($data['ta_project_qe_id'] ?? null);

        $tglUpload = $this->formatDate($data['ta_project_waktu_upload'] ?? null);
        $tglPengerjaan = $this->formatDate($data['ta_project_waktu_pengerjaan'] ?? null);
        $tglSelesai = $this->formatDate($data['ta_project_waktu_selesai'] ?? null);

        // Fetch detail data from Detail_Project_TA
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

        return view('super_admin.allproject.detail_allproject', [
            'allproject' => [
                'id' => $id,
                'nama_project' => $data['ta_project_pekerjaan'],
                // 'deskripsi_project' => $data['ta_project_deskripsi'],
                // 'qe' => $qeData['type'] ?? null,
                'foto' => $fotoData,
                'pending' => $pendingData,
                // 'tgl_upload' => $tglUpload,
                // 'tgl_pengerjaan' => $tglPengerjaan,
                // 'tgl_selesai' => $tglSelesai,
                // 'status' => $data['ta_project_status'],
                'total' => $data['ta_project_total'],
                'detail' => $detail,
            ],
            'totals' => $totals,
        ]);
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

        $pdf = Pdf::loadView('super_admin.allproject.download_superadmin', [
            'project_doc' => $project_doc,
            'grandTotal' => $grandTotal,
            'title' => $title,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('All_Project_' . now()->format('Y-m-d') . '.pdf');
    }
}
