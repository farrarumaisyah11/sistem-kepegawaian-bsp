@extends('layouts.approval')
@section('title', 'Approval Job Description')

@section('content')
@php
    $activeVersion = $jabatan->activeVersion;
    $pendingVersion = $jabatan->pendingVersion;
    $approvalStatus = $jabatan->approval_status ?? 'pending';
    $role = $user->role ?? auth()->user()->role ?? '-';

    $formatTanggalIndonesia = function ($date) {
        if (!$date) return '-';
        try {
            return \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('d F Y H:i');
        } catch (\Throwable $e) {
            return $date;
        }
    };

    $approvalDate = $formatTanggalIndonesia($jabatan->approved_at ?? null);
    $proposedDate = $formatTanggalIndonesia($jabatan->proposed_approved_at ?? null);
    $hcmDate = $formatTanggalIndonesia($jabatan->hcm_confirmed_at ?? null);
    $approverName = $pegawaiApprover->nama ?? $user->nama ?? $user->name ?? $user->username ?? 'Approver';
    $approverJabatan = $pegawaiApprover->jabatan ?? strtoupper($role);
    $approverDepartemen = $pegawaiApprover->departemen ?? '-';
    $isFinal = $jabatan->is_approval_final;
    $waitingHcm = $jabatan->is_waiting_hcm_final;
@endphp

<div class="approval-panel">
    <div class="approval-panel-head">
        <div>
            <div class="approval-eyebrow">Approval Access</div>
            <h1 class="approval-title">Konfirmasi Approval Job Description</h1>
            <p class="approval-desc">
                Halaman khusus approval. Pegawai departemen terkait melakukan approval awal. HCM yang membuka link ini akan melakukan approval final langsung.
            </p>
        </div>

        <div class="approval-actions">
            @if(!$isFinal && !$waitingHcm && $pendingVersion)
                <a href="{{ route('jabatan.approval.detail', ['jabatan' => $jabatan->id_jabatan, 'token' => $token]) }}" class="approval-btn">
                    Lihat Detail A4
                </a>
            @endif
        </div>
    </div>

    <div class="approval-panel-body">
        @if(session('success_auto'))
            <div class="approval-alert success">{{ session('success_auto') }}</div>
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

        @if($isFinal)
            <div class="approval-alert success">
                Job description ini sudah <strong>Approved Final</strong> pada <strong>{{ $hcmDate }}</strong> oleh <strong>{{ $jabatan->hcm_confirmed_by_name ?? '-' }}</strong>.
                Link approval sudah tidak aktif untuk tindakan approval baru.
            </div>
        @elseif($waitingHcm)
            <div class="approval-alert info">
                Approval awal sudah tercatat pada <strong>{{ $proposedDate }}</strong> oleh <strong>{{ $jabatan->proposed_approved_by_name ?? '-' }}</strong>.
                Final approval dilakukan dari halaman detail jabatan internal HCM.
            </div>
        @else
            <div class="approval-alert warning">
                Dokumen masih <strong>Pending Approval</strong>. Jika login sebagai pegawai, approval menjadi approval awal dan menunggu final HCM.
                Jika login sebagai HCM, approval langsung menjadi final.
            </div>
        @endif

        <div class="approval-grid-2">
            <div class="approval-card">
                <div class="approval-card-title">Data Jabatan</div>
                <div class="approval-card-body">
                    <table class="approval-table">
                        <tr><th>Nama Jabatan</th><td>{{ $jabatan->nama_jabatan ?? '-' }}</td></tr>
                        <tr><th>Departemen</th><td>{{ $jabatan->departemen ?? '-' }}</td></tr>
                        <tr><th>Golongan Jabatan</th><td>{{ $jabatan->gol_jabatan ?? '-' }}</td></tr>
                        <tr><th>Home Base</th><td>{{ $jabatan->home_base ?? '-' }}</td></tr>
                        <tr><th>Lokasi Kerja</th><td>{{ $jabatan->lokasi_kerja ?? '-' }}</td></tr>
                        <tr><th>Parent Jabatan</th><td>{{ $jabatan->parent?->nama_jabatan ?? $jabatan->parent_jabatan ?? '-' }}</td></tr>
                    </table>
                </div>
            </div>

            <div class="approval-card">
                <div class="approval-card-title">Informasi Versi & Login</div>
                <div class="approval-card-body">
                    <table class="approval-table">
                        <tr><th>Versi Resmi Aktif</th><td>{{ $activeVersion ? 'Versi '.$activeVersion->version_number : '-' }}</td></tr>
                        <tr><th>Versi yang Akan Di-approve</th><td>{{ $pendingVersion ? 'Versi '.$pendingVersion->version_number : '-' }}</td></tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($isFinal)
                                    <span class="approval-badge approved">Approved Final</span>
                                @elseif($waitingHcm)
                                    <span class="approval-badge pending">Menunggu Final HCM</span>
                                @elseif($approvalStatus === 'rejected')
                                    <span class="approval-badge danger">Rejected</span>
                                @else
                                    <span class="approval-badge pending">Pending Approval</span>
                                @endif
                            </td>
                        </tr>
                        <tr><th>Nama Login</th><td>{{ $approverName }}</td></tr>
                        <tr><th>Role</th><td>{{ strtoupper($role) }}</td></tr>
                        <tr><th>Jabatan Login</th><td>{{ $approverJabatan }}</td></tr>
                        <tr><th>Departemen Login</th><td>{{ $approverDepartemen }}</td></tr>
                        <tr><th>Approval Awal</th><td>{{ $proposedDate }}</td></tr>
                        <tr><th>Final HCM</th><td>{{ $hcmDate }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="approval-grid-1" style="margin-top:18px;">
            <div class="approval-card">
                <div class="approval-card-title">Form Approval</div>
                <div class="approval-card-body">
                    @if($isFinal)
                        <table class="approval-table">
                            <tr><th>Disetujui Awal Oleh</th><td>{{ $jabatan->approved_by_name ?? '-' }}</td></tr>
                            <tr><th>Jabatan Approver Awal</th><td>{{ $jabatan->approved_by_jabatan ?? '-' }}</td></tr>
                            <tr><th>Departemen Approver Awal</th><td>{{ $jabatan->approved_by_departemen ?? '-' }}</td></tr>
                            <tr><th>Tanggal/Jam Approval Awal</th><td>{{ $approvalDate }}</td></tr>
                            <tr><th>Disahkan HCM Oleh</th><td>{{ $jabatan->hcm_confirmed_by_name ?? '-' }}</td></tr>
                            <tr><th>Tanggal/Jam Final HCM</th><td>{{ $hcmDate }}</td></tr>
                            <tr><th>Catatan Approval</th><td>{{ $jabatan->approval_catatan ?? '-' }}</td></tr>
                            <tr><th>Catatan Final HCM</th><td>{{ $jabatan->hcm_confirmation_catatan ?? '-' }}</td></tr>
                        </table>
                    @elseif($waitingHcm)
                        <div class="approval-alert info" style="margin-bottom:0;">
                            Approval awal sudah tercatat. Dokumen tinggal menunggu approval final HCM dari halaman detail jabatan internal.
                        </div>
                    @elseif(!$pendingVersion)
                        <div class="approval-alert warning" style="margin-bottom:0;">
                            Tidak ada versi pending yang perlu di-approve.
                        </div>
                    @else
                        <form method="POST" action="{{ route('jabatan.approval.approve', ['jabatan' => $jabatan->id_jabatan, 'token' => $token]) }}" onsubmit="return approvalSubmitOnce(this);">
                            @csrf

                            <label for="approval_catatan" class="approval-form-label">
                                Catatan Approval <span style="color:#98a2b3; font-weight:700;">(opsional)</span>
                            </label>
                            <textarea name="approval_catatan" id="approval_catatan" class="approval-textarea" placeholder="Tambahkan catatan jika diperlukan...">{{ old('approval_catatan') }}</textarea>

                            <div class="approval-note" style="text-align:left; margin-top:8px; max-width:none;">
                                Sistem mencatat nama, role, jabatan, departemen, dan waktu approval sebagai audit trail.
                            </div>

                            <div class="approval-actions" style="margin-top:16px; justify-content:flex-end;">
                                <button type="submit" class="approval-btn success" id="approveSubmitBtn">
                                    {{ $role === 'hcm' ? 'Approve Final sebagai HCM' : 'Submit Approval Awal' }}
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function approvalSubmitOnce(form){
    const btn = document.getElementById('approveSubmitBtn');
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Memproses Approval...';
    }
    return true;
}
</script>
@endpush
