<?php

namespace App\Http\Controllers\super_admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;

class UserController extends Controller
{
    private function getFirestore()
    {
        return new FirestoreClient([
            'projectId' => env('FIREBASE_PROJECT_ID'),
            'keyFilePath' => storage_path('app/firebase/luwina-381dd-firebase-adminsdk-fbsvc-d4615d8138.json'),
        ]);
    }

    public function index()
    {
        $usr_doc = $this->fetchUserData();
        $role_doc = $this->fetchRoleData();
        $uker_doc = $this->fetchUkerData();

        $user = $usr_doc[0] ?? null; // ⚠️ hanya untuk test

        return view('super_admin.user.user_superadmin', compact('usr_doc', 'role_doc', 'uker_doc', 'user'));
    }

    private function fetchUserData()
    {
        $usr_collection = $this->getFirestore()->collection('User')->documents();
        $usr_doc = [];

        foreach ($usr_collection as $docu) {
            if ($docu->exists()) {
                $data = $docu->data();
                $userRoleRef = $data['user_role'];
                $userUkerRef = $data['user_sto'];

                $roleData = $this->getReferenceData($userRoleRef);
                $ukerData = $this->getReferenceData($userUkerRef);

                $usr_doc[] = [
                    'id' => $docu->id(),
                    'nik' => $data['user_nik'],
                    'nama' => $data['user_nama'],
                    'uker' => $ukerData ? $ukerData['Unit'] : null,
                    'uker_id' => $userUkerRef ? $userUkerRef->id() : null,
                    'password' => $data['user_password'],
                    'role' => $roleData ? $roleData['role'] : null,
                    'role_id' => $roleData ? $userRoleRef->id() : null,
                ];
            }
        }

        usort($usr_doc, fn($a, $b) => (int)$a['id'] <=> (int)$b['id']);
        return $usr_doc;
    }

    private function fetchRoleData()
    {
        $role_collection = $this->getFirestore()->collection('Role')->documents();
        $role_doc = [];

        foreach ($role_collection as $docr) {
            if ($docr->exists()) {
                $role_doc[] = [
                    'id' => $docr->id(),
                    'role' => $docr->data()['role'],
                ];
            }
        }

        usort($role_doc, fn($c, $d) => (int)$c['id'] <=> (int)$d['id']);
        return $role_doc;
    }

    private function fetchUkerData()
    {
        $uker_collection = $this->getFirestore()
            ->collection('Unit_Kerja')
            ->documents();

        $uker_doc = [];

        foreach ($uker_collection as $docu) {
            if ($docu->exists()) {
                $data = $docu->data();

                $uker_doc[] = [
                    'id'   => $docu->id(),       // contoh: "1"
                    'unit' => $data['Unit'] ?? null, // HARUS 'Unit'
                ];
            }
        }
        // dd($uker_doc);

        return $uker_doc;
    }


    private function getReferenceData($ref)
    {
        if ($ref && method_exists($ref, 'snapshot')) {
            $doc = $ref->snapshot();
            return $doc->exists() ? $doc->data() : null;
        }
        return null;
    }

    public function store(Request $request)
    {
        $request->validate([
            'nik'      => 'required|string',
            'nama'     => 'required|string',
            'uker'     => 'required|string',
            'role'     => 'required|string',
            'password' => 'required|string',
        ]);

        $firestore = $this->getFirestore();

        // 🔑 BUAT REFERENCE
        $roleRef = $firestore->collection('Role')->document($request->role);
        $ukerRef = $firestore->collection('Unit_Kerja')->document($request->uker);

        $firestore->collection('User')->add([
            'user_nik'      => $request->nik,
            'user_nama'     => $request->nama,
            'user_sto'      => $ukerRef, // ✅ REFERENCE
            'user_role'     => $roleRef, // ✅ REFERENCE
            'user_password' => $request->password,
        ]);

        return redirect()->back()->with('success', 'User berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nik'      => 'required|string',
            'nama'     => 'required|string',
            'uker'     => 'required|string',
            'role'     => 'required|string',
            'password' => 'required|string',
        ]);

        $firestore = $this->getFirestore();
        $roleRef = $firestore->collection('Role')->document($request->role);
        $ukerRef = $firestore->collection('Unit_Kerja')->document($request->uker);

        $firestore->collection('User')->document($id)->set([
            'user_nik'      => $request->nik,
            'user_nama'     => $request->nama,
            'user_sto'      => $ukerRef,   // ✅
            'user_role'     => $roleRef,   // ✅
            'user_password' => $request->password,
        ], ['merge' => true]);

        return redirect()->back()->with('success', 'User berhasil diperbarui');
    }

    public function destroy($id)
    {
        $firestore = $this->getFirestore();
        $firestore->collection('User')->document($id)->delete();

        return redirect()->back()->with('success', 'User berhasil dihapus');
    }
}
