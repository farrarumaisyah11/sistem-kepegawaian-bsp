@extends('layouts.app')
@section('title','Detail Pegawai')
@section('content')

@if (session('warning'))
    <div class="container-xl d-print-none mt-3">
        <div class="alert alert-warning">
            {{ session('warning') }}
        </div>
    </div>
@endif

@php
    $role = auth()->user()->role ?? null;

    $tglMasuk = (!empty($pegawai->tgl_masuk) && $pegawai->tgl_masuk !== '1111-11-11')
        ? \Carbon\Carbon::parse($pegawai->tgl_masuk)->translatedFormat('d F Y')
        : '-';

    $tmtGolUpah = (!empty($pegawai->tmt_gol_upah) && $pegawai->tmt_gol_upah !== '1111-11-11')
        ? \Carbon\Carbon::parse($pegawai->tmt_gol_upah)->translatedFormat('d F Y')
        : '-';

    $tmtGolJabatan = (!empty($pegawai->tmt_gol_jabatan) && $pegawai->tmt_gol_jabatan !== '1111-11-11')
        ? \Carbon\Carbon::parse($pegawai->tmt_gol_jabatan)->translatedFormat('d F Y')
        : '-';
@endphp

<div class="hr-page py-4">
    <div class="container-xl d-print-none mb-3">
        <div class="top-actions {{ $role === 'pegawai' ? 'pegawai-actions' : '' }}">

            @if($role !== 'pegawai')
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            @endif

            <div class="d-flex gap-2 flex-wrap">
                @if(in_array(auth()->user()->role, ['admin', 'hcm']))
                    <a href="{{ route(auth()->user()->role.'.pegawai.edit', $pegawai->nip) }}" class="btn btn-warning text-dark">
                        <i class="bi bi-pencil-square"></i> Edit Data
                    </a>
                @endif

                <button type="button" onclick="window.print()" class="btn btn-primary">
                    <i class="bi bi-printer"></i> Print
                </button>

                <button type="button" id="downloadPdfBtn" class="btn btn-success">
                    <i class="bi bi-download"></i> Download PDF
                </button>
            </div>
        </div>
    </div>

    <div class="paper-wrap">
        <div class="paper-a4" id="pegawai-print-area">

            {{-- HEADER --}}
            <div class="paper-header">
                <div class="header-grid">
                    <div class="logo-box left">
                        <img src="{{ asset('images/logo skk migas.png') }}" alt="SKK Migas">
                    </div>

                    <div class="company-box">
                        <div class="company-name">PT. BUMI SIAK PUSAKO</div>
                        <div class="company-unit">Sistem Informasi Sumber Daya Manusia</div>
                        <div class="company-address">
                            Gedung Surya Dumai Lt. 6, Jl. Jendral Sudirman No. 395 Pekanbaru 28116
                        </div>
                        <div class="company-contact">
                            Telepon: (62-761) 855764 | Facsimile: (62-761) 855765 | Website: www.bsp.co.id
                        </div>
                    </div>

                    <div class="logo-box right">
                        <img src="{{ asset('images/logo bsp.png') }}" alt="BSP">
                    </div>
                </div>

                <div class="doc-title-wrap">
                    <div class="doc-title">LAPORAN DATA PEGAWAI</div>
                    <div class="doc-subtitle">
                        Ringkasan Profil, Riwayat, dan Kompetensi Karyawan
                    </div>
                </div>
            </div>

            <div class="paper-body">
                {{-- PROFILE HEADER --}}
                <div class="profile-hero">
                    <div class="profile-photo-box">
                        @if($pegawai->foto)
                            <img src="{{ route('pegawai.foto', $pegawai->nip) }}?v={{ md5($pegawai->foto) }}"
                                alt="Foto {{ $pegawai->nama ?? 'Pegawai' }}"
                                class="profile-photo">
                        @else
                            <div class="photo-placeholder">
                                <i class="bi bi-person-bounding-box"></i>
                                <span>Belum ada foto</span>
                            </div>
                        @endif
                    </div>

                    <div class="profile-main">
                        <div class="identity-badge">PROFIL PEGAWAI</div>
                        <h1 class="pegawai-name">{{ $pegawai->nama ?? '-' }}</h1>

                        <div class="pegawai-meta">
                            <span><strong>NIP:</strong> {{ $pegawai->nip ?? '-' }}</span>
                            <span><strong>Jabatan:</strong> {{ $pegawai->jabatan ?? '-' }}</span>
                            <span><strong>Departemen:</strong> {{ $pegawai->departemen ?? '-' }}</span>
                        </div>

                        <div class="status-row">
                            <span class="info-chip">{{ $pegawai->status ?? 'Status belum diisi' }}</span>
                            <span class="info-chip">{{ $pegawai->hubungan_kerja ?? 'Hubungan kerja belum diisi' }}</span>
                            <span class="info-chip">{{ $pegawai->profesional ?? 'Profesional belum diisi' }}</span>
                            <span class="info-chip">{{ $pegawai->lokasi_kerja ?? 'Lokasi belum diisi' }}</span>
                        </div>
                    </div>
                </div>

                {{-- INFORMASI PRIBADI & KEPEGAWAIAN --}}
                <div class="section-block section-keep">
                    <div class="section-heading">
                        <i class="bi bi-person-vcard"></i>
                        <span>Informasi Pribadi dan Kepegawaian</span>
                    </div>

                    <div class="info-grid">
                        <div class="info-card">
                            <div class="info-card-title">Data Pribadi</div>
                            <table class="info-table">
                                <tr>
                                    <th>Nama Lengkap</th>
                                    <td>{{ $pegawai->nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>NIP</th>
                                    <td>{{ $pegawai->nip ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Tempat, Tanggal Lahir</th>
                                    <td>
                                        {{ $pegawai->tempat_lahir ?? '-' }}
                                        {{ (!empty($pegawai->tempat_lahir) && !empty($pegawai->tgl_lahir) && $pegawai->tgl_lahir !== '1111-11-11') ? ', ' : '' }}
                                        {{ (!empty($pegawai->tgl_lahir) && $pegawai->tgl_lahir !== '1111-11-11') ? \Carbon\Carbon::parse($pegawai->tgl_lahir)->translatedFormat('d F Y') : '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Jenis Kelamin</th>
                                    <td>{{ $pegawai->jenkel ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Agama</th>
                                    <td>{{ $pegawai->agama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Alamat</th>
                                    <td>{{ $pegawai->alamat ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="info-card">
                            <div class="info-card-title">Data Kepegawaian</div>
                            <table class="info-table">
                                <tr>
                                    <th>Departemen</th>
                                    <td>{{ $pegawai->departemen ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Jabatan</th>
                                    <td>{{ $pegawai->jabatan ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>{{ $pegawai->status ?? '-' }}</td>
                                </tr>
                                    <tr>
                                    <th>Hubungan Kerja</th>
                                    <td>{{ $pegawai->hubungan_kerja ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Lokasi Kerja</th>
                                    <td>{{ $pegawai->lokasi_kerja ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Mulai Kerja</th>
                                    <td>{{ $tglMasuk }}</td>
                                </tr>
                                <tr>
                                    <th>Profesional</th>
                                    <td>{{ $pegawai->profesional ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="section-block section-keep">
                    <div class="section-heading">
                        <i class="bi bi-diagram-3"></i>
                        <span>Golongan</span>
                    </div>

                    <div class="info-grid">
                        <div class="info-card">
                            <div class="info-card-title">Golongan Upah</div>
                            <table class="info-table">
                                <tr>
                                    <th>Golongan Upah</th>
                                    <td>{{ $pegawai->gol_upah ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>TMT Golongan Upah</th>
                                    <td>{{ $tmtGolUpah }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="info-card">
                            <div class="info-card-title">Golongan Jabatan</div>
                            <table class="info-table">
                                <tr>
                                    <th>Golongan Jabatan</th>
                                    <td>{{ $pegawai->gol_jabatan ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>TMT Golongan Jabatan</th>
                                    <td>{{ $tmtGolJabatan }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- PENDIDIKAN --}}
                <div class="section-block">
                    <div class="section-heading">
                        <i class="bi bi-mortarboard"></i>
                        <span>Riwayat Pendidikan</span>
                    </div>

                    @if($pegawai->pendidikan->isEmpty())
                        <div class="empty-state">Belum ada data pendidikan.</div>
                    @else
                        <div class="table-responsive">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal Mulai</th>
                                        <th>Tanggal Selesai</th>
                                        <th>Jenjang</th>
                                        <th>Institusi</th>
                                        <th>Jurusan</th>
                                        <th>Lokasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pegawai->pendidikan as $i => $r)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $r->pendidikan_mulai ? \Carbon\Carbon::parse($r->pendidikan_mulai)->translatedFormat('d M Y') : '-' }}</td>
                                        <td>{{ $r->pendidikan_selesai ? \Carbon\Carbon::parse($r->pendidikan_selesai)->translatedFormat('d M Y') : '-' }}</td>
                                        <td>{{ $r->jenjang_pendidikan ?? '-' }}</td>
                                        <td>{{ $r->nama_institusi ?? '-' }}</td>
                                        <td>{{ $r->jurusan ?? '-' }}</td>
                                        <td>{{ $r->lokasi_pendidikan ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- KURSUS --}}
                <div class="section-block">
                    <div class="section-heading">
                        <i class="bi bi-journal-check"></i>
                        <span>Kursus & Pelatihan</span>
                    </div>

                    @if($pegawai->kursus->isEmpty())
                        <div class="empty-state">Belum ada data kursus atau pelatihan.</div>
                    @else
                        <div class="table-responsive">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Mulai</th>
                                        <th>Selesai</th>
                                        <th>Jenis Kursus</th>
                                        <th>Nama Kegiatan</th>
                                        <th>Berlaku Mulai</th>
                                        <th>Berlaku Selesai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pegawai->kursus as $i => $r)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $r->tanggal_mulai_kursus ? \Carbon\Carbon::parse($r->tanggal_mulai_kursus)->translatedFormat('d M Y') : '-' }}</td>
                                        <td>{{ $r->tanggal_selesai_kursus ? \Carbon\Carbon::parse($r->tanggal_selesai_kursus)->translatedFormat('d M Y') : '-' }}</td>
                                        <td>{{ $r->jenis_kursus ?? '-' }}</td>
                                        <td>{{ $r->nama_kegiatan_kursus ?? '-' }}</td>
                                        <td>{{ $r->tanggal_mulai_berlaku ? \Carbon\Carbon::parse($r->tanggal_mulai_berlaku)->translatedFormat('d M Y') : '-' }}</td>
                                        <td>{{ $r->tanggal_selesai_berlaku ? \Carbon\Carbon::parse($r->tanggal_selesai_berlaku)->translatedFormat('d M Y') : '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- PENGALAMAN BSP --}}
                <div class="section-block">
                    <div class="section-heading">
                        <i class="bi bi-building"></i>
                        <span>Pengalaman Kerja di BSP</span>
                    </div>

                    @if($pegawai->pengalamanBsp->isEmpty())
                        <div class="empty-state">Belum ada data pengalaman kerja di BSP.</div>
                    @else
                        <div class="table-responsive">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Mulai</th>
                                        <th>Selesai</th>
                                        <th>Jabatan</th>
                                        <th>Lokasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pegawai->pengalamanBsp as $i => $r)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $r->pglmn_bsp_mulai ? \Carbon\Carbon::parse($r->pglmn_bsp_mulai)->translatedFormat('d M Y') : '-' }}</td>
                                        <td>{{ $r->pglmn_bsp_selesai ? \Carbon\Carbon::parse($r->pglmn_bsp_selesai)->translatedFormat('d M Y') : '-' }}</td>
                                        <td>{{ $r->pengalaman_jabatan ?? '-' }}</td>
                                        <td>{{ $r->pengalaman_lokasi ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- PENGALAMAN LUAR --}}
                <div class="section-block">
                    <div class="section-heading">
                        <i class="bi bi-briefcase"></i>
                        <span>Pengalaman Kerja di Luar BSP</span>
                    </div>

                    @if($pegawai->pengalamanLuarBsp->isEmpty())
                        <div class="empty-state">Belum ada data pengalaman kerja di luar BSP.</div>
                    @else
                        <div class="table-responsive">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Mulai</th>
                                        <th>Selesai</th>
                                        <th>Jabatan</th>
                                        <th>Lokasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pegawai->pengalamanLuarBsp as $i => $r)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $r->pglmn_luar_bsp_mulai ? \Carbon\Carbon::parse($r->pglmn_luar_bsp_mulai)->translatedFormat('d M Y') : '-' }}</td>
                                        <td>{{ $r->pglmn_luar_bsp_selesai ? \Carbon\Carbon::parse($r->pglmn_luar_bsp_selesai)->translatedFormat('d M Y') : '-' }}</td>
                                        <td>{{ $r->pengalaman_luar_jabatan ?? '-' }}</td>
                                        <td>{{ $r->pengalaman_luar_lokasi ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- KELUARGA --}}
                <div class="section-block">
                    <div class="section-heading">
                        <i class="bi bi-people"></i>
                        <span>Data Keluarga</span>
                    </div>

                    @if($pegawai->keluarga->isEmpty())
                        <div class="empty-state">Belum ada data keluarga.</div>
                    @else
                        <div class="table-responsive">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>Tanggal Lahir</th>
                                        <th>Keterangan Keluarga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pegawai->keluarga as $i => $r)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $r->nama_keluarga ?? '-' }}</td>
                                        <td>{{ $r->tanggal_keluarga ? \Carbon\Carbon::parse($r->tanggal_keluarga)->translatedFormat('d M Y') : '-' }}</td>
                                        <td>{{ $r->ket_keluarga ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- PENILAIAN --}}
                <div class="section-block">
                    <div class="section-heading">
                        <i class="bi bi-graph-up-arrow"></i>
                        <span>Penilaian / Kompetensi</span>
                    </div>

                    @if($pegawai->penilaian->isEmpty())
                        <div class="empty-state">Belum ada data penilaian / kompetensi.</div>
                    @else
                        <div class="table-responsive">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tahun</th>
                                        <th>Nilai</th>
                                        <th>Dasar Penilaian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pegawai->penilaian as $i => $r)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $r->tahun_penilaian ?? '-' }}</td>
                                        <td>{{ $r->nilai_penilaian ?? '-' }}</td>
                                        <td>{{ $r->dasar_penilaian ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- FOOTER --}}
                <div class="document-footer">
                    Dokumen ini dihasilkan oleh Sistem Informasi SDM PT. Bumi Siak Pusako.
                </div>
            </div>
        </div>
    </div>
</div>

<style>
:root{
    --hr-primary:#5f6b4b;
    --hr-primary-dark:#47513a;
    --hr-primary-soft:#eef3e7;
    --hr-primary-soft-2:#f6f8f2;
    --hr-border:#d8dfcf;
    --hr-text:#1f2937;
    --hr-muted:#6b7280;
    --hr-bg:#ffffff;
    --hr-white:#ffffff;
    --hr-table-head:#f2f5ed;
    --hr-label:#355070;
    --hr-shadow:none;
}

/* WRAPPER UTAMA */
.hr-page{
    min-height:100vh;
    font-family:"Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    color:var(--hr-text);
    padding-top:0 !important;
    padding-bottom:0 !important;
}

.top-actions{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
}

.top-actions.pegawai-actions{
    justify-content:flex-end;
}

.paper-wrap{
    padding:0 !important;
    margin:0 !important;
}

/* KERTAS */
.paper-a4{
    width:210mm;
    min-height:297mm;
    margin:0 auto;
    background:var(--hr-white) !important;
    border:none !important;
    border-radius:0 !important;
    box-shadow:none !important;
    overflow:hidden;
}

/* HEADER */
.paper-header{
    padding:8mm 12mm 4mm 12mm;
    background:#ffffff !important;
    border-bottom:1px solid var(--hr-border);
}

.header-grid{
    display:grid;
    grid-template-columns:54px 1fr 54px;
    gap:8px;
    align-items:center;
}

.logo-box{
    display:flex;
    align-items:center;
    justify-content:center;
}

.logo-box img{
    max-width:40px;
    max-height:40px;
    object-fit:contain;
}

.company-box{
    text-align:center;
}

.company-name{
    font-size:14px;
    font-weight:800;
    letter-spacing:.04em;
    color:var(--hr-primary-dark);
    text-transform:uppercase;
    line-height:1.2;
}

.company-unit{
    margin-top:2px;
    font-size:8px;
    font-weight:700;
    color:var(--hr-primary);
    text-transform:uppercase;
    letter-spacing:.05em;
}

.company-address,
.company-contact{
    font-size:8px;
    line-height:1.25;
    margin-top:2px;
    color:#4b5563;
}

.doc-title-wrap{
    margin-top:6px;
    padding:7px 10px;
    border:1px solid var(--hr-border);
    background:#ffffff;
    border-radius:10px;
    text-align:center;
}

.doc-title{
    font-size:13px;
    letter-spacing:.04em;
    font-weight:800;
    color:var(--hr-primary-dark);
    text-transform:uppercase;
    line-height:1.2;
}

.doc-subtitle{
    font-size:8px;
    margin-top:2px;
    color:var(--hr-muted);
    font-weight:600;
    line-height:1.2;
}

/* BODY */
.paper-body{
    padding:4mm 10mm 8mm 10mm;
    background:#ffffff;
}

/* PROFIL */
.profile-hero{
    display:grid;
    grid-template-columns:120px 1fr;
    gap:16px;
    padding:12px;
    align-items:center;
    border:1px solid var(--hr-border);
    background:linear-gradient(135deg, #ffffff 0%, #fafcf8 100%);
    border-radius:18px;
    page-break-inside:avoid;
    break-inside:avoid;
}

.profile-photo-box{
    width:120px;
    height:150px;
    border:1px solid var(--hr-border);
    border-radius:14px;
    overflow:hidden;
    background:#f8fafc;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-shrink:0;
}

.profile-photo{
    width:100%;
    height:100%;
    object-fit:cover;
}

.photo-placeholder{
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:6px;
    color:#94a3b8;
    font-size:12px;
    text-align:center;
}

.photo-placeholder i{
    font-size:38px;
}

.profile-main{
    min-width:0;
    padding-left:6px;
}

.identity-badge{
    display:inline-block;
    padding:5px 10px;
    border-radius:999px;
    background:var(--hr-primary-soft);
    color:var(--hr-primary-dark);
    font-size:10px;
    font-weight:800;
    letter-spacing:.06em;
    margin-bottom:8px;
}

.pegawai-name{
    margin:0 0 6px 0;
    font-size:22px;
    font-weight:800;
    color:#111827;
    line-height:1.15;
    word-break:break-word;
}

.pegawai-meta{
    margin-top:6px;
    display:flex;
    flex-wrap:wrap;
    gap:6px 14px;
    font-size:12px;
    color:#374151;
    line-height:1.4;
}

.status-row{
    margin-top:10px;
    display:flex;
    flex-wrap:wrap;
    gap:8px;
}

.info-chip{
    display:inline-flex;
    align-items:center;
    padding:5px 10px;
    border-radius:999px;
    background:#f8faf7;
    border:1px solid var(--hr-border);
    color:var(--hr-primary-dark);
    font-size:10px;
    font-weight:700;
    line-height:1.2;
}

/* SECTION */
.section-block{
    margin-top:10px;
    border:1px solid var(--hr-border);
    border-radius:16px;
    background:#ffffff;
    overflow:hidden;
}

.section-keep{
    page-break-inside:avoid;
    break-inside:avoid;
}

.section-heading{
    display:flex;
    align-items:center;
    gap:8px;
    padding:9px 12px;
    background:linear-gradient(135deg, var(--hr-primary-soft) 0%, #dde6d1 100%);
    color:var(--hr-primary-dark);
    font-size:12px;
    font-weight:800;
    letter-spacing:.01em;
    border-bottom:1px solid var(--hr-border);
    line-height:1.2;
}

.section-heading i{
    font-size:12px;
}

/* GRID INFO */
.info-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:12px;
    padding:12px;
}

.info-card{
    border:1px solid var(--hr-border);
    border-radius:0;
    overflow:hidden;
    background:#ffffff;
    page-break-inside:avoid;
    break-inside:avoid;
}

.info-card-title{
    padding:8px 10px;
    background:var(--hr-primary-soft-2);
    color:var(--hr-primary-dark);
    font-size:11px;
    font-weight:800;
    border-bottom:1px solid var(--hr-border);
    line-height:1.2;
}

.info-table{
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
}

.info-table th,
.info-table td{
    padding:8px 10px;
    border:1px solid var(--hr-border);
    vertical-align:top;
    font-size:10px;
    line-height:1.45;
}

.info-table th{
    width:38%;
    background:#f3f4f2;
    color:var(--hr-label);
    font-weight:700;
    text-align:left;
}

.info-table td{
    background:#ffffff;
    color:#1f2937;
    font-weight:600;
    word-break:break-word;
}

/* TABLE RIWAYAT */
.report-table{
    width:100%;
    border-collapse:collapse;
    font-size:10px;
}

.report-table thead th{
    background:var(--hr-table-head);
    color:var(--hr-primary-dark);
    border:1px solid var(--hr-border);
    padding:7px 6px;
    text-align:center;
    font-weight:800;
    line-height:1.2;
}

.report-table tbody td{
    border:1px solid #e5e7eb;
    padding:7px 6px;
    text-align:center;
    vertical-align:middle;
    color:#1f2937;
    line-height:1.35;
}

.report-table tbody tr:nth-child(even){
    background:#fafafa;
}

.report-table tr{
    page-break-inside:avoid;
    break-inside:avoid;
}

.empty-state{
    margin:12px;
    border:1px dashed #cbd5e1;
    background:#fafafa;
    color:#6b7280;
    border-radius:12px;
    padding:12px;
    text-align:center;
    font-style:italic;
    font-size:10px;
}

.document-footer{
    margin-top:16px;
    padding-top:10px;
    border-top:1px solid #e5e7eb;
    text-align:center;
    font-size:10px;
    color:#6b7280;
}

.table-responsive{
    width:100%;
    overflow-x:auto;
}

/* RESPONSIVE */
@media (max-width: 992px){
    .paper-a4{
        width:100%;
    }

    .header-grid{
        grid-template-columns:1fr;
        text-align:center;
    }

    .profile-hero{
        grid-template-columns:1fr;
        justify-items:center;
        text-align:center;
    }

    .profile-main{
        padding-left:0;
    }

    .pegawai-meta,
    .status-row{
        justify-content:center;
    }

    .info-grid{
        grid-template-columns:1fr;
    }

    .info-table th,
    .info-table td{
        font-size:12px;
    }
}

@media (max-width: 576px){
    .paper-body{
        padding:8px;
    }

    .paper-header{
        padding:8px;
    }

    .company-name{
        font-size:13px;
    }

    .doc-title{
        font-size:12px;
    }

    .pegawai-name{
        font-size:20px;
    }

    .info-table th{
        width:40%;
    }
}

/* PAGE */
@page{
    size:A4 portrait;
    margin:8mm;
}

/* PRINT */
@media print{
    html, body{
        width:210mm;
        min-height:297mm;
        margin:0 !important;
        padding:0 !important;
        background:#ffffff !important;
    }

    body *{
        visibility:hidden !important;
    }

    .paper-a4,
    .paper-a4 *{
        visibility:visible !important;
    }

    .paper-a4{
        position:absolute;
        left:0;
        top:0;
        width:100%;
        min-height:auto;
        margin:0;
        border:none !important;
        border-radius:0 !important;
        box-shadow:none !important;
        overflow:visible !important;
        background:#ffffff !important;
    }

    .d-print-none,
    .top-actions{
        display:none !important;
    }

    .hr-page,
    .paper-wrap,
    .paper-body,
    .paper-header{
        background:#ffffff !important;
        padding-left:0 !important;
        padding-right:0 !important;
        margin:0 !important;
    }

    .header-grid{
        display:grid !important;
        grid-template-columns:54px 1fr 54px !important;
        gap:8px !important;
        align-items:center !important;
    }

    .logo-box img{
        max-width:40px !important;
        max-height:40px !important;
    }

    .profile-hero{
        display:grid !important;
        grid-template-columns:120px 1fr !important;
        gap:16px !important;
        align-items:center !important;
        text-align:left !important;
        padding:12px !important;
    }

    .profile-photo-box{
        width:120px !important;
        height:150px !important;
        margin:0 !important;
    }

    .profile-main{
        min-width:0 !important;
        padding-left:6px !important;
    }

    .identity-badge{
        margin-bottom:8px !important;
        font-size:10px !important;
        padding:5px 10px !important;
    }

    .pegawai-name{
        font-size:22px !important;
        line-height:1.15 !important;
        margin:0 0 6px 0 !important;
    }

    .pegawai-meta{
        margin-top:6px !important;
        display:flex !important;
        flex-wrap:wrap !important;
        gap:6px 14px !important;
        font-size:12px !important;
        justify-content:flex-start !important;
    }

    .status-row{
        margin-top:10px !important;
        display:flex !important;
        flex-wrap:wrap !important;
        gap:8px !important;
        justify-content:flex-start !important;
    }

    .info-chip{
        padding:5px 10px !important;
        font-size:10px !important;
    }

    .info-grid{
        display:grid !important;
        grid-template-columns:1fr 1fr !important;
        gap:12px !important;
        padding:12px !important;
    }

    .table-responsive{
        overflow:visible !important;
    }

    .report-table{
        width:100% !important;
    }

    .report-table thead{
        display:table-header-group !important;
    }

    .section-block,
    .profile-hero,
    .info-card,
    .report-table tr{
        page-break-inside:avoid !important;
        break-inside:avoid !important;
    }

    .section-heading{
        background:#e8eee0 !important;
        color:#2f3b22 !important;
        -webkit-print-color-adjust:exact;
        print-color-adjust:exact;
    }

    .info-card-title,
    .info-table th{
        -webkit-print-color-adjust:exact;
        print-color-adjust:exact;
    }
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const downloadBtn = document.getElementById('downloadPdfBtn');
    if (!downloadBtn) return;

    downloadBtn.addEventListener('click', function () {
        const element = document.getElementById('pegawai-print-area');
        const originalText = downloadBtn.innerHTML;

        downloadBtn.disabled = true;
        downloadBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyiapkan PDF...';

        const opt = {
            margin: [6, 6, 6, 6],
            filename: 'pegawai-{{ $pegawai->nip }}-{{ \Illuminate\Support\Str::slug($pegawai->nama ?? "pegawai") }}.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: {
                scale: 2,
                useCORS: true,
                scrollY: 0
            },
            jsPDF: {
                unit: 'mm',
                format: 'a4',
                orientation: 'portrait'
            },
            pagebreak: {
                mode: ['css', 'legacy'],
                avoid: ['.info-card', '.report-table tr']
            }
        };

        html2pdf().set(opt).from(element).save().then(() => {
            downloadBtn.disabled = false;
            downloadBtn.innerHTML = originalText;
        }).catch(() => {
            downloadBtn.disabled = false;
            downloadBtn.innerHTML = originalText;
            alert('Gagal membuat PDF. Coba lagi.');
        });
    });
});
</script>
@endsection