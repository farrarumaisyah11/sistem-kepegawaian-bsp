<style>
    .header-main {
        height: 50px; /* sebelumnya pakai var(--header-height) */
        background: #fff;
        position: fixed;
        top: 0; right: 0;
        left: var(--sidebar-width);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px; /* diperkecil dari 30px */
        transition: var(--transition);
        box-shadow: 0 2px 5px rgba(0,0,0,0.06);
    }
    
    body.sidebar-mini .header-main { left: var(--sidebar-mini); }

    .header-user-info {
        display: flex;
        align-items: center;
        gap: 10px; /* sedikit dirapatkan */
    }

    @media (max-width: 992px) {
        .header-main { left: 0 !important; }
    }
</style>

<header class="header-main">
    <div class="d-flex align-items-center">
        {{-- Tombol Toggle Sidebar --}}
        <button class="btn btn-sm fs-5 border-0 me-2 text-muted" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
    </div>

    <div class="header-user-info">
        <div class="text-end d-none d-md-block">
            <p class="mb-0 fw-bold lh-1" style="font-size: 12px;">{{ auth()->user()->name ?? 'Pengguna' }}</p>
            <small class="text-uppercase text-muted fw-bold" style="font-size: 9px; letter-spacing: 0.5px;">{{ auth()->user()->role ?? 'Admin' }}</small>
        </div>
        <div class="avatar-header bg-olive-700 text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 32px; height: 32px; background-color: #78845f; font-size: 12px;">
            {{ strtoupper(substr(auth()->user()->name ?? 'P', 0, 1)) }}
        </div>
    </div>
</header>