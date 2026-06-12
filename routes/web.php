<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HcmController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\PengajuanPerubahanController;
use App\Http\Controllers\StrukturJabatanController;

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
| STRUKTUR JABATAN
|--------------------------------------------------------------------------
| Diletakkan global auth agar halaman struktur bisa diakses sesuai controller.
| Detail tetap dikontrol di controller StrukturJabatanController::show.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/struktur-jabatan', [StrukturJabatanController::class, 'index'])
        ->name('struktur-jabatan.index');

    Route::get('/struktur-jabatan/{id_jabatan}', [StrukturJabatanController::class, 'show'])
        ->whereNumber('id_jabatan')
        ->name('struktur-jabatan.show');
});

/*
|--------------------------------------------------------------------------
| GLOBAL ROUTE - FOTO, JOB DESCRIPTION PEGAWAI, DETAIL PEGAWAI
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/foto-pegawai/{path}', function ($path) {
        $path = rawurldecode($path);
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        $path = preg_replace('#^storage/#', '', $path);
        $path = preg_replace('#^public/#', '', $path);

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
| APPROVAL JOB DESCRIPTION - CORPORATE FLOW
|--------------------------------------------------------------------------
| Flow:
| 1. HCM/Admin membuat atau memperbarui jobdesc.
| 2. Sistem membuat draft_version_id dan approval_token baru.
| 3. Link approval pendek: /approval/jd/{token}.
| 4. Pegawai departemen terkait boleh approve awal.
| 5. Jika pegawai approve, status menjadi waiting_hcm_confirmation.
| 6. HCM melakukan final approval dari halaman show jabatan internal.
| 7. Jika HCM membuka link approval dan approve dari link, langsung approved final.
| 8. Setelah approved final, link approval tidak dipakai lagi untuk tindakan approval.
| 9. QR route tetap ada untuk backward compatibility, tetapi UI tidak wajib menampilkan QR.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/approval/jd/{token}', [JabatanController::class, 'approvalShortLink'])
        ->where('token', '[A-Za-z0-9\-]+')
        ->name('jabatan.approval.short');

    Route::get('/approval/jabatan/{jabatan}/qr', [JabatanController::class, 'approvalQr'])
        ->whereNumber('jabatan')
        ->name('jabatan.approval.qr');

    Route::post('/approval/jabatan/{jabatan}/record-share', [JabatanController::class, 'recordApprovalLinkShare'])
        ->whereNumber('jabatan')
        ->name('jabatan.approval.record-share');

    Route::get('/approval/jabatan/{jabatan}/{token}/detail', [JabatanController::class, 'approvalDetail'])
        ->whereNumber('jabatan')
        ->where('token', '[A-Za-z0-9\-]+')
        ->name('jabatan.approval.detail');

    Route::get('/approval/jabatan/{jabatan}/{token}', [JabatanController::class, 'approvalScan'])
        ->whereNumber('jabatan')
        ->where('token', '[A-Za-z0-9\-]+')
        ->name('jabatan.approval.scan');

    Route::post('/approval/jabatan/{jabatan}/{token}', [JabatanController::class, 'approvalApprove'])
        ->whereNumber('jabatan')
        ->where('token', '[A-Za-z0-9\-]+')
        ->name('jabatan.approval.approve');

    /*
    |--------------------------------------------------------------------------
    | Legacy confirm-final dari link approval
    |--------------------------------------------------------------------------
    | Final approval HCM sekarang dilakukan dari halaman show jabatan internal.
    | Route ini dipertahankan agar form/link lama tidak blank, tetapi diarahkan.
    |--------------------------------------------------------------------------
    */
    Route::post('/approval/jabatan/{jabatan}/{token}/confirm-final', function ($jabatan, $token) {
        return redirect()
            ->route('hcm.jabatan.show', $jabatan)
            ->withErrors([
                'approval' => 'Pengesahan final HCM sekarang dilakukan dari halaman detail jabatan, bukan dari halaman scan approval.',
            ]);
    })
        ->middleware('role:hcm')
        ->whereNumber('jabatan')
        ->where('token', '[A-Za-z0-9\-]+')
        ->name('jabatan.approval.confirm-final');
});

/*
|--------------------------------------------------------------------------
| BACKWARD COMPATIBILITY - APPROVAL PAGE GENERIC
|--------------------------------------------------------------------------
| Beberapa view lama masih memanggil route('jabatan.approval.page').
| Route ini cukup satu saja. Jangan diduplikasi.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,hcm'])->group(function () {
    Route::get('/approval-page/jabatan/{jabatan}', [JabatanController::class, 'approvalPage'])
        ->whereNumber('jabatan')
        ->name('jabatan.approval.page');
});

/*
|--------------------------------------------------------------------------
| LEGACY APPROVAL URL - REDIRECT
|--------------------------------------------------------------------------
| Agar URL lama /approval-jabatan/* tetap aman.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])
    ->prefix('approval-jabatan')
    ->name('jabatan.approval.legacy.')
    ->group(function () {
        Route::get('/{jabatan}', function ($jabatan) {
            $role = auth()->user()->role ?? 'hcm';

            if ($role === 'admin') {
                return redirect()->route('admin.jabatan.approval-page', $jabatan);
            }

            return redirect()->route('hcm.jabatan.approval-page', $jabatan);
        })
            ->middleware('role:admin,hcm')
            ->whereNumber('jabatan')
            ->name('page');

        Route::get('/{jabatan}/qr', function ($jabatan) {
            return redirect()->route('jabatan.approval.qr', $jabatan);
        })
            ->middleware('role:admin,hcm')
            ->whereNumber('jabatan')
            ->name('qr');

        Route::get('/{jabatan}/{token}/detail', function ($jabatan, $token) {
            return redirect()->route('jabatan.approval.detail', [
                'jabatan' => $jabatan,
                'token' => $token,
            ]);
        })
            ->middleware('role:hcm,pegawai')
            ->whereNumber('jabatan')
            ->where('token', '[A-Za-z0-9\-]+')
            ->name('detail');

        Route::get('/{jabatan}/{token}', function ($jabatan, $token) {
            return redirect()->route('jabatan.approval.scan', [
                'jabatan' => $jabatan,
                'token' => $token,
            ]);
        })
            ->middleware('role:hcm,pegawai')
            ->whereNumber('jabatan')
            ->where('token', '[A-Za-z0-9\-]+')
            ->name('scan');

        Route::post('/{jabatan}/{token}', [JabatanController::class, 'approvalApprove'])
            ->middleware('role:hcm,pegawai')
            ->whereNumber('jabatan')
            ->where('token', '[A-Za-z0-9\-]+')
            ->name('approve');

        Route::post('/{jabatan}/{token}/confirm-final', function ($jabatan, $token) {
            return redirect()
                ->route('hcm.jabatan.show', $jabatan)
                ->withErrors([
                    'approval' => 'Pengesahan final HCM sekarang dilakukan dari halaman detail jabatan, bukan dari halaman scan approval.',
                ]);
        })
            ->middleware('role:hcm')
            ->whereNumber('jabatan')
            ->where('token', '[A-Za-z0-9\-]+')
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

        Route::get('/jabatan/{jabatan}/approval', [JabatanController::class, 'approvalPage'])
            ->whereNumber('jabatan')
            ->name('jabatan.approval-page');

        /*
        |--------------------------------------------------------------------------
        | Apply approved jobdesc ke seluruh pegawai yang memegang jabatan ini
        |--------------------------------------------------------------------------
        | Admin boleh apply setelah final approved.
        | Controller tetap harus validasi bahwa versi sudah final approved.
        |--------------------------------------------------------------------------
        */
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

        Route::get('/jabatan/{jabatan}/approval', [JabatanController::class, 'approvalPage'])
            ->whereNumber('jabatan')
            ->name('jabatan.approval-page');

        /*
        |--------------------------------------------------------------------------
        | Final Approval HCM dari halaman show jabatan
        |--------------------------------------------------------------------------
        | Ini route utama untuk tombol Approve Final HCM.
        |--------------------------------------------------------------------------
        */
        Route::post('/jabatan/{jabatan}/approval/confirm-final', [JabatanController::class, 'approvalConfirmFinalFromShow'])
            ->whereNumber('jabatan')
            ->name('jabatan.approval.confirm-final-from-show');

        /*
        |--------------------------------------------------------------------------
        | Apply approved jobdesc ke seluruh pegawai yang memegang jabatan ini
        |--------------------------------------------------------------------------
        | Tombol ini baru boleh aktif jika jobdesc sudah approved final.
        |--------------------------------------------------------------------------
        */
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