@extends('layouts.app')

@section('title', 'Detail Struktur Jabatan')

@section('content')
@php
    $namaJabatan = $version->nama_jabatan ?? $jabatan->nama_jabatan ?? '-';

    $namaDepartemen = $jabatan->departemenMaster->nama_departemen
        ?? $version->departemen
        ?? $jabatan->departemen
        ?? '-';

    $namaParent = $parent
        ? ($parent->activeVersion->nama_jabatan ?? $parent->latestApprovedVersion->nama_jabatan ?? $parent->nama_jabatan)
        : 'Root / Jabatan Utama';

    $pemangkuAktif = $jabatan->pemangkuSaatIni ?? collect();
    $riwayatPemangku = $jabatan->riwayatPemangku ?? collect();

    $jumlahPemangkuAktif = $pemangkuAktif->count();
    $jumlahRiwayat = $riwayatPemangku->count();
    $isVacant = $jumlahPemangkuAktif < 1;
@endphp

<style>
    :root{
        --sd-navy:#273957;
        --sd-olive:#6b775c;
        --sd-olive-dark:#4f5c40;
        --sd-bg:#f6f8f4;
        --sd-card:#ffffff;
        --sd-border:#dfe5d7;
        --sd-border-soft:#edf0ea;
        --sd-text:#1f2937;
        --sd-muted:#667085;
        --sd-gold:#f4c542;
        --sd-shadow:0 14px 35px rgba(15,23,42,.07);
    }

    footer,
    .footer,
    .main-footer,
    .app-footer{
        position:static !important;
        bottom:auto !important;
        top:auto !important;
        margin-top:28px !important;
    }

    .structure-detail-page{
        min-height:calc(100dvh - 150px);
        padding:24px 18px 36px;
        background:var(--sd-bg);
        color:var(--sd-text);
    }

    .structure-detail-shell{
        width:100%;
        max-width:1320px;
        margin:0 auto;
    }

    .detail-hero{
        background:linear-gradient(135deg, #6b775c 0%, #4f5c40 100%);
        color:#ffffff;
        border-radius:22px;
        padding:24px 26px;
        margin-bottom:16px;
        box-shadow:0 14px 35px rgba(15,23,42,.08);
        border:1px solid rgba(255,255,255,.12);
    }

    .detail-hero-inner{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:18px;
        flex-wrap:wrap;
    }

    .detail-eyebrow{
        font-size:12px;
        letter-spacing:1.2px;
        text-transform:uppercase;
        color:#ffffff;
        margin-bottom:8px;
        font-weight:700;
        opacity:.9;
    }

    .detail-title{
        font-size:clamp(23px, 2.3vw, 32px);
        font-weight:700;
        margin:0 0 6px;
        line-height:1.2;
        color:#ffffff;
    }

    .detail-subtitle{
        margin:0;
        color:rgba(255,255,255,.78);
        font-size:14px;
        line-height:1.6;
        max-width:780px;
        font-weight:400;
    }

    .btn-back-structure{
        min-height:40px;
        padding:9px 15px;
        border-radius:12px;
        border:1px solid rgba(255,255,255,.28);
        background:rgba(255,255,255,.13);
        color:#ffffff;
        text-decoration:none;
        display:inline-flex;
        align-items:center;
        gap:8px;
        font-size:13px;
        font-weight:600;
        transition:.18s ease;
    }

    .btn-back-structure:hover{
        background:rgba(255,255,255,.22);
        color:#ffffff;
        transform:translateY(-1px);
    }

    .detail-summary-strip{
        display:grid;
        grid-template-columns:repeat(3, minmax(0, 1fr));
        gap:1px;
        overflow:hidden;
        border:1px solid var(--sd-border);
        border-radius:18px;
        background:var(--sd-border);
        margin-bottom:16px;
        box-shadow:0 8px 22px rgba(15,23,42,.045);
    }

    .summary-item{
        background:#ffffff;
        padding:16px 18px;
    }

    .summary-label{
        color:var(--sd-muted);
        font-size:12px;
        margin-bottom:6px;
        font-weight:700;
    }

    .summary-value{
        color:var(--sd-navy);
        font-size:22px;
        line-height:1.1;
        font-weight:700;
    }

    .detail-content-panel{
        background:#ffffff;
        border:1px solid var(--sd-border);
        border-radius:22px;
        box-shadow:var(--sd-shadow);
        overflow:hidden;
    }

    .detail-content-grid{
        display:grid;
        grid-template-columns:420px minmax(0, 1fr);
        min-height:400px;
    }

    .detail-side{
        border-right:1px solid var(--sd-border-soft);
        background:#fbfcf8;
        padding:22px;
    }

    .detail-main{
        padding:22px;
    }

    .section-block + .section-block{
        margin-top:28px;
        padding-top:22px;
        border-top:1px solid var(--sd-border-soft);
    }

    .section-header{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        margin-bottom:14px;
    }

    .section-title{
        display:flex;
        align-items:center;
        gap:9px;
        color:var(--sd-navy);
        font-size:15px;
        font-weight:700;
        margin:0;
    }

    .section-title i{
        color:var(--sd-olive-dark);
        font-size:16px;
    }

    .status-pill{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border-radius:999px;
        padding:6px 10px;
        font-size:12px;
        line-height:1;
        font-weight:700;
        white-space:nowrap;
    }

    .status-pill.filled,
    .status-active{
        color:#166534;
        background:#dcfce7;
        border:1px solid #bbf7d0;
    }

    .status-pill.vacant{
        color:#92400e;
        background:#fef3c7;
        border:1px solid #fde68a;
    }

    .status-history{
        color:#536044;
        background:#eef2eb;
        border:1px solid #d9e0d2;
    }

    .info-list{
        display:grid;
        gap:0;
        border:1px solid var(--sd-border-soft);
        border-radius:16px;
        overflow:hidden;
        background:#ffffff;
    }

    .info-row{
        display:grid;
        grid-template-columns:145px minmax(0, 1fr);
        border-bottom:1px solid var(--sd-border-soft);
    }

    .info-row:last-child{
        border-bottom:0;
    }

    .info-label,
    .info-value{
        padding:12px 13px;
        font-size:13px;
        line-height:1.45;
    }

    .info-label{
        color:#667085;
        background:#fbfcf8;
        font-weight:400;
    }

    .info-value{
        color:#1f2937;
        font-weight:400;
        word-break:break-word;
    }

    .holder-list{
        display:grid;
        gap:10px;
    }

    .holder-item{
        display:grid;
        grid-template-columns:minmax(0, 1fr) auto;
        gap:12px;
        padding:14px 15px;
        border:1px solid var(--sd-border-soft);
        border-radius:16px;
        background:#ffffff;
    }

    .holder-name{
        color:var(--sd-navy);
        font-size:14px;
        font-weight:600;
        margin-bottom:3px;
    }

    .holder-nip{
        color:var(--sd-muted);
        font-size:12.5px;
        margin-bottom:8px;
    }

    .holder-meta{
        display:grid;
        grid-template-columns:repeat(3, minmax(0, 1fr));
        gap:8px;
        color:#667085;
        font-size:12.5px;
        line-height:1.45;
    }

    .holder-meta span{
        color:#344054;
    }

    .empty-state{
        border:1px dashed #d9e0d2;
        background:#fffbeb;
        color:#92400e;
        border-radius:16px;
        padding:16px;
        display:flex;
        gap:10px;
        align-items:flex-start;
        font-size:13px;
        line-height:1.5;
    }

    .empty-state i{
        margin-top:2px;
    }

    .table-history{
        border-color:var(--sd-border-soft) !important;
        margin-bottom:0;
        font-size:13px;
    }

    .table-history th{
        background:#6b775c !important;
        color:#ffffff;
        text-align:center;
        vertical-align:middle;
        border-color:rgba(255,255,255,.18) !important;
        padding:11px 10px;
        white-space:nowrap;
        font-size:12px;
        font-weight:600;
    }

    .table-history td{
        color:#344054;
        vertical-align:middle;
        border-color:var(--sd-border-soft) !important;
        padding:11px 10px;
        font-weight:400;
    }

    .table-history tbody tr:hover td{
        background:#fbfcf8;
    }

    @media (max-width: 1100px){
        .detail-content-grid{
            grid-template-columns:1fr;
        }

        .detail-side{
            border-right:0;
            border-bottom:1px solid var(--sd-border-soft);
        }
    }

    @media (max-width: 900px){
        .detail-summary-strip{
            grid-template-columns:1fr;
        }

        .holder-meta{
            grid-template-columns:1fr;
        }
    }

    @media (max-width: 768px){
        .structure-detail-page{
            padding:16px 12px 30px;
        }

        .detail-hero{
            padding:21px 18px;
            border-radius:18px;
        }

        .btn-back-structure{
            width:100%;
            justify-content:center;
        }

        .detail-side,
        .detail-main{
            padding:18px;
        }

        .info-row{
            grid-template-columns:1fr;
        }

        .info-label{
            padding-bottom:4px;
        }

        .info-value{
            padding-top:4px;
        }

        .holder-item{
            grid-template-columns:1fr;
        }
    }
</style>

<div class="container-fluid structure-detail-page">
    <div class="structure-detail-shell">

        <div class="detail-hero">
            <div class="detail-hero-inner">
                <div>
                    <div class="detail-eyebrow">DETAIL JABATAN</div>
                    <h4 class="detail-title">{{ $namaJabatan }}</h4>
                </div>

                <a href="{{ route('struktur-jabatan.index') }}" class="btn-back-structure">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>

        <div class="detail-summary-strip">
            <div class="summary-item">
                <div class="summary-label">Status Formasi</div>
                <div class="summary-value">{{ $isVacant ? 'Vacant' : 'Terisi' }}</div>
            </div>

            <div class="summary-item">
                <div class="summary-label">Pemangku Aktif</div>
                <div class="summary-value">{{ $jumlahPemangkuAktif }}</div>
            </div>

            <div class="summary-item">
                <div class="summary-label">Riwayat Pemangku</div>
                <div class="summary-value">{{ $jumlahRiwayat }}</div>
            </div>
        </div>

        <div class="detail-content-panel">
            <div class="detail-content-grid">

                <aside class="detail-side">
                    <div class="section-block">
                        <div class="section-header">
                            <h5 class="section-title">
                                <i class="bi bi-diagram-3"></i>
                                <span>Data Jabatan</span>
                            </h5>

                            <span class="status-pill {{ $isVacant ? 'vacant' : 'filled' }}">
                                {{ $isVacant ? 'Vacant' : 'Terisi' }}
                            </span>
                        </div>

                        <div class="info-list">
                            <div class="info-row">
                                <div class="info-label">Nama Jabatan</div>
                                <div class="info-value">{{ $namaJabatan }}</div>
                            </div>

                            <div class="info-row">
                                <div class="info-label">Departemen</div>
                                <div class="info-value">{{ $namaDepartemen }}</div>
                            </div>

                            <div class="info-row">
                                <div class="info-label">Parent Jabatan</div>
                                <div class="info-value">{{ $namaParent }}</div>
                            </div>

                            <div class="info-row">
                                <div class="info-label">Golongan</div>
                                <div class="info-value">{{ $version->gol_jabatan ?? $jabatan->gol_jabatan ?? '-' }}</div>
                            </div>

                            <div class="info-row">
                                <div class="info-label">Home Base</div>
                                <div class="info-value">{{ $version->home_base ?? $jabatan->home_base ?? '-' }}</div>
                            </div>

                            <div class="info-row">
                                <div class="info-label">Lokasi Kerja</div>
                                <div class="info-value">{{ $version->lokasi_kerja ?? $jabatan->lokasi_kerja ?? '-' }}</div>
                            </div>

                            <div class="info-row">
                                <div class="info-label">Versi Aktif</div>
                                <div class="info-value">{{ $version ? 'Versi '.$version->version_number : '-' }}</div>
                            </div>
                        </div>
                    </div>
                </aside>

                <main class="detail-main">
                    <div class="section-block">
                        <div class="section-header">
                            <h5 class="section-title">
                                <i class="bi bi-person-check"></i>
                                <span>Pemangku Jabatan Saat Ini</span>
                            </h5>

                            <span class="status-pill {{ $isVacant ? 'vacant' : 'filled' }}">
                                {{ $jumlahPemangkuAktif }} Pegawai
                            </span>
                        </div>

                        @if($pemangkuAktif->count())
                            <div class="holder-list">
                                @foreach($pemangkuAktif as $pegawai)
                                    <div class="holder-item">
                                        <div>
                                            <div class="holder-name">{{ $pegawai->nama }}</div>
                                            <div class="holder-nip">NIP: {{ $pegawai->nip }}</div>

                                            <div class="holder-meta">
                                                <div>Departemen: <span>{{ $pegawai->departemenMaster->nama_departemen ?? $pegawai->departemen ?? '-' }}</span></div>
                                                <div>Status: <span>{{ $pegawai->status ?? '-' }}</span></div>
                                                <div>Lokasi: <span>{{ $pegawai->lokasi_kerja ?? '-' }}</span></div>
                                            </div>
                                        </div>

                                        <div>
                                            <span class="status-pill status-active">Aktif</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="bi bi-exclamation-circle"></i>
                                <div>
                                    Belum ada pegawai aktif yang memegang jabatan ini.
                                    Posisi akan tampil sebagai vacant pada struktur organisasi.
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="section-block">
                        <div class="section-header">
                            <h5 class="section-title">
                                <i class="bi bi-clock-history"></i>
                                <span>Riwayat Pemangku Jabatan</span>
                            </h5>

                            <span class="status-pill filled">
                                {{ $jumlahRiwayat }} Riwayat
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle table-history">
                                <thead>
                                    <tr>
                                        <th>Nama Pegawai</th>
                                        <th>NIP</th>
                                        <th>Versi JD</th>
                                        <th>Mulai</th>
                                        <th>Selesai</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($riwayatPemangku as $riwayat)
                                        <tr>
                                            <td>{{ $riwayat->pegawai?->nama ?? '-' }}</td>

                                            <td class="text-center">
                                                {{ $riwayat->nip }}
                                            </td>

                                            <td class="text-center">
                                                {{ $riwayat->version ? 'Versi '.$riwayat->version->version_number : '-' }}
                                            </td>

                                            <td class="text-center">
                                                {{ $riwayat->assigned_at ? \Carbon\Carbon::parse($riwayat->assigned_at)->locale('id')->translatedFormat('d F Y') : '-' }}
                                            </td>

                                            <td class="text-center">
                                                {{ $riwayat->ended_at ? \Carbon\Carbon::parse($riwayat->ended_at)->locale('id')->translatedFormat('d F Y') : '-' }}
                                            </td>

                                            <td class="text-center">
                                                @if($riwayat->is_current)
                                                    <span class="status-pill status-active">Aktif</span>
                                                @else
                                                    <span class="status-pill status-history">Riwayat</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                Belum ada riwayat pemangku jabatan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </main>

            </div>
        </div>

    </div>
</div>
@endsection