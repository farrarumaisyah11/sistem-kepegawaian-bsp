@extends('layouts.app')

@section('title', 'Struktur Jabatan')

@section('content')
@php
    $isFullscreen = request()->boolean('fullscreen');

    $fullscreenParams = request()->query();
    $fullscreenParams['fullscreen'] = 1;

    $normalParams = request()->query();
    unset($normalParams['fullscreen']);

    /*
    |--------------------------------------------------------------------------
    | Logo kop print
    |--------------------------------------------------------------------------
    | Sesuaikan nama file di public/images jika berbeda.
    | onerror pada <img> akan menyembunyikan logo jika file belum ada.
    |--------------------------------------------------------------------------
    */
    $logoBsp = asset('images/logo-bsp.png');
    $logoSkk = asset('images/logo-skk-migas.png');
@endphp

<style>
    :root {
        --corp-primary: #273957;
        --corp-secondary: #6b775c;
        --corp-accent: #f4c542;
        --corp-border: #d9e0d2;
        --corp-soft: #f6f8f4;
        --corp-muted: #6b7280;
        --corp-dark: #111827;
        --corp-white: #ffffff;
        --org-line: #8b967d;
        --org-danger: #ef4444;
        --org-success: #16a34a;
    }

    body.org-fullscreen-mode {
        background: #f7f9f5 !important;
    }

    body.org-fullscreen-mode .sidebar,
    body.org-fullscreen-mode .topbar,
    body.org-fullscreen-mode .navbar,
    body.org-fullscreen-mode .main-header,
    body.org-fullscreen-mode .app-header,
    body.org-fullscreen-mode header {
        display: none !important;
    }

    body.org-fullscreen-mode .main-content,
    body.org-fullscreen-mode .page-content,
    body.org-fullscreen-mode .content-wrapper,
    body.org-fullscreen-mode .container,
    body.org-fullscreen-mode .container-fluid {
        margin-left: 0 !important;
        margin-right: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
    }

    .org-page {
        min-height: calc(100vh - 80px);
        padding-bottom: 40px;
        background: #f7f9f5;
        overflow-x: hidden;
    }

    .org-page.is-fullscreen {
        min-height: 100vh;
        padding: 18px 18px 34px;
    }

    .org-page-header {
        background: linear-gradient(135deg, #273957 0%, #3f4a32 100%);
        border-radius: 22px;
        padding: 24px 28px;
        color: #fff;
        box-shadow: 0 14px 35px rgba(15, 23, 42, .16);
        margin-bottom: 22px;
    }

    .org-eyebrow {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 2px;
        color: #f4c542;
        margin-bottom: 8px;
        text-transform: uppercase;
    }

    .org-title {
        font-size: 28px;
        font-weight: 800;
        margin-bottom: 6px;
        letter-spacing: -.3px;
    }

    .org-subtitle {
        color: rgba(255,255,255,.78);
        font-size: 14px;
        margin-bottom: 0;
        max-width: 820px;
        line-height: 1.6;
    }

    .org-header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .org-btn-light {
        border: 1px solid rgba(255,255,255,.28);
        background: rgba(255,255,255,.12);
        color: #fff;
        border-radius: 14px;
        padding: 10px 16px;
        font-weight: 700;
        text-decoration: none;
        transition: .2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        cursor: pointer;
    }

    .org-btn-light:hover {
        background: rgba(255,255,255,.22);
        color: #fff;
    }

    .org-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }

    .org-summary-card {
        background: #fff;
        border: 1px solid #edf0ea;
        border-radius: 18px;
        padding: 18px 20px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
    }

    .org-summary-label {
        font-size: 12px;
        color: #6b7280;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .8px;
        margin-bottom: 8px;
    }

    .org-summary-value {
        font-size: 28px;
        color: #273957;
        font-weight: 900;
        line-height: 1;
    }

    .org-filter-card {
        background: #fff;
        border: 1px solid #edf0ea;
        border-radius: 20px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
        margin-bottom: 22px;
    }

    .org-filter-card .card-body {
        padding: 18px 20px;
    }

    .org-filter-card .form-label,
    .org-fullscreen-filter .form-label {
        color: #374151;
        font-weight: 700;
        font-size: 13px;
        margin-bottom: 7px;
    }

    .org-filter-card .form-select,
    .org-fullscreen-filter .form-select {
        border-radius: 14px;
        min-height: 44px;
        border-color: #dfe5d8;
        color: #374151;
        font-size: 14px;
    }

    .org-filter-card .form-select:focus,
    .org-fullscreen-filter .form-select:focus {
        border-color: #6b775c;
        box-shadow: 0 0 0 .2rem rgba(107,119,92,.15);
    }

    .org-fullscreen-filter {
        background: #fff;
        border: 1px solid #edf0ea;
        border-radius: 20px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
        margin-bottom: 14px;
        padding: 14px 16px;
    }

    .org-btn-primary,
    .org-btn-secondary {
        min-height: 44px;
        border-radius: 14px;
        padding: 10px 18px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
    }

    .org-btn-primary {
        background: #6b775c;
        color: #fff;
    }

    .org-btn-primary:hover {
        background: #59664b;
        color: #fff;
    }

    .org-btn-secondary {
        background: #eef2eb;
        color: #536044;
    }

    .org-btn-secondary:hover {
        background: #dfe7d8;
        color: #3f4a32;
    }

    .org-board-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 24px;
        box-shadow: 0 16px 40px rgba(15, 23, 42, .08);
        overflow: hidden;
        width: 100%;
        max-width: 100%;
    }

    .org-page.is-fullscreen .org-board-card {
        min-height: calc(100vh - 36px);
        border-radius: 22px;
    }

    .org-board-toolbar {
        background: #fbfcfa;
        border-bottom: 1px solid #edf0ea;
        padding: 16px 20px;
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: center;
        flex-wrap: wrap;
    }

    .org-board-title {
        font-weight: 900;
        color: #273957;
        font-size: 18px;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .org-board-meta {
        font-size: 12px;
        color: #6b7280;
        margin-top: 3px;
        font-weight: 600;
    }

    .org-toolbar-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .org-mini-btn {
        min-height: 34px;
        border-radius: 999px;
        padding: 7px 12px;
        border: 1px solid #dfe5d8;
        background: #fff;
        color: #536044;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .org-mini-btn:hover {
        background: #eef2eb;
        color: #3f4a32;
    }

    .org-legend {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        font-size: 12px;
        color: #6b7280;
        font-weight: 700;
    }

    .org-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .org-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }

    .org-dot-filled { background: #16a34a; }
    .org-dot-vacant { background: #ef4444; }

    .org-chart-scroll {
        width: 100%;
        overflow: auto;
        background:
            radial-gradient(circle at top left, rgba(107,119,92,.08), transparent 28%),
            linear-gradient(180deg, #ffffff 0%, #f8faf7 100%);
        padding: 46px 34px 56px;
        min-height: 640px;
        cursor: grab;
    }

    .org-chart-scroll:active { cursor: grabbing; }

    .org-page.is-fullscreen .org-chart-scroll {
        min-height: calc(100vh - 190px);
    }

    .org-chart-canvas {
        min-width: max-content;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        transform-origin: top center;
        transition: transform .2s ease;
    }

    .org-tree {
        display: flex;
        justify-content: center;
        min-width: max-content;
        width: max-content;
    }

    .org-tree,
    .org-tree ul,
    .org-tree li {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .org-tree ul {
        position: relative;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding-top: 44px;
    }

    .org-tree ul::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        width: 0;
        height: 44px;
        border-left: 2px solid var(--org-line);
        transform: translateX(-50%);
    }

    .org-tree li {
        position: relative;
        text-align: center;
        padding: 44px 14px 0;
    }

    .org-tree li::before,
    .org-tree li::after {
        content: '';
        position: absolute;
        top: 0;
        width: 50%;
        height: 44px;
        border-top: 2px solid var(--org-line);
    }

    .org-tree li::before { right: 50%; }
    .org-tree li::after { left: 50%; border-left: 2px solid var(--org-line); }

    .org-tree li:only-child::before,
    .org-tree li:only-child::after { display: none; }

    .org-tree li:only-child { padding-top: 0; }

    .org-tree li:first-child::before,
    .org-tree li:last-child::after { border: none; }

    .org-tree li:last-child::before {
        border-right: 2px solid var(--org-line);
        border-radius: 0 8px 0 0;
    }

    .org-tree li:first-child::after { border-radius: 8px 0 0 0; }

    .org-tree > ul { padding-top: 0; }
    .org-tree > ul::before { display: none; }
    .org-tree > ul > li { padding-top: 0; }
    .org-tree > ul > li::before,
    .org-tree > ul > li::after { display: none; }

    .org-node-wrap {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 2;
    }

    .org-node {
        width: 176px;
        min-height: 116px;
        background: #ffffff;
        border: 1px solid #d6dccf;
        border-radius: 16px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
        overflow: hidden;
        display: inline-flex;
        flex-direction: column;
        transition: .2s ease;
        position: relative;
        z-index: 2;
    }

    .org-node:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 30px rgba(15, 23, 42, .13);
        border-color: #b9c4ae;
    }

    .org-node-topline { height: 6px; background: var(--org-danger); }
    .org-node.is-filled .org-node-topline { background: var(--org-success); }

    .org-node-title {
        min-height: 48px;
        padding: 8px 9px 6px;
        color: #273957;
        font-size: 10.4px;
        font-weight: 900;
        line-height: 1.22;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        overflow-wrap: anywhere;
    }

    .org-node-holder {
        min-height: 34px;
        padding: 6px 9px;
        border-top: 1px solid #edf0ea;
        background: #fbfcfa;
        color: #374151;
        font-size: 9.8px;
        font-weight: 700;
        line-height: 1.2;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        overflow-wrap: anywhere;
    }

    .org-vacant-text {
        color: #ef4444;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .org-node-meta {
        display: grid;
        grid-template-columns: 1fr 34px;
        border-top: 1px solid #edf0ea;
        min-height: 30px;
    }

    .org-node-dept {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 5px 7px;
        font-size: 8.8px;
        font-weight: 800;
        color: #6b775c;
        background: #f6f8f4;
        line-height: 1.18;
        overflow-wrap: anywhere;
    }

    .org-node-count {
        display: flex;
        align-items: center;
        justify-content: center;
        border-left: 1px solid #edf0ea;
        font-size: 10.5px;
        font-weight: 900;
        color: #273957;
        background: #fff;
    }

    .org-detail-link {
        margin-top: 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 24px;
        padding: 4px 10px;
        border-radius: 999px;
        background: #eef2ff;
        color: #332da1;
        font-size: 10px;
        font-weight: 800;
        text-decoration: none;
        position: relative;
        z-index: 3;
    }

    .org-detail-link:hover { background: #332da1; color: #ffffff; }

    .org-empty {
        min-height: 320px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #6b7280;
        font-weight: 700;
        padding: 32px;
    }

    .print-report {
        display: none;
    }

    .print-page {
        background: #fff;
        page-break-after: always;
        break-after: page;
    }

    .print-page:last-child {
        page-break-after: auto;
        break-after: auto;
    }

    .print-kop {
        display: grid;
        grid-template-columns: 110px 1fr 120px;
        align-items: center;
        gap: 14px;
        border-bottom: 3px solid #273957;
        padding-bottom: 10px;
        margin-bottom: 12px;
    }

    .print-logo {
        max-height: 54px;
        max-width: 110px;
        object-fit: contain;
    }

    .print-title {
        text-align: center;
        color: #273957;
        font-weight: 900;
        font-size: 18px;
        line-height: 1.25;
        text-transform: uppercase;
        margin: 0;
    }

    .print-subtitle {
        text-align: center;
        color: #374151;
        font-weight: 800;
        font-size: 13px;
        margin-top: 3px;
        text-transform: uppercase;
    }

    .print-meta {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        color: #4b5563;
        font-size: 10px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .print-summary {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .print-summary span {
        display: inline-flex;
        border: 1px solid #d1d5db;
        border-radius: 999px;
        padding: 3px 8px;
        background: #f9fafb;
    }

    @media (max-width: 992px) {
        .org-summary-grid { grid-template-columns: 1fr; }
        .org-page-header { padding: 22px 20px; }
        .org-title { font-size: 24px; }
        .org-header-actions { justify-content: flex-start; margin-top: 16px; }
    }

    @media (max-width: 768px) {
        .org-chart-scroll { padding: 30px 16px 40px; }
        .org-filter-card .card-body { padding: 16px; }
        .org-btn-primary,
        .org-btn-secondary,
        .org-btn-light { width: 100%; justify-content: center; text-align: center; }
        .org-node { width: 156px; min-height: 106px; }
        .org-node-title { font-size: 9.4px; min-height: 42px; }
        .org-node-holder { font-size: 9px; }
        .org-node-dept { font-size: 8px; }
    }

    @page {
        size: A4 landscape;
        margin: 8mm;
    }

    @media print {
        html,
        body {
            width: 297mm;
            min-height: 210mm;
            background: #ffffff !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .sidebar,
        .topbar,
        .navbar,
        .org-page,
        .main-header,
        .app-header,
        header,
        .no-print {
            display: none !important;
        }

        .main-content,
        .page-content,
        .container,
        .container-fluid,
        .content-wrapper {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        .print-report {
            display: block !important;
            width: 100% !important;
        }

        .print-page {
            width: 100%;
            min-height: 190mm;
            overflow: hidden;
            padding: 0;
        }

        .print-chart-area {
            width: 100%;
            overflow: visible !important;
            padding: 8px 0 0;
        }

        .print-chart-scale {
            transform-origin: top left !important;
            display: inline-block;
        }

        .print-page.overview-page .print-chart-scale {
            transform: scale(.34);
        }

        .print-page.department-page .print-chart-scale {
            transform: scale(.52);
        }

        .print-page .org-tree {
            justify-content: flex-start;
        }

        .print-page .org-node {
            box-shadow: none !important;
            border: 1px solid #111827 !important;
            border-radius: 8px !important;
            width: 150px;
            min-height: 96px;
        }

        .print-page .org-node-title {
            font-size: 8.2px;
            min-height: 38px;
            padding: 6px;
        }

        .print-page .org-node-holder {
            font-size: 7.8px;
            min-height: 28px;
            padding: 5px 6px;
        }

        .print-page .org-node-dept {
            font-size: 7px;
            padding: 4px;
        }

        .print-page .org-node-count {
            font-size: 8px;
        }

        .print-page .org-detail-link {
            display: none !important;
        }
    }
</style>

<div class="container-fluid org-page {{ $isFullscreen ? 'is-fullscreen' : '' }}">

    @unless($isFullscreen)
        <div class="org-page-header no-print">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <div class="org-eyebrow">Organization Structure</div>
                    <h3 class="org-title">Struktur Jabatan Perusahaan</h3>
                    <p class="org-subtitle">
                        Bagan ini menampilkan struktur jabatan aktif berdasarkan versi approved, departemen, pemangku jabatan, dan hubungan atasan langsung melalui parent_jabatan.
                    </p>
                </div>

                <div class="org-header-actions">
                    <a href="{{ route('struktur-jabatan.index', $fullscreenParams) }}" class="org-btn-light">
                        Mode Full Screen
                    </a>
                    <button type="button" onclick="saveStructurePdf()" class="org-btn-light">
                        Save PDF Struktur
                    </button>
                </div>
            </div>
        </div>

        <div class="org-summary-grid no-print">
            <div class="org-summary-card">
                <div class="org-summary-label">Filled</div>
                <div class="org-summary-value">{{ $summary['filled'] ?? 0 }}</div>
            </div>

            <div class="org-summary-card">
                <div class="org-summary-label">Vacant</div>
                <div class="org-summary-value">{{ $summary['vacant'] ?? 0 }}</div>
            </div>

            <div class="org-summary-card">
                <div class="org-summary-label">Total Formasi</div>
                <div class="org-summary-value">{{ $summary['total'] ?? 0 }}</div>
            </div>
        </div>

        <div class="card org-filter-card no-print">
            <div class="card-body">
                <form method="GET" action="{{ route('struktur-jabatan.index') }}" class="row g-3 align-items-end">
                    <div class="col-lg-6 col-md-7">
                        <label class="form-label">Filter Departemen</label>

                        <select name="id_departemen" class="form-select">
                            <option value="">Semua Departemen</option>

                            @foreach($departemenList as $dep)
                                <option value="{{ $dep->id_departemen }}"
                                    {{ (string) $idDepartemen === (string) $dep->id_departemen ? 'selected' : '' }}>
                                    {{ $dep->nama_departemen }}
                                    @if($dep->singkatan)
                                        ({{ $dep->singkatan }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-4 col-md-5">
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="org-btn-primary" type="submit">Tampilkan</button>
                            <a href="{{ route('struktur-jabatan.index') }}" class="org-btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="org-fullscreen-filter no-print">
            <form method="GET" action="{{ route('struktur-jabatan.index') }}" class="row g-3 align-items-end">
                <input type="hidden" name="fullscreen" value="1">

                <div class="col-xl-5 col-lg-6 col-md-7">
                    <label class="form-label">Filter Departemen</label>
                    <select name="id_departemen" class="form-select">
                        <option value="">Semua Departemen</option>
                        @foreach($departemenList as $dep)
                            <option value="{{ $dep->id_departemen }}"
                                {{ (string) $idDepartemen === (string) $dep->id_departemen ? 'selected' : '' }}>
                                {{ $dep->nama_departemen }}
                                @if($dep->singkatan)
                                    ({{ $dep->singkatan }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-5 col-lg-6 col-md-5">
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="org-btn-primary" type="submit">Tampilkan</button>
                        <a href="{{ route('struktur-jabatan.index', ['fullscreen' => 1]) }}" class="org-btn-secondary">Reset</a>
                        <button type="button" onclick="saveStructurePdf()" class="org-btn-secondary">Save PDF</button>
                        <a href="{{ route('struktur-jabatan.index', $normalParams) }}" class="org-btn-secondary">Keluar Full Screen</a>
                    </div>
                </div>
            </form>
        </div>
    @endunless

    <div class="org-board-card">
        <div class="org-board-toolbar">
            <div>
                <h4 class="org-board-title">{{ $sheetTitle }}</h4>
                <div class="org-board-meta">
                    PT Bumi Siak Pusako · {{ $sheetCode }} · Struktur Jabatan Aktif
                </div>
            </div>

            <div class="org-toolbar-actions no-print">
                <div class="org-legend">
                    <span class="org-legend-item"><span class="org-dot org-dot-filled"></span> Terisi</span>
                    <span class="org-legend-item"><span class="org-dot org-dot-vacant"></span> Kosong</span>
                </div>

                <button type="button" class="org-mini-btn" data-zoom="out">−</button>
                <button type="button" class="org-mini-btn" data-zoom="reset">100%</button>
                <button type="button" class="org-mini-btn" data-zoom="in">+</button>
                <button type="button" class="org-mini-btn" onclick="saveStructurePdf()">Save PDF</button>

                @if($isFullscreen)
                    <a href="{{ route('struktur-jabatan.index', $normalParams) }}" class="org-mini-btn">Keluar Full Screen</a>
                @else
                    <a href="{{ route('struktur-jabatan.index', $fullscreenParams) }}" class="org-mini-btn">Full Screen</a>
                @endif
            </div>
        </div>

        <div class="org-chart-scroll" id="orgChartScroll">
            @if($struktur->count())
                <div class="org-chart-canvas" id="orgChartCanvas">
                    <div class="org-tree">
                        <ul>
                            @foreach($struktur as $node)
                                @include('struktur-jabatan.node', ['node' => $node])
                            @endforeach
                        </ul>
                    </div>
                </div>
            @else
                <div class="org-empty">
                    Struktur jabatan belum tersedia. Pastikan versi jabatan sudah approved dan parent_jabatan sudah diisi.
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ================= PRINT / SAVE PDF REPORT ================= --}}
<div class="print-report">
    @foreach(($printSections ?? collect()) as $section)
        <div class="print-page {{ ($section['type'] ?? '') === 'overview' ? 'overview-page' : 'department-page' }}">
            <div class="print-kop">
                <div>
                    <img src="{{ $logoBsp }}" class="print-logo" alt="Logo BSP" onerror="this.style.display='none'">
                </div>

                <div>
                    <h1 class="print-title">{{ $section['title'] ?? 'STRUKTUR ORGANISASI' }}</h1>
                    <div class="print-subtitle">{{ $section['subtitle'] ?? 'PT BUMI SIAK PUSAKO' }}</div>
                </div>

                <div style="text-align:right;">
                    <img src="{{ $logoSkk }}" class="print-logo" alt="Logo SKK Migas" onerror="this.style.display='none'">
                </div>
            </div>

            <div class="print-meta">
                <div>
                    Kode: {{ $section['code'] ?? '-' }} · Dicetak: {{ now()->format('d/m/Y H:i') }}
                </div>
                <div class="print-summary">
                    <span>Filled: {{ $section['summary']['filled'] ?? 0 }}</span>
                    <span>Vacant: {{ $section['summary']['vacant'] ?? 0 }}</span>
                    <span>Total: {{ $section['summary']['total'] ?? 0 }}</span>
                </div>
            </div>

            <div class="print-chart-area">
                @if(($section['struktur'] ?? collect())->count())
                    <div class="print-chart-scale">
                        <div class="org-tree">
                            <ul>
                                @foreach($section['struktur'] as $node)
                                    @include('struktur-jabatan.node', ['node' => $node])
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @else
                    <div class="org-empty">Struktur jabatan belum tersedia.</div>
                @endif
            </div>
        </div>
    @endforeach
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const isFullscreen = @json($isFullscreen);
        const body = document.body;
        const scrollBox = document.getElementById('orgChartScroll');
        const canvas = document.getElementById('orgChartCanvas');
        let zoom = 1;

        if (isFullscreen) {
            body.classList.add('org-fullscreen-mode');
        }

        function applyZoom() {
            if (!canvas) return;
            canvas.style.transform = `scale(${zoom})`;
        }

        document.querySelectorAll('[data-zoom]').forEach(function (button) {
            button.addEventListener('click', function () {
                const action = button.dataset.zoom;

                if (action === 'in') {
                    zoom = Math.min(1.45, zoom + 0.1);
                } else if (action === 'out') {
                    zoom = Math.max(0.55, zoom - 0.1);
                } else {
                    zoom = 1;
                }

                applyZoom();
            });
        });

        if (scrollBox) {
            let isDown = false;
            let startX = 0;
            let startY = 0;
            let scrollLeft = 0;
            let scrollTop = 0;

            scrollBox.addEventListener('mousedown', function (e) {
                isDown = true;
                startX = e.pageX - scrollBox.offsetLeft;
                startY = e.pageY - scrollBox.offsetTop;
                scrollLeft = scrollBox.scrollLeft;
                scrollTop = scrollBox.scrollTop;
            });

            scrollBox.addEventListener('mouseleave', function () { isDown = false; });
            scrollBox.addEventListener('mouseup', function () { isDown = false; });

            scrollBox.addEventListener('mousemove', function (e) {
                if (!isDown) return;

                e.preventDefault();

                const x = e.pageX - scrollBox.offsetLeft;
                const y = e.pageY - scrollBox.offsetTop;
                const walkX = x - startX;
                const walkY = y - startY;

                scrollBox.scrollLeft = scrollLeft - walkX;
                scrollBox.scrollTop = scrollTop - walkY;
            });
        }
    });

    function saveStructurePdf() {
        window.print();
    }
</script>
@endsection
