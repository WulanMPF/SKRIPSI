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
    Route::get('/allproject/detail/{id}', [AllProjectController::class, 'detail'])->name('superadmin.allproject_detail');
    Route::get('/allproject/edit/{id}', [AllProjectController::class, 'edit'])->name('superadmin.allproject_edit');
    Route::put('/allproject/update/{id}', [AllProjectController::class, 'update'])->name('superadmin.allproject_update');
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
});
