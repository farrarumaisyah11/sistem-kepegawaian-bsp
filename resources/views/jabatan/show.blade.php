@extends('layouts.app')
@section('title','Detail Jabatan')

@section('content')
@if ($errors->any())
    <div class="alert alert-danger rounded-4 shadow-sm">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $prefix = auth()->user()->role;
    $role = auth()->user()->role;
    $jabatanNotFound = $jabatanNotFound ?? false;
    $j = $jabatan ?? new \App\Models\Jabatan;

    $renderLines = function ($value) {
        if (blank($value)) {
            return ['-'];
        }

        if (is_array($value)) {
            $items = $value;
        } else {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $items = $decoded;
            } else {
                $items = preg_split('/\r\n|\r|\n/', (string) $value);
            }
        }

        $result = [];

        foreach ($items as $item) {
            $item = trim((string) $item);

            if ($item === '') {
                continue;
            }

            $splitItems = preg_split('/\r\n|\r|\n/', $item);

            foreach ($splitItems as $line) {
                $line = trim((string) $line);

                if ($line !== '') {
                    $result[] = $line;
                }
            }
        }

        return count($result) ? $result : ['-'];
    };
@endphp

<div class="jd-page">

    {{-- ACTION BAR HANYA UNTUK ADMIN / HCM --}}
    @if(in_array($role, ['admin', 'hcm']) && !$jabatanNotFound)
        <div class="container-xl d-print-none jd-action-bar">
            <div class="jd-top-actions">
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary jd-btn">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>

                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route($prefix.'.jabatan.edit', $j->id_jabatan) }}" class="btn btn-warning text-dark jd-btn">
                        <i class="bi bi-pencil-square"></i> Edit Data
                    </a>

                    <a href="{{ route('jabatan.approval.page', $j->id_jabatan) }}" class="btn btn-outline-success jd-btn">
                        <i class="bi bi-shield-check"></i> Approval
                    </a>

                    <button type="button" onclick="window.print()" class="btn btn-primary jd-btn">
                        <i class="bi bi-printer"></i> Print
                    </button>

                    <button type="button" id="downloadPdfBtn" class="btn btn-success jd-btn">
                        <i class="bi bi-download"></i> Download PDF
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="jd-paper-a4" id="jabatan-print-area">

        @if($jabatanNotFound)
            <div class="jd-empty-box">
                <div class="jd-empty-icon">
                    <i class="bi bi-briefcase"></i>
                </div>

                <h4>Belum Memiliki Jabatan</h4>

                <p>
                    Job description belum dapat ditampilkan karena data jabatan Anda belum terhubung dengan master jabatan.
                </p>
            </div>
        @else

            {{-- HEADER --}}
            <div class="jd-paper-header">
                <div class="jd-header-grid">
                    <div class="jd-logo-box">
                        <img src="{{ asset('images/logo skk migas.png') }}" alt="SKK Migas">
                    </div>

                    <div class="jd-company-box">
                        <div class="jd-company-name">PT. BUMI SIAK PUSAKO</div>
                        <div class="jd-company-unit">SISTEM INFORMASI SUMBER DAYA MANUSIA</div>
                        <div class="jd-company-address">
                            Gedung Surya Dumai Lt. 6, Jl. Jendral Sudirman No. 395 Pekanbaru 28116
                        </div>
                        <div class="jd-company-contact">
                            Telepon: (62-761) 855764 | Facsimile: (62-761) 855765 | Website: www.bsp.co.id
                        </div>
                    </div>

                    <div class="jd-logo-box">
                        <img src="{{ asset('images/logo bsp.png') }}" alt="BSP">
                    </div>
                </div>

                <div class="jd-title-wrap">
                    <div class="jd-title">JOB DESCRIPTION</div>
                    <div class="jd-subtitle">Laporan Data Jabatan</div>
                </div>
            </div>

            <div class="jd-paper-body">

                {{-- PROFIL JABATAN --}}
                <div class="jd-profile-card jd-section-keep">
                    <div class="jd-profile-badge">PROFIL JABATAN</div>

                    <h2>{{ $j->nama_jabatan ?? '-' }}</h2>

                    <div class="jd-profile-meta">
                        <span><strong>Departemen:</strong> {{ $j->departemen ?? '-' }}</span>
                        <span><strong>Golongan:</strong> {{ $j->gol_jabatan ?? '-' }}</span>
                    </div>

                    <div class="jd-chip-wrap">
                        <span class="jd-chip">{{ $j->home_base ?? 'Home Base -' }}</span>
                        <span class="jd-chip">{{ $j->lokasi_kerja ?? 'Lokasi Kerja -' }}</span>
                        <span class="jd-chip">Parent: {{ $j->parent_jabatan ?? '-' }}</span>
                    </div>
                </div>

                {{-- INFORMASI UMUM --}}
                <div class="jd-section-block jd-section-keep">
                    <div class="jd-section-heading">
                        <i class="bi bi-card-list"></i>
                        Informasi Umum Jabatan
                    </div>

                    <table class="jd-meta-table">
                        <tbody>
                            <tr>
                                <th>Nama Jabatan</th>
                                <td>{{ $j->nama_jabatan ?? '-' }}</td>
                                <th>Departemen</th>
                                <td>{{ $j->departemen ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Golongan Jabatan</th>
                                <td>{{ $j->gol_jabatan ?? '-' }}</td>
                                <th>Home Base</th>
                                <td>{{ $j->home_base ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Lokasi Kerja</th>
                                <td>{{ $j->lokasi_kerja ?? '-' }}</td>
                                <th>Parent Jabatan</th>
                                <td>{{ $j->parent_jabatan ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- TUJUAN JABATAN --}}
                <div class="jd-section-block jd-section-keep">
                    <div class="jd-section-heading">
                        <i class="bi bi-bullseye"></i>
                        Tujuan Jabatan
                    </div>

                    <div class="jd-text-block">
                        {!! nl2br(e($j->tujuan_jabatan ?? '-')) !!}
                    </div>
                </div>

                {{-- TANGGUNG JAWAB --}}
                <div class="jd-section-block">
                    <div class="jd-section-heading">
                        <i class="bi bi-list-check"></i>
                        Tanggung Jawab Jabatan
                    </div>

                    <div class="jd-list-block">
                        <ol class="jd-list">
                            @foreach($renderLines($j->tanggung_jawab) as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ol>
                    </div>
                </div>

                {{-- TANTANGAN --}}
                <div class="jd-section-block">
                    <div class="jd-section-heading">
                        <i class="bi bi-exclamation-triangle"></i>
                        Tantangan Jabatan
                    </div>

                    <div class="jd-list-block">
                        <ol class="jd-list">
                            @foreach($renderLines($j->tantangan_jabatan) as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ol>
                    </div>
                </div>

                {{-- DIMENSI DAN WEWENANG --}}
                <div class="jd-section-block jd-section-keep">
                    <div class="jd-section-heading">
                        <i class="bi bi-diagram-3"></i>
                        Dimensi dan Wewenang
                    </div>

                    <div class="jd-grid-2">
                        <div class="jd-card">
                            <div class="jd-card-title">Dimensi Jabatan</div>

                            <table class="jd-info-table">
                                <tr>
                                    <th>Dimensi Keuangan</th>
                                    <td>{{ $j->dim_keuangan ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Dimensi Non Keuangan</th>
                                    <td>{{ $j->dim_nonkeuangan ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Bawahan Langsung</th>
                                    <td>{{ $j->bawahan_langsung ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="jd-card">
                            <div class="jd-card-title">Wewenang</div>

                            <table class="jd-info-table">
                                <tr>
                                    <th>Finansial</th>
                                    <td>{{ $j->finansial ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Non Finansial</th>
                                    <td>{{ $j->non_finansial ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- HUBUNGAN KERJA --}}
                <div class="jd-section-block">
                    <div class="jd-section-heading">
                        <i class="bi bi-people"></i>
                        Hubungan Kerja
                    </div>

                    <div class="jd-grid-2">
                        <div class="jd-card">
                            <div class="jd-card-title">Internal Perusahaan</div>

                            <div class="jd-text-inside">
                                <ol class="jd-list jd-list-plain">
                                    @foreach($renderLines($j->internal_perusahaan) as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ol>
                            </div>
                        </div>

                        <div class="jd-card">
                            <div class="jd-card-title">Eksternal Perusahaan</div>

                            <div class="jd-text-inside">
                                <ol class="jd-list jd-list-plain">
                                    @foreach($renderLines($j->external_perusahaan) as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PERSYARATAN --}}
                <div class="jd-section-block">
                    <div class="jd-section-heading">
                        <i class="bi bi-award"></i>
                        Persyaratan Jabatan
                    </div>

                    <div class="jd-grid-2">
                        <div class="jd-card">
                            <div class="jd-card-title">Pengetahuan & Keterampilan</div>

                            <div class="jd-text-inside">
                                <ol class="jd-list jd-list-plain">
                                    @foreach($renderLines($j->pengetahuan_keterampilan) as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ol>
                            </div>
                        </div>

                        <div class="jd-card">
                            <div class="jd-card-title">Kompetensi</div>

                            <div class="jd-text-inside">
                                <ol class="jd-list jd-list-plain">
                                    @foreach($renderLines($j->kompetensi) as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="jd-grid-1">
                        <div class="jd-card">
                            <div class="jd-card-title">Syarat Kompetensi Jabatan</div>

                            <div class="jd-text-inside">
                                <ol class="jd-list jd-list-plain">
                                    @foreach($renderLines($j->syarat_kompetensi_jabatan) as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- STRUKTUR ORGANISASI --}}
                @if(!empty($j->struktur_file))
                    <div class="jd-section-block jd-section-keep">
                        <div class="jd-section-heading">
                            <i class="bi bi-building"></i>
                            Struktur Organisasi
                        </div>

                        <div class="jd-org-box">
                            @php
                                $ext = strtolower(pathinfo($j->struktur_file, PATHINFO_EXTENSION));
                            @endphp

                            @if(in_array($ext, ['png', 'jpg', 'jpeg', 'webp']))
                                <img src="{{ asset('storage/'.$j->struktur_file) }}"
                                     alt="Struktur Organisasi"
                                     class="jd-org-image">
                            @else
                                <a href="{{ asset('storage/'.$j->struktur_file) }}" target="_blank" class="btn btn-outline-primary">
                                    <i class="bi bi-file-earmark-pdf"></i> Lihat File Struktur Organisasi
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="jd-footer-note">
                    Dokumen ini dihasilkan oleh Sistem Informasi SDM PT. Bumi Siak Pusako.
                </div>
            </div>
        @endif
    </div>
</div>

<style>
:root{
    --jd-primary:#59684a;
    --jd-primary-dark:#3f4d35;
    --jd-primary-soft:#e7eddc;
    --jd-primary-soft-2:#f7f9f2;
    --jd-border:#d7dfcc;
    --jd-border-strong:#c5d0b8;
    --jd-text:#101828;
    --jd-muted:#667085;
    --jd-label:#344054;
    --jd-white:#ffffff;
}

html, body{
    background:#f6f8f4 !important;
}

.jd-page{
    min-height:100vh;
    background:#f6f8f4;
    font-family:"Inter", "Segoe UI", Arial, sans-serif;
    color:var(--jd-text);
    padding:28px 0 50px;
}

.jd-action-bar{
    padding-top:4px;
    padding-bottom:18px;
}

.jd-top-actions{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
}

.jd-btn{
    border-radius:10px;
    font-weight:600;
    padding:9px 16px;
}

.jd-paper-a4{
    width:210mm;
    min-height:297mm;
    margin:0 auto;
    background:var(--jd-white);
    border:1px solid #dfe6d7;
    box-shadow:0 14px 35px rgba(30, 41, 59, .08);
    overflow:hidden;
}

.jd-paper-header{
    padding:10mm 12mm 5mm 12mm;
    border-bottom:1px solid var(--jd-border-strong);
    background:#ffffff;
}

.jd-header-grid{
    display:grid;
    grid-template-columns:72px 1fr 72px;
    gap:14px;
    align-items:center;
}

.jd-logo-box{
    display:flex;
    align-items:center;
    justify-content:center;
}

.jd-logo-box img{
    max-width:56px;
    max-height:56px;
    object-fit:contain;
}

.jd-company-box{
    text-align:center;
}

.jd-company-name{
    font-size:17px;
    font-weight:800;
    line-height:1.2;
    letter-spacing:.03em;
    color:var(--jd-primary-dark);
    text-transform:uppercase;
}

.jd-company-unit{
    margin-top:4px;
    font-size:10px;
    font-weight:800;
    letter-spacing:.07em;
    color:#566447;
    text-transform:uppercase;
}

.jd-company-address,
.jd-company-contact{
    margin-top:4px;
    font-size:9px;
    line-height:1.4;
    color:#4b5563;
}

.jd-title-wrap{
    margin-top:12px;
    border:1px solid var(--jd-border);
    background:#ffffff;
    padding:12px 14px;
    text-align:center;
    border-radius:12px;
}

.jd-title{
    font-size:17px;
    font-weight:800;
    line-height:1.2;
    letter-spacing:.09em;
    color:var(--jd-primary-dark);
    text-transform:uppercase;
}

.jd-subtitle{
    margin-top:4px;
    font-size:10px;
    font-weight:600;
    color:var(--jd-muted);
}

.jd-paper-body{
    padding:6mm 12mm 9mm 12mm;
    background:#ffffff;
}

.jd-profile-card{
    border:1px solid var(--jd-border);
    border-radius:18px;
    padding:18px 20px;
    margin-bottom:14px;
    background:linear-gradient(180deg,#ffffff,#fbfcf8);
}

.jd-profile-badge{
    display:inline-block;
    padding:6px 14px;
    border-radius:999px;
    background:var(--jd-primary-soft);
    color:#324025;
    font-size:11px;
    font-weight:800;
    letter-spacing:.08em;
    text-transform:uppercase;
    margin-bottom:10px;
}

.jd-profile-card h2{
    margin:0;
    font-size:25px;
    line-height:1.2;
    font-weight:800;
    color:#0f172a;
}

.jd-profile-meta{
    display:flex;
    flex-wrap:wrap;
    gap:12px 18px;
    margin-top:10px;
    font-size:13px;
    color:#1f2937;
}

.jd-profile-meta strong{
    color:#0f172a;
}

.jd-chip-wrap{
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    margin-top:12px;
}

.jd-chip{
    display:inline-flex;
    align-items:center;
    padding:6px 12px;
    border:1px solid var(--jd-border);
    border-radius:999px;
    background:#fbfcf8;
    color:#344054;
    font-size:11px;
    font-weight:700;
}

.jd-section-block{
    margin-top:12px;
    border:1px solid var(--jd-border);
    border-radius:14px;
    background:#ffffff;
    overflow:hidden;
}

.jd-section-keep{
    page-break-inside:avoid;
    break-inside:avoid;
}

.jd-section-heading{
    display:flex;
    align-items:center;
    gap:8px;
    padding:11px 14px;
    background:var(--jd-primary-soft);
    border-bottom:1px solid var(--jd-border);
    font-size:13px;
    font-weight:800;
    letter-spacing:.02em;
    color:#27351e;
    text-transform:none;
}

.jd-section-heading i{
    font-size:14px;
    color:#405031;
}

.jd-meta-table,
.jd-info-table{
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
}

.jd-meta-table th,
.jd-meta-table td,
.jd-info-table th,
.jd-info-table td{
    border:1px solid var(--jd-border);
    padding:10px 12px;
    font-size:12px;
    line-height:1.5;
    vertical-align:top;
}

.jd-meta-table th,
.jd-info-table th{
    background:var(--jd-primary-soft-2);
    font-weight:800;
    color:var(--jd-label);
    text-align:left;
}

.jd-meta-table th{
    width:22%;
}

.jd-info-table th{
    width:40%;
}

.jd-meta-table td,
.jd-info-table td{
    font-weight:600;
    color:#111827;
    word-break:break-word;
}

.jd-text-block,
.jd-text-inside{
    padding:14px;
    font-size:12px;
    line-height:1.75;
    color:#111827;
    text-align:justify;
}

.jd-list-block{
    padding:12px 18px;
}

.jd-list{
    margin:0;
    padding-left:18px;
    font-size:12px;
    line-height:1.75;
    color:#111827;
}

.jd-list li{
    margin-bottom:6px;
}

.jd-list-plain{
    margin:0;
}

.jd-grid-2{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:12px;
    padding:14px;
}

.jd-grid-1{
    padding:0 14px 14px 14px;
}

.jd-card{
    border:1px solid var(--jd-border);
    border-radius:12px;
    background:#ffffff;
    overflow:hidden;
    break-inside:avoid;
    page-break-inside:avoid;
}

.jd-card-title{
    padding:10px 12px;
    background:var(--jd-primary-soft-2);
    border-bottom:1px solid var(--jd-border);
    font-size:12px;
    font-weight:800;
    color:#27351e;
}

.jd-org-box{
    padding:18px;
    text-align:center;
}

.jd-org-image{
    max-width:100%;
    max-height:560px;
    object-fit:contain;
    border:1px solid var(--jd-border);
    border-radius:12px;
}

.jd-footer-note{
    margin-top:16px;
    padding-top:12px;
    border-top:1px solid #d1d5db;
    text-align:center;
    font-size:11px;
    color:#6b7280;
}

.jd-empty-box{
    min-height:360px;
    padding:70px 30px;
    text-align:center;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
}

.jd-empty-icon{
    width:70px;
    height:70px;
    border-radius:50%;
    background:var(--jd-primary-soft);
    display:flex;
    align-items:center;
    justify-content:center;
    color:var(--jd-primary-dark);
    font-size:30px;
    margin-bottom:16px;
}

.jd-empty-box h4{
    font-weight:800;
    color:#111827;
}

.jd-empty-box p{
    max-width:540px;
    margin:0 auto;
    color:#667085;
    font-size:14px;
    line-height:1.6;
}

@media (max-width: 992px){
    .jd-page{
        padding:14px 0 30px;
    }

    .jd-paper-a4{
        width:100%;
        min-height:auto;
        border-left:none;
        border-right:none;
    }

    .jd-header-grid{
        grid-template-columns:1fr;
        text-align:center;
    }

    .jd-grid-2{
        grid-template-columns:1fr;
    }

    .jd-profile-card h2{
        font-size:22px;
    }

    .jd-meta-table th,
    .jd-meta-table td,
    .jd-info-table th,
    .jd-info-table td,
    .jd-text-block,
    .jd-text-inside,
    .jd-list{
        font-size:12px;
    }
}

@page{
    size:A4 portrait;
    margin:0;
}

/* PRINT HARUS SAMA DENGAN SHOW */
@media print{
    html, body{
        margin:0 !important;
        padding:0 !important;
        background:#ffffff !important;
        font-family:"Inter", "Segoe UI", Arial, sans-serif !important;
    }

    .d-print-none,
    .jd-action-bar,
    .jd-top-actions{
        display:none !important;
    }

    .jd-page{
        background:#ffffff !important;
        padding:0 !important;
        margin:0 !important;
    }

    .jd-paper-a4{
        width:210mm !important;
        min-height:297mm !important;
        margin:0 auto !important;
        background:#ffffff !important;
        border:1px solid #d5dbd1 !important;
        box-shadow:none !important;
        overflow:visible !important;
    }

    .jd-header-grid{
        display:grid !important;
        grid-template-columns:72px 1fr 72px !important;
        gap:14px !important;
        align-items:center !important;
    }

    .jd-grid-2{
        display:grid !important;
        grid-template-columns:1fr 1fr !important;
        gap:12px !important;
        padding:14px !important;
    }

    .jd-paper-header,
    .jd-paper-body,
    .jd-profile-card,
    .jd-chip,
    .jd-section-block,
    .jd-card,
    .jd-title-wrap,
    .jd-section-heading,
    .jd-card-title,
    .jd-meta-table th,
    .jd-info-table th{
        -webkit-print-color-adjust:exact !important;
        print-color-adjust:exact !important;
    }

    .jd-profile-card,
    .jd-section-block,
    .jd-card{
        break-inside:avoid !important;
        page-break-inside:avoid !important;
    }
}
</style>

@if(in_array($role, ['admin', 'hcm']) && !$jabatanNotFound)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const downloadBtn = document.getElementById('downloadPdfBtn');
        if (!downloadBtn) return;

        downloadBtn.addEventListener('click', function () {
            const element = document.getElementById('jabatan-print-area');
            const originalText = downloadBtn.innerHTML;

            downloadBtn.disabled = true;
            downloadBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyiapkan PDF...';

            const opt = {
                margin: 0,
                filename: 'jabatan-{{ $j->id_jabatan }}-{{ \Illuminate\Support\Str::slug($j->nama_jabatan ?? "jabatan") }}.pdf',
                image: { type: 'jpeg', quality: 1 },
                html2canvas: {
                    scale: 2.2,
                    useCORS: true,
                    backgroundColor: '#ffffff',
                    scrollY: 0
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                },
                pagebreak: {
                    mode: ['css', 'legacy'],
                    avoid: ['.jd-card', '.jd-section-keep', '.jd-profile-card']
                }
            };

            html2pdf()
                .set(opt)
                .from(element)
                .save()
                .then(() => {
                    downloadBtn.disabled = false;
                    downloadBtn.innerHTML = originalText;
                })
                .catch(() => {
                    downloadBtn.disabled = false;
                    downloadBtn.innerHTML = originalText;
                    alert('Gagal membuat PDF. Coba lagi.');
                });
        });
    });
    </script>
@endif

@if($jabatanNotFound)
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'warning',
            title: 'Belum memiliki jabatan',
            text: 'Job description belum dapat ditampilkan karena data jabatan Anda belum terhubung dengan master jabatan.',
            confirmButtonText: 'Mengerti'
        });
    });
    </script>
@endif

@endsection