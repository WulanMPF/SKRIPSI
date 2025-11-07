<?php

namespace App\Http\Controllers\super_admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Cache;

class MakeProjectController extends Controller
{
    private function getFirestore()
    {
        return new FirestoreClient([
            'projectId' => env('FIREBASE_PROJECT_ID'),
            'keyFilePath' => base_path(env('FIREBASE_CREDENTIALS')),
        ]);
    }

    public function index()
    {
        $qeOptions = $this->fetchQEOptions();
        [$project_ta_doc, $uraianOptions] = $this->fetchProjectTaData();

        return view('super_admin.makeproject.makeproject_superadmin', compact('qeOptions', 'project_ta_doc', 'uraianOptions'));
    }

    private function fetchQEOptions()
    {
        return Cache::remember('qe_options', 3600, function () {
            $qeCollection = $this->getFirestore()->collection('QE')->documents();
            $data = [];
            foreach ($qeCollection as $docq) {
                if ($docq->exists()) {
                    $data[] = [
                        'id' => $docq->id(),
                        'label' => $docq->data()['type'],
                    ];
                }
            }
            usort($data, fn($a, $b) => (int)$a['id'] <=> (int)$b['id']);
            return $data;
        });
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

    public function store(Request $request)
    {
        $firestore = $this->getFirestore();

        try {
            // 1. Simpan ke All_Project_TA
            $allProjectRef = $firestore->collection('All_Project_TA')->add([
                'ta_project_deskripsi'        => $request->deskripsi,
                'ta_project_foto_id'          => null,
                'ta_project_khs'              => $request->khs,
                'ta_project_pekerjaan'        => $request->pekerjaan,
                'ta_project_pelaksana'        => $request->pelaksana,
                'ta_project_pending_id'       => null,
                'ta_project_qe_id'            => $firestore->collection('QE')->document($request->qe),
                'ta_project_status'           => 'PROCESS',
                'ta_project_total'            => (float) $request->summary_after_ppn,
                'ta_project_waktu_pengerjaan' => null,
                'ta_project_waktu_selesai'    => null,
                'ta_project_waktu_upload'     => now(),
                'ta_project_witel'            => $request->witel,
            ]);

            $allProjectId = $allProjectRef->id();

            // 2. Simpan ke Detail_Project_TA
            foreach ($request->designator as $i => $designator) {
                if (empty($designator)) continue;

                $dataProjectQuery = $firestore->collection('Data_Project_TA')
                    ->where('ta_designator', '=', $designator)
                    ->limit(1)
                    ->documents();

                $dataProjectId = null;
                foreach ($dataProjectQuery as $doc) {
                    $dataProjectId = $doc->id();
                }

                if (!$dataProjectId) continue;

                $firestore->collection('Detail_Project_TA')->add([
                    'ta_detail_all_id' => $firestore->collection('All_Project_TA')->document($allProjectId),
                    'ta_detail_ta_id'  => $firestore->collection('Data_Project_TA')->document($dataProjectId),
                    'ta_detail_volume' => (int) ($request->volume[$i] ?? 0),
                ]);
            }

            // ✅ Kembalikan response JSON biar fetch tahu sukses
            return response()->json([
                'success' => true,
                'message' => 'Project berhasil dibuat!',
                'project_id' => $allProjectId
            ], 200);

        } catch (\Exception $e) {
            // ❌ Jika ada error, kirim JSON error
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
