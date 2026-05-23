<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Cloudinary\Cloudinary;

class DebugController extends Controller
{
    public function upload(Request $request)
    {
        $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));

        $file = $request->file('foto');

        $upload = $cloudinary->uploadApi()->upload(
            $file->getRealPath(),
            [
                'folder' => 'debug_test'
            ]
        );

        dd($upload);
    }
}
