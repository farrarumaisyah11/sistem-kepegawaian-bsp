@extends('layouts.approval')
@section('title', 'Approval Job Description')

@section('content')
@php
    $prefix = auth()->user()->role ?? 'hcm';
    $activeVersion = $jabatan->activeVersion;
    $pendingVersion = $jabatan->pendingVersion;
    $approvalStatus = $jabatan->approval_status ?? 'pending';

    $formatTanggalIndonesia = function ($date) {
        if (!$date) return '-';

        try {
            return \Illuminate\Support\Carbon::parse($date)
                ->locale('id')
                ->translatedFormat('d F Y H:i');
        } catch (\Throwable $e) {
            return $date;
        }
    };

    $proposedApprovalDate = $formatTanggalIndonesia($jabatan->proposed_approved_at ?? null);
    $hcmConfirmedDate = $formatTanggalIndonesia($jabatan->hcm_confirmed_at ?? null);
    $lastUpdatedDate = $formatTanggalIndonesia($jabatan->jobdesk_updated_at ?? null);

    $approvalToken = $jabatan->approval_token ?? null;

    /*
    |--------------------------------------------------------------------------
    | Short Approval URL
    |--------------------------------------------------------------------------
    | Gunakan short link internal corporate.
    | Jangan pakai layanan external shortener seperti bit.ly karena link approval
    | adalah akses dokumen internal perusahaan.
    |--------------------------------------------------------------------------
    */
    $shortApprovalUrl = $approvalToken
        ? route('jabatan.approval.short', ['token' => $approvalToken])
        : null;

    /*
    |--------------------------------------------------------------------------
    | Kondisi link boleh tampil
    |--------------------------------------------------------------------------
    | Link hanya tampil jika:
    | - Belum approved final
    | - Belum menunggu final HCM
    | - Masih ada pending version
    | - Token tersedia
    |--------------------------------------------------------------------------
    */
    $canShowApprovalLink =
        !$jabatan->is_approval_final
        && !$jabatan->is_waiting_hcm_final
        && !empty($approvalToken)
        && !empty($pendingVersion);
@endphp

<div class="approval-panel">
    <div class="approval-panel-head">
        <div>
            <div class="approval-eyebrow">Job Description Approval</div>

            <h1 class="approval-title">Approval Job Description</h1>

            <p class="approval-desc">
                Halaman ini digunakan HCM/Admin untuk memantau status approval,
                membagikan short link approval awal, dan melihat audit trail approval.
                QR approval tidak ditampilkan agar alur approval lebih terkendali melalui
                link internal dan halaman detail jabatan.
            </p>
        </div>

        <div class="approval-actions approval-actions-right">
            @if(in_array($prefix, ['admin', 'hcm'], true))
                <a href="{{ route($prefix.'.jabatan.show', $jabatan->id_jabatan) }}" class="approval-btn">
                    Kembali
                </a>
            @endif
        </div>
    </div>

    <div class="approval-panel-body">
        @if(session('success_auto'))
            <div class="approval-alert success">
                {{ session('success_auto') }}
            </div>
        @endif

        @if($errors->any())
            <div class="approval-alert danger">
                <strong>Periksa kembali data berikut:</strong>

                <ul style="margin:8px 0 0; padding-left:18px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(!empty($isLocalApprovalUrl))
            <div class="approval-alert warning">
                Link approval masih memakai alamat lokal/private. Link hanya bisa dibuka
                dari perangkat pada jaringan yang sama. Untuk penggunaan corporate lintas
                jaringan, gunakan domain internal/VPN dan set <strong>APP_APPROVAL_URL</strong>.
            </div>
        @endif

        <div class="approval-alert info">
            Alur approval: pegawai pada departemen terkait melakukan
            <strong>approval awal</strong>. Setelah itu status menjadi
            <strong>Menunggu Approval Final HCM</strong>. Approval final HCM dilakukan
            dari halaman detail jabatan internal. Jika HCM membuka short link approval
            langsung, dokumen langsung menjadi <strong>Approved Final</strong>.
        </div>

        <div class="approval-grid-2">
            <div class="approval-card">
                <div class="approval-card-title">Data Jabatan</div>

                <div class="approval-card-body">
                    <table class="approval-table">
                        <tr>
                            <th>Nama Jabatan</th>
                            <td>{{ $jabatan->nama_jabatan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Departemen</th>
                            <td>{{ $jabatan->departemen ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Golongan Jabatan</th>
                            <td>{{ $jabatan->gol_jabatan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Home Base</th>
                            <td>{{ $jabatan->home_base ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Lokasi Kerja</th>
                            <td>{{ $jabatan->lokasi_kerja ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="approval-card">
                <div class="approval-card-title">Status Approval</div>

                <div class="approval-card-body">
                    <table class="approval-table">
                        <tr>
                            <th>Versi Resmi Aktif</th>
                            <td>{{ $activeVersion ? 'Versi '.$activeVersion->version_number : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Versi Menunggu Approval</th>
                            <td>{{ $pendingVersion ? 'Versi '.$pendingVersion->version_number : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($jabatan->is_approval_final)
                                    <span class="approval-badge approved">Sudah Approved Final</span>
                                @elseif($jabatan->is_waiting_hcm_final)
                                    <span class="approval-badge pending">Menunggu Approval Final HCM</span>
                                @elseif($approvalStatus === 'rejected')
                                    <span class="approval-badge danger">Rejected</span>
                                @else
                                    <span class="approval-badge pending">Pending Approval</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Approver Awal</th>
                            <td>{{ $jabatan->proposed_approved_by_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Jabatan Approver Awal</th>
                            <td>{{ $jabatan->proposed_approved_by_jabatan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Waktu Approval Awal</th>
                            <td>{{ $proposedApprovalDate }}</td>
                        </tr>
                        <tr>
                            <th>Disahkan HCM Oleh</th>
                            <td>{{ $jabatan->hcm_confirmed_by_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Waktu Final HCM</th>
                            <td>{{ $hcmConfirmedDate }}</td>
                        </tr>
                        <tr>
                            <th>Terakhir Diperbarui</th>
                            <td>{{ $lastUpdatedDate }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="approval-grid-1" style="margin-top:18px;">
            <div class="approval-card">
                <div class="approval-card-title">
                    Short Link Approval Awal
                </div>

                <div class="approval-card-body">
                    @if($canShowApprovalLink)
                        <div class="approval-link-row" style="width:100%; max-width:920px;">
                            <input type="text"
                                   id="approvalLinkInput"
                                   class="approval-input"
                                   value="{{ $shortApprovalUrl }}"
                                   readonly>

                            <button type="button"
                                    class="approval-btn primary"
                                    onclick="copyApprovalLink()">
                                Copy Short Link
                            </button>
                        </div>

                        <div class="approval-note" style="margin-top:10px; max-width:920px; text-align:left;">
                            Short link ini hanya aktif untuk versi pending saat ini.
                            Setelah job description approved final, link akan ditutup dan token dinonaktifkan.
                            Jika job description diperbarui lagi, sistem membuat short link baru dengan token berbeda.
                        </div>

                        <div style="margin-top:10px; max-width:920px;">
                            <small style="color:#667085; font-weight:700;">
                                Format link:
                            </small>
                            <code style="display:inline-block; margin-top:4px; background:#f3f4f6; padding:6px 9px; border-radius:8px; color:#344054;">
                                /approval/jd/{{ $approvalToken }}
                            </code>
                        </div>
                    @elseif($jabatan->is_approval_final)
                        <div class="approval-alert success" style="margin-bottom:0;">
                            Job description sudah approved final. Short link approval telah ditutup.
                        </div>
                    @elseif($jabatan->is_waiting_hcm_final)
                        <div class="approval-alert info" style="margin-bottom:0;">
                            Approval awal sudah tercatat. Short link approval tidak perlu dibagikan lagi.
                            Dokumen menunggu approval final HCM dari halaman detail jabatan.
                        </div>
                    @else
                        <div class="approval-alert warning" style="margin-bottom:0;">
                            Short link approval belum tersedia karena tidak ada versi pending atau token belum dibuat.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="approval-grid-1" style="margin-top:18px;">
            <div class="approval-card">
                <div class="approval-card-title">
                    Riwayat Aktivitas Approval
                </div>

                <div class="approval-card-body">
                    <div class="table-responsive">
                        <table class="approval-table">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Aktivitas</th>
                                    <th>Pengguna</th>
                                    <th>Role</th>
                                    <th>Jabatan</th>
                                    <th>Departemen</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($jabatan->approvalLogs ?? [] as $log)
                                    <tr>
                                        <td>{{ $formatTanggalIndonesia($log->created_at) }}</td>
                                        <td>{{ $log->action_label }}</td>
                                        <td>{{ $log->actor_name ?? '-' }}</td>
                                        <td>{{ strtoupper($log->actor_role ?? '-') }}</td>
                                        <td>{{ $log->actor_jabatan ?? '-' }}</td>
                                        <td>{{ $log->actor_departemen ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            Belum ada aktivitas approval.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="approval-note" style="margin-top:10px; text-align:left;">
                        Audit trail ditampilkan per jabatan, bukan gabungan seluruh jabatan.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyApprovalLink(){
    const input = document.getElementById('approvalLinkInput');

    if (!input) {
        return;
    }

    const link = input.value;

    const copyDone = function(){
        fetch("{{ route('jabatan.approval.record-share', $jabatan->id_jabatan) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                source: 'copy_short_link',
                url_type: 'short'
            })
        }).finally(function(){
            alert('Short link approval berhasil disalin dan aktivitas copy link sudah dicatat.');
        });
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(link)
            .then(copyDone)
            .catch(function(){
                input.select();
                input.setSelectionRange(0, 99999);
                document.execCommand('copy');
                copyDone();
            });
    } else {
        input.select();
        input.setSelectionRange(0, 99999);
        document.execCommand('copy');
        copyDone();
    }
}
</script>
@endpush