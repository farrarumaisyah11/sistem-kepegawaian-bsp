<style>
    .header-main {
        height: 50px;
        background: #fff;
        position: fixed;
        top: 0;
        right: 0;
        left: var(--sidebar-current-width, var(--sidebar-width));
        z-index: 1100;
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
        position: relative;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .profile-trigger {
        border: 0;
        background: transparent;
        padding: 0;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
    }

    .profile-trigger:focus {
        outline: none;
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
        border: 2px solid rgba(120,132,95,.15);
    }

    .avatar-header img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
        border-radius: 50%;
    }

    .sidebar-toggle-btn {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: .18s ease;
    }

    .sidebar-toggle-btn:hover,
    .sidebar-toggle-btn:focus {
        background: #f6f8f4;
        color: #334027 !important;
        outline: none;
    }

    .sidebar-toggle-btn.is-mobile-disabled {
        pointer-events: none;
        opacity: .38;
        cursor: not-allowed;
        background: transparent !important;
    }

    .profile-dropdown {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        width: 240px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 18px 35px rgba(15,23,42,.14);
        padding: 12px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-6px);
        transition: .18s ease;
        z-index: 2200;
    }

    .profile-dropdown.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .profile-dropdown::before {
        content: "";
        position: absolute;
        right: 14px;
        top: -7px;
        width: 14px;
        height: 14px;
        background: #fff;
        border-left: 1px solid #e5e7eb;
        border-top: 1px solid #e5e7eb;
        transform: rotate(45deg);
    }

    .profile-dropdown-head {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 6px 4px 12px;
        border-bottom: 1px solid #eef0ea;
        margin-bottom: 8px;
    }

    .profile-dropdown-avatar {
        width: 38px;
        height: 38px;
        min-width: 38px;
        border-radius: 50%;
        background-color: #78845f;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        overflow: hidden;
    }

    .profile-dropdown-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        display: block;
    }

    .profile-dropdown-name {
        font-size: 13px;
        font-weight: 800;
        color: #1f2937;
        line-height: 1.25;
        margin: 0;
    }

    .profile-dropdown-role {
        display: block;
        margin-top: 3px;
        font-size: 10px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: .45px;
        line-height: 1.25;
    }

    .profile-dropdown-action {
        width: 100%;
        border: 0;
        background: transparent;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 10px;
        border-radius: 12px;
        color: #374151;
        font-weight: 700;
        font-size: 13px;
        transition: .18s ease;
        text-align: left;
    }

    .profile-dropdown-action:hover,
    .profile-dropdown-action:focus {
        background: #f6f8f4;
        color: #334027;
        outline: none;
    }

    .profile-dropdown-action.logout {
        color: #b42318;
    }

    .profile-dropdown-action.logout:hover,
    .profile-dropdown-action.logout:focus {
        background: #fff1f0;
        color: #b42318;
    }

    @media (max-width: 992px) {
        .header-main {
            left: var(--sidebar-mini) !important;
            padding: 0 14px;
        }

        body.sidebar-mini .header-main {
            left: var(--sidebar-mini) !important;
        }

        .sidebar-toggle-btn {
            pointer-events: none;
            opacity: .38;
            cursor: not-allowed;
        }
    }

    @media (max-width: 576px) {
        .profile-dropdown {
            width: 220px;
            right: -4px;
        }
    }

    @media print {
        .header-main {
            display: none !important;
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

            $roleHeader =
                $pegawaiLogin->jabatan
                ?? data_get($userLogin, 'role')
                ?? session('role')
                ?? 'Pegawai';

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
        <button type="button"
                class="btn btn-sm fs-5 border-0 me-2 text-muted sidebar-toggle-btn"
                id="sidebarToggleButton"
                onclick="toggleSidebar()"
                aria-label="Toggle Sidebar">
            <i class="bi bi-list"></i>
        </button>
    </div>

    <div class="header-user-info" id="headerProfileArea">
        <button type="button"
                class="profile-trigger"
                id="profileDropdownToggle"
                aria-label="Menu Profil"
                aria-expanded="false">
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
        </button>

        <div class="profile-dropdown" id="profileDropdownMenu">
            <div class="profile-dropdown-head">
                <div class="profile-dropdown-avatar">
                    @if ($roleLogin === 'pegawai' && $fotoHeader)
                        <img src="{{ $fotoHeader }}" alt="Foto Pegawai">
                    @else
                        {{ strtoupper(substr($namaHeader ?? 'P', 0, 1)) }}
                    @endif
                </div>

                <div style="min-width:0;">
                    <p class="profile-dropdown-name">
                        {{ $namaHeader }}
                    </p>
                    <span class="profile-dropdown-role">
                        {{ $roleHeader }}
                    </span>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST" class="mb-0">
                @csrf
                <button type="submit" class="profile-dropdown-action logout">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </div>
</header>

<script>
(function () {
    if (window.__BSP_HEADER_READY__) {
        return;
    }

    window.__BSP_HEADER_READY__ = true;

    document.addEventListener('DOMContentLoaded', function () {
        const mobileQueryHeader = window.matchMedia('(max-width: 992px)');
        const sidebarToggleButton = document.getElementById('sidebarToggleButton');

        function syncSidebarToggleState() {
            if (!sidebarToggleButton) {
                return;
            }

            if (mobileQueryHeader.matches) {
                sidebarToggleButton.disabled = true;
                sidebarToggleButton.setAttribute('aria-disabled', 'true');
                sidebarToggleButton.classList.add('is-mobile-disabled');
                document.body.classList.add('sidebar-mini');
                document.body.setAttribute('data-sidebar-mode', 'mobile-mini');
            } else {
                sidebarToggleButton.disabled = false;
                sidebarToggleButton.setAttribute('aria-disabled', 'false');
                sidebarToggleButton.classList.remove('is-mobile-disabled');
            }
        }

        syncSidebarToggleState();

        if (mobileQueryHeader.addEventListener) {
            mobileQueryHeader.addEventListener('change', syncSidebarToggleState);
        }

        const toggle = document.getElementById('profileDropdownToggle');
        const menu = document.getElementById('profileDropdownMenu');
        const area = document.getElementById('headerProfileArea');

        if (!toggle || !menu || !area) {
            return;
        }

        function closeProfileDropdown() {
            menu.classList.remove('show');
            toggle.setAttribute('aria-expanded', 'false');
        }

        function openProfileDropdown() {
            menu.classList.add('show');
            toggle.setAttribute('aria-expanded', 'true');
        }

        toggle.addEventListener('click', function (event) {
            event.stopPropagation();

            if (menu.classList.contains('show')) {
                closeProfileDropdown();
            } else {
                openProfileDropdown();
            }
        });

        document.addEventListener('click', function (event) {
            if (!area.contains(event.target)) {
                closeProfileDropdown();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeProfileDropdown();
            }
        });
    });
})();
</script>