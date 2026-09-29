<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PhpInfoController;
use App\Http\Controllers\ProbeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/report.json', [DashboardController::class, 'report'])->name('dashboard.report');
Route::get('/phpinfo', PhpInfoController::class)->name('phpinfo');

Route::post('/probe/storage', [ProbeController::class, 'storage'])->name('probe.storage');
Route::post('/probe/queue', [ProbeController::class, 'queue'])->name('probe.queue');
Route::post('/probe/upload', [ProbeController::class, 'upload'])->name('probe.upload');
Route::post('/probe/mail', [ProbeController::class, 'mail'])->name('probe.mail');
Route::post('/probe/http', [ProbeController::class, 'http'])->name('probe.http');
Route::post('/probe/cache', [ProbeController::class, 'cache'])->name('probe.cache');
