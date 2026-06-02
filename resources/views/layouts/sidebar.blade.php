<style>
    .sidebar {
        width: var(--sidebar-width);
        height: 100vh;
        position: fixed;
        top: 0; left: 0;
        z-index: 1050;
        background: linear-gradient(180deg, var(--olive-700), var(--olive-950));
        transition: var(--transition);
        display: flex;
        flex-direction: column;
        box-shadow: 4px 0 10px rgba(0,0,0,0.1);
    }

    .brand-box {
        height: var(--header-height);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 15px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .logo-lg { width: 140px; display: block; }
    .logo-sm { width: 38px; display: none; }

    .menu-scroll {
        flex-grow: 1;
        padding: 20px 12px;
        overflow-y: auto;
    }

    .menu-section {
        font-size: 10px;
        text-transform: uppercase;
        color: rgba(255,255,255,0.4);
        font-weight: 700;
        margin: 15px 0 8px 15px;
        letter-spacing: 1px;
    }

    .menu-link {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        color: rgba(255,255,255,0.7);
        text-decoration: none;
        border-radius: 12px;
        margin-bottom: 5px;
        position: relative;
        transition: 0.2s ease;
    }

    .menu-link i {
        font-size: 18px;
        min-width: 32px;
    }

    .menu-link:hover,
    .menu-link.active {
        background: rgba(255,255,255,0.15);
        color: var(--accent);
    }

    body.sidebar-mini .sidebar {
        width: var(--sidebar-mini);
    }

    body.sidebar-mini .logo-lg,
    body.sidebar-mini .nav-txt,
    body.sidebar-mini .menu-section {
        display: none;
    }

    body.sidebar-mini .logo-sm {
        display: block;
    }

    body.sidebar-mini .menu-link:hover::after {
        content: attr(data-name);
        position: absolute;
        left: 100%;
        margin-left: 10px;
        background: #2d3436;
        color: white;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 2000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .sidebar-footer {
        padding: 15px;
        border-top: 1px solid rgba(255,255,255,0.1);
    }

    .logout-btn {
        background: transparent;
        border: none;
        width: 100%;
        text-align: left;
    }

    .menu-badge {
        font-size: 10px;
        margin-left: auto;
    }
</style>

@php
    $role = auth()->user()->role ?? null;

    $notifCount = \App\Models\PengajuanPerubahan::whereIn('status', [
        'diajukan',
        'pending',
        'belum_diolah'
    ])->count();
@endphp

<div class="sidebar">
    <div class="brand-box">
        <img src="{{ asset('images/logo_bsp.webp') }}" class="logo-lg" alt="Logo">
        <img src="{{ asset('images/logo bsp.png') }}" class="logo-sm" alt="Logo">
    </div>

    <div class="menu-scroll">
        @auth

            {{-- ROLE ADMIN --}}
            @if($role === 'admin')
                <div class="menu-section">Menu</div>

                <a href="{{ route('admin.dashboard') }}"
                   class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                   data-name="Dashboard">
                    <i class="bi bi-speedometer2"></i>
                    <span class="nav-txt">Dashboard</span>
                </a>

                <a href="{{ route('admin.pegawai.create') }}"
                   class="menu-link {{ request()->routeIs('admin.pegawai.create') ? 'active' : '' }}"
                   data-name="Tambah Pegawai">
                    <i class="bi bi-person-plus"></i>
                    <span class="nav-txt">Tambah Pegawai</span>
                </a>

                <a href="{{ route('admin.pegawai.index') }}"
                   class="menu-link {{ request()->routeIs('admin.pegawai.index') ? 'active' : '' }}"
                   data-name="Daftar Pegawai">
                    <i class="bi bi-people"></i>
                    <span class="nav-txt">Daftar Pegawai</span>
                </a>

                <div class="menu-section">Approval</div>

                <a href="{{ route('admin.pengajuan.index') }}"
                   class="menu-link {{ request()->routeIs('admin.pengajuan.*') ? 'active' : '' }}"
                   data-name="Pengajuan Perubahan">
                    <i class="bi bi-envelope-paper"></i>
                    <span class="nav-txt">Pengajuan Perubahan</span>

                    @if($notifCount > 0)
                        <span class="badge bg-danger menu-badge">
                            {{ $notifCount }}
                        </span>
                    @endif
                </a>

                <div class="menu-section">Organisasi</div>

                <a href="{{ route('admin.jabatan.index') }}"
                   class="menu-link {{ request()->routeIs('admin.jabatan.*') ? 'active' : '' }}"
                   data-name="Daftar Jabatan">
                    <i class="bi bi-list-check"></i>
                    <span class="nav-txt">Daftar Jabatan</span>
                </a>
            @endif

            {{-- ROLE HCM --}}
            @if($role === 'hcm')
                <div class="menu-section">Menu</div>

                <a href="{{ route('hcm.dashboard') }}"
                   class="menu-link {{ request()->routeIs('hcm.dashboard') ? 'active' : '' }}"
                   data-name="Dashboard">
                    <i class="bi bi-speedometer2"></i>
                    <span class="nav-txt">Dashboard</span>
                </a>

                <div class="menu-section">Jabatan</div>

                <a href="{{ route('hcm.pegawai.index') }}"
                   class="menu-link {{ request()->routeIs('hcm.pegawai.*') ? 'active' : '' }}"
                   data-name="Daftar Pegawai">
                    <i class="bi bi-people"></i>
                    <span class="nav-txt">Daftar Pegawai</span>
                </a>

                <a href="{{ route('hcm.jabatan.index') }}"
                   class="menu-link {{ request()->routeIs('hcm.jabatan.*') ? 'active' : '' }}"
                   data-name="Daftar Jabatan">
                    <i class="bi bi-list-check"></i>
                    <span class="nav-txt">Daftar Jabatan</span>
                </a>

                <a href="{{ route('hcm.pengajuan.index') }}"
                   class="menu-link {{ request()->routeIs('hcm.pengajuan.*') ? 'active' : '' }}"
                   data-name="Pengajuan Perubahan">
                    <i class="bi bi-envelope-paper"></i>
                    <span class="nav-txt">Pengajuan Perubahan</span>

                    @if($notifCount > 0)
                        <span class="badge bg-danger menu-badge">
                            {{ $notifCount }}
                        </span>
                    @endif
                </a>

                @endif

            {{-- ROLE PEGAWAI --}}
            @if($role === 'pegawai')
                <div class="menu-section">Menu Utama</div>

                <a href="{{ route('pegawai.saya') }}"
                   class="menu-link {{ request()->routeIs('pegawai.saya') || request()->routeIs('pegawai.show') ? 'active' : '' }}"
                   data-name="Profil Saya">
                    <i class="bi bi-person-lines-fill"></i>
                    <span class="nav-txt">Profil Saya</span>
                </a>

                <a href="{{ route('pegawai.job-description') }}"
                class="menu-link {{ request()->routeIs('pegawai.job-description') ? 'active' : '' }}"
                data-name="Job Description">
                    <i class="bi bi-briefcase"></i>
                    <span class="nav-txt">Job Description</span>
                </a>

                <div class="menu-section">Layanan</div>

                <a href="{{ route('pegawai.pengajuan.index') }}"
                   class="menu-link {{ request()->routeIs('pegawai.pengajuan.index') || request()->routeIs('pegawai.pengajuan.show') ? 'active' : '' }}"
                   data-name="Riwayat Pengajuan">
                    <i class="bi bi-file-earmark-text"></i>
                    <span class="nav-txt">Riwayat Pengajuan</span>
                </a>

                <a href="{{ route('pegawai.pengajuan.create') }}"
                   class="menu-link {{ request()->routeIs('pegawai.pengajuan.create') ? 'active' : '' }}"
                   data-name="Ajukan Perubahan">
                    <i class="bi bi-pencil-square"></i>
                    <span class="nav-txt">Ajukan Perubahan</span>
                </a>
            @endif

        @endauth
    </div>

    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="menu-link logout-btn" data-name="Keluar">
                <i class="bi bi-box-arrow-right"></i>
                <span class="nav-txt">Keluar</span>
            </button>
        </form>
    </div>
</div>