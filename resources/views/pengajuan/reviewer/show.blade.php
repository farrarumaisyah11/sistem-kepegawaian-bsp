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

    $sectionIcons = [
        'pendidikan' => 'bi-mortarboard',
        'kursus'     => 'bi-patch-check',
        'peng_bsp'   => 'bi-building-check',
        'peng_luar'  => 'bi-briefcase',
        'keluarga'   => 'bi-people',
        'penilaian'  => 'bi-graph-up-arrow',
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
        'id_jabatan' => 'ID Jabatan',
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

    $dateFields = [
        'tgl_lahir',
        'tmt_gol_jabatan',
        'tmt_gol_upah',
        'tgl_masuk',
        'pendidikan_mulai',
        'pendidikan_selesai',
        'tanggal_mulai_kursus',
        'tanggal_selesai_kursus',
        'tanggal_mulai_berlaku',
        'tanggal_selesai_berlaku',
        'pglmn_bsp_mulai',
        'pglmn_bsp_selesai',
        'pglmn_luar_bsp_mulai',
        'pglmn_luar_bsp_selesai',
        'tanggal_keluarga',
    ];

    $labelFieldPengajuan = fn($key) => $fieldLabels[$key] ?? ucwords(str_replace('_', ' ', $key));

    $valuePengajuan = function ($key, $value) use ($dateFields) {
        if ($key === 'foto' && $value) {
            return '<a class="file-link" href="' . asset('storage/' . $value) . '" target="_blank" rel="noopener">
                        <i class="bi bi-image"></i>
                        <span>Lihat Foto</span>
                    </a>';
        }

        if ($value === null || $value === '') {
            return '<span class="empty-value">-</span>';
        }

        if (is_array($value)) {
            return e(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        if (in_array($key, $dateFields, true)) {
            try {
                return e(\Carbon\Carbon::parse($value)->format('d/m/Y'));
            } catch (\Throwable $exception) {
                return e($value);
            }
        }

        return e($value);
    };

    $statusTone = match($pengajuan->status) {
        'diajukan', 'pending', 'belum_diolah' => 'status-new',
        'diproses' => 'status-process',
        'diterima', 'disetujui' => 'status-approved',
        'ditolak' => 'status-rejected',
        default => 'status-default',
    };

    $statusIcon = match($pengajuan->status) {
        'diajukan', 'pending', 'belum_diolah' => 'bi-inbox',
        'diproses' => 'bi-hourglass-split',
        'diterima', 'disetujui' => 'bi-check-circle',
        'ditolak' => 'bi-x-circle',
        default => 'bi-circle',
    };

    $namaPegawai = $pengajuan->pegawai->nama ?? ($pegawaiPayload['nama'] ?? $pengajuan->nama_pegawai ?? '-');
    $jenisLabel = $pengajuan->jenis === 'buat_baru' ? 'Buat Baru' : 'Update / Replace';
    $totalSection = collect(['pendidikan','kursus','peng_bsp','peng_luar','keluarga','penilaian'])
        ->sum(fn($key) => count($payload[$key] ?? []));
@endphp

<div class="review-page">
    @if(session('success'))
        <div class="alert alert-success page-alert">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger page-alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="review-toolbar">
        <a href="{{ route(auth()->user()->role.'.pengajuan.index') }}" class="btn btn-back">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali</span>
        </a>

        <span class="status-chip {{ $statusTone }}">
            <i class="bi {{ $statusIcon }}"></i>
            <span>{{ $pengajuan->status_label }}</span>
        </span>
    </div>

    <div class="hero-card mb-4">
        <div class="hero-content">
            <div class="hero-title-wrap">
                <span class="hero-icon"><i class="bi bi-file-earmark-text"></i></span>
                <div>
                    <div class="eyebrow">Review Pengajuan</div>
                    <h2 class="hero-title">Detail Pengajuan Perubahan</h2>
                    <p class="hero-subtitle">
                        Pengajuan perubahan data pegawai dengan NIP <strong>{{ $pengajuan->nip }}</strong>.
                    </p>
                </div>
            </div>

            <div class="submitted-card">
                <div class="submitted-label">Tanggal Pengajuan</div>
                <div class="submitted-value">{{ optional($pengajuan->created_at)->format('d/m/Y') ?? '-' }}</div>
                <div class="submitted-time">{{ optional($pengajuan->created_at)->format('H:i') ?? '-' }} WIB</div>
            </div>
        </div>

        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-icon blue"><i class="bi bi-credit-card-2-front"></i></div>
                <div>
                    <div class="summary-label">NIP</div>
                    <div class="summary-value">{{ $pengajuan->nip }}</div>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon teal"><i class="bi bi-person-badge"></i></div>
                <div>
                    <div class="summary-label">Nama Pegawai</div>
                    <div class="summary-value">{{ $namaPegawai }}</div>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon amber"><i class="bi bi-arrow-repeat"></i></div>
                <div>
                    <div class="summary-label">Jenis Pengajuan</div>
                    <div class="summary-value">{{ $jenisLabel }}</div>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon violet"><i class="bi bi-folder-check"></i></div>
                <div>
                    <div class="summary-label">Total Detail</div>
                    <div class="summary-value">{{ count($pegawaiPayload) + $totalSection }} Item</div>
                </div>
            </div>
        </div>
    </div>

    @if($pengajuan->catatan_pegawai || $pengajuan->catatan_reviewer)
        <div class="notes-grid mb-4">
            @if($pengajuan->catatan_pegawai)
                <div class="note-panel">
                    <div class="note-icon employee"><i class="bi bi-chat-left-text"></i></div>
                    <div>
                        <div class="note-title">Catatan Pegawai</div>
                        <div class="note-body">{{ $pengajuan->catatan_pegawai }}</div>
                    </div>
                </div>
            @endif

            @if($pengajuan->catatan_reviewer)
                <div class="note-panel reviewer">
                    <div class="note-icon reviewer"><i class="bi bi-clipboard-check"></i></div>
                    <div>
                        <div class="note-title">Catatan Reviewer</div>
                        <div class="note-body">{{ $pengajuan->catatan_reviewer }}</div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if(!empty($pegawaiPayload))
        <div class="data-card mb-4">
            <div class="data-card-header">
                <div class="section-heading">
                    <span class="section-number">1</span>
                    <div>
                        <h5>Informasi Pribadi</h5>
                        <p>Data identitas pegawai yang diajukan untuk diperbarui.</p>
                    </div>
                </div>
                <span class="section-pill personal">{{ count($pegawaiPayload) }} Field</span>
            </div>

            <div class="data-card-body">
                <div class="table-responsive">
                    <table class="table detail-table mb-0">
                        <thead>
                            <tr>
                                <th class="field-header">Field</th>
                                <th>Data Diajukan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pegawaiPayload as $key => $value)
                                <tr>
                                    <td class="field-name">{{ $labelFieldPengajuan($key) }}</td>
                                    <td class="field-value">{!! $valuePengajuan($key, $value) !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @php
        $sectionNo = !empty($pegawaiPayload) ? 2 : 1;
    @endphp

    @foreach(['pendidikan','kursus','peng_bsp','peng_luar','keluarga','penilaian'] as $sectionKey)
        @php
            $rows = $payload[$sectionKey] ?? [];
        @endphp

        @if(empty($rows))
            @continue
        @endif

        <div class="data-card mb-4">
            <div class="data-card-header">
                <div class="section-heading">
                    <span class="section-number"><i class="bi {{ $sectionIcons[$sectionKey] }}"></i></span>
                    <div>
                        <h5>{{ $sectionNo++ }}. {{ $sectionLabels[$sectionKey] }}</h5>
                        <p>Detail data yang diajukan pegawai.</p>
                    </div>
                </div>
                <span class="section-pill">{{ count($rows) }} Item</span>
            </div>

            <div class="data-card-body">
                @foreach($rows as $index => $row)
                    <div class="detail-block">
                        <div class="detail-block-header">
                            <span>Data #{{ $index + 1 }}</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table detail-table mb-0">
                                <tbody>
                                    @foreach($row as $key => $value)
                                        <tr>
                                            <td class="field-name">{{ $labelFieldPengajuan($key) }}</td>
                                            <td class="field-value">{!! $valuePengajuan($key, $value) !!}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="action-card mb-4">
        <div class="action-card-header">
            <div>
                <div class="eyebrow dark">AKSI REVIEWER</div>
                <h5>Keputusan Pengajuan</h5>
                <p>Kelola status pengajuan dan catatan reviewer.</p>
            </div>
            <span class="status-chip {{ $statusTone }}">
                <i class="bi {{ $statusIcon }}"></i>
                <span>{{ $pengajuan->status_label }}</span>
            </span>
        </div>

        <div class="action-card-body">
            @if(in_array($pengajuan->status, ['diterima', 'disetujui', 'ditolak']))
                <div class="final-state">
                    <i class="bi bi-lock-fill"></i>
                    <span>Pengajuan ini sudah final dengan status <strong>{{ $pengajuan->status_label }}</strong>.</span>
                </div>
            @else
                <form action="{{ route(auth()->user()->role.'.pengajuan.proses', $pengajuan) }}" method="POST" class="process-form">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-process">
                        <i class="bi bi-hourglass-split"></i>
                        <span>Tandai Diproses</span>
                    </button>
                </form>

                <div class="decision-grid">
                    <form action="{{ route(auth()->user()->role.'.pengajuan.terima', $pengajuan) }}" method="POST" class="decision-panel approve-panel js-approve-form">
                        @csrf
                        @method('PATCH')
                        <div class="decision-title">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Approve Pengajuan</span>
                        </div>
                        <label class="form-label">Catatan Approve Opsional</label>
                        <textarea name="catatan_reviewer" class="form-control" rows="4" placeholder="Contoh: Data sudah sesuai."></textarea>
                        <button type="submit" class="btn btn-approve">
                            <i class="bi bi-database-check"></i>
                            <span>Approve & Simpan ke Database</span>
                        </button>
                    </form>

                    <form action="{{ route(auth()->user()->role.'.pengajuan.tolak', $pengajuan) }}" method="POST" class="decision-panel reject-panel" onsubmit="return confirm('Tolak pengajuan ini?')">
                        @csrf
                        @method('PATCH')
                        <div class="decision-title">
                            <i class="bi bi-x-circle-fill"></i>
                            <span>Tolak Pengajuan</span>
                        </div>
                        <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="catatan_reviewer" class="form-control" rows="4" required placeholder="Contoh: Dokumen pendukung belum lengkap."></textarea>
                        <button type="submit" class="btn btn-reject">
                            <i class="bi bi-send-x"></i>
                            <span>Tolak Pengajuan</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    :root {
        --corp-navy: #23324d;
        --corp-navy-2: #30415f;
        --corp-olive: #66764f;
        --corp-olive-dark: #4f5f39;
        --corp-teal: #0f766e;
        --corp-blue: #2563eb;
        --corp-amber: #d97706;
        --corp-violet: #6d5bd0;
        --corp-green: #15803d;
        --corp-red: #dc2626;
        --corp-border: #e5e9ef;
        --corp-soft: #f6f8fb;
        --corp-muted: #6b7280;
        --corp-text: #111827;
    }

    body {
        background:
            background-color: #f4f6f2;
    }

    .review-page {
        width: 100%;
        max-width: 1400px;
        margin: -12px auto 0;
    }

    .review-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 16px;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 42px;
        padding: 10px 16px;
        border: 1px solid #d8dee8;
        border-radius: 12px;
        background: #ffffff;
        color: var(--corp-navy);
        font-weight: 600;
        box-shadow: 0 6px 16px rgba(15, 23, 42, .04);
    }

    .btn-back:hover {
        background: var(--corp-navy);
        border-color: var(--corp-navy);
        color: #ffffff;
    }

    .status-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 38px;
        padding: 8px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .2px;
        white-space: nowrap;
    }

    .status-new {
        background: #f4f6f2;
        color: var(--corp-olive-dark);
        border: 1px solid #dce3d5;
    }

    .status-process {
        background: #f8f4e6;
        color: #74623c;
        border: 1px solid #e8dcc0;
    }

    .status-approved {
        background: #eef5eb;
        color: #4f653a;
        border: 1px solid #d7e4cf;
    }

    .status-rejected {
        background: #f8eeee;
        color: #8f3f3f;
        border: 1px solid #ead4d4;
    }

    .status-default {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
    }

    .hero-card {
        position: relative;
        overflow: hidden;
        border: 1px solid var(--corp-border);
        border-left: 5px solid var(--corp-olive);
        border-radius: 16px;
        background: #ffffff;
        color: var(--corp-text);
        box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
    }

    .hero-card::after {
        display: none;
    }

    .hero-content {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 24px;
        padding: 22px 24px 18px;
        border-bottom: 1px solid #eef1f5;
    }

    .hero-title-wrap {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        min-width: 0;
    }

    .hero-icon {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: #eff5eb;
        color: var(--corp-olive-dark);
        font-size: 18px;
    }

    .eyebrow {
        color: var(--corp-olive);
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .4px;
        margin-bottom: 5px;
    }

    .eyebrow.dark {
        color: var(--corp-olive);
    }

    .hero-title {
        margin: 0 0 6px;
        color: var(--corp-navy);
        font-size: 25px;
        font-weight: 700;
        letter-spacing: 0;
    }

    .hero-subtitle {
        margin: 0;
        color: var(--corp-muted);
        font-size: 15px;
        line-height: 1.6;
    }

    .hero-subtitle strong {
        color: var(--corp-navy);
        font-weight: 600;
    }

    .submitted-card {
        flex: 0 0 auto;
        min-width: 190px;
        padding: 13px 16px;
        border: 1px solid #e6ebf1;
        border-radius: 12px;
        background: #f8fafc;
        text-align: right;
    }

    .submitted-label {
        color: var(--corp-muted);
        font-size: 12px;
        font-weight: 600;
    }

    .submitted-value {
        color: var(--corp-navy);
        font-size: 17px;
        font-weight: 700;
        margin-top: 5px;
    }

    .submitted-time {
        color: var(--corp-olive);
        font-size: 13px;
        font-weight: 600;
        margin-top: 2px;
    }

    .summary-grid {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0;
        padding: 0;
    }

    .summary-card {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
        padding: 16px 20px;
        border-right: 1px solid #eef1f5;
        background: #ffffff;
    }

    .summary-card:last-child {
        border-right: none;
    }

    .summary-icon {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        font-size: 17px;
    }

    .summary-icon.blue {
        background: #eef4ff;
        color: var(--corp-blue);
    }

    .summary-icon.teal {
        background: #edf7f5;
        color: var(--corp-teal);
    }

    .summary-icon.amber {
        background: #fff6df;
        color: var(--corp-amber);
    }

    .summary-icon.violet {
        background: #f1efff;
        color: var(--corp-violet);
    }

    .summary-label {
        color: var(--corp-muted);
        font-size: 12px;
        font-weight: 600;
        text-transform: none;
        letter-spacing: 0;
    }

    .summary-value {
        color: var(--corp-navy);
        font-size: 16px;
        font-weight: 600;
        line-height: 1.28;
        margin-top: 2px;
        overflow-wrap: anywhere;
    }

    .page-alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        border: none;
        border-radius: 14px;
        font-weight: 600;
    }

    .notes-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .note-panel {
        display: flex;
        gap: 14px;
        padding: 18px;
        border: 1px solid #dfe8d8;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .05);
    }

    .note-panel.reviewer {
        border-color: #ffd3d3;
    }

    .note-icon {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        font-size: 18px;
    }

    .note-icon.employee {
        background: #edf7ed;
        color: var(--corp-green);
    }

    .note-icon.reviewer {
        background: #fff0f0;
        color: var(--corp-red);
    }

    .note-title {
        color: var(--corp-navy);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .6px;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .note-body {
        color: #374151;
        font-size: 14px;
        line-height: 1.7;
        white-space: pre-line;
    }

    .data-card,
    .action-card {
        overflow: hidden;
        border: 1px solid var(--corp-border);
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .05);
    }

    .data-card-header,
    .action-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 22px;
        border-bottom: 1px solid var(--corp-border);
    }

    .data-card-header {
        background:
            linear-gradient(90deg, #f8fafc, #ffffff 48%, #f5f8f2);
    }

    .section-heading {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    .section-number {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: var(--corp-navy);
        color: #ffffff;
        font-size: 16px;
        font-weight: 700;
    }

    .section-heading h5,
    .action-card-header h5 {
        margin: 0 0 4px;
        color: var(--corp-navy);
        font-size: 18px;
        font-weight: 700;
    }

    .section-heading p,
    .action-card-header p {
        margin: 0;
        color: var(--corp-muted);
        font-size: 13px;
        line-height: 1.5;
    }

    .section-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 7px 13px;
        border-radius: 999px;
        background: #edf7f4;
        color: var(--corp-teal);
        border: 1px solid #cce9e3;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .section-pill.personal {
        background: #edf2ff;
        color: #1d4ed8;
        border-color: #cbdcff;
    }

    .data-card-body,
    .action-card-body {
        padding: 20px 22px;
    }

    .detail-table {
        border: 1px solid var(--corp-border);
        table-layout: fixed;
    }

    .detail-table thead th {
        padding: 14px 16px;
        background: var(--corp-navy);
        border-color: rgba(255, 255, 255, .12);
        color: #ffffff;
        font-size: 13px;
        font-weight: 600;
        vertical-align: middle;
    }

    .detail-table td {
        padding: 14px 16px;
        border-color: #edf0f4;
        color: var(--corp-text);
        font-size: 14px;
        vertical-align: top;
        overflow-wrap: anywhere;
    }

    .detail-table tbody tr:nth-child(even) td {
        background: #fbfcfe;
    }

    .detail-table tbody tr:hover td {
        background: #f6faf5;
    }

    .field-header,
    .field-name {
        width: 30%;
    }

    .field-name {
        background: #f7f9fc;
        color: var(--corp-navy);
        font-weight: 600;
    }

    .field-value {
        color: #1f2937;
        font-weight: 500;
    }

    .empty-value {
        color: #9ca3af;
        font-weight: 600;
    }

    .file-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 34px;
        padding: 7px 12px;
        border-radius: 10px;
        background: #eef6ff;
        color: #1d4ed8;
        font-weight: 600;
        text-decoration: none;
    }

    .file-link:hover {
        background: #dbeafe;
        color: #1e40af;
    }

    .detail-block {
        overflow: hidden;
        border: 1px solid var(--corp-border);
        border-radius: 14px;
        background: #ffffff;
    }

    .detail-block:not(:last-child) {
        margin-bottom: 18px;
    }

    .detail-block-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 16px;
        background:
            linear-gradient(90deg, var(--corp-olive-dark), var(--corp-olive));
        color: #ffffff;
        font-weight: 600;
    }

    .detail-block .detail-table {
        border: none;
    }

    .action-card-header {
        background: #fbfcfa;
        border-left: 4px solid var(--corp-olive);
    }

    .action-card-body {
        background: #ffffff;
    }

    .process-form {
        margin-bottom: 16px;
    }

    .btn-process,
    .btn-approve,
    .btn-reject {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        min-height: 48px;
        border-radius: 12px;
        font-weight: 600;
        border: none;
    }

    .btn-process {
        background: #f8f4e6;
        color: #74623c;
        border: 1px solid #e8dcc0;
    }

    .btn-process:hover {
        background: #f1ead7;
        color: #5f5031;
    }

    .decision-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .decision-panel {
        padding: 18px;
        border-radius: 16px;
        border: 1px solid var(--corp-border);
        background: #ffffff;
    }

    .approve-panel {
        background: #fbfcfa;
        border-color: #d7e4cf;
    }

    .reject-panel {
        background: #fcfbfa;
        border-color: #ead4d4;
    }

    .decision-title {
        display: flex;
        align-items: center;
        gap: 9px;
        color: var(--corp-navy);
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 14px;
    }

    .approve-panel .decision-title i {
        color: var(--corp-green);
    }

    .reject-panel .decision-title i {
        color: var(--corp-red);
    }

    .form-label {
        color: var(--corp-navy);
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .form-control {
        min-height: 112px;
        border: 1px solid #dbe2ea;
        border-radius: 12px;
        color: #1f2937;
        font-size: 14px;
        resize: vertical;
        margin-bottom: 14px;
    }

    .form-control:focus {
        border-color: var(--corp-olive);
        box-shadow: 0 0 0 .2rem rgba(102, 118, 79, .16);
    }

    .btn-approve {
        background: var(--corp-green);
        color: #ffffff;
    }

    .btn-approve:hover {
        background: #116932;
        color: #ffffff;
    }

    .btn-reject {
        background: var(--corp-red);
        color: #ffffff;
    }

    .btn-reject:hover {
        background: #b91c1c;
        color: #ffffff;
    }

    .final-state {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 16px 18px;
        border-radius: 14px;
        background: #eef6ff;
        color: #1f3b57;
        font-weight: 600;
    }

    @media (max-width: 1200px) {
        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .summary-card:nth-child(2) {
            border-right: none;
        }

        .summary-card:nth-child(n+3) {
            border-top: 1px solid #eef1f5;
        }
    }

    @media (max-width: 992px) {
        .hero-content,
        .data-card-header,
        .action-card-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .submitted-card {
            width: 100%;
            text-align: left;
        }

        .notes-grid,
        .decision-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .review-page {
            margin-top: -6px;
        }

        .review-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .btn-back,
        .review-toolbar .status-chip {
            width: 100%;
        }

        .hero-content,
        .data-card-body,
        .action-card-body {
            padding: 18px;
        }

        .hero-title {
            font-size: 25px;
        }

        .summary-grid {
            grid-template-columns: 1fr;
            padding: 0;
        }

        .summary-card {
            border-right: none;
            border-top: 1px solid #eef1f5;
            padding: 15px 18px;
        }

        .summary-card:first-child {
            border-top: none;
        }

        .data-card-header,
        .action-card-header {
            padding: 16px 18px;
        }

        .section-heading {
            align-items: flex-start;
        }

        .detail-table {
            min-width: 620px;
        }

        .field-header,
        .field-name {
            width: 38%;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const approveForm = document.querySelector('.js-approve-form');

        if (!approveForm || typeof Swal === 'undefined') {
            return;
        }

        approveForm.addEventListener('submit', function (event) {
            event.preventDefault();

            Swal.fire({
                icon: 'question',
                title: 'Approve Pengajuan?',
                text: 'Data pengajuan akan disimpan ke database pegawai.',
                showCancelButton: true,
                confirmButtonText: 'Ya, Approve',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#15803d',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                const submitButton = approveForm.querySelector('button[type="submit"]');

                if (submitButton) {
                    submitButton.disabled = true;
                }

                Swal.fire({
                    title: 'Memproses Approve',
                    text: 'Mohon tunggu sebentar.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                approveForm.submit();
            });
        });
    });
</script>
@endpush
