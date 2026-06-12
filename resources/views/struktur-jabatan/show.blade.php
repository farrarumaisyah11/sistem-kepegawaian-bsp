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
@endphp

<style>
    .structure-detail-page {
        padding-bottom: 34px;
        background: #f7f9f5;
    }

    .detail-hero {
        background: linear-gradient(135deg, #273957 0%, #3f4a32 100%);
        color: #ffffff;
        border-radius: 22px;
        padding: 24px 28px;
        margin-bottom: 22px;
        box-shadow: 0 14px 35px rgba(15, 23, 42, .16);
    }

    .detail-eyebrow {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 2px;
        color: #f4c542;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .detail-title {
        font-size: 27px;
        font-weight: 900;
        margin-bottom: 6px;
        letter-spacing: -.3px;
    }

    .detail-subtitle {
        color: rgba(255,255,255,.78);
        font-size: 14px;
        line-height: 1.6;
        margin: 0;
    }

    .btn-back-structure {
        min-height: 42px;
        padding: 10px 16px;
        border-radius: 14px;
        border: 1px solid rgba(255,255,255,.28);
        background: rgba(255,255,255,.12);
        color: #ffffff;
        font-weight: 800;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-back-structure:hover {
        background: rgba(255,255,255,.22);
        color: #ffffff;
    }

    .detail-card {
        border: 1px solid #edf0ea;
        border-radius: 20px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .07);
        overflow: hidden;
        background: #ffffff;
    }

    .detail-card-header {
        background: #fbfcfa;
        border-bottom: 1px solid #edf0ea;
        padding: 16px 18px;
        color: #273957;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .3px;
        font-size: 14px;
    }

    .detail-card-body {
        padding: 18px;
    }

    .info-table {
        margin-bottom: 0;
    }

    .info-table th {
        width: 34%;
        background: #f6f8f4;
        color: #536044;
        font-size: 13px;
        vertical-align: middle;
    }

    .info-table td {
        color: #111827;
        font-size: 13.5px;
        vertical-align: middle;
        font-weight: 600;
    }

    .holder-card {
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 14px 16px;
        margin-bottom: 10px;
        background: #ffffff;
    }

    .holder-name {
        color: #273957;
        font-weight: 900;
        font-size: 15px;
        margin-bottom: 4px;
    }

    .holder-meta {
        color: #6b7280;
        font-size: 12.5px;
        line-height: 1.6;
        font-weight: 600;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 800;
    }

    .status-active {
        background: #dcfce7;
        color: #166534;
    }

    .status-history {
        background: #eef2eb;
        color: #536044;
    }

    .table-history th {
        background: #6b775c !important;
        color: #ffffff;
        font-size: 12.5px;
        text-align: center;
        vertical-align: middle;
    }

    .table-history td {
        font-size: 13px;
        vertical-align: middle;
    }

    @media (max-width: 768px) {
        .detail-hero {
            padding: 22px 18px;
        }

        .detail-title {
            font-size: 23px;
        }

        .btn-back-structure {
            width: 100%;
            margin-top: 14px;
        }
    }
</style>

<div class="container-fluid structure-detail-page">
    <div class="detail-hero">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <div class="detail-eyebrow">Structure Detail</div>
                <h4 class="detail-title">{{ $namaJabatan }}</h4>
                <p class="detail-subtitle">
                    Detail parent jabatan, departemen, pemangku saat ini, dan riwayat pemangku jabatan.
                </p>
            </div>

            <a href="{{ route('struktur-jabatan.index') }}" class="btn-back-structure">
                Kembali ke Struktur
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="detail-card">
                <div class="detail-card-header">
                    Data Jabatan
                </div>

                <div class="detail-card-body">
                    <table class="table table-bordered info-table">
                        <tr>
                            <th>Nama Jabatan</th>
                            <td>{{ $namaJabatan }}</td>
                        </tr>
                        <tr>
                            <th>Departemen</th>
                            <td>{{ $namaDepartemen }}</td>
                        </tr>
                        <tr>
                            <th>Parent Jabatan</th>
                            <td>{{ $namaParent }}</td>
                        </tr>
                        <tr>
                            <th>Golongan</th>
                            <td>{{ $version->gol_jabatan ?? $jabatan->gol_jabatan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Home Base</th>
                            <td>{{ $version->home_base ?? $jabatan->home_base ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Lokasi Kerja</th>
                            <td>{{ $version->lokasi_kerja ?? $jabatan->lokasi_kerja ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Versi Aktif</th>
                            <td>{{ $version ? 'Versi '.$version->version_number : '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="detail-card mb-4">
                <div class="detail-card-header">
                    Pemangku Jabatan Saat Ini
                </div>

                <div class="detail-card-body">
                    @forelse($jabatan->pemangkuSaatIni as $pegawai)
                        <div class="holder-card">
                            <div class="holder-name">{{ $pegawai->nama }}</div>
                            <div class="holder-meta">NIP: {{ $pegawai->nip }}</div>
                            <div class="holder-meta">Departemen: {{ $pegawai->departemenMaster->nama_departemen ?? $pegawai->departemen ?? '-' }}</div>
                            <div class="holder-meta">Status: {{ $pegawai->status ?? '-' }}</div>
                        </div>
                    @empty
                        <div class="alert alert-warning mb-0 rounded-4">
                            Belum ada pegawai aktif yang memegang jabatan ini.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="detail-card">
                <div class="detail-card-header">
                    Riwayat Pemangku Jabatan
                </div>

                <div class="detail-card-body">
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
                                @forelse($jabatan->riwayatPemangku as $riwayat)
                                    <tr>
                                        <td>{{ $riwayat->pegawai?->nama ?? '-' }}</td>
                                        <td class="text-center">{{ $riwayat->nip }}</td>
                                        <td class="text-center">{{ $riwayat->version ? 'Versi '.$riwayat->version->version_number : '-' }}</td>
                                        <td class="text-center">
                                            {{ $riwayat->assigned_at ? \Carbon\Carbon::parse($riwayat->assigned_at)->locale('id')->translatedFormat('d F Y') : '-' }}
                                        </td>
                                        <td class="text-center">
                                            {{ $riwayat->ended_at ? \Carbon\Carbon::parse($riwayat->ended_at)->locale('id')->translatedFormat('d F Y') : '-' }}
                                        </td>
                                        <td class="text-center">
                                            @if($riwayat->is_current)
                                                <span class="status-badge status-active">Aktif</span>
                                            @else
                                                <span class="status-badge status-history">Riwayat</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            Belum ada riwayat pemangku jabatan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
