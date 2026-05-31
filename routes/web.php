<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\super_admin\UserController;
use App\Http\Controllers\super_admin\MakeProjectController;
use App\Http\Controllers\super_admin\AllProjectController;
use App\Http\Controllers\super_admin\ProcessController;
use App\Http\Controllers\super_admin\AccController;
use App\Http\Controllers\super_admin\RejectController;
use App\Http\Controllers\telkom_akses\AllProjectController as TAAllProjectController;
use App\Http\Controllers\telkom_akses\ProcessController as TAProcessController;
use App\Http\Controllers\telkom_akses\AccController as TAAccController;
use App\Http\Controllers\telkom_akses\RejectController as TARejectController;
use App\Http\Controllers\mitra\AllProjectController as MAllProjectController;
use App\Http\Controllers\mitra\MakeProjectController as MMakeProjectController;
use App\Http\Controllers\mitra\ProcessController as MProcessController;
use App\Http\Controllers\mitra\AccController as MAccController;
use App\Http\Controllers\mitra\RejectController as MRejectController;

// Route Login Page
Route::get('/', [AuthController::class, 'index'])->name('login');
Route::post('/login-proses', [AuthController::class, 'proses_login'])->name('login-proses');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Super Admin
Route::prefix('superadmin')->group(function () {
    // user
    Route::get('/user', [UserController::class, 'index'])->name('superadmin.user');
    Route::post('/user/store', [UserController::class, 'store'])->name('superadmin.user_store');
    Route::post('/user/update/{id}', [UserController::class, 'update'])->name('superadmin.user_update');
    Route::delete('/user/delete/{id}', [UserController::class, 'destroy'])->name('superadmin.user_destroy');
    // make project
    Route::get('/makeproject', [MakeProjectController::class, 'index'])->name('superadmin.makeproject');
    Route::post('/makeproject', [MakeProjectController::class, 'store'])->name('superadmin.makeproject_store');
    // all project
    Route::get('/allproject', [AllProjectController::class, 'index'])->name('superadmin.allproject');
    Route::post('/allproject', [AllProjectController::class, 'create'])->name('superadmin.allproject_create');
    Route::get('/allproject/process/detail/{id}', [AllProjectController::class, 'detailProcess'])->name('superadmin.allproject_process_detail');
    Route::delete('/allproject/process/detail/{id}/destroy/{detailId}', [AllProjectController::class, 'destroyProcess'])
        ->name('superadmin.allproject_process_destroy');
    Route::get('/allproject/process/edit/{id}', [AllProjectController::class, 'editProcess'])->name('superadmin.allproject_process_edit');
    Route::put('/allproject/process/update/{id}', [AllProjectController::class, 'updateProcess'])->name('superadmin.allproject_process_update');
    Route::delete('/allproject/process/{id}/destroy', [AllProjectController::class, 'destroyProjectProcess'])
        ->name('superadmin.allproject_process_destroy_project');
    Route::post('/allproject/process/{id}/acc', [AllProjectController::class, 'accProcess'])->name('superadmin.allproject_process.acc');
    Route::post('/allproject/process/{id}/reject', [AllProjectController::class, 'rejectProcess'])->name('superadmin.allproject_process.reject');
    Route::get('/allproject/acc/detail/{id}', [AllProjectController::class, 'detailAcc'])->name('superadmin.allproject_acc_detail');
    Route::delete('/allproject/acc/detail/{id}/destroy/{detailId}', [AllProjectController::class, 'destroyAcc'])
        ->name('superadmin.allproject_acc_destroy');
    Route::get('/allproject/acc/edit/{id}', [AllProjectController::class, 'editAcc'])->name('superadmin.allproject_acc_edit');
    Route::put('/allproject/acc/update/{id}', [AllProjectController::class, 'updateAcc'])->name('superadmin.allproject_acc_update');
    Route::delete('/allproject/acc/{id}/destroy', [AllProjectController::class, 'destroyProjectAcc'])
        ->name('superadmin.allproject_acc_destroy_project');
    Route::post('/allproject/acc/{id}/kerjakan', [AllProjectController::class, 'kerjakanAcc'])->name('superadmin.allproject.acc.kerjakan');
    Route::post('/allproject/acc/{id}/done', [AllProjectController::class, 'storeFotoAcc'])->name('superadmin.allproject.acc.storeFoto');
    Route::post('/allproject/acc/{id}/pending', [AllProjectController::class, 'pendingAcc'])->name('superadmin.allproject.acc.pending');
    Route::get('/allproject/reject/detail/{id}', [AllProjectController::class, 'detailReject'])->name('superadmin.allproject_reject_detail');
    Route::delete('/allproject/reject/detail/{id}/destroy/{detailId}', [AllProjectController::class, 'destroyReject'])
        ->name('superadmin.allproject_reject_destroy');
    Route::get('/allproject/reject/edit/{id}', [AllProjectController::class, 'editReject'])->name('superadmin.allproject_reject_edit');
    Route::put('/allproject/reject/update/{id}', [AllProjectController::class, 'updateReject'])->name('superadmin.allproject_reject_update');
    Route::delete('/allproject/reject/{id}/destroy', [AllProjectController::class, 'destroyProjectReject'])
        ->name('superadmin.allproject_reject_destroy_project');
    Route::post('/allproject/reject/{id}/upload-revisi', [AllProjectController::class, 'updateRevisiReject'])
        ->name('superadmin.allproject_reject_upload_revisi');
    Route::get('/allproject/download', [AllProjectController::class, 'downloadPDF'])->name('superadmin.allproject_download');
    // Process
    Route::get('/process', [ProcessController::class, 'index'])->name('superadmin.process');
    Route::get('/process/detail/{id}', [ProcessController::class, 'detail'])->name('superadmin.process_detail');
    Route::delete('/process/detail/{id}/destroy/{detailId}', [ProcessController::class, 'destroy'])
        ->name('superadmin.process_destroy');
    Route::get('/process/edit/{id}', [ProcessController::class, 'edit'])->name('superadmin.process_edit');
    Route::put('/process/update/{id}', [ProcessController::class, 'update'])->name('superadmin.process_update');
    Route::delete('/process/{id}/destroy', [ProcessController::class, 'destroyProject'])
        ->name('superadmin.process_destroy_project');
    Route::post('/process/{id}/acc', [ProcessController::class, 'acc'])->name('superadmin.process.acc');
    Route::post('/process/{id}/reject', [ProcessController::class, 'reject'])->name('superadmin.process.reject');
    // acc
    Route::get('/acc', [AccController::class, 'index'])->name('superadmin.acc');
    Route::get('/acc/detail/{id}', [AccController::class, 'detail'])->name('superadmin.acc_detail');
    Route::delete('/acc/detail/{id}/destroy/{detailId}', [AccController::class, 'destroy'])
        ->name('superadmin.acc_destroy');
    Route::get('/acc/edit/{id}', [AccController::class, 'edit'])->name('superadmin.acc_edit');
    Route::put('/acc/update/{id}', [AccController::class, 'update'])->name('superadmin.acc_update');
    Route::delete('/acc/{id}/destroy', [AccController::class, 'destroyProject'])
        ->name('superadmin.acc_destroy_project');
    Route::post('/acc/{id}/kerjakan', [AccController::class, 'kerjakan'])->name('superadmin.acc.kerjakan');
    /* =========================
    ROUTE PERT
    ========================= */
    Route::post('/acc/{id}/pert/store', [AccController::class, 'storePert'])
        ->name('superadmin.pert.store');
    Route::post('/acc/{id}/done', [AccController::class, 'storeFoto'])->name('superadmin.acc.storeFoto');
    Route::post('/acc/{id}/pending', [AccController::class, 'pending'])->name('superadmin.acc.pending');
    // reject
    Route::get('/reject', [RejectController::class, 'index'])->name('superadmin.reject');
    Route::get('/reject/detail/{id}', [RejectController::class, 'detail'])->name('superadmin.reject_detail');
    Route::delete('/reject/detail/{id}/destroy/{detailId}', [RejectController::class, 'destroy'])
        ->name('superadmin.reject_destroy');
    Route::get('/reject/edit/{id}', [RejectController::class, 'edit'])->name('superadmin.reject_edit');
    Route::put('/reject/update/{id}', [RejectController::class, 'update'])->name('superadmin.reject_update');
    Route::delete('/reject/{id}/destroy', [RejectController::class, 'destroyProject'])
        ->name('superadmin.reject_destroy_project');
    Route::post('/reject/{id}/upload-revisi', [RejectController::class, 'updateRevisi'])
        ->name('superadmin.reject_upload_revisi');
    Route::post('/superadmin/acc/{id}/send-to-mitra', [AccController::class, 'sendToMitra'])
        ->name('superadmin.acc_sendToMitra');
});

// Telkom Akses
Route::prefix('telkomakses')->group(function () {
    // all project
    Route::get('/allproject', [TAAllProjectController::class, 'index'])->name('telkomakses.allproject');
    Route::get('/allproject/download', [TAAllProjectController::class, 'downloadPDF'])->name('telkomakses.allproject_download');
    Route::get('/allproject/process/detail/{id}', [TAAllProjectController::class, 'detailProcess'])->name('telkomakses.allproject_process_detail');
    Route::post('/allproject/process/{id}/acc', [TAAllProjectController::class, 'accProcess'])->name('telkomakses.allproject_process.acc');
    Route::post('/allproject/process/{id}/reject', [TAAllProjectController::class, 'rejectProcess'])->name('telkomakses.allproject_process.reject');
    Route::get('/allproject/acc/detail/{id}', [TAAllProjectController::class, 'detailAcc'])->name('telkomakses.allproject_acc_detail');
    Route::get('/allproject/reject/detail/{id}', [TAAllProjectController::class, 'detailReject'])->name('telkomakses.allproject_reject_detail');
    // Process
    Route::get('/process', [TAProcessController::class, 'index'])->name('telkomakses.process');
    Route::get('/process/detail/{id}', [TAProcessController::class, 'detail'])->name('telkomakses.process_detail');
    Route::post('/process/{id}/acc', [TAProcessController::class, 'acc'])->name('telkomakses.process.acc');
    Route::post('/process/{id}/reject', [TAProcessController::class, 'reject'])->name('telkomakses.process.reject');
    // acc
    Route::get('/acc', [TAAccController::class, 'index'])->name('telkomakses.acc');
    Route::get('/acc/detail/{id}', [TAAccController::class, 'detail'])->name('telkomakses.acc_detail');
    // reject
    Route::get('/reject', [TARejectController::class, 'index'])->name('telkomakses.reject');
    Route::get('/reject/detail/{id}', [TARejectController::class, 'detail'])->name('telkomakses.reject_detail');
});

// Mitra
Route::prefix('mitra')->group(function () {
    // make project
    Route::get('/makeproject', [MMakeProjectController::class, 'index'])->name('mitra.makeproject');
    Route::post('/makeproject', [MMakeProjectController::class, 'store'])->name('mitra.makeproject_store');
    // all project
    Route::get('/allproject', [MAllProjectController::class, 'index'])->name('mitra.allproject');
    Route::post('/allproject', [MAllProjectController::class, 'create'])->name('mitra.allproject_create');


    Route::get('/allproject/process/detail/{id}', [MAllProjectController::class, 'detailProcess'])->name('mitra.allproject_process_detail');
    Route::delete('/allproject/process/detail/{id}/destroy/{detailId}', [MAllProjectController::class, 'destroyProcess'])
        ->name('mitra.allproject_process_destroy');
    Route::get('/allproject/process/edit/{id}', [MAllProjectController::class, 'editProcess'])->name('mitra.allproject_process_edit');
    Route::put('/allproject/process/update/{id}', [MAllProjectController::class, 'updateProcess'])->name('mitra.allproject_process_update');
    Route::delete('/allproject/process/{id}/destroy', [MAllProjectController::class, 'destroyProjectProcess'])
        ->name('mitra.allproject_process_destroy_project');
    Route::post('/allproject/process/{id}/acc', [MAllProjectController::class, 'accProcess'])->name('mitra.allproject_process.acc');
    Route::post('/allproject/process/{id}/reject', [MAllProjectController::class, 'rejectProcess'])->name('mitra.allproject_process.reject');


    Route::get('/allproject/acc/detail/{id}', [MAllProjectController::class, 'detailAcc'])->name('mitra.allproject_acc_detail');
    Route::delete('/allproject/acc/detail/{id}/destroy/{detailId}', [MAllProjectController::class, 'destroyAcc'])
        ->name('mitra.allproject_acc_destroy');
    Route::get('/allproject/acc/edit/{id}', [MAllProjectController::class, 'editAcc'])->name('mitra.allproject_acc_edit');
    Route::put('/allproject/acc/update/{id}', [MAllProjectController::class, 'updateAcc'])->name('mitra.allproject_acc_update');
    Route::delete('/allproject/acc/{id}/destroy', [MAllProjectController::class, 'destroyProjectAcc'])
        ->name('mitra.allproject_acc_destroy_project');
    Route::post('/allproject/acc/{id}/kerjakan', [MAllProjectController::class, 'kerjakanAcc'])->name('mitra.allproject_acc.kerjakan');
    Route::post('/allproject/acc/{id}/done', [MAllProjectController::class, 'storeFotoAcc'])->name('mitra.allproject_acc.storeFoto');
    Route::post('/allproject/acc/{id}/pending', [MAllProjectController::class, 'pendingAcc'])->name('mitra.allproject_acc.pending');


    Route::get('/allproject/reject/detail/{id}', [MAllProjectController::class, 'detailReject'])->name('mitra.allproject_reject_detail');
    Route::delete('/allproject/reject/detail/{id}/destroy/{detailId}', [MAllProjectController::class, 'destroyReject'])
        ->name('mitra.allproject_reject_destroy');
    Route::get('/allproject/reject/edit/{id}', [MAllProjectController::class, 'editReject'])->name('mitra.allproject_reject_edit');
    Route::put('/allproject/reject/update/{id}', [MAllProjectController::class, 'updateReject'])->name('mitra.allproject_reject_update');
    Route::delete('/allproject/reject/{id}/destroy', [MAllProjectController::class, 'destroyProjectReject'])
        ->name('mitra.allproject_reject_destroy_project');
    Route::post('/allproject/reject/{id}/upload-revisi', [MAllProjectController::class, 'updateRevisiReject'])
        ->name('mitra.allproject_reject_upload_revisi');


    Route::get('/allproject/download', [MAllProjectController::class, 'downloadPDF'])->name('mitra.allproject_download');
    // Process
    Route::get('/process', [MProcessController::class, 'index'])->name('mitra.process');
    Route::get('/process/detail/{id}', [MProcessController::class, 'detail'])->name('mitra.process_detail');
    Route::delete('/process/detail/{id}/destroy/{detailId}', [MProcessController::class, 'destroy'])
        ->name('mitra.process_destroy');
    Route::get('/process/edit/{id}', [MProcessController::class, 'edit'])->name('mitra.process_edit');
    Route::put('/process/update/{id}', [MProcessController::class, 'update'])->name('mitra.process_update');
    Route::delete('/process/{id}/destroy', [MProcessController::class, 'destroyProject'])
        ->name('mitra.process_destroy_project');
    Route::post('/process/{id}/acc', [MProcessController::class, 'acc'])->name('mitra.process.acc');
    Route::post('/process/{id}/reject', [MProcessController::class, 'reject'])->name('mitra.process.reject');
    // acc
    Route::get('/acc', [MAccController::class, 'index'])->name('mitra.acc');
    Route::get('/acc/detail/{id}', [MAccController::class, 'detail'])->name('mitra.acc_detail');
    Route::delete('/acc/detail/{id}/destroy/{detailId}', [MAccController::class, 'destroy'])
        ->name('mitra.acc_destroy');
    Route::get('/acc/edit/{id}', [MAccController::class, 'edit'])->name('mitra.acc_edit');
    Route::put('/acc/update/{id}', [MAccController::class, 'update'])->name('mitra.acc_update');
    Route::delete('/acc/{id}/destroy', [MAccController::class, 'destroyProject'])
        ->name('mitra.acc_destroy_project');
    Route::post('/acc/{id}/kerjakan', [MAccController::class, 'kerjakan'])->name('mitra.acc.kerjakan');
    Route::post('/acc/{id}/done', [MAccController::class, 'storeFoto'])->name('mitra.acc.storeFoto');
    Route::post('/acc/{id}/pending', [MAccController::class, 'pending'])->name('mitra.acc.pending');
    // reject
    Route::get('/reject', [MRejectController::class, 'index'])->name('mitra.reject');
    Route::get('/reject/detail/{id}', [MRejectController::class, 'detail'])->name('mitra.reject_detail');
    Route::delete('/reject/detail/{id}/destroy/{detailId}', [MRejectController::class, 'destroy'])
        ->name('mitra.reject_destroy');
    Route::get('/reject/edit/{id}', [MRejectController::class, 'edit'])->name('mitra.reject_edit');
    Route::put('/reject/update/{id}', [MRejectController::class, 'update'])->name('mitra.reject_update');
    Route::delete('/reject/{id}/destroy', [MRejectController::class, 'destroyProject'])
        ->name('mitra.reject_destroy_project');
    Route::post('/reject/{id}/upload-revisi', [MRejectController::class, 'updateRevisi'])
        ->name('mitra.reject_upload_revisi');
});

// use App\Http\Controllers\DebugController;

// // HALAMAN FORM DEBUG
// Route::get('/debug-upload', function () {
//     return view('debug-upload');
// });

// PROSES UPLOAD DEBUG
// Route::post('/debug-upload', [DebugController::class, 'upload']);