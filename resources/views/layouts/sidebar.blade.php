<style>
    html,
    body {
        min-height: 100%;
    }

    body {
        --sidebar-current-width: var(--sidebar-width);
        position: relative;
        overflow-x: hidden;
    }

    body.sidebar-mini {
        --sidebar-current-width: var(--sidebar-mini);
    }

    .sidebar-page-rail {
        position: absolute;
        top: 0;
        left: 0;
        width: var(--sidebar-current-width);
        height: var(--sidebar-page-height, 100vh);
        min-height: 100vh;
        background: linear-gradient(180deg, var(--olive-700), var(--olive-950));
        z-index: 10;
        pointer-events: none;
        transition: var(--transition);
    }

    .sidebar {
        width: var(--sidebar-width);
        height: 100dvh;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1300;
        background: linear-gradient(180deg, var(--olive-700), var(--olive-950));
        transition: var(--transition);
        display: flex;
        flex-direction: column;
        box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        overflow: visible;
    }

    .brand-box {
        height: var(--header-height);
        min-height: var(--header-height);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 12px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        flex-shrink: 0;
        position: relative;
        z-index: 2;
    }

    .logo-lg {
        width: 140px;
        max-width: 100%;
        display: block;
        object-fit: contain;
    }

    .logo-sm {
        width: 38px;
        height: 38px;
        object-fit: contain;
        display: none;
    }

    .logo-fallback {
        display: none;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.16);
        color: var(--accent);
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .3px;
    }

    .menu-scroll {
        flex: 1 1 auto;
        padding: 20px 12px;
        overflow-y: auto;
        overflow-x: hidden;
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,.25) transparent;
        position: relative;
        z-index: 2;
    }

    .menu-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .menu-scroll::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,.25);
        border-radius: 999px;
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
        display: flex !important;
        align-items: center;
        padding: 12px 16px;
        color: rgba(255,255,255,0.7);
        text-decoration: none;
        border-radius: 12px;
        margin-bottom: 5px;
        position: relative;
        transition: 0.2s ease;
        width: 100%;
        min-height: 44px;
        border: 0;
        background: transparent;
        text-align: left;
        cursor: pointer;
    }

    .menu-link i {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        width: 32px;
        min-width: 32px;
        flex-shrink: 0;
        color: currentColor;
        line-height: 1;
    }

    .nav-txt {
        display: block;
        flex: 1;
        min-width: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: clip;
    }

    .menu-link.has-badge {
        padding-right: 46px;
    }

    .menu-link.has-badge .nav-txt {
        font-size: 15px;
        letter-spacing: -0.2px;
    }

    .menu-badge {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 10px;
        min-width: 22px;
        height: 18px;
        padding: 3px 6px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        z-index: 2;
    }

    .menu-link:hover,
    .menu-link:focus,
    .menu-link.active {
        background: rgba(255,255,255,0.15);
        color: var(--accent);
        outline: none;
    }

    body.sidebar-mini .sidebar {
        width: var(--sidebar-mini);
    }

    body.sidebar-mini .logo-lg,
    body.sidebar-mini .nav-txt,
    body.sidebar-mini .menu-section {
        display: none !important;
    }

    body.sidebar-mini .logo-sm {
        display: block !important;
    }

    body.sidebar-mini .brand-box {
        padding: 10px;
    }

    body.sidebar-mini .menu-scroll {
        padding: 20px 8px;
        overflow-x: hidden;
    }

    body.sidebar-mini .menu-link {
        justify-content: center;
        padding: 12px;
    }

    body.sidebar-mini .menu-link.has-badge {
        padding: 12px;
    }

    body.sidebar-mini .menu-link i {
        width: 44px;
        min-width: 44px;
        height: 44px;
        font-size: 19px;
    }

    body.sidebar-mini .menu-badge {
        display: none !important;
    }

    .sidebar-floating-tooltip {
        position: fixed;
        left: 0;
        top: 0;
        transform: translateY(-50%) translateX(-8px) scale(.96);
        background: linear-gradient(135deg, rgba(255,255,255,.98), rgba(247,249,242,.98));
        color: #26351d;
        padding: 11px 15px 11px 12px;
        border-radius: 16px;
        font-size: 13px;
        font-weight: 800;
        white-space: nowrap;
        max-width: calc(100vw - var(--sidebar-mini) - 24px);
        overflow: visible;
        z-index: 5000;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        border: 1px solid rgba(111,127,89,.18);
        box-shadow:
            0 18px 38px rgba(15,23,42,.20),
            0 8px 16px rgba(111,127,89,.10),
            inset 0 1px 0 rgba(255,255,255,.85);
        transition:
            opacity .18s ease,
            visibility .18s ease,
            transform .18s ease;
    }

    .sidebar-floating-tooltip.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(-50%) translateX(0) scale(1);
    }

    .sidebar-floating-tooltip::before {
        content: "";
        position: absolute;
        left: -7px;
        top: 50%;
        width: 14px;
        height: 14px;
        background: #ffffff;
        transform: translateY(-50%) rotate(45deg);
        border-left: 1px solid rgba(111,127,89,.14);
        border-bottom: 1px solid rgba(111,127,89,.14);
        border-radius: 3px;
    }

    .sidebar-floating-tooltip::after {
        content: "";
        position: absolute;
        left: 0;
        top: 12px;
        bottom: 12px;
        width: 4px;
        border-radius: 999px;
        background: linear-gradient(180deg, var(--accent), rgba(111,127,89,.75));
    }

    .sidebar-floating-tooltip-inner {
        display: flex;
        align-items: center;
        gap: 11px;
        position: relative;
        z-index: 2;
    }

    .sidebar-tooltip-icon {
        width: 34px;
        height: 34px;
        min-width: 34px;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(111,127,89,.18), rgba(243,201,75,.25));
        color: #5b6b45;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        box-shadow: inset 0 0 0 1px rgba(111,127,89,.12);
    }

    .sidebar-tooltip-text {
        display: flex;
        flex-direction: column;
        line-height: 1.15;
        padding-right: 2px;
    }

    .sidebar-tooltip-label {
        font-size: 13px;
        font-weight: 900;
        color: #1f2b16;
        max-width: 190px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sidebar-tooltip-caption {
        margin-top: 4px;
        font-size: 9px;
        letter-spacing: .09em;
        color: #6f7f59;
        text-transform: uppercase;
        font-weight: 900;
    }

    :is(
        .main-content,
        .content-wrapper,
        .page-wrapper,
        .app-main,
        .layout-main,
        .content-page,
        .main-panel,
        main.content,
        main.main-content
    ) {
        margin-left: var(--sidebar-current-width);
        width: calc(100% - var(--sidebar-current-width));
        transition: var(--transition);
    }

    @media (max-width: 992px) {
        body {
            --sidebar-current-width: var(--sidebar-mini);
        }

        .sidebar-page-rail {
            width: var(--sidebar-mini);
        }

        .sidebar {
            width: var(--sidebar-mini) !important;
        }

        .sidebar .logo-lg,
        .sidebar .nav-txt,
        .sidebar .menu-section {
            display: none !important;
        }

        .sidebar .logo-sm {
            display: block !important;
        }

        .sidebar .brand-box {
            padding: 10px;
        }

        .sidebar .menu-scroll {
            padding: 20px 8px;
            overflow-x: hidden;
        }

        .sidebar .menu-link {
            justify-content: center;
            padding: 12px;
        }

        .sidebar .menu-link.has-badge {
            padding: 12px;
        }

        .sidebar .menu-link i {
            width: 44px;
            min-width: 44px;
            height: 44px;
            font-size: 19px;
        }

        .sidebar .menu-badge {
            display: none !important;
        }

        :is(
            .main-content,
            .content-wrapper,
            .page-wrapper,
            .app-main,
            .layout-main,
            .content-page,
            .main-panel,
            main.content,
            main.main-content
        ) {
            margin-left: var(--sidebar-mini) !important;
            width: calc(100% - var(--sidebar-mini)) !important;
        }
    }

    @media print {
        .sidebar-page-rail,
        .sidebar,
        .sidebar-floating-tooltip {
            display: none !important;
        }

        :is(
            .main-content,
            .content-wrapper,
            .page-wrapper,
            .app-main,
            .layout-main,
            .content-page,
            .main-panel,
            main.content,
            main.main-content
        ) {
            margin-left: 0 !important;
            width: 100% !important;
        }
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

<div class="sidebar-page-rail" aria-hidden="true"></div>
<div class="sidebar-floating-tooltip" id="sidebarFloatingTooltip" aria-hidden="true"></div>

<aside class="sidebar" aria-label="Sidebar Navigation">
    <div class="brand-box">
        <img src="{{ asset('images/logo_bsp.webp') }}"
             class="logo-lg"
             alt="Logo BSP"
             onerror="this.style.display='none';">

        <img src="{{ asset('images/logo bsp.png') }}"
             class="logo-sm"
             alt="Logo BSP"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">

        <span class="logo-fallback">BSP</span>
    </div>

    <div class="menu-scroll">
        @auth
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
                   class="menu-link has-badge {{ request()->routeIs('admin.pengajuan.*') ? 'active' : '' }}"
                   data-name="Pengajuan Perubahan">
                    <i class="bi bi-envelope-paper"></i>
                    <span class="nav-txt">Pengajuan Perubahan</span>

                    @if($notifCount > 0)
                        <span class="badge bg-danger menu-badge">{{ $notifCount }}</span>
                    @endif
                </a>

                <div class="menu-section">Organisasi</div>

                <a href="{{ route('struktur-jabatan.index') }}"
                   class="menu-link {{ request()->routeIs('struktur-jabatan.*') ? 'active' : '' }}"
                   data-name="Struktur Organisasi">
                    <i class="bi bi-diagram-3"></i>
                    <span class="nav-txt">Struktur Organisasi</span>
                </a>
            @endif

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

                <a href="{{ route('struktur-jabatan.index') }}"
                   class="menu-link {{ request()->routeIs('struktur-jabatan.*') ? 'active' : '' }}"
                   data-name="Struktur Organisasi">
                    <i class="bi bi-diagram-3"></i>
                    <span class="nav-txt">Struktur Organisasi</span>
                </a>

                <a href="{{ route('hcm.pengajuan.index') }}"
                   class="menu-link has-badge {{ request()->routeIs('hcm.pengajuan.*') ? 'active' : '' }}"
                   data-name="Pengajuan Perubahan">
                    <i class="bi bi-envelope-paper"></i>
                    <span class="nav-txt">Pengajuan Perubahan</span>

                    @if($notifCount > 0)
                        <span class="badge bg-danger menu-badge">{{ $notifCount }}</span>
                    @endif
                </a>
            @endif

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

                <a href="{{ route('struktur-jabatan.index') }}"
                   class="menu-link {{ request()->routeIs('struktur-jabatan.*') ? 'active' : '' }}"
                   data-name="Struktur Organisasi">
                    <i class="bi bi-diagram-3"></i>
                    <span class="nav-txt">Struktur Organisasi</span>
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
</aside>

<script>
(function () {
    if (window.__BSP_SIDEBAR_READY__) {
        return;
    }

    window.__BSP_SIDEBAR_READY__ = true;

    const mobileQuery = window.matchMedia('(max-width: 992px)');

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function updateSidebarPageHeight() {
        const height = Math.max(
            document.body ? document.body.scrollHeight : 0,
            document.documentElement ? document.documentElement.scrollHeight : 0,
            window.innerHeight || 0
        );

        document.documentElement.style.setProperty('--sidebar-page-height', height + 'px');
    }

    function applySidebarModeFromStorage() {
        if (mobileQuery.matches) {
            document.body.classList.add('sidebar-mini');
            document.body.setAttribute('data-sidebar-mode', 'mobile-mini');
            return;
        }

        try {
            const savedMode = localStorage.getItem('bsp-sidebar-mode');

            if (savedMode === 'mini') {
                document.body.classList.add('sidebar-mini');
                document.body.setAttribute('data-sidebar-mode', 'desktop-mini');
            } else {
                document.body.classList.remove('sidebar-mini');
                document.body.setAttribute('data-sidebar-mode', 'desktop-full');
            }
        } catch (e) {
            document.body.classList.remove('sidebar-mini');
            document.body.setAttribute('data-sidebar-mode', 'desktop-full');
        }
    }

    function getSidebarWidth() {
        const rootStyles = getComputedStyle(document.documentElement);
        const bodyStyles = getComputedStyle(document.body);

        const mini = parseFloat(rootStyles.getPropertyValue('--sidebar-mini'));
        const current = parseFloat(bodyStyles.getPropertyValue('--sidebar-current-width'));

        if (mobileQuery.matches && Number.isFinite(mini)) {
            return mini;
        }

        if (Number.isFinite(current)) {
            return current;
        }

        return 70;
    }

    function isCompactSidebar() {
        return document.body.classList.contains('sidebar-mini') || mobileQuery.matches;
    }

    function showSidebarTooltip(link) {
        const tooltip = document.getElementById('sidebarFloatingTooltip');

        if (!tooltip || !link || !isCompactSidebar()) {
            return;
        }

        const label = link.getAttribute('data-name') || link.textContent.trim();
        const iconClass = link.querySelector('i')?.className || 'bi bi-circle';

        if (!label) {
            return;
        }

        const rect = link.getBoundingClientRect();
        const sidebarWidth = getSidebarWidth();

        tooltip.innerHTML = `
            <div class="sidebar-floating-tooltip-inner">
                <span class="sidebar-tooltip-icon">
                    <i class="${escapeHtml(iconClass)}"></i>
                </span>
                <span class="sidebar-tooltip-text">
                    <span class="sidebar-tooltip-label">${escapeHtml(label)}</span>
                    <span class="sidebar-tooltip-caption">Menu Sistem</span>
                </span>
            </div>
        `;

        tooltip.style.left = (sidebarWidth + 14) + 'px';
        tooltip.style.top = (rect.top + rect.height / 2) + 'px';
        tooltip.classList.add('show');

        window.clearTimeout(tooltip._hideTimer);
        tooltip._hideTimer = window.setTimeout(function () {
            tooltip.classList.remove('show');
        }, 1700);
    }

    function hideSidebarTooltip() {
        const tooltip = document.getElementById('sidebarFloatingTooltip');

        if (tooltip) {
            tooltip.classList.remove('show');
        }
    }

    window.toggleSidebar = function () {
        if (mobileQuery.matches) {
            document.body.classList.add('sidebar-mini');
            document.body.setAttribute('data-sidebar-mode', 'mobile-mini');
            updateSidebarPageHeight();
            return;
        }

        const nextIsMini = !document.body.classList.contains('sidebar-mini');

        document.body.classList.toggle('sidebar-mini', nextIsMini);
        document.body.setAttribute('data-sidebar-mode', nextIsMini ? 'desktop-mini' : 'desktop-full');

        try {
            localStorage.setItem('bsp-sidebar-mode', nextIsMini ? 'mini' : 'full');
        } catch (e) {
            // Abaikan localStorage error.
        }

        updateSidebarPageHeight();
    };

    function bindTooltipEvents() {
        document.querySelectorAll('.sidebar .menu-link').forEach(function (link) {
            if (link.dataset.tooltipBound === '1') {
                return;
            }

            link.dataset.tooltipBound = '1';

            link.addEventListener('mouseenter', function () {
                showSidebarTooltip(link);
            });

            link.addEventListener('mouseleave', function () {
                hideSidebarTooltip();
            });

            link.addEventListener('focus', function () {
                showSidebarTooltip(link);
            });

            link.addEventListener('blur', function () {
                hideSidebarTooltip();
            });

            link.addEventListener('touchstart', function () {
                showSidebarTooltip(link);
            }, { passive: true });
        });
    }

    function initSidebar() {
        applySidebarModeFromStorage();
        bindTooltipEvents();
        updateSidebarPageHeight();

        window.addEventListener('load', updateSidebarPageHeight);
        window.addEventListener('resize', updateSidebarPageHeight);
        window.addEventListener('scroll', updateSidebarPageHeight, { passive: true });

        if ('ResizeObserver' in window && document.body) {
            const observer = new ResizeObserver(updateSidebarPageHeight);
            observer.observe(document.body);
        }

        setInterval(updateSidebarPageHeight, 1200);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebar);
    } else {
        initSidebar();
    }

    if (mobileQuery.addEventListener) {
        mobileQuery.addEventListener('change', function () {
            applySidebarModeFromStorage();
            hideSidebarTooltip();
            updateSidebarPageHeight();
        });
    }
})();
</script>