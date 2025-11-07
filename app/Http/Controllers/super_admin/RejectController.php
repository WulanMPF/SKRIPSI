<?php

namespace App\Http\Controllers\super_admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;
use Carbon\Carbon;
use App\Imports\TaImport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;

class RejectController extends Controller
{
    private function getFirestore()
    {
        return new FirestoreClient([
            'projectId' => env('FIREBASE_PROJECT_ID'),
            'keyFilePath' => base_path(env('FIREBASE_CREDENTIALS')),
        ]);
    }

    public function index(Request $request)
    {
        $foto_doc = $this->fetchFotoData();
        $pending_doc = $this->fetchPendingData();
        $qe_doc = $this->fetchQEData();
        [$reject_doc, $grandTotal] = $this->fetchRejectData($request);

        return view('super_admin.reject.reject_superadmin', compact('reject_doc', 'grandTotal', 'qe_doc'));
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

    private function fetchRejectData($start = null, $end = null)
    {
        $reject_collection = $this->getFirestore()->collection('All_Project_TA')->documents();
        $reject_doc = [];
        $tot = 0;

        foreach ($reject_collection as $docr) {
            if ($docr->exists()) {
                $data = $docr->data();

                if (($data['ta_project_status'] ?? '') !== 'REJECT') {
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

                $rejectFotoRef = $data['ta_project_foto_id'];
                $rejectPendingRef = $data['ta_project_pending_id'];
                $rejectQERef = $data['ta_project_qe_id'];

                $fotoData = $this->getReferenceData($rejectFotoRef);
                $pendingData = $this->getReferenceData($rejectPendingRef);
                $qeData = $this->getReferenceData($rejectQERef);

                $tglUpload = $this->formatDate($data['ta_project_waktu_upload'] ?? null);
                $tglPengerjaan = $this->formatDate($data['ta_project_waktu_pengerjaan'] ?? null);
                $tglSelesai = $this->formatDate($data['ta_project_waktu_selesai'] ?? null);
                $totalValue = (float) ($data['ta_project_total'] ?? 0);

                $reject_doc[] = [
                    'id' => $docr->id(),
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

        return [$reject_doc, number_format($tot, 0, ',', '.')];
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

        if (!$doc->exists()) {
            return redirect()->route('superadmin.reject')->with('error', 'Data project tidak ditemukan');
        }

        $data = $doc->data();
        $fotoData = $this->getReferenceData($data['ta_project_foto_id'] ?? null);
        $pendingData = $this->getReferenceData($data['ta_project_pending_id'] ?? null);
        $qeData = $this->getReferenceData($data['ta_project_qe_id'] ?? null);

        $tglUpload = $this->formatDate($data['ta_project_waktu_upload'] ?? null);
        $tglPengerjaan = $this->formatDate($data['ta_project_waktu_pengerjaan'] ?? null);
        $tglSelesai = $this->formatDate($data['ta_project_waktu_selesai'] ?? null);

        $detailDocs = $firestore->collection('Detail_Project_TA')
            ->where('ta_detail_all_id', '=', $docRef) // filter by project reference
            ->documents();

        $detail = [];

        foreach ($detailDocs as $d) {
            if (!$d->exists()) continue;

            $row = $d->data();
            $designatorRef = $row['ta_detail_ta_id'];
            $designatorData = $this->getReferenceData($designatorRef);

            $hargaMaterial = $designatorData['ta_harga_material'] ?? 0;
            $hargaJasa = $designatorData['ta_harga_jasa'] ?? 0;
            $volume = $row['ta_detail_volume'] ?? 0;

            $detail[] = (object)[
                'id'             => $d->id(),
                'designator' => $designatorData['ta_designator'] ?? '',
                'uraian' => $designatorData['ta_uraian_pekerjaan'] ?? '',
                'satuan' => $designatorData['ta_satuan'] ?? '',
                'harga_material' => $hargaMaterial,
                'harga_jasa' => $hargaJasa,
                'volume' => $volume,
                'total_material' => $hargaMaterial * $volume,
                'total_jasa' => $hargaJasa * $volume,
            ];
        }

        $totals = $this->hitungTotal($detailDocs);

        return view('super_admin.reject.detail_reject', [
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

    public function edit($id)
    {
        $firestore = $this->getFirestore();
        $docRef = $firestore->collection('All_Project_TA')->document($id);
        $doc = $docRef->snapshot();

        if (!$doc->exists()) {
            return redirect()->route('superadmin.reject')->with('error', 'Data project tidak ditemukan');
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

        return view('super_admin.reject.edit_reject', [
            'reject' => [
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
            return redirect()->route('superadmin.reject')->with('error', 'Project tidak ditemukan');
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
            ->route('superadmin.reject_detail', $id)
            ->with('success', 'Project berhasil diperbarui');
    }

    public function updateRevisi(Request $request, $id)
    {
        try {
            $firestore = $this->getFirestore();

            // 🔒 Validasi hanya file revisi (tanpa QE dan deskripsi)
            $request->validate([
                'file' => 'required|mimes:xlsx,xls',
            ]);

            // 🔹 Ambil dokumen project lama
            $projectRef = $firestore->collection('All_Project_TA')->document($id);
            $projectSnap = $projectRef->snapshot();

            if (!$projectSnap->exists()) {
                return back()->with('error', 'Data project tidak ditemukan.');
            }

            // Ambil data lama (QE dan deskripsi tetap dipakai)
            $oldData = $projectSnap->data();
            $oldQERef = $oldData['ta_project_qe_id'] ?? null;
            $oldDeskripsi = $oldData['ta_project_deskripsi'] ?? '-';
            $oldNamaProject = $oldData['ta_project_pekerjaan'] ?? '-';

            // 1️⃣ Jalankan import Excel
            $rows = Excel::toCollection(new TAImport, $request->file('file'))[0];
            $import = new TAImport();
            $import->collection($rows);

            $header = $import->headerData;
            $details = $import->detailData;

            // 2️⃣ Hapus semua detail lama dari project ini
            $detailDocs = $firestore->collection('Detail_Project_TA')
                ->where('ta_detail_all_id', '=', $projectRef)
                ->documents();

            foreach ($detailDocs as $detail) {
                if ($detail->exists()) {
                    $firestore->collection('Detail_Project_TA')->document($detail->id())->delete();
                }
            }

            // 3️⃣ Tambahkan ulang detail baru dari file revisi
            $dataProjectCollection = $firestore->collection('Data_Project_TA');
            $totalMaterial = 0;
            $totalJasa = 0;

            foreach ($details as $detail) {
                $designator = $detail['designator'];
                $volume = (float)$detail['volume'];

                // 🔍 Cari designator di Data_Project_TA
                $dataTA = $dataProjectCollection->where('ta_designator', '=', $designator)->documents();
                $dataRef = null;
                $hargaMaterial = 0;
                $hargaJasa = 0;

                foreach ($dataTA as $d) {
                    if ($d->exists()) {
                        $dataRef = $dataProjectCollection->document($d->id());
                        $hargaMaterial = $d->data()['ta_harga_material'] ?? 0;
                        $hargaJasa = $d->data()['ta_harga_jasa'] ?? 0;
                        break;
                    }
                }

                if (!$dataRef) continue;

                $firestore->collection('Detail_Project_TA')->add([
                    'ta_detail_all_id' => $projectRef,
                    'ta_detail_ta_id'  => $dataRef,
                    'ta_detail_volume' => $volume,
                ]);

                // Hitung total baru
                $totalMaterial += $hargaMaterial * $volume;
                $totalJasa += $hargaJasa * $volume;
            }

            $total = $totalMaterial + $totalJasa;
            $ppn = $total * 0.11;
            $grandTotal = $total + $ppn;

            // 4️⃣ Update data di All_Project_TA (ganti detail dan total, tapi QE & deskripsi tetap)
            $projectRef->update([
                ['path' => 'ta_project_qe_id', 'value' => $oldQERef], // tetap pakai QE lama
                ['path' => 'ta_project_pekerjaan', 'value' => $header['ta_project_pekerjaan'] ?? $oldNamaProject], // 🔥 ambil dari file revisi jika ada
                ['path' => 'ta_project_deskripsi', 'value' => $oldDeskripsi], // deskripsi tetap
                ['path' => 'ta_project_khs', 'value' => $header['ta_project_khs'] ?? $oldData['ta_project_khs'] ?? '-'],
                ['path' => 'ta_project_pelaksana', 'value' => $header['ta_project_pelaksana'] ?? $oldData['ta_project_pelaksana'] ?? '-'],
                ['path' => 'ta_project_witel', 'value' => $header['ta_project_witel'] ?? $oldData['ta_project_witel'] ?? '-'],
                ['path' => 'ta_project_status', 'value' => 'PROCESS'], // status otomatis jadi PROCESS
                ['path' => 'ta_project_total', 'value' => $grandTotal],
                ['path' => 'ta_project_waktu_upload', 'value' => Carbon::now()],
            ]);

            return back()->with('success', 'Revisi berhasil diupload! Data lama diganti kecuali QE & deskripsi (tetap).');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id, $detailId)
    {
        $firestore = $this->getFirestore();

        // Referensi ke dokumen Detail_Project_TA yang ingin dihapus
        $detailRef = $firestore->collection('Detail_Project_TA')->document($detailId);
        $detailDoc = $detailRef->snapshot();

        if (!$detailDoc->exists()) {
            return redirect()
                ->route('superadmin.reject_detail', $id)
                ->with('error', 'Data detail tidak ditemukan.');
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

        return redirect()
            ->route('superadmin.reject_detail', $id)
            ->with('success', 'Material berhasil dihapus.');
    }

    public function destroyProject($id)
    {
        $firestore = $this->getFirestore();

        // Referensi ke dokumen project utama
        $projectRef = $firestore->collection('All_Project_TA')->document($id);
        $projectSnap = $projectRef->snapshot();

        if (!$projectSnap->exists()) {
            return redirect()->back()->with('error', 'Data project tidak ditemukan.');
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

        return redirect()->route('superadmin.reject')->with('success', 'Data project dan seluruh detail material berhasil dihapus.');
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
}
