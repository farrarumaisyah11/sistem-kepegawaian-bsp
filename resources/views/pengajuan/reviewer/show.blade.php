@extends('layouts.app')

@section('title', 'Detail Pengajuan Perubahan')

@section('content')
@php
    $pegawaiPayload = $payload['pegawai'] ?? [];

    $sectionLabels = [
        'pegawai'    => 'Informasi Pribadi',
        'pendidikan' => 'Pendidikan',
        'kursus'     => 'Kursus & Pelatihan',
        'peng_bsp'   => 'Pengalaman BSP',
        'peng_luar'  => 'Pengalaman Luar BSP',
        'keluarga'   => 'Keluarga',
        'penilaian'  => 'Penilaian / Kompetensi',
    ];

    $fieldLabels = [
        'nip' => 'NIP',
        'nama' => 'Nama',
        'tempat_lahir' => 'Tempat Lahir',
        'tgl_lahir' => 'Tanggal Lahir',
        'jenkel' => 'Jenis Kelamin',
        'agama' => 'Agama',
        'alamat' => 'Alamat',
        'gol_upah' => 'Golongan Upah',
        'gol_jabatan' => 'Golongan Jabatan',
        'tmt_gol_jabatan' => 'TMT Golongan Jabatan',
        'tmt_gol_upah' => 'TMT Golongan Upah',
        'jabatan' => 'Jabatan',
        'departemen' => 'Departemen',
        'hubungan_kerja' => 'Hubungan Kerja',
        'lokasi_kerja' => 'Lokasi Kerja',
        'status' => 'Status',
        'tgl_masuk' => 'Tanggal Mulai Kerja',
        'profesional' => 'Profesional',
        'foto' => 'Foto',

        'pendidikan_mulai' => 'Tanggal Mulai',
        'pendidikan_selesai' => 'Tanggal Selesai',
        'jenjang_pendidikan' => 'Jenjang Pendidikan',
        'nama_institusi' => 'Nama Institusi',
        'jurusan' => 'Jurusan',
        'lokasi_pendidikan' => 'Lokasi Pendidikan',

        'tanggal_mulai_kursus' => 'Tanggal Mulai',
        'tanggal_selesai_kursus' => 'Tanggal Selesai',
        'jenis_kursus' => 'Jenis Kursus',
        'nama_kegiatan_kursus' => 'Nama Kegiatan',
        'tanggal_mulai_berlaku' => 'Masa Berlaku Mulai',
        'tanggal_selesai_berlaku' => 'Masa Berlaku Selesai',

        'pglmn_bsp_mulai' => 'Tanggal Mulai',
        'pglmn_bsp_selesai' => 'Tanggal Selesai',
        'pengalaman_jabatan' => 'Jabatan',
        'pengalaman_lokasi' => 'Lokasi',

        'pglmn_luar_bsp_mulai' => 'Tanggal Mulai',
        'pglmn_luar_bsp_selesai' => 'Tanggal Selesai',
        'pengalaman_luar_jabatan' => 'Jabatan',
        'pengalaman_luar_lokasi' => 'Lokasi',

        'nama_keluarga' => 'Nama',
        'tanggal_keluarga' => 'Tanggal Lahir',
        'ket_keluarga' => 'Keterangan',

        'tahun_penilaian' => 'Tahun Penilaian',
        'nilai_penilaian' => 'Nilai',
        'dasar_penilaian' => 'Dasar Penilaian',
    ];

    function labelFieldPengajuan($key, $fieldLabels) {
        return $fieldLabels[$key] ?? ucwords(str_replace('_', ' ', $key));
    }

    function valuePengajuan($key, $value) {
        if ($key === 'foto' && $value) {
            return '<a href="'.asset('storage/'.$value).'" target="_blank">Lihat Foto</a>';
        }

        if (is_array($value)) {
            return e(json_encode($value));
        }

        return e($value ?: '-');
    }
@endphp

<div class="container pt-2 pb-4">

    @if(session('success'))
        <div class="alert alert-success rounded-3">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger rounded-3">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route(auth()->user()->role.'.pengajuan.index') }}" class="btn btn-outline-secondary">
            ← Kembali
        </a>

        <span class="badge bg-{{ $pengajuan->status_badge }} px-3 py-2">
            {{ $pengajuan->status_label }}
        </span>
    </div>

    <div class="card border-0 shadow-sm p-4 mb-4 page-card">
        <div class="d-flex flex-wrap justify-content-between gap-3">
            <div>
                <h4 class="fw-bold mb-1">Detail Pengajuan Perubahan</h4>
                <div class="text-muted small">
                    Pengajuan dari pegawai dengan NIP <b>{{ $pengajuan->nip }}</b>.
                </div>
            </div>

            <div class="text-md-end">
                <div class="small text-muted">Tanggal Pengajuan</div>
                <div class="fw-semibold">{{ optional($pengajuan->created_at)->format('d/m/Y H:i') }}</div>
            </div>
        </div>

        <hr>

        <div class="row g-3">
            <div class="col-md-3">
                <div class="info-box">
                    <div class="label">NIP</div>
                    <div class="value">{{ $pengajuan->nip }}</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="info-box">
                    <div class="label">Nama Pegawai</div>
                    <div class="value">{{ $pengajuan->pegawai->nama ?? ($pegawaiPayload['nama'] ?? '-') }}</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="info-box">
                    <div class="label">Jenis Pengajuan</div>
                    <div class="value">{{ $pengajuan->jenis === 'buat_baru' ? 'Buat Baru' : 'Update / Replace' }}</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="info-box">
                    <div class="label">Status</div>
                    <div class="value">{{ $pengajuan->status_label }}</div>
                </div>
            </div>
        </div>

        @if($pengajuan->catatan_pegawai)
            <div class="mt-3">
                <div class="fw-semibold mb-1">Catatan Pegawai</div>
                <div class="note-box">{{ $pengajuan->catatan_pegawai }}</div>
            </div>
        @endif

        @if($pengajuan->catatan_reviewer)
            <div class="mt-3">
                <div class="fw-semibold mb-1">Catatan Reviewer</div>
                <div class="note-box danger-soft">{{ $pengajuan->catatan_reviewer }}</div>
            </div>
        @endif
    </div>

    {{-- INFORMASI PRIBADI --}}
    <div class="card border-0 shadow-sm p-4 mb-4 page-card">
        <h5 class="fw-bold mb-3">1. Informasi Pribadi</h5>

        @if(empty($pegawaiPayload))
            <div class="text-muted">Tidak ada data informasi pribadi yang diajukan.</div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:30%;">Field</th>
                            <th>Data Diajukan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pegawaiPayload as $key => $value)
                            <tr>
                                <td class="fw-semibold">{{ labelFieldPengajuan($key, $fieldLabels) }}</td>
                                <td>{!! valuePengajuan($key, $value) !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- CHILD SECTIONS --}}
    @foreach(['pendidikan', 'kursus', 'peng_bsp', 'peng_luar', 'keluarga', 'penilaian'] as $sectionKey)
        @php
            $rows = $payload[$sectionKey] ?? [];
        @endphp

        <div class="card border-0 shadow-sm p-4 mb-4 page-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">
                    {{ $loop->iteration + 1 }}. {{ $sectionLabels[$sectionKey] }}
                </h5>
                <span class="badge bg-light text-dark border">
                    {{ count($rows) }} data
                </span>
            </div>

            @if(empty($rows))
                <div class="text-muted">
                    Tidak ada data {{ strtolower($sectionLabels[$sectionKey]) }} yang diajukan.
                </div>
            @else
                @foreach($rows as $index => $row)
                    <div class="child-box mb-3">
                        <div class="fw-bold mb-2">Data #{{ $index + 1 }}</div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <tbody>
                                    @foreach($row as $key => $value)
                                        <tr>
                                            <td class="fw-semibold bg-light" style="width:30%;">
                                                {{ labelFieldPengajuan($key, $fieldLabels) }}
                                            </td>
                                            <td>{!! valuePengajuan($key, $value) !!}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    @endforeach

    {{-- ACTION --}}
    <div class="card border-0 shadow-sm p-4 page-card">
        <h5 class="fw-bold mb-3">Aksi Reviewer</h5>

        @if(in_array($pengajuan->status, ['diterima', 'ditolak']))
            <div class="alert alert-info mb-0">
                Pengajuan ini sudah final dengan status <b>{{ $pengajuan->status_label }}</b>.
            </div>
        @else
            <div class="d-flex flex-wrap gap-2 mb-3">
                <form action="{{ route(auth()->user()->role.'.pengajuan.proses', $pengajuan) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-warning">
                        Tandai Diproses
                    </button>
                </form>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <form action="{{ route(auth()->user()->role.'.pengajuan.terima', $pengajuan) }}" method="POST"
                          onsubmit="return confirm('Terima pengajuan ini dan apply data ke database pegawai?')">
                        @csrf
                        @method('PATCH')

                        <label class="form-label fw-semibold">Catatan Approve Opsional</label>
                        <textarea name="catatan_reviewer" class="form-control mb-2" rows="3"
                                  placeholder="Contoh: Data sudah sesuai."></textarea>

                        <button type="submit" class="btn btn-success w-100">
                            Approve & Simpan ke Database
                        </button>
                    </form>
                </div>

                <div class="col-md-6">
                    <form action="{{ route(auth()->user()->role.'.pengajuan.tolak', $pengajuan) }}" method="POST"
                          onsubmit="return confirm('Tolak pengajuan ini?')">
                        @csrf
                        @method('PATCH')

                        <label class="form-label fw-semibold">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="catatan_reviewer" class="form-control mb-2" rows="3" required
                                  placeholder="Contoh: Dokumen pendukung belum lengkap."></textarea>

                        <button type="submit" class="btn btn-danger w-100">
                            Tolak Pengajuan
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .page-card {
        border-radius: 18px;
    }

    .info-box {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 12px;
        background: #f9fafb;
    }

    .info-box .label {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 4px;
    }

    .info-box .value {
        font-weight: 700;
        color: #374151;
    }

    .note-box {
        border: 1px solid #e5e7eb;
        background: #f9fafb;
        border-radius: 14px;
        padding: 12px;
        white-space: pre-line;
    }

    .danger-soft {
        background: #fff5f5;
        border-color: #fecaca;
    }

    .child-box {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 14px;
        background: #ffffff;
    }

    .table th,
    .table td {
        font-size: 14px;
    }
</style>
@endpush