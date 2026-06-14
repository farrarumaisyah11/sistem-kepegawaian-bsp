<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Approval') - {{ config('app.name', 'Sistem SDM') }}</title>

    <style>
        :root{
            --apv-primary:#59684a;
            --apv-primary-dark:#3f4d35;
            --apv-primary-soft:#eef2ea;
            --apv-border:#d7dfcc;
            --apv-border-soft:#e5e7eb;
            --apv-text:#111827;
            --apv-muted:#667085;
            --apv-bg:#ffffff;
            --apv-page:#f8faf7;
            --apv-success:#166534;
            --apv-success-bg:#dcfce7;
            --apv-warning:#92400e;
            --apv-warning-bg:#fef3c7;
            --apv-danger:#b42318;
            --apv-danger-bg:#fee4e2;
        }

        *{
            box-sizing:border-box;
        }

        html,
        body{
            margin:0;
            padding:0;
            min-height:100%;
            background:var(--apv-page);
            color:var(--apv-text);
            font-family:"Inter", "Segoe UI", Arial, sans-serif;
            line-height:1.5;
        }

        a{
            color:inherit;
        }

        .approval-shell{
            min-height:100vh;
            display:flex;
            flex-direction:column;
            background:var(--apv-page);
        }

        .approval-topbar{
            height:64px;
            background:#ffffff;
            position:fixed;
            top:0;
            left:0;
            right:0;
            z-index:1000;
            display:flex;
            align-items:center;
            box-shadow:0 2px 5px rgba(0,0,0,0.06);
            border-bottom:1px solid rgba(215,223,204,.75);
        }

        .approval-topbar-inner{
            width:100%;
            height:100%;
            padding:0 24px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:16px;
        }

        .approval-brand{
            display:flex;
            align-items:center;
            gap:0;
            min-width:0;
        }

        .approval-brand-logo{
            width:100px;
            height:100px;
            flex:0 0 100px;
            display:flex;
            align-items:center;
            justify-content:center;
            margin-right:-24px;
        }

        .approval-brand-logo img{
            width:100%;
            height:100%;
            object-fit:contain;
            display:block;
        }

        .approval-brand-text{
            min-width:0;
            display:flex;
            flex-direction:column;
            justify-content:center;
            line-height:1.15;
            margin-left:0;
        }

        .approval-brand-title{
            font-size:14px;
            font-weight:900;
            color:var(--apv-primary-dark);
            text-transform:uppercase;
            letter-spacing:.04em;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        .approval-brand-subtitle{
            font-size:11px;
            color:var(--apv-muted);
            margin-top:3px;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
            font-weight:600;
        }

        .approval-profile-area{
            position:relative;
            display:flex;
            align-items:center;
            flex-shrink:0;
        }

        .approval-profile-trigger{
            border:0;
            background:transparent;
            padding:0;
            display:inline-flex;
            align-items:center;
            gap:10px;
            cursor:pointer;
            border-radius:14px;
        }

        .approval-profile-trigger:focus{
            outline:none;
        }

        .approval-profile-trigger:hover .avatar-header,
        .approval-profile-trigger:focus .avatar-header{
            box-shadow:0 0 0 3px rgba(89,104,74,.13);
        }

        .approval-user-text{
            text-align:right;
        }

        .approval-user-name{
            margin:0;
            font-size:12px;
            line-height:1;
            font-weight:700;
            color:#111827;
            max-width:190px;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        .approval-user-role{
            display:block;
            margin-top:5px;
            font-size:9px;
            letter-spacing:.5px;
            text-transform:uppercase;
            font-weight:700;
            color:#667085;
            max-width:190px;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        .avatar-header{
            width:34px;
            height:34px;
            min-width:34px;
            min-height:34px;
            border-radius:50%;
            background-color:#78845f;
            font-size:12px;
            overflow:hidden;
            flex-shrink:0;
            display:flex;
            align-items:center;
            justify-content:center;
            color:#fff;
            font-weight:700;
            box-shadow:0 2px 6px rgba(0,0,0,.12);
            transition:.18s ease;
        }

        .avatar-header img{
            width:100%;
            height:100%;
            object-fit:cover;
            object-position:center;
            display:block;
            border-radius:50%;
        }

        .approval-profile-dropdown{
            position:absolute;
            top:calc(100% + 12px);
            right:0;
            width:240px;
            background:#fff;
            border:1px solid #e5e7eb;
            border-radius:16px;
            box-shadow:0 18px 35px rgba(15,23,42,.14);
            padding:12px;
            opacity:0;
            visibility:hidden;
            transform:translateY(-6px);
            transition:.18s ease;
            z-index:2200;
        }

        .approval-profile-dropdown.show{
            opacity:1;
            visibility:visible;
            transform:translateY(0);
        }

        .approval-profile-dropdown::before{
            content:"";
            position:absolute;
            right:14px;
            top:-7px;
            width:14px;
            height:14px;
            background:#fff;
            border-left:1px solid #e5e7eb;
            border-top:1px solid #e5e7eb;
            transform:rotate(45deg);
        }

        .approval-profile-dropdown-head{
            display:flex;
            align-items:center;
            gap:10px;
            padding:6px 4px 12px;
            border-bottom:1px solid #eef0ea;
            margin-bottom:8px;
        }

        .approval-profile-dropdown-avatar{
            width:38px;
            height:38px;
            min-width:38px;
            border-radius:50%;
            background-color:#78845f;
            color:#fff;
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:800;
            overflow:hidden;
        }

        .approval-profile-dropdown-avatar img{
            width:100%;
            height:100%;
            object-fit:cover;
            border-radius:50%;
            display:block;
        }

        .approval-profile-dropdown-name{
            font-size:13px;
            font-weight:700;
            color:#1f2937;
            line-height:1.25;
            margin:0;
            max-width:160px;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        .approval-profile-dropdown-role{
            display:block;
            margin-top:3px;
            font-size:10px;
            font-weight:700;
            color:#6b7280;
            text-transform:uppercase;
            letter-spacing:.45px;
            line-height:1.25;
            max-width:160px;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        .approval-logout-form{
            margin:0;
        }

        .approval-profile-dropdown-action{
            width:100%;
            border:0;
            background:transparent;
            display:flex;
            align-items:center;
            gap:10px;
            padding:10px;
            border-radius:12px;
            color:#374151;
            font-weight:700;
            font-size:13px;
            transition:.18s ease;
            text-align:left;
            cursor:pointer;
        }

        .approval-profile-dropdown-action:hover,
        .approval-profile-dropdown-action:focus{
            background:#f6f8f4;
            color:#334027;
            outline:none;
        }

        .approval-profile-dropdown-action.logout{
            color:#b42318;
        }

        .approval-profile-dropdown-action.logout:hover,
        .approval-profile-dropdown-action.logout:focus{
            background:#fff1f0;
            color:#b42318;
        }

        .approval-main{
            width:min(1120px, calc(100% - 32px));
            margin:0 auto;
            padding:92px 0 44px;
            flex:1;
        }

        .approval-panel{
            background:#ffffff;
            border:1px solid var(--apv-border);
            border-radius:20px;
            box-shadow:0 14px 34px rgba(30,41,59,.06);
            overflow:hidden;
        }

        .approval-panel-head{
            padding:22px 24px;
            border-bottom:1px solid var(--apv-border);
            background:#ffffff;
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            gap:16px;
            flex-wrap:wrap;
        }

        .approval-eyebrow{
            color:var(--apv-primary);
            font-size:11px;
            font-weight:900;
            letter-spacing:.16em;
            text-transform:uppercase;
            margin-bottom:8px;
        }

        .approval-title{
            margin:0;
            color:var(--apv-primary-dark);
            font-size:24px;
            line-height:1.2;
            font-weight:900;
        }

        .approval-desc{
            margin:8px 0 0;
            color:var(--apv-muted);
            font-size:14px;
            max-width:680px;
        }

        .approval-panel-body{
            padding:24px;
        }

        .approval-grid-2{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:18px;
        }

        .approval-grid-1{
            display:grid;
            grid-template-columns:1fr;
            gap:18px;
        }

        .approval-card{
            background:#ffffff;
            border:1px solid var(--apv-border);
            border-radius:16px;
            overflow:hidden;
        }

        .approval-card-title{
            padding:13px 16px;
            background:var(--apv-primary-soft);
            border-bottom:1px solid var(--apv-border);
            color:var(--apv-primary-dark);
            font-size:13px;
            font-weight:800;
            display:flex;
            justify-content:space-between;
            gap:12px;
            align-items:center;
        }

        .approval-card-body{
            padding:16px;
        }

        .approval-table{
            width:100%;
            border-collapse:collapse;
            table-layout:fixed;
        }

        .approval-table th,
        .approval-table td{
            border:1px solid var(--apv-border);
            padding:10px 12px;
            vertical-align:top;
            font-size:13px;
            word-break:break-word;
        }

        .approval-table th{
            width:38%;
            background:#f7f9f2;
            color:#344054;
            font-weight:800;
            text-align:left;
        }

        .approval-table td{
            color:var(--apv-text);
            font-weight:600;
        }

        .approval-badge{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:6px 12px;
            border-radius:999px;
            font-size:12px;
            font-weight:800;
            border:1px solid transparent;
        }

        .approval-badge.approved{
            color:var(--apv-success);
            background:var(--apv-success-bg);
            border-color:#86efac;
        }

        .approval-badge.pending{
            color:var(--apv-warning);
            background:var(--apv-warning-bg);
            border-color:#fde68a;
        }

        .approval-badge.danger{
            color:var(--apv-danger);
            background:var(--apv-danger-bg);
            border-color:#fda29b;
        }

        .approval-btn{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:8px;
            padding:10px 16px;
            border-radius:12px;
            border:1px solid var(--apv-border);
            background:#ffffff;
            color:#344054;
            font-weight:700;
            font-size:13px;
            text-decoration:none;
            cursor:pointer;
            min-height:42px;
        }

        .approval-btn:hover{
            background:#f8faf7;
        }

        .approval-btn.primary{
            border-color:var(--apv-primary-dark);
            background:var(--apv-primary-dark);
            color:#ffffff;
        }

        .approval-btn.primary:hover{
            background:#34402d;
        }

        .approval-btn.success{
            border-color:#15803d;
            background:#15803d;
            color:#ffffff;
        }

        .approval-btn.success:hover{
            background:#166534;
        }

        .approval-btn:disabled{
            cursor:not-allowed;
            opacity:.65;
        }

        .approval-actions{
            display:flex;
            gap:10px;
            align-items:center;
            flex-wrap:wrap;
        }

        .approval-alert{
            border-radius:14px;
            padding:12px 14px;
            font-size:13px;
            margin-bottom:16px;
            border:1px solid var(--apv-border);
        }

        .approval-alert.success{
            color:var(--apv-success);
            background:var(--apv-success-bg);
            border-color:#86efac;
        }

        .approval-alert.warning{
            color:var(--apv-warning);
            background:var(--apv-warning-bg);
            border-color:#fde68a;
        }

        .approval-alert.danger{
            color:var(--apv-danger);
            background:var(--apv-danger-bg);
            border-color:#fda29b;
        }

        .approval-form-label{
            display:block;
            margin-bottom:8px;
            color:#344054;
            font-size:13px;
            font-weight:800;
        }

        .approval-textarea,
        .approval-input{
            width:100%;
            border:1px solid var(--apv-border);
            border-radius:12px;
            padding:12px 14px;
            font:inherit;
            font-size:13px;
            color:var(--apv-text);
            background:#ffffff;
            outline:none;
        }

        .approval-textarea{
            min-height:110px;
            resize:vertical;
        }

        .approval-textarea:focus,
        .approval-input:focus{
            border-color:var(--apv-primary);
            box-shadow:0 0 0 3px rgba(89,104,74,.14);
        }

        .approval-link-row{
            display:flex;
            gap:10px;
            align-items:center;
        }

        .approval-link-row .approval-input{
            flex:1;
            min-width:0;
        }

        .approval-qr-wrap{
            display:flex;
            align-items:center;
            justify-content:center;
            flex-direction:column;
            gap:14px;
            text-align:center;
        }

        .approval-qr-box{
            width:296px;
            height:296px;
            border:1px solid var(--apv-border);
            border-radius:20px;
            display:flex;
            align-items:center;
            justify-content:center;
            background:#ffffff;
            padding:14px;
        }

        .approval-qr-box img{
            width:100%;
            height:100%;
            object-fit:contain;
            display:block;
        }

        .approval-note{
            color:var(--apv-muted);
            font-size:13px;
            line-height:1.6;
            max-width:620px;
        }

        .approval-footer{
            padding:16px 0 24px;
            text-align:center;
            color:#98a2b3;
            font-size:12px;
        }

        @media(max-width: 768px){
            .approval-topbar{
                height:60px;
            }

            .approval-topbar-inner{
                padding:0 14px;
            }

            .approval-brand-logo{
                width:60px;
                height:60px;
                flex:0 0 60px;
                margin-right:-14px;
            }

            .approval-brand-title{
                font-size:12px;
            }

            .approval-brand-subtitle{
                font-size:10px;
            }

            .approval-user-text{
                display:none;
            }

            .approval-profile-dropdown{
                width:220px;
                right:-2px;
            }

            .approval-main{
                width:min(100% - 20px, 1120px);
                padding-top:82px;
            }

            .approval-panel-head,
            .approval-panel-body{
                padding:18px;
            }

            .approval-grid-2{
                grid-template-columns:1fr;
            }

            .approval-title{
                font-size:21px;
            }

            .approval-table th,
            .approval-table td{
                display:block;
                width:100%;
            }

            .approval-table th{
                border-bottom:none;
            }

            .approval-link-row{
                flex-direction:column;
                align-items:stretch;
            }

            .approval-qr-box{
                width:240px;
                height:240px;
            }
        }

        @media print{
            .approval-topbar,
            .approval-footer{
                display:none !important;
            }

            .approval-main{
                padding:0;
                width:100%;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    @php
        use App\Models\Pegawai;
        use Illuminate\Support\Facades\Route;

        $userLogin = auth()->user();

        $fotoHeader = null;
        $pegawaiLogin = null;

        $roleLoginRaw =
            data_get($userLogin, 'role')
            ?? session('role')
            ?? 'pegawai';

        $roleLoginKey = strtolower(trim((string) $roleLoginRaw));

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
            ?? 'Approval Access';

        /*
        |--------------------------------------------------------------------------
        | Khusus Role Pegawai
        |--------------------------------------------------------------------------
        | Jika role login adalah pegawai, nama dan jabatan diambil dari tabel pegawai.
        | Untuk HCM/admin, data tetap memakai auth/session seperti sebelumnya.
        |--------------------------------------------------------------------------
        */
        if ($roleLoginKey === 'pegawai') {
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

        $inisialHeader = strtoupper(substr($namaHeader ?? 'P', 0, 1));
    @endphp

    <div class="approval-shell">
        <header class="approval-topbar">
            <div class="approval-topbar-inner">
                <div class="approval-brand">
                    <div class="approval-brand-logo">
                        <img src="{{ asset('images/logo_bsp.webp') }}" alt="Logo BSP">
                    </div>

                    <div class="approval-brand-text">
                        <div class="approval-brand-title">PT. Bumi Siak Pusako</div>
                    </div>
                </div>

                <div class="approval-profile-area" id="approvalProfileArea">
                    <button type="button"
                            class="approval-profile-trigger"
                            id="approvalProfileToggle"
                            aria-label="Menu Profil"
                            aria-expanded="false">
                        <div class="approval-user-text">
                            <p class="approval-user-name">
                                {{ $namaHeader }}
                            </p>

                            <small class="approval-user-role">
                                {{ $roleHeader }}
                            </small>
                        </div>

                        <div class="avatar-header">
                            @if ($roleLoginKey === 'pegawai' && $fotoHeader)
                                <img src="{{ $fotoHeader }}" alt="Foto Pegawai">
                            @else
                                {{ $inisialHeader }}
                            @endif
                        </div>
                    </button>

                    <div class="approval-profile-dropdown" id="approvalProfileDropdown">
                        <div class="approval-profile-dropdown-head">
                            <div class="approval-profile-dropdown-avatar">
                                @if ($roleLoginKey === 'pegawai' && $fotoHeader)
                                    <img src="{{ $fotoHeader }}" alt="Foto Pegawai">
                                @else
                                    {{ $inisialHeader }}
                                @endif
                            </div>

                            <div style="min-width:0;">
                                <p class="approval-profile-dropdown-name">
                                    {{ $namaHeader }}
                                </p>

                                <span class="approval-profile-dropdown-role">
                                    {{ $roleHeader }}
                                </span>
                            </div>
                        </div>

                        <form action="{{ route('logout') }}" method="POST" class="approval-logout-form">
                            @csrf
                            <button type="submit" class="approval-profile-dropdown-action logout">
                                <span aria-hidden="true">↪</span>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="approval-main">
            @if(session('success_auto'))
                <div class="approval-alert success">{{ session('success_auto') }}</div>
            @endif

            @if(session('warning'))
                <div class="approval-alert warning">{{ session('warning') }}</div>
            @endif

            @if($errors->any())
                <div class="approval-alert danger">
                    <span>Terjadi kesalahan:</span>
                    <ul style="margin:8px 0 0; padding-left:18px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="approval-footer">
            Dokumen approval ini diproses melalui Sistem Informasi SDM PT. Bumi Siak Pusako.
        </footer>
    </div>

    <script>
        (function () {
            if (window.__BSP_APPROVAL_PROFILE_READY__) {
                return;
            }

            window.__BSP_APPROVAL_PROFILE_READY__ = true;

            document.addEventListener('DOMContentLoaded', function () {
                const toggle = document.getElementById('approvalProfileToggle');
                const menu = document.getElementById('approvalProfileDropdown');
                const area = document.getElementById('approvalProfileArea');

                if (!toggle || !menu || !area) {
                    return;
                }

                function closeApprovalProfileDropdown() {
                    menu.classList.remove('show');
                    toggle.setAttribute('aria-expanded', 'false');
                }

                function openApprovalProfileDropdown() {
                    menu.classList.add('show');
                    toggle.setAttribute('aria-expanded', 'true');
                }

                toggle.addEventListener('click', function (event) {
                    event.stopPropagation();

                    if (menu.classList.contains('show')) {
                        closeApprovalProfileDropdown();
                    } else {
                        openApprovalProfileDropdown();
                    }
                });

                menu.addEventListener('click', function (event) {
                    event.stopPropagation();
                });

                document.addEventListener('click', function (event) {
                    if (!area.contains(event.target)) {
                        closeApprovalProfileDropdown();
                    }
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        closeApprovalProfileDropdown();
                    }
                });
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>