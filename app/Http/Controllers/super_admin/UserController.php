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
            'keyFilePath' => base_path(env('FIREBASE_CREDENTIALS')),
        ]);
    }

    public function index()
    {
        $usr_doc = $this->fetchUserData();
        $role_doc = $this->fetchRoleData();

        return view('super_admin.user.user_superadmin', compact('usr_doc', 'role_doc'));
    }

    private function fetchUserData()
    {
        $usr_collection = $this->getFirestore()->collection('User')->documents();
        $usr_doc = [];

        foreach ($usr_collection as $docu) {
            if ($docu->exists()) {
                $data = $docu->data();
                $userRoleRef = $data['user_role'];

                $roleData = $this->getReferenceData($userRoleRef);

                $usr_doc[] = [
                    'id' => $docu->id(),
                    'nik' => $data['user_nik'],
                    'nama' => $data['user_nama'],
                    'uker' => $data['user_sto'],
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
        $roleRef = $firestore->collection('Role')->document($request->role);

        $firestore->collection('User')->add([
            'user_nik'      => $request->nik,
            'user_nama'     => $request->nama,
            'user_sto'      => $request->uker,
            'user_role'     => $roleRef,
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

        $firestore->collection('User')->document($id)->set([
            'user_nik'      => $request->nik,
            'user_nama'     => $request->nama,
            'user_sto'      => $request->uker,
            'user_role'     => $roleRef,
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