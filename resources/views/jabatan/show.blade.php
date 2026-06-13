@extends('layouts.app')
@section('title','Detail Jabatan')

@section('content')
@if ($errors->any())
    <div class="container-xl d-print-none mt-3">
        <div class="alert alert-danger rounded-4 shadow-sm">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

@if(session('success_auto'))
    <div class="container-xl d-print-none mt-3">
        <div class="alert alert-success rounded-4 shadow-sm">
            {{ session('success_auto') }}
        </div>
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

    /*
    |--------------------------------------------------------------------------
    | Approval Display
    |--------------------------------------------------------------------------
    | Ditampilkan di show, print, dan download PDF.
    | Ringkasan kecil muncul di kop setiap halaman.
    | Detail lengkap ditampilkan pada kartu approval dan blok pengesahan bawah.
    |--------------------------------------------------------------------------
    */
    $approvalStatus = $j->approval_status ?? 'pending';
    $approvalFlowStatus = $j->approval_flow_status ?? 'pending';

    $isFinalApproval = $j->is_approval_final ?? false;
    $isWaitingHcmFinal = $j->is_waiting_hcm_final ?? false;

    if ($isFinalApproval) {
        $approvalStatusText = 'Approved Final';
        $approvalStatusClass = 'approved';
    } elseif ($isWaitingHcmFinal) {
        $approvalStatusText = 'Menunggu Approval Final HCM';
        $approvalStatusClass = 'pending';
    } else {
        $approvalStatusText = match ($approvalStatus) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'pending' => 'Pending Approval',
            default => 'Pending Approval',
        };

        $approvalStatusClass = match ($approvalStatus) {
            'approved' => 'approved',
            'rejected' => 'rejected',
            'pending' => 'pending',
            default => 'pending',
        };
    }

    $isApproved = $isFinalApproval || $approvalStatus === 'approved';

    $approvalDate = '-';
    if (!empty($j->approved_at)) {
        try {
            $approvalDate = \Illuminate\Support\Carbon::parse($j->approved_at)
                ->locale('id')
                ->translatedFormat('d F Y H:i');
        } catch (\Throwable $e) {
            $approvalDate = $j->approved_at;
        }
    }

    $hcmFinalDate = '-';
    if (!empty($j->hcm_confirmed_at)) {
        try {
            $hcmFinalDate = \Illuminate\Support\Carbon::parse($j->hcm_confirmed_at)
                ->locale('id')
                ->translatedFormat('d F Y H:i');
        } catch (\Throwable $e) {
            $hcmFinalDate = $j->hcm_confirmed_at;
        }
    }

    $approvedByName = $j->approved_by_name ?? $j->proposed_approved_by_name ?? '-';
    $approvedByRole = !empty($j->approved_by_role ?? $j->proposed_approved_by_role) ? strtoupper($j->approved_by_role ?? $j->proposed_approved_by_role) : '-';
    $approvedByJabatan = $j->approved_by_jabatan ?? $j->proposed_approved_by_jabatan ?? '-';
    $approvedByDepartemen = $j->approved_by_departemen ?? $j->proposed_approved_by_departemen ?? '-';
    $approvalCatatan = $j->approval_catatan ?? $j->proposed_approval_catatan ?? '-';


    $formatTanggalApprovalLog = function ($date) {
        if (!$date) return '-';
        try {
            return \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('d F Y H:i');
        } catch (\Throwable $e) {
            return $date;
        }
    };

    $approvalLogs = $j->relationLoaded('approvalLogs')
        ? $j->approvalLogs->where('id_jabatan', $j->id_jabatan)->values()
        : collect();

    $approvalLogs = $approvalLogs->take(50);

    $pendingVersionLabel = $j->pendingVersion
        ? 'Versi '.$j->pendingVersion->version_number
        : '-';

    $activeVersionLabel = $j->activeVersion
        ? 'Versi '.$j->activeVersion->version_number
        : '-';
@endphp

<div class="jd-page">

    {{-- CORPORATE ACTION AREA HANYA UNTUK ADMIN / HCM --}}
    @if(in_array($role, ['admin', 'hcm']) && !$jabatanNotFound)
        @php
            $canShowApprovalLink = (bool) ($j->can_approval_link_action ?? false);
            $canFinalApprove = $role === 'hcm' && (bool) ($j->can_hcm_final_approve_from_show ?? false);
            $canApplyJobdesc = in_array($role, ['admin', 'hcm'], true) && (bool) ($j->can_apply_approved_version ?? false);
        @endphp

        <div class="container-xl d-print-none jd-corporate-panel">
            <div class="jd-command-card">
                <div class="jd-command-left">
                    <a href="{{ route($prefix.'.jabatan.index') }}" class="btn btn-light jd-btn jd-btn-muted">
                        <i class="bi bi-arrow-left"></i>
                        <span>Kembali</span>
                    </a>

                    <div class="jd-command-title">
                        <div class="jd-command-eyebrow">Detail Jabatan</div>
                        <div class="jd-command-name">{{ $j->nama_jabatan ?? '-' }}</div>
                        <div class="jd-command-meta">
                            {{ $j->departemen ?? '-' }} · Active {{ $activeVersionLabel }} · Pending {{ $pendingVersionLabel }}
                        </div>
                    </div>
                </div>

                <div class="jd-command-right">
                    <a href="{{ route($prefix.'.jabatan.edit', $j->id_jabatan) }}"
                       class="btn btn-warning text-dark jd-btn">
                        <i class="bi bi-pencil-square"></i>
                        <span>Edit</span>
                    </a>

                    <button type="button" onclick="printJabatanA4()" class="btn btn-outline-primary jd-btn">
                        <i class="bi bi-printer"></i>
                        <span>Print A4</span>
                    </button>

                    <button type="button" id="downloadPdfBtn" class="btn btn-primary jd-btn">
                        <i class="bi bi-download"></i>
                        <span>Download PDF</span>
                    </button>
                </div>
            </div>

            <div class="jd-workflow-card">
                <div class="jd-workflow-status">
                    <div class="jd-workflow-label">Workflow Approval</div>
                    <div class="jd-workflow-main">
                        <span class="jd-status-pill {{ $approvalStatusClass }}">
                            {{ $approvalStatusText }}
                        </span>

                        @if($isWaitingHcmFinal)
                            <span class="jd-action-note">
                                Approval awal sudah tercatat. Dokumen menunggu pengesahan final HCM.
                            </span>
                        @elseif($isFinalApproval)
                            <span class="jd-action-note">
                                Dokumen sudah final approved. Link approval ditutup sampai ada pembaruan berikutnya.
                            </span>
                        @else
                            <span class="jd-action-note">
                                Dokumen masih menunggu approval awal dari approver departemen atau HCM.
                            </span>
                        @endif
                    </div>
                </div>

                <div class="jd-workflow-actions">
                    @if($canShowApprovalLink)
                        <a href="{{ route($prefix.'.jabatan.approval-page', $j->id_jabatan) }}"
                           class="btn btn-outline-success jd-btn">
                            <i class="bi bi-link-45deg"></i>
                            <span>Link Approval</span>
                        </a>
                    @endif

                    @if($role === 'hcm')
                        <form method="POST"
                              action="{{ route('hcm.jabatan.approval.confirm-final-from-show', $j->id_jabatan) }}"
                              class="jd-inline-form"
                              onsubmit="return confirm('Setujui final job description ini sebagai HCM? Setelah final, link approval akan ditutup.');">
                            @csrf

                            <button type="submit"
                                    class="btn btn-success jd-btn"
                                    {{ $canFinalApprove ? '' : 'disabled' }}>
                                <i class="bi bi-check2-circle"></i>
                                <span>Approve Final HCM</span>
                            </button>
                        </form>
                    @endif

                    <form method="POST"
                          action="{{ route($prefix.'.jabatan.apply-approved-version', $j->id_jabatan) }}"
                          class="jd-inline-form"
                          onsubmit="return confirm('Terapkan job description approved final ini ke seluruh pegawai yang memegang jabatan tersebut?');">
                        @csrf

                        <button type="submit"
                                class="btn btn-outline-success jd-btn"
                                {{ $canApplyJobdesc ? '' : 'disabled' }}>
                            <i class="bi bi-people"></i>
                            <span>Terapkan ke Pegawai</span>
                        </button>
                    </form>

                    <button type="button"
                            class="btn btn-outline-secondary jd-btn"
                            data-jd-modal-open="approvalLogModal">
                        <i class="bi bi-clock-history"></i>
                        <span>Riwayat Approval</span>
                        <span class="jd-btn-count">{{ $approvalLogs->count() }}</span>
                    </button>
                </div>
            </div>

            <div class="jd-modal-backdrop" id="approvalLogModal" aria-hidden="true">
                <div class="jd-modal-panel" role="dialog" aria-modal="true" aria-labelledby="approvalLogModalTitle">
                    <div class="jd-modal-head">
                        <div>
                            <div class="jd-modal-eyebrow">Audit Trail</div>
                            <h5 class="jd-modal-title" id="approvalLogModalTitle">Riwayat Approval Jabatan Ini</h5>
                            <div class="jd-modal-subtitle">
                                Log berikut hanya milik jabatan <strong>{{ $j->nama_jabatan ?? '-' }}</strong>, bukan gabungan seluruh jabatan.
                            </div>
                        </div>

                        <button type="button" class="jd-modal-close" data-jd-modal-close="approvalLogModal" aria-label="Tutup">
                            &times;
                        </button>
                    </div>

                    <div class="jd-modal-toolbar">
                        <div class="jd-audit-count">{{ $approvalLogs->count() }} aktivitas</div>

                        <div class="jd-modal-toolbar-actions">
                            <button type="button" class="btn btn-outline-primary jd-btn jd-btn-sm" id="downloadApprovalLogPdfBtn">
                                <i class="bi bi-file-earmark-pdf"></i>
                                <span>Download PDF Log</span>
                            </button>

                            <button type="button" class="btn btn-outline-success jd-btn jd-btn-sm" id="downloadApprovalLogCsvBtn">
                                <i class="bi bi-filetype-csv"></i>
                                <span>Download CSV</span>
                            </button>
                        </div>
                    </div>

                    <div class="jd-modal-body">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle jd-audit-table mb-0" id="approvalLogTable">
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Aktivitas</th>
                                        <th>Pengguna</th>
                                        <th>Role</th>
                                        <th>Jabatan</th>
                                        <th>Departemen</th>
                                        <th>Versi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($approvalLogs as $log)
                                        <tr>
                                            <td>{{ $formatTanggalApprovalLog($log->created_at) }}</td>
                                            <td>{{ $log->action_label ?? '-' }}</td>
                                            <td>{{ $log->actor_name ?? '-' }}</td>
                                            <td>{{ strtoupper($log->actor_role ?? '-') }}</td>
                                            <td>{{ $log->actor_jabatan ?? '-' }}</td>
                                            <td>{{ $log->actor_departemen ?? '-' }}</td>
                                            <td class="text-center">
                                                {{ $log->version ? 'V'.$log->version->version_number : '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-3">
                                                Belum ada riwayat approval untuk jabatan ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="jd-modal-note">
                            Kolom IP tidak ditampilkan pada UI agar tampilan lebih aman dan ringkas untuk kebutuhan internal corporate.
                        </div>
                    </div>
                </div>
            </div>

            <div class="jd-log-export-area" id="approvalLogExportArea" aria-hidden="true">
                <div class="jd-log-export-page">
                    <div class="jd-log-export-header jd-avoid-break">
                        <div>
                            <div class="jd-log-export-company">PT. Bumi Siak Pusako</div>
                            <div class="jd-log-export-title">Riwayat Approval Job Description</div>
                            <div class="jd-log-export-subtitle">
                                Jabatan: {{ $j->nama_jabatan ?? '-' }} · Departemen: {{ $j->departemen ?? '-' }}
                            </div>
                        </div>

                        <div class="jd-log-export-status">
                            Status<br>
                            <strong>{{ $approvalStatusText }}</strong><br>
                            <span>Dicetak: {{ now()->locale('id')->translatedFormat('d F Y H:i') }}</span>
                        </div>
                    </div>

                    <div class="jd-log-export-summary jd-avoid-break">
                        <div><strong>Versi Aktif:</strong> {{ $activeVersionLabel }}</div>
                        <div><strong>Versi Pending:</strong> {{ $pendingVersionLabel }}</div>
                        <div><strong>Total Aktivitas:</strong> {{ $approvalLogs->count() }}</div>
                    </div>

                    <div class="jd-log-export-list">
                        @forelse($approvalLogs as $log)
                            <div class="jd-log-export-item jd-avoid-break">
                                <div class="jd-log-export-item-top">
                                    <strong>{{ $log->action_label ?? '-' }}</strong>
                                    <span>{{ $formatTanggalApprovalLog($log->created_at) }}</span>
                                </div>
                                <table>
                                    <tr>
                                        <th>Pengguna</th>
                                        <td>{{ $log->actor_name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Role</th>
                                        <td>{{ strtoupper($log->actor_role ?? '-') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Jabatan</th>
                                        <td>{{ $log->actor_jabatan ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Departemen</th>
                                        <td>{{ $log->actor_departemen ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Versi</th>
                                        <td>{{ $log->version ? 'Versi '.$log->version->version_number : '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                        @empty
                            <div class="jd-log-export-item jd-avoid-break">
                                Belum ada riwayat approval untuk jabatan ini.
                            </div>
                        @endforelse
                    </div>
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
            <div class="jd-paper-header" data-export-header="true">
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

                    <div class="jd-approval-mini {{ $approvalStatusClass }}">
                        @if($isApproved)
                            <span class="jd-approval-dot"></span>
                            Approved oleh {{ $approvedByName }} pada {{ $approvalDate }}
                        @else
                            <span class="jd-approval-dot"></span>
                            {{ $approvalStatusText }}
                        @endif
                    </div>
                </div>
            </div>

            <div class="jd-paper-body" id="jabatan-content-source">

                {{-- PROFIL JABATAN --}}
                <div class="jd-profile-card jd-export-item jd-avoid-break">
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

                    <div class="jd-profile-approval-row">
                        <span class="jd-approval-badge {{ $approvalStatusClass }}">
                            {{ $approvalStatusText }}
                        </span>

                        @if($isApproved)
                            <span class="jd-profile-approval-text">
                                Disetujui oleh <strong>{{ $approvedByName }}</strong> pada <strong>{{ $approvalDate }}</strong>
                            </span>
                        @else
                            <span class="jd-profile-approval-text">
                                Dokumen ini belum mendapatkan approval final.
                            </span>
                        @endif
                    </div>
                </div>

                {{-- STATUS APPROVAL --}}
                <div class="jd-section-block jd-export-item jd-avoid-break">
                    <div class="jd-section-heading">
                        <i class="bi bi-shield-check"></i>
                        Status Approval Job Description
                    </div>

                    <div class="jd-approval-summary">
                        <div class="jd-approval-summary-main">
                            <span class="jd-approval-badge {{ $approvalStatusClass }}">
                                {{ $approvalStatusText }}
                            </span>

                            @if($isApproved)
                                <div>
                                    <div class="jd-approval-summary-title">Dokumen sudah disetujui</div>
                                    <div class="jd-approval-summary-desc">
                                        Approval tercatat otomatis oleh sistem berdasarkan akun approver.
                                    </div>
                                </div>
                            @else
                                <div>
                                    <div class="jd-approval-summary-title">Dokumen belum disetujui</div>
                                    <div class="jd-approval-summary-desc">
                                        Job description masih menunggu proses approval.
                                    </div>
                                </div>
                            @endif
                        </div>

                        <table class="jd-approval-table">
                            <tbody>
                                <tr>
                                    <th>Disetujui Oleh</th>
                                    <td>{{ $approvedByName }}</td>
                                    <th>Tanggal & Jam</th>
                                    <td>{{ $approvalDate }}</td>
                                </tr>
                                <tr>
                                    <th>Role Approver</th>
                                    <td>{{ $approvedByRole }}</td>
                                    <th>Jabatan Approver</th>
                                    <td>{{ $approvedByJabatan }}</td>
                                </tr>
                                <tr>
                                    <th>Departemen Approver</th>
                                    <td>{{ $approvedByDepartemen }}</td>
                                    <th>Catatan</th>
                                    <td>{{ $approvalCatatan }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- INFORMASI UMUM --}}
                <div class="jd-section-block jd-export-item jd-avoid-break">
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
                <div class="jd-section-block jd-export-item jd-avoid-break">
                    <div class="jd-section-heading">
                        <i class="bi bi-bullseye"></i>
                        Tujuan Jabatan
                    </div>

                    <div class="jd-text-block">
                        {!! nl2br(e($j->tujuan_jabatan ?? '-')) !!}
                    </div>
                </div>

                {{-- TANGGUNG JAWAB --}}
                <div class="jd-section-block jd-export-item jd-avoid-break">
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
                <div class="jd-section-block jd-export-item jd-avoid-break">
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
                <div class="jd-section-block jd-export-item jd-avoid-break">
                    <div class="jd-section-heading">
                        <i class="bi bi-diagram-3"></i>
                        Dimensi dan Wewenang
                    </div>

                    <div class="jd-grid-2">
                        <div class="jd-card jd-avoid-break">
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

                        <div class="jd-card jd-avoid-break">
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
                <div class="jd-section-block jd-export-item jd-avoid-break">
                    <div class="jd-section-heading">
                        <i class="bi bi-people"></i>
                        Hubungan Kerja
                    </div>

                    <div class="jd-grid-2">
                        <div class="jd-card jd-avoid-break">
                            <div class="jd-card-title">Internal Perusahaan</div>

                            <div class="jd-text-inside">
                                <ol class="jd-list jd-list-plain">
                                    @foreach($renderLines($j->internal_perusahaan) as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ol>
                            </div>
                        </div>

                        <div class="jd-card jd-avoid-break">
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
                <div class="jd-section-block jd-export-item jd-avoid-break">
                    <div class="jd-section-heading">
                        <i class="bi bi-award"></i>
                        Persyaratan Jabatan
                    </div>

                    <div class="jd-grid-2">
                        <div class="jd-card jd-avoid-break">
                            <div class="jd-card-title">Pengetahuan & Keterampilan</div>

                            <div class="jd-text-inside">
                                <ol class="jd-list jd-list-plain">
                                    @foreach($renderLines($j->pengetahuan_keterampilan) as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ol>
                            </div>
                        </div>

                        <div class="jd-card jd-avoid-break">
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
                        <div class="jd-card jd-avoid-break">
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
                    <div class="jd-section-block jd-export-item jd-avoid-break">
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

                <div class="jd-footer-note jd-export-item jd-avoid-break">
                    <div class="jd-approval-signoff {{ $approvalStatusClass }}">
                        @if($isApproved)
                            <div class="jd-signoff-label">PENGESAHAN DOKUMEN</div>
                            <div class="jd-signoff-status">APPROVED</div>
                            <div class="jd-signoff-meta">
                                Disetujui oleh <strong>{{ $approvedByName }}</strong>
                                sebagai <strong>{{ $approvedByJabatan }}</strong>
                                pada <strong>{{ $approvalDate }}</strong>.
                            </div>
                            <div class="jd-signoff-meta">
                                Departemen: {{ $approvedByDepartemen }} • Role: {{ $approvedByRole }}
                            </div>
                        @else
                            <div class="jd-signoff-label">STATUS DOKUMEN</div>
                            <div class="jd-signoff-status">PENDING APPROVAL</div>
                            <div class="jd-signoff-meta">
                                Dokumen ini belum memperoleh approval final.
                            </div>
                        @endif
                    </div>

                    <div class="jd-footer-system-note">
                        Dokumen ini dihasilkan oleh Sistem Informasi SDM PT. Bumi Siak Pusako.
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Area ini khusus hasil susunan halaman untuk Print dan Download PDF. Jangan dihapus. --}}
    <div id="jabatan-export-root" class="jd-export-root" aria-hidden="true"></div>
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
    margin-top:4px;
    margin-bottom:18px;
}

.jd-toolbar-card{
    background:#ffffff;
    border:1px solid #e5e7eb;
    border-radius:18px;
    padding:14px 16px;
    box-shadow:0 10px 28px rgba(15,23,42,.06);
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    flex-wrap:wrap;
}

.jd-toolbar-left,
.jd-toolbar-right{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
}

.jd-toolbar-title{
    border-left:1px solid #e5e7eb;
    padding-left:12px;
    min-width:220px;
}

.jd-toolbar-label{
    font-size:11px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.08em;
    color:#6b7280;
    line-height:1.2;
}

.jd-toolbar-name{
    font-size:14px;
    font-weight:800;
    color:#273957;
    line-height:1.3;
    max-width:440px;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.jd-btn{
    border-radius:12px;
    font-weight:700;
    min-height:40px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    white-space:nowrap;
}

.jd-approval-action-card{
    margin-top:10px;
    background:#f8fafc;
    border:1px solid #e5e7eb;
    border-radius:18px;
    padding:14px 16px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    flex-wrap:wrap;
}

.jd-approval-action-info{
    min-width:260px;
    flex:1;
}

.jd-approval-action-label{
    font-size:11px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.08em;
    color:#6b7280;
    margin-bottom:6px;
}

.jd-approval-action-main{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
}

.jd-status-pill{
    display:inline-flex;
    align-items:center;
    padding:7px 11px;
    border-radius:999px;
    font-size:12px;
    font-weight:900;
    line-height:1;
}

.jd-status-pill.approved{
    background:#dcfce7;
    color:#166534;
    border:1px solid #bbf7d0;
}

.jd-status-pill.pending{
    background:#fef3c7;
    color:#92400e;
    border:1px solid #fde68a;
}

.jd-status-pill.rejected{
    background:#fee2e2;
    color:#991b1b;
    border:1px solid #fecaca;
}

.jd-action-note{
    font-size:13px;
    color:#64748b;
    font-weight:600;
}

.jd-approval-action-buttons{
    display:flex;
    align-items:center;
    justify-content:flex-end;
    gap:8px;
    flex-wrap:wrap;
}

.jd-approval-action-buttons form{
    margin:0;
}

.jd-approval-action-buttons .btn:disabled{
    opacity:.55;
    cursor:not-allowed;
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
    box-sizing:border-box;
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

.jd-approval-mini{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:6px;
    margin-top:8px;
    padding:6px 12px;
    border-radius:999px;
    font-size:9px;
    font-weight:800;
    line-height:1.25;
    letter-spacing:.04em;
    text-transform:uppercase;
    border:1px solid transparent;
    max-width:100%;
}

.jd-approval-mini.approved{
    background:#ecfdf3;
    color:#067647;
    border-color:#abefc6;
}

.jd-approval-mini.pending{
    background:#fffaeb;
    color:#b54708;
    border-color:#fedf89;
}

.jd-approval-mini.rejected{
    background:#fef3f2;
    color:#b42318;
    border-color:#fecdca;
}

.jd-approval-dot{
    width:7px;
    height:7px;
    border-radius:50%;
    background:currentColor;
    display:inline-flex;
    flex:0 0 auto;
}

.jd-paper-body{
    padding:6mm 12mm 9mm 12mm;
    background:#ffffff;
    box-sizing:border-box;
}

.jd-profile-card{
    border:1px solid var(--jd-border);
    border-radius:18px;
    padding:18px 20px;
    margin-bottom:14px;
    background:linear-gradient(180deg,#ffffff,#fbfcf8);
    box-sizing:border-box;
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
    word-break:break-word;
}

.jd-profile-meta{
    display:flex;
    flex-wrap:wrap;
    gap:12px 18px;
    margin-top:10px;
    font-size:13px;
    line-height:1.45;
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
    line-height:1.2;
    word-break:break-word;
}

.jd-profile-approval-row{
    display:flex;
    align-items:center;
    flex-wrap:wrap;
    gap:10px;
    margin-top:14px;
    padding-top:14px;
    border-top:1px dashed var(--jd-border);
}

.jd-profile-approval-text{
    font-size:11px;
    color:#475467;
    line-height:1.45;
    font-weight:600;
}

.jd-approval-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border-radius:999px;
    padding:7px 14px;
    font-size:10px;
    font-weight:900;
    line-height:1.2;
    letter-spacing:.05em;
    text-transform:uppercase;
    border:1px solid transparent;
    white-space:nowrap;
}

.jd-approval-badge.approved{
    background:#dcfce7;
    color:#166534;
    border-color:#86efac;
}

.jd-approval-badge.pending{
    background:#fef3c7;
    color:#92400e;
    border-color:#fde68a;
}

.jd-approval-badge.rejected{
    background:#fee2e2;
    color:#991b1b;
    border-color:#fecaca;
}

.jd-approval-summary{
    padding:14px;
}

.jd-approval-summary-main{
    display:flex;
    align-items:center;
    gap:12px;
    padding:14px;
    border:1px solid var(--jd-border);
    border-radius:12px;
    background:#fbfcf8;
    margin-bottom:12px;
}

.jd-approval-summary-title{
    font-size:13px;
    font-weight:900;
    color:#111827;
    line-height:1.3;
}

.jd-approval-summary-desc{
    margin-top:2px;
    font-size:11px;
    font-weight:600;
    color:#667085;
    line-height:1.45;
}

.jd-approval-table{
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
}

.jd-approval-table th,
.jd-approval-table td{
    border:1px solid var(--jd-border);
    padding:9px 10px;
    vertical-align:top;
    font-size:11px;
    line-height:1.45;
}

.jd-approval-table th{
    width:22%;
    background:var(--jd-primary-soft-2);
    color:var(--jd-label);
    font-weight:900;
    text-align:left;
}

.jd-approval-table td{
    font-weight:700;
    color:#111827;
    word-break:break-word;
    overflow-wrap:anywhere;
}

.jd-section-block{
    margin-top:12px;
    border:1px solid var(--jd-border);
    border-radius:14px;
    background:#ffffff;
    overflow:hidden;
    box-sizing:border-box;
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
    line-height:1.25;
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
    overflow-wrap:anywhere;
}

.jd-text-block,
.jd-text-inside{
    padding:14px;
    font-size:12px;
    line-height:1.75;
    color:#111827;
    text-align:justify;
    word-break:break-word;
    overflow-wrap:anywhere;
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
    break-inside:avoid;
    page-break-inside:avoid;
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
    box-sizing:border-box;
}

.jd-card-title{
    padding:10px 12px;
    background:var(--jd-primary-soft-2);
    border-bottom:1px solid var(--jd-border);
    font-size:12px;
    font-weight:800;
    color:#27351e;
    line-height:1.25;
}

.jd-org-box{
    padding:18px;
    text-align:center;
}

.jd-org-image{
    max-width:100%;
    max-height:520px;
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

.jd-approval-signoff{
    border:1px solid var(--jd-border);
    border-radius:14px;
    padding:14px 16px;
    margin-bottom:12px;
    background:#fbfcf8;
    text-align:left;
}

.jd-approval-signoff.approved{
    border-color:#86efac;
    background:#f0fdf4;
}

.jd-approval-signoff.pending{
    border-color:#fde68a;
    background:#fffbeb;
}

.jd-approval-signoff.rejected{
    border-color:#fecaca;
    background:#fef2f2;
}

.jd-signoff-label{
    font-size:9px;
    font-weight:900;
    letter-spacing:.12em;
    color:#667085;
    text-transform:uppercase;
    margin-bottom:4px;
}

.jd-signoff-status{
    font-size:15px;
    font-weight:900;
    letter-spacing:.08em;
    color:#111827;
    text-transform:uppercase;
    margin-bottom:6px;
}

.jd-signoff-meta{
    font-size:11px;
    line-height:1.55;
    color:#344054;
    font-weight:600;
}

.jd-footer-system-note{
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
    -webkit-print-color-adjust:exact;
    print-color-adjust:exact;
}

.jd-avoid-break{
    break-inside:avoid;
    page-break-inside:avoid;
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

    .jd-approval-summary-main{
        align-items:flex-start;
        flex-direction:column;
    }

    .jd-approval-table,
    .jd-approval-table tbody,
    .jd-approval-table tr,
    .jd-approval-table th,
    .jd-approval-table td{
        display:block;
        width:100%;
    }

    .jd-approval-table th{
        border-bottom:0;
    }
}

/* ===== EXPORT/PDF/PRINT ROOT: sumber tunggal agar Print dan PDF sama persis ===== */
.jd-export-root{
    position:absolute;
    left:-99999px;
    top:0;
    width:210mm;
    background:#ffffff;
    z-index:-1;
}

.jd-export-page{
    width:210mm;
    height:297mm;
    min-height:297mm;
    max-height:297mm;
    background:#ffffff;
    box-sizing:border-box;
    overflow:hidden;
    page-break-after:always;
    break-after:page;
    border:0;
}

.jd-export-page:last-child{
    page-break-after:auto;
    break-after:auto;
}

.jd-export-page .jd-paper-header{
    width:100%;
    flex:0 0 auto;
}

.jd-export-page .jd-export-body{
    padding:7mm 12mm 9mm 12mm;
    background:#ffffff;
    box-sizing:border-box;
}

.jd-export-page .jd-export-body > .jd-export-item:first-child{
    margin-top:0 !important;
}

.jd-export-page .jd-profile-card,
.jd-export-page .jd-section-block,
.jd-export-page .jd-footer-note{
    break-inside:avoid !important;
    page-break-inside:avoid !important;
}

@page{
    size:A4 portrait;
    margin:0;
}


@media (max-width: 768px){
    .jd-toolbar-card,
    .jd-approval-action-card{
        align-items:stretch;
    }

    .jd-toolbar-left,
    .jd-toolbar-right,
    .jd-approval-action-buttons{
        width:100%;
    }

    .jd-toolbar-right .jd-btn,
    .jd-approval-action-buttons .jd-btn,
    .jd-approval-action-buttons form{
        width:100%;
    }

    .jd-toolbar-title{
        border-left:none;
        padding-left:0;
        width:100%;
    }

    .jd-toolbar-name{
        max-width:100%;
        white-space:normal;
    }
}

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

    body.jd-printing *{
        visibility:hidden !important;
    }

    body.jd-printing #jabatan-export-root,
    body.jd-printing #jabatan-export-root *{
        visibility:visible !important;
    }

    body.jd-printing #jabatan-export-root{
        display:block !important;
        position:absolute !important;
        left:0 !important;
        top:0 !important;
        width:210mm !important;
        z-index:999999 !important;
        background:#ffffff !important;
    }

    body.jd-printing .jd-export-page{
        width:210mm !important;
        height:297mm !important;
        min-height:297mm !important;
        max-height:297mm !important;
        margin:0 !important;
        box-shadow:none !important;
        border:0 !important;
        overflow:hidden !important;
    }

    body:not(.jd-printing) .d-print-none,
    body:not(.jd-printing) .jd-action-bar,
    body:not(.jd-printing) .jd-toolbar-card,
    body:not(.jd-printing) .jd-approval-action-card{
        display:none !important;
    }

    body:not(.jd-printing) .jd-page{
        background:#ffffff !important;
        padding:0 !important;
        margin:0 !important;
    }

    body:not(.jd-printing) .jd-paper-a4{
        width:210mm !important;
        min-height:297mm !important;
        margin:0 !important;
        background:#ffffff !important;
        border:0 !important;
        box-shadow:none !important;
        overflow:visible !important;
    }
}


/* Corporate action panel: hanya area aksi, tidak mengubah desain A4 job description */
.jd-corporate-panel{
    margin-top:4px;
    margin-bottom:18px;
}

.jd-command-card,
.jd-workflow-card,
.jd-audit-card{
    background:#ffffff;
    border:1px solid #e5e7eb;
    border-radius:18px;
    box-shadow:0 10px 28px rgba(15,23,42,.06);
}

.jd-command-card{
    padding:14px 16px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    flex-wrap:wrap;
}

.jd-command-left,
.jd-command-right,
.jd-workflow-actions{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
}

.jd-command-title{
    border-left:1px solid #e5e7eb;
    padding-left:12px;
    min-width:240px;
}

.jd-command-eyebrow,
.jd-workflow-label{
    font-size:11px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.08em;
    color:#667085;
    line-height:1.2;
}

.jd-command-name{
    font-size:15px;
    font-weight:900;
    color:#273957;
    line-height:1.3;
    max-width:520px;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.jd-command-meta{
    margin-top:2px;
    font-size:12px;
    color:#667085;
    font-weight:700;
}

.jd-btn-muted{
    border:1px solid #e5e7eb;
}

.jd-workflow-card{
    margin-top:10px;
    padding:14px 16px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    flex-wrap:wrap;
    background:#f8fafc;
}

.jd-workflow-status{
    flex:1;
    min-width:280px;
}

.jd-workflow-main{
    margin-top:6px;
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
}

.jd-inline-form{
    margin:0;
}

.jd-audit-card{
    margin-top:10px;
    overflow:hidden;
}

.jd-audit-head{
    padding:14px 16px;
    background:#ffffff;
    border-bottom:1px solid #edf0ea;
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:12px;
    flex-wrap:wrap;
}

.jd-audit-title{
    font-size:14px;
    font-weight:900;
    color:#273957;
    text-transform:uppercase;
    letter-spacing:.03em;
}

.jd-audit-subtitle{
    font-size:12.5px;
    color:#667085;
    font-weight:600;
    margin-top:2px;
}

.jd-audit-count{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-height:30px;
    padding:6px 10px;
    border-radius:999px;
    background:#eef2eb;
    color:#536044;
    font-size:12px;
    font-weight:900;
}

.jd-audit-body{
    padding:0;
}

.jd-audit-table th{
    background:#f6f8f4;
    color:#536044;
    font-size:11.5px;
    text-transform:uppercase;
    letter-spacing:.04em;
    border-bottom:1px solid #e5e7eb;
    white-space:nowrap;
}

.jd-audit-table td{
    font-size:12.5px;
    color:#344054;
    font-weight:600;
    border-color:#edf0ea;
}

@media (max-width: 768px){
    .jd-command-left,
    .jd-command-right,
    .jd-workflow-actions,
    .jd-inline-form,
    .jd-command-right .jd-btn,
    .jd-workflow-actions .jd-btn{
        width:100%;
    }

    .jd-command-title{
        border-left:none;
        padding-left:0;
        width:100%;
    }

    .jd-command-name{
        max-width:100%;
        white-space:normal;
    }
}

/* =========================
   Corporate Approval Log Modal
   ========================= */
.jd-btn-count{
    min-width:22px;
    height:22px;
    padding:0 7px;
    border-radius:999px;
    background:#eef2eb;
    color:#536044;
    font-size:11px;
    font-weight:900;
    display:inline-flex;
    align-items:center;
    justify-content:center;
}

.jd-btn-sm{
    min-height:36px;
    font-size:12px;
    padding:7px 12px;
}

.jd-modal-backdrop{
    position:fixed;
    inset:0;
    background:rgba(15,23,42,.62);
    z-index:9999;
    display:none;
    align-items:center;
    justify-content:center;
    padding:22px;
}

.jd-modal-backdrop.is-open{
    display:flex;
}

.jd-modal-panel{
    width:min(1120px, 100%);
    max-height:88vh;
    background:#ffffff;
    border-radius:22px;
    box-shadow:0 24px 70px rgba(15,23,42,.32);
    border:1px solid rgba(255,255,255,.5);
    overflow:hidden;
    display:flex;
    flex-direction:column;
}

.jd-modal-head{
    background:linear-gradient(135deg, #273957 0%, #3f4a32 100%);
    color:#ffffff;
    padding:18px 22px;
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:16px;
}

.jd-modal-eyebrow{
    color:#f4c542;
    font-size:11px;
    font-weight:900;
    letter-spacing:.12em;
    text-transform:uppercase;
    margin-bottom:5px;
}

.jd-modal-title{
    margin:0;
    font-size:18px;
    font-weight:900;
}

.jd-modal-subtitle{
    margin-top:5px;
    font-size:12.5px;
    color:rgba(255,255,255,.78);
    line-height:1.45;
}

.jd-modal-close{
    border:0;
    width:36px;
    height:36px;
    border-radius:999px;
    background:rgba(255,255,255,.14);
    color:#ffffff;
    font-size:26px;
    line-height:1;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
}

.jd-modal-close:hover{
    background:rgba(255,255,255,.24);
}

.jd-modal-toolbar{
    padding:12px 18px;
    background:#fbfcfa;
    border-bottom:1px solid #e5e7eb;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    flex-wrap:wrap;
}

.jd-modal-toolbar-actions{
    display:flex;
    align-items:center;
    justify-content:flex-end;
    gap:8px;
    flex-wrap:wrap;
}

.jd-modal-body{
    padding:16px 18px 18px;
    overflow:auto;
}

.jd-modal-note{
    margin-top:10px;
    padding:10px 12px;
    border-radius:14px;
    background:#f8fafc;
    color:#667085;
    font-size:12.5px;
    font-weight:600;
}

.jd-log-export-area{
    position:fixed;
    left:-99999px;
    top:0;
    width:210mm;
    background:#ffffff;
    color:#101828;
    z-index:-1;
    visibility:hidden;
}

.jd-log-export-page{
    width:210mm;
    min-height:297mm;
    padding:14mm 13mm;
    background:#ffffff;
    font-family:"Inter", "Segoe UI", Arial, sans-serif;
}

.jd-log-export-header{
    border-bottom:3px solid #273957;
    padding-bottom:10px;
    margin-bottom:12px;
    display:flex;
    justify-content:space-between;
    gap:14px;
}

.jd-log-export-company{
    font-size:12px;
    font-weight:900;
    letter-spacing:.08em;
    text-transform:uppercase;
    color:#6b775c;
}

.jd-log-export-title{
    font-size:20px;
    font-weight:900;
    color:#273957;
    margin-top:3px;
    text-transform:uppercase;
}

.jd-log-export-subtitle{
    margin-top:4px;
    font-size:11px;
    font-weight:700;
    color:#667085;
}

.jd-log-export-status{
    min-width:42mm;
    text-align:right;
    font-size:10.5px;
    color:#344054;
    font-weight:700;
}

.jd-log-export-status strong{
    color:#273957;
    font-size:12px;
}

.jd-log-export-status span{
    color:#667085;
    font-size:9.5px;
}

.jd-log-export-summary{
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    gap:8px;
    margin-bottom:12px;
}

.jd-log-export-summary div{
    border:1px solid #d7dfcc;
    background:#f7f9f2;
    border-radius:10px;
    padding:8px 9px;
    font-size:10.5px;
    color:#344054;
}

.jd-log-export-list{
    display:block;
}

.jd-log-export-item{
    border:1px solid #d7dfcc;
    border-radius:10px;
    padding:9px 10px;
    margin-bottom:8px;
    background:#ffffff;
    break-inside:avoid;
    page-break-inside:avoid;
}

.jd-log-export-item-top{
    display:flex;
    justify-content:space-between;
    gap:10px;
    color:#273957;
    font-size:11px;
    margin-bottom:7px;
    border-bottom:1px solid #edf0ea;
    padding-bottom:5px;
}

.jd-log-export-item-top span{
    color:#667085;
    font-weight:700;
    white-space:nowrap;
}

.jd-log-export-item table{
    width:100%;
    border-collapse:collapse;
    font-size:10.2px;
}

.jd-log-export-item th,
.jd-log-export-item td{
    border:1px solid #edf0ea;
    padding:5px 6px;
    vertical-align:top;
}

.jd-log-export-item th{
    width:27%;
    background:#f7f9f2;
    color:#536044;
    text-align:left;
}

/* PDF/Print safety: each card should move to next page instead of being cut */
.jd-export-item,
.jd-card,
.jd-info-card,
.jd-approval-block,
.jd-avoid-break{
    break-inside:avoid;
    page-break-inside:avoid;
}

@media (max-width: 768px){
    .jd-modal-backdrop{
        padding:10px;
    }

    .jd-modal-panel{
        max-height:92vh;
        border-radius:18px;
    }

    .jd-modal-head{
        padding:16px;
    }

    .jd-modal-toolbar-actions,
    .jd-modal-toolbar-actions .jd-btn{
        width:100%;
    }
}

@media print{
    .jd-modal-backdrop,
    .jd-log-export-area{
        display:none !important;
    }
}




/* =========================================================
   CORPORATE A4 EXPORT ENGINE
   Tampilan kop surat memakai clone dari .jd-paper-header show.
   Isi dibuat per halaman A4 agar card tidak kepotong.
   ========================================================= */
.jd-paper-a4{
    overflow:visible !important;
}

.jd-export-root{
    position:fixed;
    left:-99999px;
    top:0;
    width:210mm;
    max-width:210mm;
    min-width:210mm;
    background:#ffffff;
    z-index:-1;
    visibility:hidden;
    opacity:1;
    pointer-events:none;
    overflow:visible;
}

.jd-export-page{
    width:210mm;
    height:297mm;
    min-height:297mm;
    max-height:297mm;
    margin:0;
    padding:0;
    background:#ffffff;
    box-sizing:border-box;
    overflow:hidden;
    page-break-after:always;
    break-after:page;
    border:0;
    box-shadow:none;
    display:block;
}

.jd-export-page:last-child{
    page-break-after:auto;
    break-after:auto;
}

.jd-export-page .jd-paper-header{
    width:210mm;
    box-sizing:border-box;
    flex:0 0 auto;
    margin:0 !important;
    border-radius:0 !important;
}

.jd-export-body{
    padding:6mm 12mm 9mm 12mm;
    background:#ffffff;
    box-sizing:border-box;
    overflow:hidden;
}

.jd-export-body > .jd-export-item:first-child,
.jd-export-body > .jd-profile-card:first-child,
.jd-export-body > .jd-section-block:first-child,
.jd-export-body > .jd-footer-note:first-child{
    margin-top:0 !important;
}

.jd-export-page .jd-profile-card,
.jd-export-page .jd-section-block,
.jd-export-page .jd-card,
.jd-export-page .jd-footer-note,
.jd-export-page .jd-approval-signoff,
.jd-export-page .jd-avoid-break{
    break-inside:avoid !important;
    page-break-inside:avoid !important;
}

.jd-export-page .jd-continuation-label{
    display:inline-flex;
    margin-left:8px;
    padding:3px 8px;
    border-radius:999px;
    background:#ffffff;
    color:#667085;
    border:1px solid #d7dfcc;
    font-size:9px;
    font-weight:900;
    letter-spacing:.06em;
    text-transform:uppercase;
}

.jd-export-page .jd-section-block.jd-export-continuation{
    margin-top:0 !important;
}

.jd-export-page .jd-section-block,
.jd-export-page .jd-profile-card,
.jd-export-page .jd-card,
.jd-export-page .jd-footer-note,
.jd-export-page .jd-title-wrap,
.jd-export-page .jd-section-heading,
.jd-export-page .jd-card-title,
.jd-export-page .jd-meta-table th,
.jd-export-page .jd-info-table th,
.jd-export-page .jd-approval-table th,
.jd-export-page .jd-chip,
.jd-export-page .jd-approval-badge,
.jd-export-page .jd-approval-mini,
.jd-export-page .jd-approval-signoff{
    -webkit-print-color-adjust:exact !important;
    print-color-adjust:exact !important;
}

.jd-export-page table{
    page-break-inside:auto;
    break-inside:auto;
}

.jd-export-page tr,
.jd-export-page li{
    page-break-inside:avoid;
    break-inside:avoid;
}

body.jd-exporting-a4{
    background:#ffffff !important;
}

body.jd-exporting-a4 #jabatan-export-root{
    left:0 !important;
    top:0 !important;
    z-index:999999 !important;
    visibility:visible !important;
    opacity:1 !important;
    background:#ffffff !important;
}

@page{
    size:A4 portrait;
    margin:0;
}

@media print{
    html,
    body{
        width:210mm !important;
        min-width:210mm !important;
        margin:0 !important;
        padding:0 !important;
        background:#ffffff !important;
        overflow:visible !important;
        -webkit-print-color-adjust:exact !important;
        print-color-adjust:exact !important;
    }

    body.jd-printing *{
        visibility:hidden !important;
    }

    body.jd-printing #jabatan-export-root,
    body.jd-printing #jabatan-export-root *{
        visibility:visible !important;
    }

    body.jd-printing #jabatan-export-root{
        display:block !important;
        position:absolute !important;
        left:0 !important;
        top:0 !important;
        width:210mm !important;
        min-width:210mm !important;
        max-width:210mm !important;
        background:#ffffff !important;
        z-index:999999 !important;
    }

    body.jd-printing .jd-export-page{
        width:210mm !important;
        height:297mm !important;
        min-height:297mm !important;
        max-height:297mm !important;
        margin:0 !important;
        padding:0 !important;
        overflow:hidden !important;
        border:0 !important;
        box-shadow:none !important;
    }

    body.jd-printing .d-print-none,
    body.jd-printing .jd-corporate-panel,
    body.jd-printing .jd-modal-backdrop,
    body.jd-printing .jd-log-export-area{
        display:none !important;
    }
}
</style>

@if(!$jabatanNotFound)
    <script src="{{ asset('vendor/html2pdf/html2pdf.bundle.min.js') }}"></script>
    <script>
    (function () {
        const A4_HEIGHT_MM = 297;
        const MAX_PDF_SCALE = 2.25;

        function ensureHtml2PdfLibrary() {
            return new Promise(function (resolve, reject) {
                if (window.html2pdf) {
                    resolve();
                    return;
                }

                const cdn = document.createElement('script');
                cdn.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
                cdn.onload = function () {
                    if (window.html2pdf) {
                        resolve();
                    } else {
                        reject(new Error('html2pdf tidak tersedia'));
                    }
                };
                cdn.onerror = reject;
                document.head.appendChild(cdn);
            });
        }

        function mmToPx(mm) {
            const probe = document.createElement('div');
            probe.style.position = 'absolute';
            probe.style.left = '-99999px';
            probe.style.top = '0';
            probe.style.width = mm + 'mm';
            probe.style.height = '1px';
            document.body.appendChild(probe);
            const px = probe.getBoundingClientRect().width;
            probe.remove();
            return px;
        }

        function waitForImages(container) {
            const images = Array.from(container.querySelectorAll('img'));
            if (!images.length) return Promise.resolve();

            return Promise.all(images.map(function (img) {
                if (img.complete && img.naturalWidth !== 0) {
                    return Promise.resolve();
                }

                return new Promise(function (resolve) {
                    img.onload = resolve;
                    img.onerror = resolve;
                });
            }));
        }

        function waitForFonts() {
            if (document.fonts && document.fonts.ready) {
                return document.fonts.ready.catch(function () {});
            }
            return Promise.resolve();
        }

        function getJobDescriptionElement() {
            return document.getElementById('jabatan-print-area');
        }

        function getExportRoot() {
            return document.getElementById('jabatan-export-root');
        }

        function prepareRootForMeasure(root) {
            root.innerHTML = '';
            root.removeAttribute('aria-hidden');
            root.style.position = 'fixed';
            root.style.left = '0';
            root.style.top = '0';
            root.style.width = '210mm';
            root.style.minWidth = '210mm';
            root.style.maxWidth = '210mm';
            root.style.visibility = 'hidden';
            root.style.opacity = '1';
            root.style.zIndex = '-1';
            root.style.pointerEvents = 'none';
            root.style.background = '#ffffff';
            root.style.overflow = 'visible';
        }

        function showRootForExport(root) {
            root.removeAttribute('aria-hidden');
            root.style.position = 'fixed';
            root.style.left = '0';
            root.style.top = '0';
            root.style.width = '210mm';
            root.style.minWidth = '210mm';
            root.style.maxWidth = '210mm';
            root.style.visibility = 'visible';
            root.style.opacity = '1';
            root.style.zIndex = '999999';
            root.style.pointerEvents = 'none';
            root.style.background = '#ffffff';
            root.style.overflow = 'visible';
        }

        function hideRoot(root) {
            if (!root) return;
            root.setAttribute('aria-hidden', 'true');
            root.style.position = 'fixed';
            root.style.left = '-99999px';
            root.style.top = '0';
            root.style.visibility = 'hidden';
            root.style.zIndex = '-1';
            root.style.pointerEvents = 'none';
            root.style.background = '#ffffff';
        }

        function visibleHeight(element) {
            return Math.ceil(element.getBoundingClientRect().height);
        }

        function createExportPage(root, headerSource) {
            const page = document.createElement('div');
            page.className = 'jd-export-page';

            const header = headerSource.cloneNode(true);
            header.removeAttribute('data-export-header');

            const body = document.createElement('div');
            body.className = 'jd-export-body';

            page.appendChild(header);
            page.appendChild(body);
            root.appendChild(page);

            const pageHeight = mmToPx(A4_HEIGHT_MM);
            const headerHeight = visibleHeight(header);
            const bodyStyle = window.getComputedStyle(body);
            const bodyPaddingTop = parseFloat(bodyStyle.paddingTop) || 0;
            const bodyPaddingBottom = parseFloat(bodyStyle.paddingBottom) || 0;
            const availableHeight = pageHeight - headerHeight - bodyPaddingTop - bodyPaddingBottom;

            return {
                page: page,
                body: body,
                availableHeight: Math.max(100, availableHeight)
            };
        }

        function hasBodyContent(pageState) {
            return pageState.body.children.length > 0;
        }

        function markContinuation(section, number) {
            section.classList.add('jd-export-continuation');
            const heading = section.querySelector('.jd-section-heading');
            if (heading && !heading.querySelector('.jd-continuation-label')) {
                const badge = document.createElement('span');
                badge.className = 'jd-continuation-label';
                badge.textContent = 'Lanjutan ' + number;
                heading.appendChild(badge);
            }
        }

        function cloneWithoutListItems(item) {
            const clone = item.cloneNode(true);
            const lists = clone.querySelectorAll('ol.jd-list, ul.jd-list');
            lists.forEach(function (list) {
                list.innerHTML = '';
            });
            return clone;
        }

        function findSplittableList(item) {
            return item.querySelector('ol.jd-list, ul.jd-list');
        }

        function appendNormalItem(item, state, root, headerSource) {
            let clone = item.cloneNode(true);
            clone.classList.add('jd-export-item', 'jd-avoid-break');
            state.current.body.appendChild(clone);

            if (state.current.body.scrollHeight > state.current.availableHeight && hasBodyContent(state.current)) {
                clone.remove();
                state.current = createExportPage(root, headerSource);
                clone = item.cloneNode(true);
                clone.classList.add('jd-export-item', 'jd-avoid-break');
                state.current.body.appendChild(clone);
            }
        }

        function appendSplitListSection(item, state, root, headerSource) {
            const sourceList = findSplittableList(item);
            if (!sourceList) {
                appendNormalItem(item, state, root, headerSource);
                return;
            }

            const sourceItems = Array.from(sourceList.children).filter(function (li) {
                return li.tagName && li.tagName.toLowerCase() === 'li';
            });

            if (!sourceItems.length) {
                appendNormalItem(item, state, root, headerSource);
                return;
            }

            let continuationNo = 0;
            let section = null;
            let targetList = null;

            function startSection(forceNewPage) {
                if (forceNewPage || !state.current || (state.current.body.scrollHeight > 0 && state.current.body.scrollHeight > state.current.availableHeight)) {
                    state.current = createExportPage(root, headerSource);
                }

                continuationNo += 1;
                section = cloneWithoutListItems(item);
                section.classList.add('jd-export-item', 'jd-avoid-break');
                if (continuationNo > 1) {
                    markContinuation(section, continuationNo - 1);
                }
                targetList = findSplittableList(section);
                state.current.body.appendChild(section);

                if (state.current.body.scrollHeight > state.current.availableHeight && state.current.body.children.length > 1) {
                    section.remove();
                    state.current = createExportPage(root, headerSource);
                    section = cloneWithoutListItems(item);
                    section.classList.add('jd-export-item', 'jd-avoid-break');
                    if (continuationNo > 1) {
                        markContinuation(section, continuationNo - 1);
                    }
                    targetList = findSplittableList(section);
                    state.current.body.appendChild(section);
                }
            }

            startSection(false);

            sourceItems.forEach(function (sourceLi) {
                const li = sourceLi.cloneNode(true);
                targetList.appendChild(li);

                if (state.current.body.scrollHeight > state.current.availableHeight) {
                    li.remove();

                    if (!targetList.children.length) {
                        targetList.appendChild(li);
                        return;
                    }

                    startSection(true);
                    targetList.appendChild(li);
                }
            });
        }

        function appendSectionCardsIndividually(item, state, root, headerSource) {
            const directCards = Array.from(item.querySelectorAll(':scope > .jd-grid-2 > .jd-card, :scope > .jd-grid-1 > .jd-card'));
            if (!directCards.length) {
                appendNormalItem(item, state, root, headerSource);
                return;
            }

            let continuationNo = 0;

            directCards.forEach(function (card, index) {
                continuationNo += 1;
                const section = item.cloneNode(true);
                section.classList.add('jd-export-item', 'jd-avoid-break');

                const grids = Array.from(section.querySelectorAll(':scope > .jd-grid-2, :scope > .jd-grid-1'));
                grids.forEach(function (grid) {
                    grid.classList.remove('jd-grid-2');
                    grid.classList.add('jd-grid-1');
                    grid.innerHTML = '';
                });

                const firstGrid = section.querySelector(':scope > .jd-grid-1');
                if (firstGrid) {
                    firstGrid.appendChild(card.cloneNode(true));
                }

                if (index > 0) {
                    markContinuation(section, continuationNo - 1);
                }

                state.current.body.appendChild(section);
                if (state.current.body.scrollHeight > state.current.availableHeight && state.current.body.children.length > 1) {
                    section.remove();
                    state.current = createExportPage(root, headerSource);
                    state.current.body.appendChild(section);
                }
            });
        }

        function buildJabatanExportPages() {
            const root = getExportRoot();
            const source = getJobDescriptionElement();
            const headerSource = source ? source.querySelector('[data-export-header="true"]') : null;
            const contentSource = document.getElementById('jabatan-content-source');

            if (!root || !source || !headerSource || !contentSource) {
                return null;
            }

            prepareRootForMeasure(root);

            const state = {
                current: createExportPage(root, headerSource)
            };

            const items = Array.from(contentSource.children).filter(function (item) {
                return item.nodeType === 1;
            });

            items.forEach(function (item) {
                const isListSection = !!findSplittableList(item);
                const hasCardGrid = item.querySelector(':scope > .jd-grid-2 > .jd-card, :scope > .jd-grid-1 > .jd-card');

                if (isListSection) {
                    appendSplitListSection(item, state, root, headerSource);
                } else if (hasCardGrid) {
                    appendSectionCardsIndividually(item, state, root, headerSource);
                } else {
                    appendNormalItem(item, state, root, headerSource);
                }
            });

            return root;
        }

        function beforeA4Export(root) {
            document.body.classList.add('jd-exporting-a4');
            showRootForExport(root);
        }

        function afterA4Export() {
            const root = getExportRoot();
            document.body.classList.remove('jd-exporting-a4');
            document.body.classList.remove('jd-printing');
            hideRoot(root);
        }

        function openApprovalLogModal() {
            const modal = document.getElementById('approvalLogModal');
            if (!modal) return;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeApprovalLogModal() {
            const modal = document.getElementById('approvalLogModal');
            if (!modal) return;
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        function downloadApprovalLogCsv() {
            const table = document.getElementById('approvalLogTable');
            if (!table) return;

            const rows = Array.from(table.querySelectorAll('tr'));
            const csv = rows.map(function (row) {
                return Array.from(row.querySelectorAll('th, td')).map(function (cell) {
                    const text = (cell.innerText || '').replace(/\s+/g, ' ').trim();
                    return '"' + text.replace(/"/g, '""') + '"';
                }).join(',');
            }).join('\n');

            const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');

            link.href = url;
            link.download = 'riwayat-approval-jabatan-{{ $j->id_jabatan }}-{{ \Illuminate\Support\Str::slug($j->nama_jabatan ?? "jabatan") }}.csv';
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
        }

        function makeLogPdfVisible(area) {
            if (!area) return;
            area.removeAttribute('aria-hidden');
            area.style.display = 'block';
            area.style.position = 'fixed';
            area.style.left = '0';
            area.style.top = '0';
            area.style.width = '210mm';
            area.style.maxWidth = '210mm';
            area.style.minWidth = '210mm';
            area.style.height = 'auto';
            area.style.zIndex = '999999';
            area.style.visibility = 'visible';
            area.style.opacity = '1';
            area.style.pointerEvents = 'none';
            area.style.background = '#ffffff';
            area.style.transform = 'none';
            area.style.overflow = 'visible';
        }

        function hideLogPdf(area) {
            if (!area) return;
            area.setAttribute('aria-hidden', 'true');
            area.style.position = 'fixed';
            area.style.left = '-99999px';
            area.style.top = '0';
            area.style.zIndex = '-1';
            area.style.visibility = 'hidden';
            area.style.pointerEvents = 'none';
        }

        function downloadApprovalLogPdf(button) {
            const area = document.getElementById('approvalLogExportArea');
            if (!area) return;

            const originalText = button ? button.innerHTML : '';
            if (button) {
                button.disabled = true;
                button.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyiapkan PDF...';
            }

            ensureHtml2PdfLibrary()
                .then(function () {
                    makeLogPdfVisible(area);
                    return waitForFonts().then(function () { return waitForImages(area); });
                })
                .then(function () {
                    const opt = {
                        margin: 0,
                        filename: 'riwayat-approval-jabatan-{{ $j->id_jabatan }}-{{ \Illuminate\Support\Str::slug($j->nama_jabatan ?? "jabatan") }}.pdf',
                        image: { type: 'jpeg', quality: 1 },
                        html2canvas: {
                            scale: 2.2,
                            useCORS: true,
                            allowTaint: true,
                            backgroundColor: '#ffffff',
                            scrollX: 0,
                            scrollY: 0,
                            windowWidth: area.scrollWidth,
                            windowHeight: area.scrollHeight
                        },
                        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait', compress: true },
                        pagebreak: { mode: ['css', 'legacy'], avoid: ['.jd-avoid-break', '.jd-log-export-item'] }
                    };
                    return html2pdf().set(opt).from(area).save();
                })
                .then(function () {
                    hideLogPdf(area);
                    if (button) { button.disabled = false; button.innerHTML = originalText; }
                })
                .catch(function (error) {
                    hideLogPdf(area);
                    if (button) { button.disabled = false; button.innerHTML = originalText; }
                    alert('Gagal membuat PDF log. Pastikan file public/vendor/html2pdf/html2pdf.bundle.min.js sudah ada, lalu refresh halaman.');
                    console.error(error);
                });
        }

        window.printJabatanA4 = function () {
            const root = buildJabatanExportPages();
            if (!root) {
                window.print();
                return;
            }

            document.body.classList.add('jd-printing');
            showRootForExport(root);

            const cleanAfterPrint = function () {
                afterA4Export();
                window.removeEventListener('afterprint', cleanAfterPrint);
            };

            window.addEventListener('afterprint', cleanAfterPrint);

            waitForFonts()
                .then(function () { return waitForImages(root); })
                .then(function () {
                    setTimeout(function () { window.print(); }, 250);
                });
        };

        function downloadJobDescriptionPdf(button) {
            const originalText = button ? button.innerHTML : '';
            if (button) {
                button.disabled = true;
                button.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyiapkan PDF...';
            }

            ensureHtml2PdfLibrary()
                .then(function () {
                    const root = buildJabatanExportPages();
                    if (!root) throw new Error('Area PDF tidak ditemukan.');
                    beforeA4Export(root);
                    return waitForFonts().then(function () { return waitForImages(root); }).then(function () { return root; });
                })
                .then(function (root) {
                    const opt = {
                        margin: 0,
                        filename: 'job-description-{{ $j->id_jabatan }}-{{ \Illuminate\Support\Str::slug($j->nama_jabatan ?? "jabatan") }}.pdf',
                        image: { type: 'jpeg', quality: 1 },
                        html2canvas: {
                            scale: MAX_PDF_SCALE,
                            useCORS: true,
                            allowTaint: true,
                            backgroundColor: '#ffffff',
                            scrollX: 0,
                            scrollY: 0,
                            windowWidth: root.scrollWidth,
                            windowHeight: root.scrollHeight
                        },
                        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait', compress: true },
                        pagebreak: {
                            mode: ['css', 'legacy'],
                            before: ['.jd-export-page'],
                            avoid: ['.jd-avoid-break', '.jd-profile-card', '.jd-section-block', '.jd-card', '.jd-footer-note', '.jd-approval-signoff']
                        }
                    };
                    return html2pdf().set(opt).from(root).save();
                })
                .then(function () {
                    afterA4Export();
                    if (button) { button.disabled = false; button.innerHTML = originalText; }
                })
                .catch(function (error) {
                    afterA4Export();
                    if (button) { button.disabled = false; button.innerHTML = originalText; }
                    alert('Gagal membuat PDF. Pastikan file public/vendor/html2pdf/html2pdf.bundle.min.js sudah ada, lalu refresh halaman.');
                    console.error(error);
                });
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-jd-modal-open]').forEach(function (button) {
                button.addEventListener('click', function () {
                    const target = button.getAttribute('data-jd-modal-open');
                    if (target === 'approvalLogModal') openApprovalLogModal();
                });
            });

            document.querySelectorAll('[data-jd-modal-close]').forEach(function (button) {
                button.addEventListener('click', closeApprovalLogModal);
            });

            const approvalLogModal = document.getElementById('approvalLogModal');
            if (approvalLogModal) {
                approvalLogModal.addEventListener('click', function (event) {
                    if (event.target === approvalLogModal) closeApprovalLogModal();
                });
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') closeApprovalLogModal();
            });

            const downloadLogCsvBtn = document.getElementById('downloadApprovalLogCsvBtn');
            if (downloadLogCsvBtn) downloadLogCsvBtn.addEventListener('click', downloadApprovalLogCsv);

            const downloadLogPdfBtn = document.getElementById('downloadApprovalLogPdfBtn');
            if (downloadLogPdfBtn) {
                downloadLogPdfBtn.addEventListener('click', function () { downloadApprovalLogPdf(downloadLogPdfBtn); });
            }

            const downloadBtn = document.getElementById('downloadPdfBtn');
            if (downloadBtn) {
                downloadBtn.addEventListener('click', function () { downloadJobDescriptionPdf(downloadBtn); });
            }
        });
    })();
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
