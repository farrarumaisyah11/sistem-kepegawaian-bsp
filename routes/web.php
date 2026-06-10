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
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Route khusus untuk menampilkan foto pegawai dari folder storage/public
    |--------------------------------------------------------------------------
    | Dipakai oleh header:
    | route('foto.pegawai', ['path' => $pathFoto])
    |
    | Bisa membaca file dari:
    | - storage/app/public/karyawan/...
    | - storage/app/public/pengajuan/foto/...
    | - storage/app/karyawan/...
    | - storage/app/pengajuan/foto/...
    | - public/karyawan/...
    | - public/pengajuan/foto/...
    |--------------------------------------------------------------------------
    */
    Route::get('/foto-pegawai/{path}', function ($path) {
        $path = rawurldecode($path);
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        // Bersihkan awalan kalau ada
        $path = preg_replace('#^storage/#', '', $path);
        $path = preg_replace('#^public/#', '', $path);

        // Keamanan: cegah akses folder/file sembarangan
        if (str_contains($path, '..')) {
            abort(403);
        }

        $allowedFolders = [
            'karyawan/',
            'pengajuan/foto/',
        ];

        $isAllowed = false;

        foreach ($allowedFolders as $folder) {
            if (str_starts_with($path, $folder)) {
                $isAllowed = true;
                break;
            }
        }

        if (!$isAllowed) {
            abort(403);
        }

        $fileCandidates = [
            storage_path('app/public/' . $path),
            storage_path('app/' . $path),
            public_path($path),
        ];

        foreach ($fileCandidates as $filePath) {
            if (file_exists($filePath) && is_file($filePath)) {
                return response()->file($filePath);
            }
        }

        abort(404);
    })->where('path', '.*')->name('foto.pegawai');

    Route::get('/pegawai/foto/{pegawai:nip}', [PegawaiController::class, 'foto'])
        ->whereNumber('pegawai')
        ->name('pegawai.foto');

    Route::get('/pegawai/job-description', [PegawaiController::class, 'jobDescription'])
        ->middleware('role:pegawai')
        ->name('pegawai.job-description');

    Route::get('/pegawai/job-description/{assignment}/detail', [PegawaiController::class, 'jobDescriptionDetail'])
        ->middleware('role:pegawai')
        ->whereNumber('assignment')
        ->name('pegawai.job-description.detail');

    Route::post('/pegawai/job-description/{assignment}/acknowledge', [PegawaiController::class, 'acknowledgeJobDescription'])
        ->middleware('role:pegawai')
        ->whereNumber('assignment')
        ->name('pegawai.job-description.acknowledge');

    Route::get('/pegawai/{pegawai:nip}', [PegawaiController::class, 'show'])
        ->whereNumber('pegawai')
        ->name('pegawai.show');
});

/*
|--------------------------------------------------------------------------
| APPROVAL JOB DESCRIPTION VIA QR
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,hcm'])
    ->prefix('approval-jabatan')
    ->name('jabatan.approval.')
    ->group(function () {

        Route::get('/{jabatan}', [JabatanController::class, 'approvalPage'])
            ->whereNumber('jabatan')
            ->name('page');

        Route::get('/{jabatan}/qr', [JabatanController::class, 'approvalQr'])
            ->whereNumber('jabatan')
            ->name('qr');
    });

Route::middleware(['auth', 'role:hcm,pegawai'])
    ->prefix('approval-jabatan')
    ->name('jabatan.approval.')
    ->group(function () {

        Route::get('/{jabatan}/{token}', [JabatanController::class, 'approvalScan'])
            ->whereNumber('jabatan')
            ->where('token', '[A-Za-z0-9\-]+')
            ->name('scan');

        Route::get('/{jabatan}/{token}/detail', [JabatanController::class, 'approvalDetail'])
            ->whereNumber('jabatan')
            ->where('token', '[A-Za-z0-9\-]+')
            ->name('detail');

        Route::post('/{jabatan}/{token}', [JabatanController::class, 'approvalApprove'])
            ->whereNumber('jabatan')
            ->where('token', '[A-Za-z0-9\-]+')
            ->name('approve');

        Route::post('/{jabatan}/{token}/confirm-final', [JabatanController::class, 'approvalConfirmFinal'])
            ->whereNumber('jabatan')
            ->where('token', '[A-Za-z0-9\-]+')
            ->middleware('role:hcm')
            ->name('confirm-final');
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

        Route::get('/dashboard', [AdminController::class, 'index'])
            ->name('dashboard');

        Route::get('/dashboard/stats', [AdminController::class, 'stats'])
            ->name('dashboard.stats');

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

        Route::get('/pegawai/{pegawai:nip}', [PegawaiController::class, 'show'])
            ->whereNumber('pegawai')
            ->name('pegawai.show');

        Route::resource('jabatan', JabatanController::class);

        Route::get('/jabatan/{jabatan}/print', [JabatanController::class, 'print'])
            ->whereNumber('jabatan')
            ->name('jabatan.print');

        Route::get('/jabatan/{jabatan}/export/excel', [JabatanController::class, 'exportExcel'])
            ->whereNumber('jabatan')
            ->name('jabatan.export.excel');

        Route::get('/jabatan/{jabatan}/versions', [JabatanController::class, 'versions'])
            ->whereNumber('jabatan')
            ->name('jabatan.versions');

        Route::get('/jabatan/{jabatan}/versions/{version}', [JabatanController::class, 'showVersion'])
            ->whereNumber('jabatan')
            ->whereNumber('version')
            ->name('jabatan.versions.show');

        Route::post('/jabatan/{jabatan}/apply-approved-version', [JabatanController::class, 'applyApprovedVersionToPegawai'])
            ->whereNumber('jabatan')
            ->name('jabatan.apply-approved-version');
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

        Route::get('/dashboard', [HcmController::class, 'index'])
            ->name('dashboard');

        Route::get('/dashboard/stats', [HcmController::class, 'stats'])
            ->name('dashboard.stats');

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

        Route::get('/pegawai/{pegawai:nip}', [PegawaiController::class, 'show'])
            ->whereNumber('pegawai')
            ->name('pegawai.show');

        Route::resource('jabatan', JabatanController::class);

        Route::get('/jabatan/{jabatan}/print', [JabatanController::class, 'print'])
            ->whereNumber('jabatan')
            ->name('jabatan.print');

        Route::get('/jabatan/{jabatan}/export/excel', [JabatanController::class, 'exportExcel'])
            ->whereNumber('jabatan')
            ->name('jabatan.export.excel');

        Route::get('/jabatan/{jabatan}/versions', [JabatanController::class, 'versions'])
            ->whereNumber('jabatan')
            ->name('jabatan.versions');

        Route::get('/jabatan/{jabatan}/versions/{version}', [JabatanController::class, 'showVersion'])
            ->whereNumber('jabatan')
            ->whereNumber('version')
            ->name('jabatan.versions.show');

        Route::post('/jabatan/{jabatan}/apply-approved-version', [JabatanController::class, 'applyApprovedVersionToPegawai'])
            ->whereNumber('jabatan')
            ->name('jabatan.apply-approved-version');
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

    Route::get('/pegawai/{pegawai:nip}/edit', [PegawaiController::class, 'edit'])
        ->whereNumber('pegawai')
        ->name('pegawai.edit');

    Route::get('/pengajuan-perubahan', [PengajuanPerubahanController::class, 'indexPegawai'])
        ->name('pegawai.pengajuan.index');

    Route::get('/pengajuan-perubahan/create', [PengajuanPerubahanController::class, 'createPegawai'])
        ->name('pegawai.pengajuan.create');

    Route::post('/pengajuan-perubahan', [PengajuanPerubahanController::class, 'storePegawai'])
        ->name('pegawai.pengajuan.store');

    Route::get('/pengajuan-perubahan/{pengajuan}', [PengajuanPerubahanController::class, 'showPegawai'])
        ->name('pegawai.pengajuan.show');

    Route::get('/change-password', [PegawaiController::class, 'showChangePasswordForm'])
        ->name('change-password');

    Route::post('/change-password', [PegawaiController::class, 'changePassword'])
        ->name('change-password.update');
});