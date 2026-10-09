<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CampusCareController;
use App\Http\Controllers\OfficerController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

// AREA PUBLIK
Route::get('/', [CampusCareController::class, 'home'])
    ->name('home');

Route::get('/lapor', [ReportController::class, 'create'])
    ->name('lapor');

Route::post('/lapor', [ReportController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('lapor.store');

Route::get('/tiket', [TicketController::class, 'index'])
    ->name('tiket');

Route::post('/tiket/cek', [TicketController::class, 'lookup'])
    ->middleware('throttle:20,1')
    ->name('tiket.lookup');

Route::post('/tiket/balas', [TicketController::class, 'reply'])
    ->middleware('throttle:10,1')
    ->name('tiket.reply');

Route::get('/bantuan', [CampusCareController::class, 'help'])
    ->name('bantuan');

// LOGIN PETUGAS
Route::get('/masuk', [AuthController::class, 'showLogin'])
    ->name('masuk');

Route::post('/masuk', [AuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('masuk.proses');

Route::post('/keluar', [AuthController::class, 'logout'])
    ->middleware('cc.officer')
    ->name('keluar');

// AREA PETUGAS
Route::middleware('cc.officer')
    ->prefix('dasbor')
    ->group(function () {
        Route::get('/', [OfficerController::class, 'dashboard'])
            ->name('dasbor');

        Route::get('/laporan/{id}', [OfficerController::class, 'detail'])
            ->name('laporan.detail');

        Route::patch('/laporan/{id}/status', [OfficerController::class, 'updateStatus'])
            ->name('laporan.status');

        Route::patch('/laporan/{id}/urgensi', [OfficerController::class, 'updateUrgency'])
            ->name('laporan.urgensi');

        Route::patch('/laporan/{id}/tugaskan', [OfficerController::class, 'assign'])
            ->name('laporan.assign');

        Route::post('/laporan/{id}/catatan', [OfficerController::class, 'addNote'])
            ->name('laporan.catatan');

        Route::post('/laporan/{id}/pesan', [OfficerController::class, 'sendMessage'])
            ->name('laporan.pesan');

        Route::get('/laporan/{id}/lampiran/{fileId}', [OfficerController::class, 'attachment'])
            ->name('laporan.lampiran');

        Route::get('/statistik', [OfficerController::class, 'statistics'])
            ->name('statistik');

        Route::get('/pola', [OfficerController::class, 'patterns'])
            ->name('pola');

        // Hanya ADMIN
        Route::middleware('cc.admin')->group(function () {
            Route::get('/audit', [OfficerController::class, 'audit'])
                ->name('audit');

            Route::post('/laporan/{id}/override-identitas',
                [OfficerController::class, 'overrideIdentity'])
                ->name('laporan.override-identitas');
        });
    });
