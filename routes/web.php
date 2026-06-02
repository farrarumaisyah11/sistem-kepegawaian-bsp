<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HcmController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\PengajuanPerubahanController;

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLoginForm'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.post');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    return match (auth()->user()->role) {
        'admin'   => redirect()->route('admin.dashboard'),
        'hcm'     => redirect()->route('hcm.dashboard'),
        'pegawai' => redirect()->route('pegawai.saya'),
        default   => abort(403),
    };
});

/*
|--------------------------------------------------------------------------
| GLOBAL ROUTE - FOTO, JOB DESCRIPTION PEGAWAI, DETAIL PEGAWAI
|--------------------------------------------------------------------------
| PENTING:
| /pegawai/job-description harus berada DI ATAS /pegawai/{pegawai:nip}
| supaya tidak dianggap sebagai parameter NIP.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/pegawai/foto/{pegawai:nip}', [PegawaiController::class, 'foto'])
        ->whereNumber('pegawai')
        ->name('pegawai.foto');

    Route::get('/pegawai/job-description', [PegawaiController::class, 'jobDescription'])
        ->middleware('role:pegawai')
        ->name('pegawai.job-description');

    Route::get('/pegawai/{pegawai:nip}', [PegawaiController::class, 'show'])
        ->whereNumber('pegawai')
        ->name('pegawai.show');
});

/*
|--------------------------------------------------------------------------
| APPROVAL JOB DESCRIPTION VIA QR
|--------------------------------------------------------------------------
| Route ini dibuat global, bukan /admin atau /hcm, supaya QR code punya URL tetap.
| Tetap aman karena hanya bisa diakses role admin/hcm.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,hcm'])->group(function () {
    Route::get('/jabatan/{jabatan}/approval', [JabatanController::class, 'approvalPage'])
        ->whereNumber('jabatan')
        ->name('jabatan.approval.page');

    Route::get('/jabatan/{jabatan}/approval-qr', [JabatanController::class, 'approvalQr'])
        ->whereNumber('jabatan')
        ->name('jabatan.approval.qr');

    Route::get('/jabatan/{jabatan}/approval/{token}', [JabatanController::class, 'approvalScan'])
        ->whereNumber('jabatan')
        ->where('token', '[A-Za-z0-9\-]+')
        ->name('jabatan.approval.scan');

    Route::post('/jabatan/{jabatan}/approval/{token}', [JabatanController::class, 'approvalApprove'])
        ->whereNumber('jabatan')
        ->where('token', '[A-Za-z0-9\-]+')
        ->name('jabatan.approval.approve');
});

/*
|--------------------------------------------------------------------------
| ADMIN AREA
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard Admin
        |--------------------------------------------------------------------------
        */
        Route::get('/dashboard', [AdminController::class, 'index'])
            ->name('dashboard');

        Route::get('/dashboard/stats', [AdminController::class, 'stats'])
            ->name('dashboard.stats');

        /*
        |--------------------------------------------------------------------------
        | Pengajuan Perubahan - Admin
        |--------------------------------------------------------------------------
        */
        Route::get('/pengajuan', [PengajuanPerubahanController::class, 'indexReviewer'])
            ->name('pengajuan.index');

        Route::get('/pengajuan/{pengajuan}', [PengajuanPerubahanController::class, 'showReviewer'])
            ->name('pengajuan.show');

        Route::match(['post', 'patch'], '/pengajuan/{pengajuan}/proses', [PengajuanPerubahanController::class, 'proses'])
            ->name('pengajuan.proses');

        Route::match(['post', 'patch'], '/pengajuan/{pengajuan}/terima', [PengajuanPerubahanController::class, 'terima'])
            ->name('pengajuan.terima');

        Route::match(['post', 'patch'], '/pengajuan/{pengajuan}/tolak', [PengajuanPerubahanController::class, 'tolak'])
            ->name('pengajuan.tolak');

        Route::delete('/pengajuan/{pengajuan}', [PengajuanPerubahanController::class, 'destroyReviewer'])
            ->name('pengajuan.destroy');

        /*
        |--------------------------------------------------------------------------
        | Pegawai - Admin
        |--------------------------------------------------------------------------
        */
        Route::resource('pegawai', PegawaiController::class)
            ->except(['show']);

        Route::get('/pegawai/{pegawai:nip}/print', [PegawaiController::class, 'print'])
            ->whereNumber('pegawai')
            ->name('pegawai.print');

        Route::get('/pegawai/template-excel', [PegawaiController::class, 'downloadTemplate'])
            ->name('pegawai.templateExcel');

        Route::get('/pegawai/import-excel', fn () => view('pegawai.import_excel'))
            ->name('pegawai.import.form');

        Route::post('/pegawai/import-excel/preview', [PegawaiController::class, 'importPreview'])
            ->name('pegawai.import.preview');

        Route::get('/pegawai/import-excel/preview/show', [PegawaiController::class, 'showImportPreview'])
            ->name('pegawai.importPreview.show');

        Route::post('/pegawai/import-excel/save', [PegawaiController::class, 'saveImportPreview'])
            ->name('pegawai.import.save');

        /*
        |--------------------------------------------------------------------------
        | Detail Pegawai Admin
        |--------------------------------------------------------------------------
        | Taruh paling bawah supaya /pegawai/create tidak ketangkap sebagai NIP.
        */
        Route::get('/pegawai/{pegawai:nip}', [PegawaiController::class, 'show'])
            ->whereNumber('pegawai')
            ->name('pegawai.show');

        /*
        |--------------------------------------------------------------------------
        | Jabatan - Admin
        |--------------------------------------------------------------------------
        */
        Route::resource('jabatan', JabatanController::class);

        Route::get('/jabatan/{jabatan}/print', [JabatanController::class, 'print'])
            ->whereNumber('jabatan')
            ->name('jabatan.print');

        Route::get('/jabatan/{jabatan}/export/pdf', [JabatanController::class, 'exportPdf'])
            ->whereNumber('jabatan')
            ->name('jabatan.export.pdf');

        Route::get('/jabatan/{jabatan}/export/excel', [JabatanController::class, 'exportExcel'])
            ->whereNumber('jabatan')
            ->name('jabatan.export.excel');
    });

/*
|--------------------------------------------------------------------------
| HCM AREA
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:hcm'])
    ->prefix('hcm')
    ->name('hcm.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard HCM
        |--------------------------------------------------------------------------
        */
        Route::get('/dashboard', [HcmController::class, 'index'])
            ->name('dashboard');

        Route::get('/dashboard/stats', [HcmController::class, 'stats'])
            ->name('dashboard.stats');

        /*
        |--------------------------------------------------------------------------
        | Pengajuan Perubahan - HCM
        |--------------------------------------------------------------------------
        */
        Route::get('/pengajuan', [PengajuanPerubahanController::class, 'indexReviewer'])
            ->name('pengajuan.index');

        Route::get('/pengajuan/{pengajuan}', [PengajuanPerubahanController::class, 'showReviewer'])
            ->name('pengajuan.show');

        Route::match(['post', 'patch'], '/pengajuan/{pengajuan}/proses', [PengajuanPerubahanController::class, 'proses'])
            ->name('pengajuan.proses');

        Route::match(['post', 'patch'], '/pengajuan/{pengajuan}/terima', [PengajuanPerubahanController::class, 'terima'])
            ->name('pengajuan.terima');

        Route::match(['post', 'patch'], '/pengajuan/{pengajuan}/tolak', [PengajuanPerubahanController::class, 'tolak'])
            ->name('pengajuan.tolak');

        Route::delete('/pengajuan/{pengajuan}', [PengajuanPerubahanController::class, 'destroyReviewer'])
            ->name('pengajuan.destroy');

        /*
        |--------------------------------------------------------------------------
        | Pegawai - HCM
        |--------------------------------------------------------------------------
        */
        Route::resource('pegawai', PegawaiController::class)
            ->except(['show']);

        Route::get('/pegawai/{pegawai:nip}/print', [PegawaiController::class, 'print'])
            ->whereNumber('pegawai')
            ->name('pegawai.print');

        Route::get('/pegawai/template-excel', [PegawaiController::class, 'downloadTemplate'])
            ->name('pegawai.templateExcel');

        Route::get('/pegawai/import-excel', fn () => view('pegawai.import_excel'))
            ->name('pegawai.import.form');

        Route::post('/pegawai/import-excel/preview', [PegawaiController::class, 'importPreview'])
            ->name('pegawai.import.preview');

        Route::get('/pegawai/import-excel/preview/show', [PegawaiController::class, 'showImportPreview'])
            ->name('pegawai.importPreview.show');

        Route::post('/pegawai/import-excel/save', [PegawaiController::class, 'saveImportPreview'])
            ->name('pegawai.import.save');

        /*
        |--------------------------------------------------------------------------
        | Detail Pegawai HCM
        |--------------------------------------------------------------------------
        | Taruh paling bawah supaya /pegawai/create tidak ketangkap sebagai NIP.
        */
        Route::get('/pegawai/{pegawai:nip}', [PegawaiController::class, 'show'])
            ->whereNumber('pegawai')
            ->name('pegawai.show');

        /*
        |--------------------------------------------------------------------------
        | Jabatan - HCM
        |--------------------------------------------------------------------------
        */
        Route::resource('jabatan', JabatanController::class);

        Route::get('/jabatan/{jabatan}/print', [JabatanController::class, 'print'])
            ->whereNumber('jabatan')
            ->name('jabatan.print');

        Route::get('/jabatan/{jabatan}/export/pdf', [JabatanController::class, 'exportPdf'])
            ->whereNumber('jabatan')
            ->name('jabatan.export.pdf');

        Route::get('/jabatan/{jabatan}/export/excel', [JabatanController::class, 'exportExcel'])
            ->whereNumber('jabatan')
            ->name('jabatan.export.excel');
    });

/*
|--------------------------------------------------------------------------
| STRUKTUR ORGANISASI
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,hcm'])->group(function () {
    Route::get('/struktur-organisasi', fn () => view('struktur.index'))
        ->name('struktur.index');
});

/*
|--------------------------------------------------------------------------
| PEGAWAI AREA
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:pegawai'])->group(function () {

    Route::get('/pegawai-saya', function () {
        $nip = auth()->user()->nip ?? auth()->user()->username;

        return redirect()->route('pegawai.show', $nip);
    })->name('pegawai.saya');

    /*
    |--------------------------------------------------------------------------
    | Edit Data Pegawai Sendiri
    |--------------------------------------------------------------------------
    */
    Route::get('/pegawai/{pegawai:nip}/edit', [PegawaiController::class, 'edit'])
        ->whereNumber('pegawai')
        ->name('pegawai.edit');

    /*
    |--------------------------------------------------------------------------
    | Pengajuan Perubahan - Pegawai
    |--------------------------------------------------------------------------
    */
    Route::get('/pengajuan-perubahan', [PengajuanPerubahanController::class, 'indexPegawai'])
        ->name('pegawai.pengajuan.index');

    Route::get('/pengajuan-perubahan/create', [PengajuanPerubahanController::class, 'createPegawai'])
        ->name('pegawai.pengajuan.create');

    Route::post('/pengajuan-perubahan', [PengajuanPerubahanController::class, 'storePegawai'])
        ->name('pegawai.pengajuan.store');

    Route::get('/pengajuan-perubahan/{pengajuan}', [PengajuanPerubahanController::class, 'showPegawai'])
        ->name('pegawai.pengajuan.show');

    /*
    |--------------------------------------------------------------------------
    | Change Password - Pegawai
    |--------------------------------------------------------------------------
    */
    Route::get('/change-password', [PegawaiController::class, 'showChangePasswordForm'])
        ->name('change-password');

    Route::post('/change-password', [PegawaiController::class, 'changePassword'])
        ->name('change-password.update');
});