<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;

class AuthController extends Controller
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
        return view('auth.login');
    }

    public function proses_login(Request $request)
    {
        $request->validate([
            'nik' => 'required',
            'password' => 'required'
        ]);

        $firestore = $this->getFirestore();

        // Cari user berdasarkan NIK
        $userSnapshot = $firestore->collection('User')
            ->where('user_nik', '=', $request->nik)
            ->limit(1)
            ->documents();

        if ($userSnapshot->isEmpty()) {
            return back()->withErrors(['nik' => 'NIK tidak ditemukan']);
        }

        $userDoc = $userSnapshot->rows()[0];
        $userData = $userDoc->data();

        // Cek password
        if (($userData['user_password'] ?? '') !== $request->password) {
            return back()->withErrors(['password' => 'Password salah']);
        }

        // Ambil role dari Firestore reference
        $roleName = 'Unknown Role';
        if (!empty($userData['user_role']) && $userData['user_role'] instanceof \Google\Cloud\Firestore\DocumentReference) {
            $roleSnapshot = $userData['user_role']->snapshot();
            $roleName = $roleSnapshot->exists()
                ? (string) ($roleSnapshot->data()['role'] ?? 'Unknown Role')
                : 'Unknown Role';
        }

        // Simpan hanya data primitif ke session
        session([
            'user_id'   => (string) $userDoc->id(),
            'user_nama' => (string) ($userData['user_nama'] ?? ''),
            'user_nik'  => (string) $userData['user_nik'],
            'role'      => $roleName
        ]);


        // Redirect sesuai role
        if ($roleName === 'Super Admin') {
            return redirect()->route('superadmin.allproject');
        } elseif ($roleName === 'Telkom Akses') {
            return redirect()->route('telkomakses.allproject');
        } elseif ($roleName === 'Mitra') {
            return redirect()->route('mitra.allproject');
        } else {
            return redirect('/');
        }
    }

    public function logout(Request $request)
    {
        session()->flush();
        return redirect()->route('login');
    }
}
