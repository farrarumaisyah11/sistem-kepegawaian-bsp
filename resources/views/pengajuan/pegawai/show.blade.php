@extends('layouts.app')

@section('title', 'Detail Pengajuan Perubahan')

@section('content')
@php
    \Carbon\Carbon::setLocale('id');
    $payload = $payload ?? ($pengajuan->payload ?? []);

    if (is_string($payload)) {
        $payload = json_decode($payload, true) ?: [];
    }

    if (!is_array($payload)) {
        $payload = [];
    }

    $statusKey = $pengajuan->status ?? '-';

    $statusLabel = match($statusKey) {
        'pending', 'belum_diolah', 'diajukan' => 'Baru Masuk',
        'diproses'                            => 'Diproses',
        'diterima', 'disetujui'               => 'Diterima',
        'ditolak'                             => 'Ditolak',
        default                               => strtoupper(str_replace('_', ' ', $statusKey)),
    };

    $statusClass = match($statusKey) {
        'diterima', 'disetujui'               => 'status-accepted',
        'ditolak'                             => 'status-rejected',
        'diproses'                            => 'status-process',
        'pending', 'belum_diolah', 'diajukan' => 'status-pending',
        default                               => 'status-default',
    };

    $sectionLabels = [
        'pegawai'    => 'Informasi Pribadi',
        'pendidikan' => 'Pendidikan',
        'kursus'     => 'Kursus & Pelatihan',
        'peng_bsp'   => 'Pengalaman BSP',
        'peng_luar'  => 'Pengalaman Luar BSP',
        'keluarga'   => 'Data Keluarga',
        'penilaian'  => 'Penilaian / Kompetensi',
    ];

    $fieldLabels = [
        'nama' => 'Nama',
        'tempat_lahir' => 'Tempat Lahir',
        'tgl_lahir' => 'Tanggal Lahir',
        'jenkel' => 'Jenis Kelamin',
        'agama' => 'Agama',
        'alamat' => 'Alamat',
        'profesional' => 'Profesional',
        'tmt_gol_jabatan' => 'TMT Golongan Jabatan',
        'gol_jabatan' => 'Golongan Jabatan',
        'id_jabatan' => 'ID Jabatan',
        'jabatan' => 'Jabatan',
        'departemen' => 'Departemen',
        'hubungan_kerja' => 'Hubungan Kerja',
        'lokasi_kerja' => 'Lokasi Kerja',
        'status' => 'Status Pegawai',
        'tmt_gol_upah' => 'TMT Golongan Upah',
        'gol_upah' => 'Golongan Upah',
        'tgl_masuk' => 'Tanggal Mulai Kerja',
        'foto' => 'Foto',

        'id_pendidikan' => 'ID Pendidikan',
        'pendidikan_mulai' => 'Tanggal Mulai',
        'pendidikan_selesai' => 'Tanggal Selesai',
        'jenjang_pendidikan' => 'Jenjang Pendidikan',
        'nama_institusi' => 'Nama Institusi',
        'jurusan' => 'Jurusan',
        'lokasi_pendidikan' => 'Lokasi Pendidikan',

        'id_kursus' => 'ID Kursus',
        'tanggal_mulai_kursus' => 'Tanggal Mulai',
        'tanggal_selesai_kursus' => 'Tanggal Selesai',
        'jenis_kursus' => 'Jenis Kursus',
        'nama_kegiatan_kursus' => 'Nama Kegiatan',
        'tanggal_mulai_berlaku' => 'Masa Berlaku Mulai',
        'tanggal_selesai_berlaku' => 'Masa Berlaku Selesai',

        'id_pengalaman_bsp' => 'ID Pengalaman BSP',
        'pglmn_bsp_mulai' => 'Tanggal Mulai',
        'pglmn_bsp_selesai' => 'Tanggal Selesai',
        'pengalaman_jabatan' => 'Jabatan',
        'pengalaman_lokasi' => 'Lokasi',

        'id_pengalaman_luar_bsp' => 'ID Pengalaman Luar BSP',
        'pglmn_luar_bsp_mulai' => 'Tanggal Mulai',
        'pglmn_luar_bsp_selesai' => 'Tanggal Selesai',
        'pengalaman_luar_jabatan' => 'Jabatan',
        'pengalaman_luar_lokasi' => 'Lokasi',

        'id_keluarga' => 'ID Keluarga',
        'nama_keluarga' => 'Nama Keluarga',
        'tanggal_keluarga' => 'Tanggal Lahir',
        'ket_keluarga' => 'Keterangan Keluarga',

        'id_penilaian' => 'ID Penilaian',
        'tahun_penilaian' => 'Tahun Penilaian',
        'nilai_penilaian' => 'Nilai',
        'dasar_penilaian' => 'Dasar Penilaian',
    ];

    $formatLabel = function ($key) use ($fieldLabels) {
        return $fieldLabels[$key] ?? \Illuminate\Support\Str::title(str_replace('_', ' ', $key));
    };

    $formatValue = function ($value) {
        if ($value === null || $value === '') {
            return '-';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        $stringValue = (string) $value;

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $stringValue)) {
            try {
                return \Illuminate\Support\Carbon::parse($stringValue)->locale('id')->translatedFormat('d F Y');
            } catch (\Throwable $e) {
                return $stringValue;
            }
        }

        return $stringValue;
    };

    $formatDateTime = function ($value) {
        if (!$value) {
            return '-';
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d F Y') . ', ' . \Illuminate\Support\Carbon::parse($value)->timezone('Asia/Jakarta')->format('H:i') . ' WIB';
        } catch (\Throwable $e) {
            return $value;
        }
    };

    $backRoute = auth()->user()->role === 'pegawai'
        ? route('pegawai.pengajuan.index')
        : route(auth()->user()->role . '.pengajuan.index');

    $totalSections = collect($sectionLabels)
        ->filter(fn($label, $key) => !empty($payload[$key] ?? null))
        ->count();

    $totalFields = 0;

    foreach ($payload as $key => $items) {
        if ($key === 'pegawai' && is_array($items)) {
            $totalFields += count($items);
            continue;
        }

        if (is_array($items)) {
            foreach ($items as $row) {
                if (is_array($row)) {
                    $totalFields += count($row);
                }
            }
        }
    }
@endphp

<div class="pengajuan-detail-page">
    <div class="detail-header-card mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <div class="eyebrow">DETAIL PENGAJUAN</div>
                <h2 class="page-title">Pengajuan Perubahan Data</h2>
                <p class="page-subtitle mb-0">
                    Lihat status pengajuan, catatan dari HCM/Admin, dan rincian data yang Anda ajukan.
                </p>
            </div>

            <a href="{{ $backRoute }}" class="btn-back">
                <span>‹</span> Kembali
            </a>
        </div>
    </div>

    <div class="summary-grid mb-4">
        <div class="summary-card main-summary">
            <div class="summary-label">Status Pengajuan</div>
            <div class="status-pill {{ $statusClass }}">
                {{ $statusLabel }}
            </div>
            <div class="summary-note mt-3">
                ID Pengajuan #{{ $pengajuan->id_pengajuan ?? $pengajuan->getKey() }}
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Tanggal Diajukan</div>
            <div class="summary-value">{{ $formatDateTime($pengajuan->created_at) }}</div>
            <div class="summary-note">Waktu pengajuan masuk ke sistem.</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Rincian Perubahan</div>
            <div class="summary-value">{{ $totalSections }} Bagian</div>
            <div class="summary-note">{{ $totalFields }} field data diajukan.</div>
        </div>
    </div>

    <div class="status-flow-card mb-4">
        <div class="flow-item {{ in_array($statusKey, ['diajukan', 'pending', 'belum_diolah', 'diproses', 'diterima', 'disetujui', 'ditolak']) ? 'active' : '' }}">
            <div class="flow-dot"></div>
            <div>
                <div class="flow-title">Diajukan</div>
                <div class="flow-desc">{{ $formatDateTime($pengajuan->created_at) }}</div>
            </div>
        </div>

        <div class="flow-line"></div>

        <div class="flow-item {{ in_array($statusKey, ['diproses', 'diterima', 'disetujui', 'ditolak']) ? 'active' : '' }}">
            <div class="flow-dot"></div>
            <div>
                <div class="flow-title">Diproses</div>
                <div class="flow-desc">{{ $formatDateTime($pengajuan->dilihat_pada ?? null) }}</div>
            </div>
        </div>

        <div class="flow-line"></div>

        <div class="flow-item {{ in_array($statusKey, ['diterima', 'disetujui', 'ditolak']) ? 'active' : '' }}">
            <div class="flow-dot"></div>
            <div>
                <div class="flow-title">
                    {{ in_array($statusKey, ['ditolak']) ? 'Ditolak' : 'Selesai' }}
                </div>
                <div class="flow-desc">
                    {{ $formatDateTime($pengajuan->diproses_pada ?? $pengajuan->ditolak_pada ?? null) }}
                </div>
            </div>
        </div>
    </div>

    @if(!empty($pengajuan->catatan_pegawai))
        <div class="note-card note-pegawai mb-4">
            <div class="note-title">Catatan yang Anda Kirim</div>
            <div class="note-text">{{ $pengajuan->catatan_pegawai }}</div>
        </div>
    @endif

    @if(!empty($pengajuan->catatan_reviewer) || !empty($pengajuan->catatan_admin))
        <div class="note-card note-reviewer mb-4">
            <div class="note-title">Catatan HCM/Admin</div>
            <div class="note-text">{{ $pengajuan->catatan_reviewer ?? $pengajuan->catatan_admin }}</div>
        </div>
    @endif

    <div class="detail-card">
        <div class="detail-card-head">
            <div>
                <h5 class="detail-title">Data yang Diajukan</h5>
                <p class="detail-subtitle mb-0">
                    Berikut rincian data yang masuk dalam pengajuan perubahan.
                </p>
            </div>
        </div>

        <div class="detail-card-body">
            @if(empty($payload))
                <div class="empty-state">
                    Tidak ada rincian perubahan pada pengajuan ini.
                </div>
            @else
                @foreach($sectionLabels as $sectionKey => $sectionTitle)
                    @php
                        $items = $payload[$sectionKey] ?? null;
                    @endphp

                    @if(!empty($items))
                        <div class="section-block">
                            <div class="section-title-row">
                                <div>
                                    <div class="section-eyebrow">Bagian</div>
                                    <h6 class="section-title">{{ $sectionTitle }}</h6>
                                </div>
                            </div>

                            @if($sectionKey === 'pegawai')
                                <div class="table-responsive">
                                    <table class="table detail-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 34%;">Field Data</th>
                                                <th>Nilai yang Diajukan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($items as $field => $value)
                                                <tr>
                                                    <td class="field-name">{{ $formatLabel($field) }}</td>
                                                    <td>
                                                        @if($field === 'foto')
                                                            <span class="value-new">Foto baru diajukan</span>
                                                        @else
                                                            <span class="value-new">{{ $formatValue($value) }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                @foreach($items as $rowIndex => $row)
                                    <div class="row-block">
                                        <div class="row-block-title">
                                            Data {{ $loop->iteration }}
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table detail-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 34%;">Field Data</th>
                                                        <th>Nilai yang Diajukan</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach((array) $row as $field => $value)
                                                        <tr>
                                                            <td class="field-name">{{ $formatLabel($field) }}</td>
                                                            <td>
                                                                <span class="value-new">{{ $formatValue($value) }}</span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @endif
                @endforeach
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .pengajuan-detail-page {
        padding-top: 2px;
        padding-bottom: 28px;
    }

    .detail-header-card {
        background: #fbfcfa;
        border: 1px solid #eef1ec;
        padding: 22px 25px;
    }

    .eyebrow,
    .section-eyebrow {
        color: #6b775c;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 2px;
        margin-bottom: 8px;
        text-transform: uppercase;
    }

    .page-title {
        color: #3f4a32;
        font-size: 31px;
        font-weight: 700;
        letter-spacing: -.3px;
        margin-bottom: 8px;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 14px;
        line-height: 1.65;
        max-width: 850px;
        font-weight: 400;
    }

    .btn-back {
        min-height: 42px;
        padding: 10px 18px;
        border-radius: 14px;
        background: #ffffff;
        border: 1px solid #dfe5d8;
        color: #536044;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        white-space: nowrap;
        transition: .2s ease;
    }

    .btn-back span {
        font-size: 22px;
        line-height: 1;
        margin-top: -2px;
    }

    .btn-back:hover {
        background: #6b775c;
        border-color: #6b775c;
        color: #ffffff;
        transform: translateY(-1px);
    }

    .summary-grid {
        display: grid;
        grid-template-columns: 1.15fr .85fr .85fr;
        gap: 16px;
    }

    .summary-card,
    .status-flow-card,
    .note-card,
    .detail-card {
        background: #ffffff;
        border: 1px solid #e8ece5;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
    }

    .summary-card {
        border-radius: 20px;
        padding: 18px;
    }

    .main-summary {
        background: linear-gradient(135deg, #d3d9c7, #c4ccb6);
        border-color: rgba(255,255,255,.55);
    }

    .summary-label {
        color: #536044;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: 10px;
    }

    .summary-value {
        color: #1f2937;
        font-size: 21px;
        font-weight: 500;
        line-height: 1.25;
    }

    .summary-note {
        color: #6b7280;
        font-size: 12.5px;
        font-weight: 400;
        line-height: 1.55;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        padding: 8px 15px;
        border-radius: 999px;
        font-size: 12.5px;
        font-weight: 700;
        letter-spacing: .035em;
        text-transform: uppercase;
    }

    .status-pending {
        background: #e8eef5;
        color: #273957;
        border: 1px solid #b9c8dc;
    }

    .status-process {
        background: #fff3c4;
        color: #7a5200;
        border: 1px solid #f3c94b;
    }

    .status-accepted {
        background: #e7f6ec;
        color: #1f7a3a;
        border: 1px solid #9bd6aa;
    }

    .status-rejected {
        background: #fde8e8;
        color: #b42318;
        border: 1px solid #f3b3b0;
    }

    .status-default {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .status-flow-card {
        border-radius: 20px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        overflow-x: auto;
    }

    .flow-item {
        min-width: 170px;
        display: flex;
        align-items: center;
        gap: 10px;
        opacity: .45;
    }

    .flow-item.active {
        opacity: 1;
    }

    .flow-dot {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #d8ded1;
        box-shadow: 0 0 0 6px rgba(107,119,92,.08);
        flex-shrink: 0;
    }

    .flow-item.active .flow-dot {
        background: #6b775c;
    }

    .flow-title {
        color: #3f4a32;
        font-size: 13px;
        font-weight: 600;
    }

    .flow-desc {
        color: #6b7280;
        font-size: 12px;
        margin-top: 2px;
        white-space: nowrap;
        font-weight: 400;
    }

    .flow-line {
        height: 2px;
        min-width: 64px;
        background: #e5e7eb;
        flex: 1;
    }

    .note-card {
        border-radius: 20px;
        padding: 18px 20px;
        border-left: 5px solid #c5a059;
    }

    .note-reviewer {
        border-left-color: #6b775c;
    }

    .note-title {
        color: #3f4a32;
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 7px;
    }

    .note-text {
        color: #374151;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.65;
        white-space: pre-line;
    }

    .detail-card {
        border-radius: 22px;
        overflow: hidden;
    }

    .detail-card-head {
        padding: 18px 22px;
        background: linear-gradient(180deg, #fcfdfb, #f8faf6);
        border-bottom: 1px solid #edf1e8;
    }

    .detail-title {
        color: #3f4a32;
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .detail-subtitle {
        color: #6b7280;
        font-size: 13px;
        font-weight: 400;
        line-height: 1.55;
    }

    .detail-card-body {
        padding: 20px 22px;
    }

    .section-block {
        padding-bottom: 22px;
        margin-bottom: 22px;
        border-bottom: 1px solid #edf1e8;
    }

    .section-block:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .section-title-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 13px;
    }

    .section-title {
        color: #3f4a32;
        font-size: 17px;
        font-weight: 700;
        margin-bottom: 0;
    }

    .row-block {
        background: #fbfcfa;
        border: 1px solid #eef1ec;
        border-radius: 18px;
        padding: 14px;
        margin-bottom: 13px;
    }

    .row-block:last-child {
        margin-bottom: 0;
    }

    .row-block-title {
        display: inline-flex;
        align-items: center;
        padding: 6px 11px;
        border-radius: 999px;
        background: #eef2eb;
        color: #536044;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .detail-table {
        margin-bottom: 0;
        border: 1px solid #e8ece5;
        background: #ffffff;
    }

    .detail-table thead th {
        background: #6b775c;
        color: #ffffff;
        border-color: rgba(255,255,255,.12);
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        padding: 12px 14px;
        vertical-align: middle;
    }

    .detail-table tbody td {
        border-color: #e8ece5;
        padding: 12px 14px;
        vertical-align: top;
        font-size: 13.5px;
        font-weight: 400;
    }

    .field-name {
        color: #374151;
        font-weight: 500;
        background: #fbfcfa;
    }

    .value-new {
        color: #3f4a32;
        font-weight: 400;
        word-break: break-word;
    }

    .empty-state {
        padding: 24px;
        background: #fbfcfa;
        border: 1px dashed #d8ded1;
        border-radius: 18px;
        color: #6b7280;
        font-weight: 500;
        text-align: center;
    }

    @media (max-width: 991px) {
        .summary-grid {
            grid-template-columns: 1fr;
        }

        .status-flow-card {
            align-items: flex-start;
        }

        .flow-line {
            min-width: 36px;
        }
    }

    @media (max-width: 768px) {
        .detail-header-card {
            padding: 20px 18px;
        }

        .page-title {
            font-size: 26px;
        }

        .btn-back {
            width: 100%;
            justify-content: center;
        }

        .detail-card-body {
            padding: 16px;
        }

        .summary-card {
            padding: 16px;
        }
    }
</style>
@endpush