<style>
    .header-main {
        height: 50px;
        background: #fff;
        position: fixed;
        top: 0;
        right: 0;
        left: var(--sidebar-width);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
        transition: var(--transition);
        box-shadow: 0 2px 5px rgba(0,0,0,0.06);
    }

    body.sidebar-mini .header-main {
        left: var(--sidebar-mini);
    }

    .header-user-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .avatar-header {
        width: 32px;
        height: 32px;
        min-width: 32px;
        min-height: 32px;
        border-radius: 50%;
        background-color: #78845f;
        font-size: 12px;
        overflow: hidden;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: bold;
    }

    .avatar-header img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
        border-radius: 50%;
    }

    @media (max-width: 992px) {
        .header-main {
            left: 0 !important;
        }
    }
</style>

@php
    use App\Models\Pegawai;
    use Illuminate\Support\Facades\Route;

    $userLogin = auth()->user();

    $fotoHeader = null;
    $pegawaiLogin = null;

    $roleLogin = data_get($userLogin, 'role') ?? session('role') ?? 'pegawai';

    /*
    |--------------------------------------------------------------------------
    | DEFAULT HEADER
    |--------------------------------------------------------------------------
    | Untuk admin dan HCM, header dibuat seperti header lama.
    | Jadi tidak ikut berubah saat buka halaman pegawai lain.
    |--------------------------------------------------------------------------
    */
    $namaHeader =
        data_get($userLogin, 'name')
        ?? data_get($userLogin, 'nama')
        ?? data_get($userLogin, 'username')
        ?? session('nama')
        ?? session('name')
        ?? 'Pengguna';

    $roleHeader =
        data_get($userLogin, 'role')
        ?? session('role')
        ?? 'Admin';

    /*
    |--------------------------------------------------------------------------
    | KHUSUS ROLE PEGAWAI
    |--------------------------------------------------------------------------
    | Kalau yang login adalah pegawai, baru ambil data dari tb_karyawan
    | berdasarkan NIP akun login dari tb_daftar.
    |--------------------------------------------------------------------------
    */
    if ($roleLogin === 'pegawai') {
        $nipLogin =
            data_get($userLogin, 'nip')
            ?? data_get($userLogin, 'username')
            ?? session('nip')
            ?? session('login_nip')
            ?? data_get(session('user'), 'nip')
            ?? data_get(session('pegawai'), 'nip')
            ?? null;

        if ($nipLogin) {
            $pegawaiLogin = Pegawai::where('nip', $nipLogin)->first();
        }

        if ($pegawaiLogin) {
            $namaHeader = $pegawaiLogin->nama ?? $namaHeader;

            /*
            |--------------------------------------------------------------------------
            | Teks kecil di bawah nama
            |--------------------------------------------------------------------------
            | Untuk pegawai, lebih bagus tampilkan jabatan aktif.
            | Kalau jabatan kosong, baru tampilkan role pegawai.
            |--------------------------------------------------------------------------
            */
            $roleHeader =
                $pegawaiLogin->jabatan
                ?? data_get($userLogin, 'role')
                ?? session('role')
                ?? 'Pegawai';

            /*
            |--------------------------------------------------------------------------
            | FOTO PEGAWAI
            |--------------------------------------------------------------------------
            | Foto bisa berada di:
            | - storage/app/public/karyawan/...
            | - storage/app/public/pengajuan/foto/...
            | - storage/app/karyawan/...
            | - storage/app/pengajuan/foto/...
            | - public/karyawan/...
            | - public/pengajuan/foto/...
            |--------------------------------------------------------------------------
            */
            if (!empty($pegawaiLogin->foto)) {
                $fotoDb = str_replace('\\', '/', $pegawaiLogin->foto);
                $fotoDb = ltrim($fotoDb, '/');

                $fotoDb = preg_replace('#^storage/#', '', $fotoDb);
                $fotoDb = preg_replace('#^public/#', '', $fotoDb);

                $namaFile = basename($fotoDb);

                $opsiFoto = [
                    $fotoDb,
                    'karyawan/' . $namaFile,
                    'pengajuan/foto/' . $namaFile,
                ];

                foreach ($opsiFoto as $pathFoto) {
                    $pathFoto = str_replace('\\', '/', $pathFoto);
                    $pathFoto = ltrim($pathFoto, '/');

                    $pathFoto = preg_replace('#^storage/#', '', $pathFoto);
                    $pathFoto = preg_replace('#^public/#', '', $pathFoto);

                    $storagePublicPath = storage_path('app/public/' . $pathFoto);
                    $storageAppPath = storage_path('app/' . $pathFoto);
                    $publicPath = public_path($pathFoto);

                    if (file_exists($storagePublicPath) && is_file($storagePublicPath)) {
                        if (Route::has('foto.pegawai')) {
                            $fotoHeader = route('foto.pegawai', ['path' => $pathFoto]) . '?v=' . filemtime($storagePublicPath);
                        } else {
                            $fotoHeader = asset('storage/' . $pathFoto) . '?v=' . filemtime($storagePublicPath);
                        }

                        break;
                    }

                    if (file_exists($storageAppPath) && is_file($storageAppPath)) {
                        if (Route::has('foto.pegawai')) {
                            $fotoHeader = route('foto.pegawai', ['path' => $pathFoto]) . '?v=' . filemtime($storageAppPath);
                        } else {
                            $fotoHeader = asset('storage/' . $pathFoto) . '?v=' . filemtime($storageAppPath);
                        }

                        break;
                    }

                    if (file_exists($publicPath) && is_file($publicPath)) {
                        if (Route::has('foto.pegawai')) {
                            $fotoHeader = route('foto.pegawai', ['path' => $pathFoto]) . '?v=' . filemtime($publicPath);
                        } else {
                            $fotoHeader = asset($pathFoto) . '?v=' . filemtime($publicPath);
                        }

                        break;
                    }
                }
            }
        }
    }
@endphp

<header class="header-main">
    <div class="d-flex align-items-center">
        {{-- Tombol Toggle Sidebar --}}
        <button class="btn btn-sm fs-5 border-0 me-2 text-muted" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
    </div>

    <div class="header-user-info">
        <div class="text-end d-none d-md-block">
            <p class="mb-0 fw-bold lh-1" style="font-size: 12px;">
                {{ $namaHeader }}
            </p>

            <small class="text-uppercase text-muted fw-bold" style="font-size: 9px; letter-spacing: 0.5px;">
                {{ $roleHeader }}
            </small>
        </div>

        <div class="avatar-header shadow-sm">
            @if ($roleLogin === 'pegawai' && $fotoHeader)
                <img src="{{ $fotoHeader }}" alt="Foto Pegawai">
            @else
                {{ strtoupper(substr($namaHeader ?? 'P', 0, 1)) }}
            @endif
        </div>
    </div>
</header>