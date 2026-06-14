@php
    $prefix = auth()->user()->role ?? 'hcm';
    $canManageApproval = in_array($prefix, ['admin', 'hcm'], true);

    $approvalLogs = collect();

    try {
        if (isset($jabatan) && $jabatan) {
            $approvalLogs = $jabatan->relationLoaded('approvalLogs')
                ? ($jabatan->approvalLogs ?? collect())
                : $jabatan->approvalLogs()
                    ->where('id_jabatan', $jabatan->id_jabatan)
                    ->with('version')
                    ->orderByDesc('created_at')
                    ->orderByDesc('id_jabatan_approval_log')
                    ->get();
        }
    } catch (\Throwable $e) {
        $approvalLogs = collect();
    }

    $formatApprovalDate = function ($date) {
        if (!$date) return '-';

        try {
            return \Illuminate\Support\Carbon::parse($date)
                ->locale('id')
                ->translatedFormat('d F Y H:i');
        } catch (\Throwable $e) {
            return $date;
        }
    };

    $approvalStatusLabel = $jabatan->approval_status_label
        ?? (($jabatan->approval_flow_status ?? null) === 'approved_final'
            ? 'Approved Final'
            : (($jabatan->approval_flow_status ?? null) === 'waiting_hcm_confirmation'
                ? 'Menunggu Approval Final HCM'
                : 'Pending Approval'));

    $approvalVersion = $jabatan->pendingVersion
        ?: $jabatan->activeVersion
        ?: $jabatan->latestApprovedVersion
        ?: null;

    $jabatanNama = $jabatan->nama_jabatan ?? '-';
    $departemenNama = $jabatan->departemenMaster->nama_departemen ?? $jabatan->departemen ?? '-';

    $modalId = 'approvalLogModal-' . ($jabatan->id_jabatan ?? '0');
    $tableId = 'approvalLogTable-' . ($jabatan->id_jabatan ?? '0');
@endphp

<div class="jd-approval-action-card d-print-none">
    <div class="jd-approval-action-info">
        <div class="jd-approval-action-label">Kontrol Approval Job Description</div>
        <div class="jd-approval-action-main">
            <span class="jd-approval-status-chip {{ ($jabatan->is_approval_final ?? false) ? 'is-approved' : (($jabatan->is_waiting_hcm_final ?? false) ? 'is-waiting' : 'is-pending') }}">
                {{ $approvalStatusLabel }}
            </span>

            @if(($jabatan->is_waiting_hcm_final ?? false) && $jabatan->pendingVersion)
                <span class="jd-approval-help-text">
                    Approval awal sudah tercatat. Dokumen menunggu pengesahan final HCM.
                </span>
            @elseif($jabatan->is_approval_final ?? false)
                <span class="jd-approval-help-text">
                    Dokumen sudah final approved. Link approval versi ini sudah ditutup.
                </span>
            @else
                <span class="jd-approval-help-text">
                    Dokumen masih berada dalam proses approval.
                </span>
            @endif
        </div>
    </div>

    <div class="jd-approval-action-buttons">
        <a href="{{ route($prefix.'.jabatan.index') }}" class="btn btn-light jd-approval-btn">
            Kembali
        </a>

        @if($canManageApproval && ($jabatan->can_approval_link_action ?? false))
            <a href="{{ route($prefix.'.jabatan.approval-page', $jabatan->id_jabatan) }}" class="btn btn-outline-primary jd-approval-btn">
                Link Approval
            </a>
        @endif

        @if(($prefix === 'hcm') && ($jabatan->approval_flow_status ?? null) === 'waiting_hcm_confirmation' && $jabatan->pendingVersion)
            <form method="POST"
                  action="{{ route('hcm.jabatan.approval.confirm-final-from-show', $jabatan->id_jabatan) }}"
                  class="m-0"
                  onsubmit="return finalHcmSubmitOnce(this);">
                @csrf
                <input type="hidden" name="hcm_confirmation_catatan" value="">
                <button type="submit" class="btn btn-success jd-approval-btn">
                    Approve Final HCM
                </button>
            </form>
        @elseif($prefix === 'hcm')
            <button type="button" class="btn btn-success jd-approval-btn" disabled>
                Approve Final HCM
            </button>
        @endif

        @if($jabatan->activeVersion && !$jabatan->pendingVersion && in_array($prefix, ['admin', 'hcm'], true))
            <form method="POST"
                  action="{{ route($prefix.'.jabatan.apply-approved-version', $jabatan->id_jabatan) }}"
                  class="m-0">
                @csrf
                <button type="submit"
                        class="btn btn-outline-success jd-approval-btn"
                        onclick="return confirm('Terapkan versi approved terbaru ke seluruh pegawai aktif yang memegang jabatan ini? Riwayat versi lama pegawai tetap disimpan.');">
                    Terapkan ke Pegawai
                </button>
            </form>
        @elseif(in_array($prefix, ['admin', 'hcm'], true))
            <button type="button" class="btn btn-outline-success jd-approval-btn" disabled>
                Terapkan ke Pegawai
            </button>
        @endif

        <button type="button"
                class="btn btn-outline-dark jd-approval-btn"
                data-bs-toggle="modal"
                data-bs-target="#{{ $modalId }}">
            Riwayat Approval
            @if($approvalLogs->count())
                <span class="jd-log-count">{{ $approvalLogs->count() }}</span>
            @endif
        </button>
    </div>
</div>

@if(($prefix === 'hcm') && ($jabatan->approval_flow_status ?? null) === 'waiting_hcm_confirmation' && $jabatan->pendingVersion)
    <div class="alert alert-warning mt-3 mb-0 d-print-none jd-approval-alert">
        Approval awal sudah dilakukan oleh
        <span class="jd-alert-emphasis">{{ $jabatan->proposed_approved_by_name ?? '-' }}</span>
        pada
        <span class="jd-alert-emphasis">{{ $formatApprovalDate($jabatan->proposed_approved_at ?? null) }}</span>.
        Dokumen tinggal menunggu approval final dari HCM.
    </div>
@endif

<div class="modal fade jd-approval-log-modal d-print-none"
     id="{{ $modalId }}"
     tabindex="-1"
     aria-labelledby="{{ $modalId }}Label"
     aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header jd-modal-header">
                <div>
                    <h5 class="modal-title" id="{{ $modalId }}Label">
                        Riwayat Approval Job Description
                    </h5>
                    <div class="jd-modal-subtitle">
                        {{ $jabatanNama }} · {{ $departemenNama }}
                    </div>
                </div>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body jd-modal-body">
                <div class="jd-log-description-card">
                    <div class="jd-log-description-label">Keterangan Log</div>
                    <div class="jd-log-description-text">
                        Log aktivitas approval ini menampilkan riwayat khusus untuk jabatan
                        <span>{{ $jabatanNama }}</span>
                        pada departemen
                        <span>{{ $departemenNama }}</span>.
                    </div>
                </div>

                <div class="jd-log-toolbar">
                    <div class="jd-log-toolbar-info">
                        {{ $approvalLogs->count() }} aktivitas approval tercatat untuk jabatan ini.
                    </div>

                    <div class="jd-log-toolbar-actions">
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary"
                                onclick="downloadApprovalLogCsv('{{ $tableId }}', '{{ $jabatan->id_jabatan ?? 0 }}')">
                            Download CSV
                        </button>
                    </div>
                </div>

                <div class="jd-log-document">
                    <div class="jd-log-document-header">
                        <div class="jd-log-logo-box">
                            <img src="{{ asset('images/logo skk migas.png') }}" alt="SKK Migas">
                        </div>

                        <div class="jd-log-title-box">
                            <div class="jd-log-company">PT. BUMI SIAK PUSAKO</div>
                            <div class="jd-log-title">RIWAYAT APPROVAL JOB DESCRIPTION</div>
                        </div>

                        <div class="jd-log-logo-box">
                            <img src="{{ asset('images/logo bsp.png') }}" alt="BSP">
                        </div>
                    </div>

                    <div class="jd-log-meta-card">
                        <div class="jd-log-meta-grid">
                            <div>
                                <span>Nama Jabatan</span>
                                <div>{{ $jabatanNama }}</div>
                            </div>
                            <div>
                                <span>Departemen</span>
                                <div>{{ $departemenNama }}</div>
                            </div>
                            <div>
                                <span>Status Approval</span>
                                <div>{{ $approvalStatusLabel }}</div>
                            </div>
                            <div>
                                <span>Versi</span>
                                <div>{{ $approvalVersion ? 'Versi '.$approvalVersion->version_number : '-' }}</div>
                            </div>
                            <div>
                                <span>Approval Awal</span>
                                <div>{{ $jabatan->proposed_approved_by_name ?? '-' }}</div>
                            </div>
                            <div>
                                <span>Final HCM</span>
                                <div>{{ $jabatan->hcm_confirmed_by_name ?? '-' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="jd-log-table-card">
                        <div class="jd-log-section-title">Daftar Aktivitas Approval</div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle jd-log-table" id="{{ $tableId }}">
                                <thead>
                                    <tr>
                                        <th style="width: 15%;">Waktu</th>
                                        <th style="width: 23%;">Aktivitas</th>
                                        <th style="width: 18%;">Pengguna</th>
                                        <th style="width: 10%;">Role</th>
                                        <th style="width: 17%;">Jabatan</th>
                                        <th style="width: 17%;">Departemen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($approvalLogs as $log)
                                        <tr>
                                            <td>{{ $formatApprovalDate($log->created_at) }}</td>
                                            <td>{{ $log->action_label }}</td>
                                            <td>{{ $log->actor_name ?? '-' }}</td>
                                            <td>{{ strtoupper($log->actor_role ?? '-') }}</td>
                                            <td>{{ $log->actor_jabatan ?? '-' }}</td>
                                            <td>{{ $log->actor_departemen ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                Belum ada aktivitas approval untuk jabatan ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="jd-log-footer">
                        <div>
                            Dilihat oleh: <span>{{ auth()->user()->nama ?? auth()->user()->name ?? auth()->user()->username ?? '-' }}</span>
                        </div>
                        <div>
                            Waktu akses: <span>{{ now()->locale('id')->translatedFormat('d F Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button"
                        class="btn btn-outline-secondary"
                        onclick="downloadApprovalLogCsv('{{ $tableId }}', '{{ $jabatan->id_jabatan ?? 0 }}')">
                    Download CSV
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .jd-approval-action-card {
        margin-top: 14px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 14px 16px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }

    .jd-approval-action-info {
        flex: 1;
        min-width: 280px;
    }

    .jd-approval-action-label {
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #6b7280;
        margin-bottom: 7px;
    }

    .jd-approval-action-main {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .jd-approval-status-chip {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 7px 11px;
        font-size: 12px;
        font-weight: 900;
        line-height: 1;
        border: 1px solid transparent;
    }

    .jd-approval-status-chip.is-approved {
        background: #dcfce7;
        color: #166534;
        border-color: #bbf7d0;
    }

    .jd-approval-status-chip.is-waiting {
        background: #fef3c7;
        color: #92400e;
        border-color: #fde68a;
    }

    .jd-approval-status-chip.is-pending {
        background: #eef2ff;
        color: #3730a3;
        border-color: #c7d2fe;
    }

    .jd-approval-help-text {
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
    }

    .jd-approval-action-buttons {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .jd-approval-btn {
        min-height: 40px;
        border-radius: 12px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        white-space: nowrap;
    }

    .jd-log-count {
        margin-left: 4px;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        border-radius: 999px;
        background: #273957;
        color: #ffffff;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .jd-approval-alert {
        border-radius: 14px;
        font-weight: 600;
    }

    .jd-alert-emphasis {
        font-weight: 700;
        color: #78350f;
    }

    .jd-modal-header {
        background: linear-gradient(135deg, #273957 0%, #3f4a32 100%);
        color: #ffffff;
    }

    .jd-modal-subtitle {
        font-size: 12px;
        color: rgba(255,255,255,.78);
        font-weight: 600;
        margin-top: 3px;
    }

    .jd-modal-body {
        background: #f8fafc;
    }

    .jd-log-description-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 12px 14px;
        margin-bottom: 12px;
    }

    .jd-log-description-label {
        color: #273957;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 5px;
    }

    .jd-log-description-text {
        color: #475569;
        font-size: 13px;
        line-height: 1.5;
        font-weight: 600;
    }

    .jd-log-description-text span {
        color: #111827;
        font-weight: 800;
    }

    .jd-log-toolbar {
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .jd-log-toolbar-info {
        color: #475569;
        font-size: 13px;
        font-weight: 700;
    }

    .jd-log-toolbar-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .jd-log-document {
        background: #ffffff;
        color: #111827;
        padding: 16px;
        width: 100%;
        max-width: 100%;
        margin: 0 auto;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .08);
    }

    .jd-log-document-header {
        display: grid;
        grid-template-columns: 88px 1fr 88px;
        gap: 14px;
        align-items: center;
        border-bottom: 3px solid #273957;
        padding-bottom: 12px;
        margin-bottom: 14px;
    }

    .jd-log-logo-box {
        width: 78px;
        height: 58px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #ffffff;
    }

    .jd-log-logo-box img {
        max-width: 70px;
        max-height: 50px;
        object-fit: contain;
    }

    .jd-log-title-box {
        text-align: center;
    }

    .jd-log-company {
        font-size: 15px;
        font-weight: 900;
        color: #273957;
        letter-spacing: .06em;
    }

    .jd-log-title {
        margin-top: 4px;
        font-size: 18px;
        font-weight: 900;
        color: #111827;
    }

    .jd-log-meta-card,
    .jd-log-table-card,
    .jd-log-footer {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 12px;
        margin-top: 12px;
        background: #ffffff;
    }

    .jd-log-meta-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px 14px;
    }

    .jd-log-meta-grid span {
        display: block;
        font-size: 10px;
        color: #64748b;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: 3px;
    }

    .jd-log-meta-grid div div {
        display: block;
        color: #111827;
        font-size: 12px;
        line-height: 1.35;
        font-weight: 700;
    }

    .jd-log-section-title {
        color: #273957;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 8px;
    }

    .jd-log-table {
        margin-bottom: 0;
        font-size: 11px;
    }

    .jd-log-table th {
        background: #273957 !important;
        color: #ffffff !important;
        text-align: center;
        vertical-align: middle;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .jd-log-table td {
        vertical-align: top;
        font-size: 10.5px;
        line-height: 1.35;
        color: #111827;
    }

    .jd-log-footer {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        font-size: 10.5px;
        color: #475569;
    }

    .jd-log-footer span {
        color: #111827;
        font-weight: 700;
    }

    @media (max-width: 768px) {
        .jd-approval-action-card {
            align-items: stretch;
        }

        .jd-approval-action-buttons,
        .jd-approval-action-buttons form,
        .jd-approval-action-buttons .jd-approval-btn {
            width: 100%;
        }

        .jd-log-document {
            width: 100%;
            padding: 14px;
        }

        .jd-log-document-header {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .jd-log-logo-box {
            margin: 0 auto;
        }

        .jd-log-meta-grid {
            grid-template-columns: 1fr;
        }
    }

    @media print {
        .jd-approval-action-card,
        .jd-approval-alert,
        .jd-approval-log-modal,
        .modal-backdrop {
            display: none !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
function finalHcmSubmitOnce(form){
    const button = form.querySelector('button[type="submit"]');
    if (button) {
        button.disabled = true;
        button.textContent = 'Memproses Final Approval...';
    }
    return true;
}

function safeFileName(value) {
    return String(value || '')
        .replace(/[\\/:*?"<>|]/g, '-')
        .replace(/\s+/g, ' ')
        .trim();
}

function downloadApprovalLogCsv(tableId, jabatanId) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const rows = [];

    table.querySelectorAll('tr').forEach(function(row){
        const cols = [];
        row.querySelectorAll('th, td').forEach(function(cell){
            let text = (cell.innerText || '').replace(/\s+/g, ' ').trim();
            text = '"' + text.replace(/"/g, '""') + '"';
            cols.push(text);
        });
        rows.push(cols.join(','));
    });

    const csv = '\ufeff' + rows.join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');

    link.href = url;
    link.download = safeFileName('Riwayat Approval Jabatan ' + jabatanId + '.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}
</script>
@endpush
