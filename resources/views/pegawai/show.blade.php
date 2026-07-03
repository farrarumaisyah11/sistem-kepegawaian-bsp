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

{{-- ========================================================================
     EXPORT KHUSUS PRINT & PDF DETAIL PEGAWAI
     - Tidak mengubah tampilan Detail Pegawai pada layar.
     - Hanya dipakai ketika tombol Print atau Download PDF ditekan.
     - Setiap halaman A4 memiliki kop yang sama.
     - Setiap lanjutan dibuat sebagai card/section baru yang tertutup penuh.
     ======================================================================== --}}
<style>
/* ========================================================================
   EXPORT-ONLY LAYOUT
   Selector dibatasi ke .pegawai-export-root / body.pegawai-printing sehingga
   tidak mengubah tampilan Detail Pegawai yang sudah ada pada layar.
   ======================================================================== */
.pegawai-export-root{
    position:fixed;
    left:-30000px;
    top:0;
    width:210mm;
    min-width:210mm;
    max-width:210mm;
    margin:0;
    padding:0;
    display:block;
    visibility:visible;
    opacity:1;
    z-index:-9999;
    pointer-events:none;
    background:#ffffff;
}

.pegawai-export-page{
    width:210mm;
    height:297mm;
    min-height:297mm;
    max-height:297mm;
    box-sizing:border-box;
    margin:0;
    padding:0;
    overflow:hidden;
    display:flex;
    flex-direction:column;
    background:#ffffff;
    border:1px solid #dfe6d7;
    box-shadow:none;
    break-after:page;
    page-break-after:always;
}

/*
|--------------------------------------------------------------------------
| Print safe page
|--------------------------------------------------------------------------
| Browser print engine tidak selalu menghitung area cetak sama persis dengan
| A4 canvas. Karena itu halaman khusus Print dibuat 279mm, lalu @page
| memberi margin atas-bawah 9mm. Sisa 18mm adalah area aman agar card yang
| sudah dipetakan JavaScript tidak terpotong di tepi bawah kertas.
| PDF tetap memakai 297mm penuh sehingga tampilannya tidak berubah.
*/
.pegawai-export-page--print{
    height:279mm;
    min-height:279mm;
    max-height:279mm;
}

.pegawai-export-page:last-child{
    break-after:auto;
    page-break-after:auto;
}

.pegawai-export-page .paper-header{
    flex:0 0 auto;
    box-sizing:border-box;
    padding:10mm 12mm 5mm 12mm;
    border-bottom:1px solid var(--hr-border-strong);
    background:#ffffff;
}

/* Ruang atas ini sengaja disediakan pada setiap halaman lanjutan agar card
   tidak menempel ke kop. Semua card yang dilanjutkan dibangun ulang lengkap
   dengan border atas, header section, dan frame-nya. */
.pegawai-export-body{
    flex:1 1 auto;
    min-height:0;
    box-sizing:border-box;
    overflow:hidden;
    padding:8mm 12mm 10mm 12mm;
    background:#ffffff;
}

.pegawai-export-body > :first-child{
    margin-top:0 !important;
}

/* Hasil export selalu desktop layout, walaupun tombol ditekan dari HP. */
.pegawai-export-root .header-grid{
    display:grid !important;
    grid-template-columns:72px 1fr 72px !important;
    gap:14px !important;
    align-items:center !important;
}

.pegawai-export-root .logo-box img{
    max-width:56px !important;
    max-height:56px !important;
}

.pegawai-export-root .profile-hero{
    display:grid !important;
    grid-template-columns:118px 1fr !important;
    gap:16px !important;
    align-items:center !important;
    text-align:left !important;
}

.pegawai-export-root .profile-photo-box{
    width:106px !important;
    height:132px !important;
    margin:0 !important;
}

.pegawai-export-root .pegawai-meta,
.pegawai-export-root .status-row{
    justify-content:flex-start !important;
}

.pegawai-export-root .info-grid{
    display:grid !important;
    grid-template-columns:1fr 1fr !important;
    gap:12px !important;
    padding:14px !important;
}

.pegawai-export-root .table-responsive{
    overflow:visible !important;
}

.pegawai-export-root .report-table{
    width:100% !important;
    table-layout:fixed !important;
}

.pegawai-export-root .report-table thead{
    display:table-header-group !important;
}

.pegawai-export-root .profile-hero,
.pegawai-export-root .section-block,
.pegawai-export-root .info-card,
.pegawai-export-root .empty-state,
.pegawai-export-root .document-footer{
    page-break-inside:avoid !important;
    break-inside:avoid !important;
    break-inside:avoid-page !important;
}

.pegawai-export-root .section-heading,
.pegawai-export-root .info-card-title,
.pegawai-export-root .report-table thead{
    page-break-after:avoid !important;
    break-after:avoid !important;
    break-after:avoid-page !important;
}

.pegawai-export-root .info-table tr,
.pegawai-export-root .report-table tr{
    page-break-inside:avoid !important;
    break-inside:avoid !important;
    break-inside:avoid-page !important;
}

/* Label hanya ada pada frame/card lanjutan, tidak mengubah show di layar. */
.pegawai-export-root .export-continuation-label{
    display:inline-flex;
    align-items:center;
    margin-left:auto;
    padding:3px 8px;
    border:1px solid var(--hr-border-strong);
    border-radius:999px;
    background:#ffffff;
    color:#52613f;
    font-size:9px;
    font-weight:800;
    letter-spacing:.03em;
    line-height:1.2;
    white-space:nowrap;
}

.pegawai-export-root .paper-header,
.pegawai-export-root .paper-body,
.pegawai-export-root .profile-hero,
.pegawai-export-root .info-chip,
.pegawai-export-root .section-block,
.pegawai-export-root .info-card,
.pegawai-export-root .doc-title-wrap,
.pegawai-export-root .section-heading,
.pegawai-export-root .info-card-title,
.pegawai-export-root .info-table th,
.pegawai-export-root .report-table thead th{
    -webkit-print-color-adjust:exact !important;
    print-color-adjust:exact !important;
}

@page{
    size:A4 portrait;
    margin:9mm 0 !important;
}

@page:first{
    margin:9mm 0 !important;
}

@media print{
    /* Saat tombol Print dipakai, hanya halaman export yang terlihat. */
    body.pegawai-printing #pegawai-print-area,
    body.pegawai-printing #pegawai-print-area *,
    body.pegawai-printing .top-actions,
    body.pegawai-printing .d-print-none{
        visibility:hidden !important;
    }

    body.pegawai-printing .pegawai-export-root,
    body.pegawai-printing .pegawai-export-root *{
        visibility:visible !important;
    }

    body.pegawai-printing .pegawai-export-root{
        position:absolute !important;
        left:0 !important;
        top:0 !important;
        width:210mm !important;
        min-width:210mm !important;
        max-width:210mm !important;
        z-index:2147483647 !important;
        display:block !important;
        opacity:1 !important;
        overflow:visible !important;
        background:#ffffff !important;
    }

    body.pegawai-printing .pegawai-export-page{
        width:210mm !important;
        height:279mm !important;
        min-height:279mm !important;
        max-height:279mm !important;
        box-sizing:border-box !important;
        margin:0 !important;
        border:1px solid #dfe6d7 !important;
        box-shadow:none !important;
        overflow:hidden !important;
        break-after:page !important;
        page-break-after:always !important;
    }

    body.pegawai-printing .pegawai-export-page:last-child{
        break-after:auto !important;
        page-break-after:auto !important;
    }

    body.pegawai-printing .pegawai-export-page .paper-header{
        padding:10mm 12mm 5mm 12mm !important;
    }

    body.pegawai-printing .pegawai-export-body{
        padding:8mm 12mm 10mm 12mm !important;
        overflow:hidden !important;
    }

    /* Jangan izinkan browser memecah card yang telah dibentuk ulang oleh
       export engine. Jika tidak muat, engine sudah membuat frame baru pada
       halaman selanjutnya, lengkap dengan border atas dan heading. */
    body.pegawai-printing .pegawai-export-body > .profile-hero,
    body.pegawai-printing .pegawai-export-body > .section-block,
    body.pegawai-printing .pegawai-export-body > .empty-state,
    body.pegawai-printing .pegawai-export-body > .document-footer,
    body.pegawai-printing .pegawai-export-body .info-card,
    body.pegawai-printing .pegawai-export-body .report-table tr{
        break-inside:avoid !important;
        break-inside:avoid-page !important;
        page-break-inside:avoid !important;
    }
}
</style>

{{-- File lokal diprioritaskan. Bila belum ada, script akan mencoba CDN otomatis. --}}
<script src="{{ asset('vendor/html2pdf/html2pdf.bundle.min.js') }}"></script>
<script>
(function () {
    'use strict';

    const EXPORT_ROOT_ID = 'pegawai-export-root';
    const PDF_SCALE = 2;
    const DESKTOP_CAPTURE_WIDTH = 1440;
    const A4_WIDTH_MM = 210;
    const A4_HEIGHT_MM = 297;

    function getSourcePaper() {
        return document.getElementById('pegawai-print-area');
    }

    function getOrCreateExportRoot() {
        let root = document.getElementById(EXPORT_ROOT_ID);

        if (!root) {
            root = document.createElement('div');
            root.id = EXPORT_ROOT_ID;
            root.className = 'pegawai-export-root';
            document.body.appendChild(root);
        }

        return root;
    }

    function resetExportRoot(root, mode) {
        root.innerHTML = '';
        root.className = 'pegawai-export-root' + (mode === 'print' ? ' pegawai-export-root--print' : ' pegawai-export-root--pdf');
        root.dataset.exportMode = mode || 'pdf';
        root.style.left = '-30000px';
        root.style.top = '0';
        root.style.zIndex = '-9999';
        root.style.visibility = 'visible';
        root.style.opacity = '1';
        root.style.display = 'block';
        root.style.pointerEvents = 'none';
    }

    function removeExportRoot() {
        const root = document.getElementById(EXPORT_ROOT_ID);
        if (root) {
            root.remove();
        }
    }

    function waitForFonts() {
        if (document.fonts && document.fonts.ready) {
            return document.fonts.ready.catch(function () {});
        }

        return Promise.resolve();
    }

    function waitForImages(node) {
        if (!node) {
            return Promise.resolve();
        }

        const images = Array.from(node.querySelectorAll('img'));

        return Promise.all(images.map(function (img) {
            if (img.complete && img.naturalWidth > 0) {
                return Promise.resolve();
            }

            return new Promise(function (resolve) {
                const done = function () { resolve(); };
                img.addEventListener('load', done, { once: true });
                img.addEventListener('error', done, { once: true });
                setTimeout(done, 5000);
            });
        }));
    }

    function nextFrame() {
        return new Promise(function (resolve) {
            requestAnimationFrame(function () {
                requestAnimationFrame(resolve);
            });
        });
    }

    function ensureHtml2Pdf() {
        if (window.html2pdf) {
            return Promise.resolve();
        }

        return new Promise(function (resolve, reject) {
            const existing = document.querySelector('script[data-pegawai-html2pdf-cdn="true"]');

            if (existing) {
                existing.addEventListener('load', function () {
                    window.html2pdf ? resolve() : reject(new Error('Library PDF tidak tersedia.'));
                }, { once: true });

                existing.addEventListener('error', function () {
                    reject(new Error('Library PDF tidak dapat dimuat.'));
                }, { once: true });

                return;
            }

            const cdn = document.createElement('script');
            cdn.dataset.pegawaiHtml2pdfCdn = 'true';
            cdn.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
            cdn.async = true;
            cdn.onload = function () {
                window.html2pdf ? resolve() : reject(new Error('Library PDF tidak tersedia.'));
            };
            cdn.onerror = function () {
                reject(new Error('Library PDF tidak dapat dimuat.'));
            };
            document.head.appendChild(cdn);
        });
    }

    function createExportPage(root, headerTemplate) {
        const page = document.createElement('section');
        const mode = root.dataset.exportMode || 'pdf';
        page.className = 'pegawai-export-page ' + (mode === 'print' ? 'pegawai-export-page--print' : 'pegawai-export-page--pdf');

        const header = headerTemplate.cloneNode(true);
        const body = document.createElement('div');
        body.className = 'pegawai-export-body';

        page.appendChild(header);
        page.appendChild(body);
        root.appendChild(page);

        return { page: page, body: body };
    }

    function pageHasBodyContent(current) {
        return !!(current && current.body && current.body.children.length);
    }

    function pageOverflows(current) {
        if (!current || !current.body) {
            return false;
        }

        return Math.ceil(current.body.scrollHeight) > Math.floor(current.body.clientHeight) + 1;
    }

    function addContinuationLabel(item, continuationNumber) {
        const heading = item.querySelector('.section-heading');

        if (!heading || heading.querySelector('.export-continuation-label')) {
            return;
        }

        const label = document.createElement('span');
        label.className = 'export-continuation-label';
        label.textContent = 'Lanjutan ' + continuationNumber;
        heading.appendChild(label);
    }

    /* Letakkan satu card/section utuh. Bila tidak muat di sisa halaman,
       objek dipindahkan secara utuh ke halaman baru. */
    function placeWholeItem(sourceItem, state, root, headerTemplate) {
        let clone = sourceItem.cloneNode(true);
        state.current.body.appendChild(clone);

        if (!pageOverflows(state.current)) {
            return true;
        }

        clone.remove();

        if (pageHasBodyContent(state.current)) {
            state.current = createExportPage(root, headerTemplate);
            clone = sourceItem.cloneNode(true);
            state.current.body.appendChild(clone);

            if (!pageOverflows(state.current)) {
                return true;
            }

            clone.remove();
        }

        return false;
    }

    function getDirectInfoCards(sourceSection) {
        const cards = [];

        Array.from(sourceSection.querySelectorAll('.info-grid')).forEach(function (grid) {
            if (grid.closest('.section-block') !== sourceSection) {
                return;
            }

            Array.from(grid.children).forEach(function (child) {
                if (child.classList && child.classList.contains('info-card')) {
                    cards.push(child);
                }
            });
        });

        return cards;
    }

    function buildSectionWithSingleInfoCard(sourceSection, sourceCard) {
        const section = sourceSection.cloneNode(true);
        const grids = Array.from(section.querySelectorAll('.info-grid')).filter(function (grid) {
            return grid.closest('.section-block') === section;
        });

        if (!grids.length) {
            return null;
        }

        grids.forEach(function (grid, index) {
            if (index === 0) {
                grid.innerHTML = '';
                grid.appendChild(sourceCard.cloneNode(true));
            } else {
                grid.remove();
            }
        });

        return section;
    }

    function getFirstInfoCard(section) {
        return Array.from(section.querySelectorAll('.info-card')).find(function (card) {
            return card.closest('.section-block') === section;
        }) || null;
    }

    function getInfoTable(card) {
        return card ? card.querySelector('table.info-table') : null;
    }

    function buildInfoSectionWithRows(sourceSection, sourceCard, rows) {
        const section = buildSectionWithSingleInfoCard(sourceSection, sourceCard);
        const card = getFirstInfoCard(section);
        const table = getInfoTable(card);

        if (!section || !table) {
            return null;
        }

        Array.from(table.querySelectorAll('tr')).forEach(function (row) {
            row.remove();
        });

        rows.forEach(function (row) {
            table.appendChild(row.cloneNode(true));
        });

        return section;
    }

    /* Jika sebuah info-card tunggal terlalu tinggi, pecah per baris tabel.
       Setiap pecahan tetap berada dalam section dan info-card baru penuh. */
    function splitSingleInfoCardRows(sourceSection, sourceCard, state, root, headerTemplate) {
        const sourceTable = getInfoTable(sourceCard);
        const sourceRows = sourceTable ? Array.from(sourceTable.querySelectorAll('tr')) : [];

        if (!sourceRows.length) {
            return false;
        }

        let part = 0;
        let frame = null;
        let currentRows = [];

        function startPart() {
            part += 1;
            currentRows = [];
            frame = buildInfoSectionWithRows(sourceSection, sourceCard, currentRows);

            if (!frame) {
                return false;
            }

            if (part > 1) {
                addContinuationLabel(frame, part - 1);
            }

            state.current.body.appendChild(frame);

            if (pageOverflows(state.current) && pageHasBodyContent(state.current)) {
                frame.remove();
                state.current = createExportPage(root, headerTemplate);
                frame = buildInfoSectionWithRows(sourceSection, sourceCard, currentRows);

                if (part > 1) {
                    addContinuationLabel(frame, part - 1);
                }

                state.current.body.appendChild(frame);
            }

            return true;
        }

        if (!startPart()) {
            return false;
        }

        sourceRows.forEach(function (sourceRow) {
            const row = sourceRow.cloneNode(true);
            const table = getInfoTable(getFirstInfoCard(frame));
            table.appendChild(row);
            currentRows.push(sourceRow);

            if (!pageOverflows(state.current)) {
                return;
            }

            row.remove();
            currentRows.pop();

            /* Baris tunggal yang sangat tinggi tidak boleh membuat halaman kosong. */
            if (!currentRows.length) {
                table.appendChild(row);
                currentRows.push(sourceRow);
                return;
            }

            state.current = createExportPage(root, headerTemplate);
            startPart();

            const nextTable = getInfoTable(getFirstInfoCard(frame));
            nextTable.appendChild(row);
            currentRows.push(sourceRow);
        });

        return true;
    }

    function splitInfoCardSection(sourceSection, state, root, headerTemplate) {
        const cards = getDirectInfoCards(sourceSection);

        if (!cards.length) {
            return false;
        }

        for (let index = 0; index < cards.length; index += 1) {
            const card = cards[index];
            const section = buildSectionWithSingleInfoCard(sourceSection, card);

            if (!section) {
                continue;
            }

            if (index > 0) {
                addContinuationLabel(section, index);
            }

            if (placeWholeItem(section, state, root, headerTemplate)) {
                continue;
            }

            /* Saat section satu kartu masih terlalu tinggi, pecah per baris. */
            if (splitSingleInfoCardRows(sourceSection, card, state, root, headerTemplate)) {
                continue;
            }

            /* Fallback terukur: mulai card di halaman baru, bukan di sisa halaman. */
            if (pageHasBodyContent(state.current)) {
                state.current = createExportPage(root, headerTemplate);
            }

            state.current.body.appendChild(section.cloneNode(true));
        }

        return true;
    }

    function getReportTable(section) {
        return Array.from(section.querySelectorAll('table.report-table')).find(function (table) {
            return table.closest('.section-block') === section;
        }) || null;
    }

    function buildEmptyReportSection(sourceSection) {
        const section = sourceSection.cloneNode(true);
        const table = getReportTable(section);

        if (table) {
            const body = table.querySelector('tbody');
            if (body) {
                body.innerHTML = '';
            }
        }

        return section;
    }

    /* Tabel panjang dipecah per baris. Di setiap halaman baru dibuat section
       lengkap yang memiliki border atas, heading, header tabel, dan label Lanjutan. */
    function splitReportTableSection(sourceSection, state, root, headerTemplate) {
        const sourceTable = getReportTable(sourceSection);

        if (!sourceTable) {
            return false;
        }

        const sourceRows = Array.from(sourceTable.querySelectorAll('tbody > tr'));

        if (!sourceRows.length) {
            return false;
        }

        let part = 0;
        let frame = null;
        let targetTableBody = null;

        function beginPart(preferNewPage) {
            if (preferNewPage) {
                state.current = createExportPage(root, headerTemplate);
            }

            part += 1;
            frame = buildEmptyReportSection(sourceSection);

            if (part > 1) {
                addContinuationLabel(frame, part - 1);
            }

            targetTableBody = getReportTable(frame).querySelector('tbody');
            state.current.body.appendChild(frame);

            /* Jika frame baru tidak muat pada sisa halaman, pindahkan frame utuh
               ke halaman berikutnya. Kondisi halaman kosong tidak membuat page baru lagi. */
            if (pageOverflows(state.current) && pageHasBodyContent(state.current)) {
                frame.remove();
                state.current = createExportPage(root, headerTemplate);
                frame = buildEmptyReportSection(sourceSection);

                if (part > 1) {
                    addContinuationLabel(frame, part - 1);
                }

                targetTableBody = getReportTable(frame).querySelector('tbody');
                state.current.body.appendChild(frame);
            }
        }

        beginPart(false);

        sourceRows.forEach(function (sourceRow) {
            const row = sourceRow.cloneNode(true);
            targetTableBody.appendChild(row);

            if (!pageOverflows(state.current)) {
                return;
            }

            row.remove();

            /* Satu baris luar biasa tinggi tetap dipertahankan dalam frame baru,
               karena tidak ada pemisahan aman di tengah isi satu baris. */
            if (!targetTableBody.children.length) {
                targetTableBody.appendChild(row);
                return;
            }

            beginPart(true);
            targetTableBody.appendChild(row);
        });

        return true;
    }

    function splitOversizedSection(sourceSection, state, root, headerTemplate) {
        if (getReportTable(sourceSection)) {
            return splitReportTableSection(sourceSection, state, root, headerTemplate);
        }

        if (getDirectInfoCards(sourceSection).length) {
            return splitInfoCardSection(sourceSection, state, root, headerTemplate);
        }

        return false;
    }

    function addSourceItem(sourceItem, state, root, headerTemplate) {
        if (placeWholeItem(sourceItem, state, root, headerTemplate)) {
            return;
        }

        if (sourceItem.classList.contains('section-block') && splitOversizedSection(sourceItem, state, root, headerTemplate)) {
            return;
        }

        /* Fallback: jangan meletakkan item besar di ujung halaman sebelumnya.
           Item selalu mulai pada halaman baru agar tidak ada card tanpa border atas. */
        if (pageHasBodyContent(state.current)) {
            state.current = createExportPage(root, headerTemplate);
        }

        state.current.body.appendChild(sourceItem.cloneNode(true));
    }

    function removeEmptyPages(root) {
        Array.from(root.querySelectorAll('.pegawai-export-page')).forEach(function (page) {
            const body = page.querySelector('.pegawai-export-body');
            if (!body || !body.children.length) {
                page.remove();
            }
        });
    }

    async function buildExportPages(mode = 'pdf') {
        const sourcePaper = getSourcePaper();

        if (!sourcePaper) {
            throw new Error('Area Detail Pegawai tidak ditemukan.');
        }

        const headerTemplate = sourcePaper.querySelector('.paper-header');
        const bodySource = sourcePaper.querySelector('.paper-body');

        if (!headerTemplate || !bodySource) {
            throw new Error('Kop atau isi Detail Pegawai tidak ditemukan.');
        }

        await waitForFonts();
        await waitForImages(sourcePaper);

        const root = getOrCreateExportRoot();
        resetExportRoot(root, mode);

        const state = {
            current: createExportPage(root, headerTemplate)
        };

        Array.from(bodySource.children)
            .filter(function (node) { return node.nodeType === 1; })
            .forEach(function (item) {
                addSourceItem(item, state, root, headerTemplate);
            });

        removeEmptyPages(root);
        await nextFrame();
        await waitForImages(root);

        const pages = Array.from(root.querySelectorAll('.pegawai-export-page'));

        if (!pages.length) {
            throw new Error('Tidak ada halaman yang dapat diexport.');
        }

        return { root: root, pages: pages };
    }

    function captureOptions(page) {
        return {
            margin: 0,
            image: { type: 'jpeg', quality: 1 },
            html2canvas: {
                scale: PDF_SCALE,
                useCORS: true,
                allowTaint: true,
                backgroundColor: '#ffffff',
                scrollX: 0,
                scrollY: 0,
                windowWidth: DESKTOP_CAPTURE_WIDTH,
                windowHeight: Math.max(1600, page.scrollHeight)
            },
            jsPDF: {
                unit: 'mm',
                format: 'a4',
                orientation: 'portrait',
                compress: true
            },
            pagebreak: { mode: [] }
        };
    }

    async function renderPageCanvas(page) {
        const worker = window.html2pdf()
            .set(captureOptions(page))
            .from(page)
            .toCanvas();

        return worker.get('canvas');
    }

    /* PDF dibangun secara satu-canvas = satu-halaman A4. Tidak menggunakan root
       panjang, sehingga tidak ada halaman ekstra/blank akibat pembulatan page break. */
    async function savePagesAsPdf(pages, filename) {
        if (!pages.length) {
            throw new Error('Tidak ada halaman PDF yang dapat dibuat.');
        }

        const firstCanvas = await renderPageCanvas(pages[0]);
        const firstWorker = window.html2pdf()
            .set(captureOptions(pages[0]))
            .from(firstCanvas, 'canvas')
            .toPdf();

        const pdf = await firstWorker.get('pdf');

        if (!pdf || typeof pdf.addImage !== 'function') {
            throw new Error('Objek PDF internal tidak tersedia.');
        }

        /* html2pdf terkadang membentuk page tambahan kosong saat mengubah canvas
           pertama. Hapus hanya page tambahan tersebut sebelum halaman berikutnya ditambah. */
        if (typeof pdf.getNumberOfPages === 'function' && typeof pdf.deletePage === 'function') {
            while (pdf.getNumberOfPages() > 1) {
                pdf.deletePage(pdf.getNumberOfPages());
            }
        }

        for (let index = 1; index < pages.length; index += 1) {
            const canvas = await renderPageCanvas(pages[index]);
            pdf.addPage('a4', 'portrait');
            pdf.addImage(
                canvas.toDataURL('image/jpeg', 1),
                'JPEG',
                0,
                0,
                A4_WIDTH_MM,
                A4_HEIGHT_MM,
                undefined,
                'FAST'
            );
        }

        pdf.save(filename);
    }

    /* Langsung ke dialog print native browser. Tidak membuka route, tab, atau
       preview halaman A4 baru. Dialog browser tidak bisa dihilangkan secara paksa. */
    function printPegawaiA4() {
        /* Print memakai halaman aman 279mm, bukan canvas PDF 297mm.
           Ini memberi buffer fisik atas dan bawah agar browser tidak
           memotong card terakhir pada halaman. */
        buildExportPages('print')
            .then(function (result) {
                document.body.classList.add('pegawai-printing');
                result.root.style.left = '0';
                result.root.style.top = '0';
                result.root.style.zIndex = '2147483647';

                const cleanup = function () {
                    document.body.classList.remove('pegawai-printing');
                    removeExportRoot();
                    window.removeEventListener('afterprint', cleanup);
                };

                window.addEventListener('afterprint', cleanup);
                setTimeout(function () { window.print(); }, 120);
            })
            .catch(function (error) {
                console.error(error);
                window.print();
            });
    }

    window.printPegawaiA4 = printPegawaiA4;

    document.addEventListener('DOMContentLoaded', function () {
        const downloadButton = document.getElementById('downloadPdfBtn');

        if (!downloadButton) {
            return;
        }

        downloadButton.addEventListener('click', async function () {
            const originalText = downloadButton.innerHTML;

            downloadButton.disabled = true;
            downloadButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyiapkan PDF...';

            try {
                await ensureHtml2Pdf();
                const result = await buildExportPages('pdf');
                const filename = @json('pegawai-' . $pegawai->nip . '-' . \Illuminate\Support\Str::slug($pegawai->nama ?? 'pegawai') . '.pdf');
                await savePagesAsPdf(result.pages, filename);
            } catch (error) {
                console.error(error);
                alert('PDF tidak dapat dibuat. Pastikan koneksi internet tersedia atau simpan html2pdf.bundle.min.js pada public/vendor/html2pdf/, kemudian refresh halaman.');
            } finally {
                removeExportRoot();
                downloadButton.disabled = false;
                downloadButton.innerHTML = originalText;
            }
        });
    });
})();
</script>
@endsection
