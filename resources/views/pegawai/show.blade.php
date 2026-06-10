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
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary hr-btn">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            @endif

            <div class="d-flex gap-2 flex-wrap">
                @if(in_array(auth()->user()->role, ['admin', 'hcm']))
                    <a href="{{ route(auth()->user()->role.'.pegawai.edit', $pegawai->nip) }}" class="btn btn-warning text-dark hr-btn">
                        <i class="bi bi-pencil-square"></i> Edit Data
                    </a>
                @endif

                <button type="button" onclick="printPegawaiA4()" class="btn btn-primary hr-btn">
                    <i class="bi bi-printer"></i> Print
                </button>

                <button type="button" id="downloadPdfBtn" class="btn btn-success hr-btn">
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
    --hr-primary:#59684a;
    --hr-primary-dark:#3f4d35;
    --hr-primary-soft:#e7eddc;
    --hr-primary-soft-2:#f7f9f2;
    --hr-border:#d7dfcc;
    --hr-border-strong:#c5d0b8;
    --hr-text:#101828;
    --hr-muted:#667085;
    --hr-label:#344054;
    --hr-white:#ffffff;
}

html, body{
    background:#f6f8f4 !important;
}

.hr-page{
    min-height:100vh;
    background:#f6f8f4;
    font-family:"Inter", "Segoe UI", Arial, sans-serif;
    color:var(--hr-text);
    padding:28px 0 50px !important;
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

.hr-btn{
    border-radius:10px;
    font-weight:600;
    padding:9px 16px;
}

.paper-wrap{
    width:100%;
    padding:0 !important;
    margin:0 !important;
    display:flex;
    justify-content:center;
}

.paper-a4{
    width:210mm;
    min-height:297mm;
    margin:0 auto;
    background:var(--hr-white);
    border:1px solid #dfe6d7;
    border-radius:0;
    box-shadow:0 14px 35px rgba(30, 41, 59, .08);
    overflow:hidden;
}

.paper-header{
    padding:10mm 12mm 5mm 12mm;
    border-bottom:1px solid var(--hr-border-strong);
    background:#ffffff;
}

.header-grid{
    display:grid;
    grid-template-columns:72px 1fr 72px;
    gap:14px;
    align-items:center;
}

.logo-box{
    display:flex;
    align-items:center;
    justify-content:center;
}

.logo-box img{
    max-width:56px;
    max-height:56px;
    object-fit:contain;
}

.company-box{
    text-align:center;
}

.company-name{
    font-size:17px;
    font-weight:800;
    line-height:1.2;
    letter-spacing:.03em;
    color:var(--hr-primary-dark);
    text-transform:uppercase;
}

.company-unit{
    margin-top:4px;
    font-size:10px;
    font-weight:800;
    letter-spacing:.07em;
    color:#566447;
    text-transform:uppercase;
}

.company-address,
.company-contact{
    margin-top:4px;
    font-size:9px;
    line-height:1.4;
    color:#4b5563;
}

.doc-title-wrap{
    margin-top:12px;
    border:1px solid var(--hr-border);
    background:#ffffff;
    padding:12px 14px;
    text-align:center;
    border-radius:12px;
}

.doc-title{
    font-size:17px;
    font-weight:800;
    line-height:1.2;
    letter-spacing:.09em;
    color:var(--hr-primary-dark);
    text-transform:uppercase;
}

.doc-subtitle{
    margin-top:4px;
    font-size:10px;
    font-weight:600;
    color:var(--hr-muted);
}

.paper-body{
    padding:6mm 12mm 9mm 12mm;
    background:#ffffff;
}

.profile-hero{
    display:grid;
    grid-template-columns:118px 1fr;
    gap:16px;
    align-items:center;
    border:1px solid var(--hr-border);
    border-radius:18px;
    padding:16px 18px;
    margin-bottom:14px;
    background:linear-gradient(180deg,#ffffff,#fbfcf8);
    page-break-inside:avoid;
    break-inside:avoid;
}

.profile-photo-box{
    width:106px;
    height:132px;
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
    font-size:11px;
    text-align:center;
    padding:8px;
}

.photo-placeholder i{
    font-size:34px;
}

.profile-main{
    min-width:0;
}

.identity-badge{
    display:inline-block;
    padding:6px 14px;
    border-radius:999px;
    background:var(--hr-primary-soft);
    color:#324025;
    font-size:11px;
    font-weight:800;
    letter-spacing:.08em;
    text-transform:uppercase;
    margin-bottom:10px;
}

.pegawai-name{
    margin:0;
    font-size:25px;
    line-height:1.2;
    font-weight:800;
    color:#0f172a;
    word-break:break-word;
}

.pegawai-meta{
    display:flex;
    flex-wrap:wrap;
    gap:12px 18px;
    margin-top:10px;
    font-size:12px;
    line-height:1.45;
    color:#1f2937;
}

.pegawai-meta strong{
    color:#0f172a;
}

.status-row{
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    margin-top:12px;
}

.info-chip{
    display:inline-flex;
    align-items:center;
    padding:6px 12px;
    border:1px solid var(--hr-border);
    border-radius:999px;
    background:#fbfcf8;
    color:#344054;
    font-size:10px;
    font-weight:700;
    line-height:1.2;
    max-width:100%;
    word-break:break-word;
}

.section-block{
    margin-top:12px;
    border:1px solid var(--hr-border);
    border-radius:14px;
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
    padding:11px 14px;
    background:var(--hr-primary-soft);
    border-bottom:1px solid var(--hr-border);
    font-size:13px;
    font-weight:800;
    letter-spacing:.02em;
    color:#27351e;
    line-height:1.25;
}

.section-heading i{
    font-size:14px;
    color:#405031;
}

.info-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:12px;
    padding:14px;
}

.info-card{
    border:1px solid var(--hr-border);
    border-radius:12px;
    overflow:hidden;
    background:#ffffff;
    page-break-inside:avoid;
    break-inside:avoid;
}

.info-card-title{
    padding:10px 12px;
    background:var(--hr-primary-soft-2);
    border-bottom:1px solid var(--hr-border);
    color:#27351e;
    font-size:12px;
    font-weight:800;
    line-height:1.25;
}

.info-table{
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
}

.info-table th,
.info-table td{
    border:1px solid var(--hr-border);
    padding:9px 10px;
    vertical-align:top;
    font-size:11px;
    line-height:1.5;
}

.info-table th{
    width:40%;
    background:var(--hr-primary-soft-2);
    color:var(--hr-label);
    font-weight:800;
    text-align:left;
}

.info-table td{
    background:#ffffff;
    color:#111827;
    font-weight:600;
    word-break:break-word;
    overflow-wrap:anywhere;
}

.table-responsive{
    width:100%;
    overflow:visible !important;
}

.report-table{
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
    font-size:9px;
}

.report-table thead{
    display:table-header-group;
}

.report-table thead th{
    background:var(--hr-primary-soft-2);
    color:#27351e;
    border:1px solid var(--hr-border);
    padding:6px 5px;
    text-align:center;
    font-weight:800;
    line-height:1.25;
    vertical-align:middle;
    word-break:break-word;
    overflow-wrap:anywhere;
}

.report-table tbody td{
    border:1px solid #e5e7eb;
    padding:6px 5px;
    text-align:center;
    vertical-align:middle;
    color:#1f2937;
    line-height:1.35;
    word-break:break-word;
    overflow-wrap:anywhere;
}

.report-table tbody tr:nth-child(even){
    background:#fafafa;
}

.report-table tr{
    page-break-inside:avoid;
    break-inside:avoid;
}

.report-table th:first-child,
.report-table td:first-child{
    width:8%;
}

/* ATURAN PAGE BREAK UNTUK PRINT/PDF
   Kalau section/card tidak cukup di sisa halaman, pindahkan ke halaman berikutnya.
   Baris tabel tetap dijaga supaya tidak terpotong di tengah. */
.profile-hero,
.section-block,
.info-card,
.empty-state,
.document-footer{
    page-break-inside:avoid;
    break-inside:avoid;
    break-inside:avoid-page;
}

.section-heading,
.info-card-title,
.report-table thead{
    page-break-after:avoid;
    break-after:avoid;
    break-after:avoid-page;
}

.info-table tr,
.report-table tr{
    page-break-inside:avoid;
    break-inside:avoid;
    break-inside:avoid-page;
}

.info-table,
.report-table{
    page-break-inside:auto;
    break-inside:auto;
}

.empty-state{
    margin:14px;
    border:1px dashed #cbd5e1;
    background:#fafafa;
    color:#6b7280;
    border-radius:12px;
    padding:12px;
    text-align:center;
    font-style:italic;
    font-size:11px;
}

.document-footer{
    margin-top:16px;
    padding-top:12px;
    border-top:1px solid #d1d5db;
    text-align:center;
    font-size:11px;
    color:#6b7280;
}

.paper-header,
.paper-body,
.profile-hero,
.info-chip,
.section-block,
.info-card,
.doc-title-wrap,
.section-heading,
.info-card-title,
.info-table th,
.report-table thead th{
    -webkit-print-color-adjust:exact;
    print-color-adjust:exact;
}

@media (max-width: 992px){
    .hr-page{
        padding:14px 0 30px !important;
    }

    .paper-a4{
        width:100%;
        min-height:auto;
        border-left:none;
        border-right:none;
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

    .pegawai-meta,
    .status-row{
        justify-content:center;
    }

    .info-grid{
        grid-template-columns:1fr;
    }

    .info-table th,
    .info-table td,
    .report-table thead th,
    .report-table tbody td{
        font-size:12px;
    }
}

@page{
    size:A4 portrait;
    margin:8mm 0 8mm 0;
}

@page:first{
    margin:0;
}

#pegawai-print-clone{
    display:none;
}


/* Saat proses download PDF, aturan ini membantu html2pdf membaca page break dengan rapi */
body.pdf-exporting .paper-a4,
body.pdf-exporting .paper-body,
body.pdf-exporting .section-block,
body.pdf-exporting .info-card{
    overflow:visible !important;
}

body.pdf-exporting .profile-hero,
body.pdf-exporting .section-block,
body.pdf-exporting .info-card,
body.pdf-exporting .empty-state,
body.pdf-exporting .document-footer{
    page-break-inside:avoid !important;
    break-inside:avoid !important;
    break-inside:avoid-page !important;
}

body.pdf-exporting .section-heading,
body.pdf-exporting .info-card-title,
body.pdf-exporting .report-table thead{
    page-break-after:avoid !important;
    break-after:avoid !important;
    break-after:avoid-page !important;
}

body.pdf-exporting .info-table tr,
body.pdf-exporting .report-table tr{
    page-break-inside:avoid !important;
    break-inside:avoid !important;
    break-inside:avoid-page !important;
}

/* Jarak aman atas-bawah halaman PDF agar section lanjutan tidak terlalu mepet tepi kertas */
body.pdf-exporting .paper-a4{
    padding-top:0 !important;
    padding-bottom:0 !important;
}


/* PRINT: paksa hanya lembar A4 pegawai yang tampil, bukan layout/sidebar */
@media print{
    html,
    body{
        width:210mm !important;
        min-width:210mm !important;
        margin:0 !important;
        padding:0 !important;
        background:#ffffff !important;
        overflow:visible !important;
        font-family:"Inter", "Segoe UI", Arial, sans-serif !important;
        -webkit-print-color-adjust:exact !important;
        print-color-adjust:exact !important;
    }

    body *{
        visibility:hidden !important;
    }

    /* Sembunyikan semua elemen layout bawaan aplikasi/sidebar/header */
    aside,
    nav,
    .sidebar,
    .app-sidebar,
    .main-sidebar,
    .navbar,
    .topbar,
    .main-header,
    .app-header,
    .footer,
    .d-print-none,
    .top-actions{
        display:none !important;
        visibility:hidden !important;
    }

    /* Hilangkan pengaruh margin-left/padding dari layout utama */
    #app,
    .app,
    .wrapper,
    .app-wrapper,
    .layout-wrapper,
    .page-wrapper,
    .content-wrapper,
    .main-content,
    .content,
    .page-content,
    main,
    .container,
    .container-fluid,
    .container-xl,
    .hr-page,
    .paper-wrap{
        margin:0 !important;
        padding:0 !important;
        width:100% !important;
        max-width:none !important;
        min-width:0 !important;
        left:auto !important;
        right:auto !important;
        top:auto !important;
        transform:none !important;
        background:#ffffff !important;
        box-shadow:none !important;
    }

    /* Fallback kalau user print dari shortcut/browser */
    #pegawai-print-area,
    #pegawai-print-area *{
        visibility:visible !important;
    }

    #pegawai-print-area{
        position:absolute !important;
        left:0 !important;
        top:0 !important;
        right:auto !important;
        width:210mm !important;
        max-width:210mm !important;
        min-height:297mm !important;
        margin:0 !important;
        background:#ffffff !important;
        border:1px solid #d5dbd1 !important;
        box-shadow:none !important;
        overflow:visible !important;
        z-index:999999 !important;
    }

    /* Mode tombol Print: gunakan clone langsung di body supaya tidak ikut sidebar */
    body.pegawai-printing #pegawai-print-area,
    body.pegawai-printing #pegawai-print-area *{
        visibility:hidden !important;
    }

    body.pegawai-printing #pegawai-print-clone{
        display:block !important;
        visibility:visible !important;
        position:absolute !important;
        left:0 !important;
        top:0 !important;
        right:auto !important;
        width:210mm !important;
        max-width:210mm !important;
        margin:0 !important;
        padding:0 !important;
        background:#ffffff !important;
        z-index:9999999 !important;
    }

    body.pegawai-printing #pegawai-print-clone,
    body.pegawai-printing #pegawai-print-clone *{
        visibility:visible !important;
    }

    body.pegawai-printing #pegawai-print-clone .paper-a4{
        position:static !important;
        width:210mm !important;
        max-width:210mm !important;
        min-height:297mm !important;
        margin:0 !important;
        background:#ffffff !important;
        border:1px solid #d5dbd1 !important;
        box-shadow:none !important;
        overflow:visible !important;
    }

    .paper-header{
        padding:10mm 12mm 5mm 12mm !important;
        border-bottom:1px solid var(--hr-border-strong) !important;
        background:#ffffff !important;
    }

    .paper-body{
        padding:6mm 12mm 9mm 12mm !important;
        background:#ffffff !important;
    }

    .header-grid{
        display:grid !important;
        grid-template-columns:72px 1fr 72px !important;
        gap:14px !important;
        align-items:center !important;
    }

    .logo-box img{
        max-width:56px !important;
        max-height:56px !important;
    }

    .profile-hero{
        display:grid !important;
        grid-template-columns:118px 1fr !important;
        gap:16px !important;
        align-items:center !important;
        text-align:left !important;
    }

    .profile-photo-box{
        width:106px !important;
        height:132px !important;
        margin:0 !important;
    }

    .pegawai-meta,
    .status-row{
        justify-content:flex-start !important;
    }

    .info-grid{
        display:grid !important;
        grid-template-columns:1fr 1fr !important;
        gap:12px !important;
        padding:14px !important;
    }

    .table-responsive{
        overflow:visible !important;
    }

    .report-table{
        width:100% !important;
        table-layout:fixed !important;
    }

    .report-table thead{
        display:table-header-group !important;
    }

    .paper-a4,
    .paper-body,
    .section-block,
    .info-card{
        overflow:visible !important;
    }

    .profile-hero,
    .section-block,
    .section-keep,
    .info-card,
    .empty-state,
    .document-footer{
        page-break-inside:avoid !important;
        break-inside:avoid !important;
        break-inside:avoid-page !important;
    }

    .section-heading,
    .info-card-title,
    .report-table thead{
        page-break-after:avoid !important;
        break-after:avoid !important;
        break-after:avoid-page !important;
    }

    .info-table tr,
    .report-table tr{
        page-break-inside:avoid !important;
        break-inside:avoid !important;
        break-inside:avoid-page !important;
    }

    .info-table,
    .report-table{
        page-break-inside:auto !important;
        break-inside:auto !important;
    }

    .paper-header,
    .paper-body,
    .profile-hero,
    .info-chip,
    .section-block,
    .info-card,
    .doc-title-wrap,
    .section-heading,
    .info-card-title,
    .info-table th,
    .report-table thead th{
        -webkit-print-color-adjust:exact !important;
        print-color-adjust:exact !important;
    }
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function printPegawaiA4() {
    const source = document.getElementById('pegawai-print-area');
    if (!source) {
        window.print();
        return;
    }

    const oldClone = document.getElementById('pegawai-print-clone');
    if (oldClone) {
        oldClone.remove();
    }

    const cloneWrapper = document.createElement('div');
    cloneWrapper.id = 'pegawai-print-clone';

    const clonedPaper = source.cloneNode(true);
    clonedPaper.id = 'pegawai-print-area-clone';

    cloneWrapper.appendChild(clonedPaper);
    document.body.appendChild(cloneWrapper);
    document.body.classList.add('pegawai-printing');

    const cleanupPrint = function () {
        document.body.classList.remove('pegawai-printing');
        const activeClone = document.getElementById('pegawai-print-clone');
        if (activeClone) {
            activeClone.remove();
        }
        window.removeEventListener('afterprint', cleanupPrint);
    };

    window.addEventListener('afterprint', cleanupPrint);

    setTimeout(function () {
        window.print();
    }, 80);
}

document.addEventListener('DOMContentLoaded', function () {
    const downloadBtn = document.getElementById('downloadPdfBtn');
    if (!downloadBtn) return;

    downloadBtn.addEventListener('click', function () {
        const element = document.getElementById('pegawai-print-area');
        const originalText = downloadBtn.innerHTML;

        downloadBtn.disabled = true;
        downloadBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyiapkan PDF...';

        const opt = {
            // Top dan bottom margin dibuat agar halaman lanjutan tidak terlalu mepet ke atas/bawah.
            // Kiri-kanan tetap 0 supaya lebar A4 tetap sama seperti tampilan show.
            margin: [8, 0, 8, 0],
            filename: 'pegawai-{{ $pegawai->nip }}-{{ \Illuminate\Support\Str::slug($pegawai->nama ?? "pegawai") }}.pdf',
            image: { type: 'jpeg', quality: 1 },
            html2canvas: {
                scale: 2.2,
                useCORS: true,
                backgroundColor: '#ffffff',
                scrollX: 0,
                scrollY: 0
            },
            jsPDF: {
                unit: 'mm',
                format: 'a4',
                orientation: 'portrait'
            },
            pagebreak: {
                mode: ['css', 'legacy'],
                avoid: ['.profile-hero', '.section-block', '.info-card', '.empty-state', '.document-footer', '.report-table tr']
            }
        };

        document.body.classList.add('pdf-exporting');

        setTimeout(function () {
            html2pdf()
                .set(opt)
                .from(element)
                .save()
                .then(() => {
                    document.body.classList.remove('pdf-exporting');
                    downloadBtn.disabled = false;
                    downloadBtn.innerHTML = originalText;
                })
                .catch(() => {
                    document.body.classList.remove('pdf-exporting');
                    downloadBtn.disabled = false;
                    downloadBtn.innerHTML = originalText;
                    alert('Gagal membuat PDF. Coba lagi.');
                });
        }, 120);
    });
});
</script>
@endsection