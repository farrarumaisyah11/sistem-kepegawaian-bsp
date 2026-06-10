@extends('layouts.approval')
@section('title', 'Approval Job Description')

@section('content')
@php
    $activeVersion = $jabatan->activeVersion;
    $pendingVersion = $jabatan->pendingVersion;
    $approvalStatus = $jabatan->approval_status ?? 'pending';
    $role = $user->role ?? auth()->user()->role ?? '-';

    $approvalDate = '-';
    if (!empty($jabatan->approved_at)) {
        try {
            $approvalDate = \Illuminate\Support\Carbon::parse($jabatan->approved_at)
                ->locale('id')
                ->translatedFormat('d F Y H:i');
        } catch (\Throwable $e) {
            $approvalDate = $jabatan->approved_at;
        }
    }

    $approverName = $pegawaiApprover->nama ?? $user->nama ?? $user->name ?? $user->username ?? 'Approver';
    $approverJabatan = $pegawaiApprover->jabatan ?? strtoupper($role);
    $approverDepartemen = $pegawaiApprover->departemen ?? '-';
@endphp

<div class="approval-panel">
    <div class="approval-panel-head">
        <div>
            <div class="approval-eyebrow">Approval Access</div>
            <h1 class="approval-title">Konfirmasi Approval Job Description</h1>
            <p class="approval-desc">
                Periksa kembali data jabatan sebelum melakukan approval. Approval hanya dapat dilakukan oleh akun HCM/Manager.
            </p>
        </div>

        <div class="approval-actions">
            <a href="{{ route('jabatan.approval.detail', ['jabatan' => $jabatan->id_jabatan, 'token' => $token]) }}" class="approval-btn">
                Lihat Detail Job Description
            </a>
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

        @if($approvalStatus === 'approved' && !$pendingVersion)
            <div class="approval-alert success">
                Job description ini sudah disetujui pada <strong>{{ $approvalDate }}</strong> oleh <strong>{{ $jabatan->approved_by_name ?? '-' }}</strong>.
            </div>
        @else
            <div class="approval-alert warning">
                Status dokumen masih <strong>Pending Approval</strong>. Approval akan membuat versi pending menjadi versi resmi terbaru.
                Versi lama tidak dihapus, tetapi diarsipkan.
            </div>
        @endif

        @if(empty($pegawaiApprover))
            <div class="approval-alert warning">
                Data pegawai untuk akun ini belum ditemukan berdasarkan NIP/username. Sistem tetap dapat memproses approval,
                tetapi nama dan jabatan approver akan memakai data akun login sebagai fallback.
            </div>
        @endif

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
                        <tr>
                            <th>Parent Jabatan</th>
                            <td>{{ $jabatan->parent_jabatan ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="approval-card">
                <div class="approval-card-title">Informasi Versi & Approver</div>
                <div class="approval-card-body">
                    <table class="approval-table">
                        <tr>
                            <th>Versi Resmi Aktif</th>
                            <td>{{ $activeVersion ? 'Versi '.$activeVersion->version_number : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Versi yang Akan Di-approve</th>
                            <td>{{ $pendingVersion ? 'Versi '.$pendingVersion->version_number : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($approvalStatus === 'approved' && !$pendingVersion)
                                    <span class="approval-badge approved">Approved</span>
                                @elseif($approvalStatus === 'rejected')
                                    <span class="approval-badge danger">Rejected</span>
                                @else
                                    <span class="approval-badge pending">Pending Approval</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Nama Approver</th>
                            <td>{{ $approvalStatus === 'approved' && !$pendingVersion ? ($jabatan->approved_by_name ?? '-') : $approverName }}</td>
                        </tr>
                        <tr>
                            <th>Role</th>
                            <td>{{ $approvalStatus === 'approved' && !$pendingVersion ? strtoupper($jabatan->approved_by_role ?? '-') : strtoupper($role) }}</td>
                        </tr>
                        <tr>
                            <th>Jabatan</th>
                            <td>{{ $approvalStatus === 'approved' && !$pendingVersion ? ($jabatan->approved_by_jabatan ?? '-') : $approverJabatan }}</td>
                        </tr>
                        <tr>
                            <th>Departemen</th>
                            <td>{{ $approvalStatus === 'approved' && !$pendingVersion ? ($jabatan->approved_by_departemen ?? '-') : $approverDepartemen }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Approval Terakhir</th>
                            <td>{{ $approvalDate }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="approval-grid-1" style="margin-top:18px;">
            <div class="approval-card">
                <div class="approval-card-title">Form Approval</div>
                <div class="approval-card-body">
                    @if($approvalStatus === 'approved' && !$pendingVersion)
                        <table class="approval-table">
                            <tr>
                                <th>Disetujui Oleh</th>
                                <td>{{ $jabatan->approved_by_name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Jabatan Approver</th>
                                <td>{{ $jabatan->approved_by_jabatan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Departemen Approver</th>
                                <td>{{ $jabatan->approved_by_departemen ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal/Jam Approval</th>
                                <td>{{ $approvalDate }}</td>
                            </tr>
                            <tr>
                                <th>Catatan</th>
                                <td>{{ $jabatan->approval_catatan ?? '-' }}</td>
                            </tr>
                        </table>
                    @else
                        <form method="POST" action="{{ route('jabatan.approval.approve', ['jabatan' => $jabatan->id_jabatan, 'token' => $token]) }}" onsubmit="return confirmApprovalSubmit(this);">
                            @csrf

                            <label for="approval_catatan" class="approval-form-label">Catatan Approval <span style="color:#98a2b3; font-weight:700;">(opsional)</span></label>
                            <textarea name="approval_catatan" id="approval_catatan" class="approval-textarea" placeholder="Tambahkan catatan jika diperlukan...">{{ old('approval_catatan') }}</textarea>

                            <label for="approval_password" class="approval-form-label" style="margin-top:14px;">Konfirmasi Password Akun <span style="color:#dc2626; font-weight:800;">*</span></label>
                            <input type="password"
                                   name="approval_password"
                                   id="approval_password"
                                   class="approval-input"
                                   placeholder="Masukkan password akun Anda untuk approval"
                                   autocomplete="current-password"
                                   required>
                            <div class="approval-note" style="text-align:left; margin-top:8px; max-width:none;">
                                Password digunakan sebagai konfirmasi approval agar proses persetujuan dapat dipertanggungjawabkan.
                            </div>

                            <div class="approval-actions" style="margin-top:16px; justify-content:flex-end;">
                                <button type="submit" class="approval-btn success" id="approveSubmitBtn">
                                    Approve Job Description
                                </button>
                            </div>
                        </form>
                        @if(
    ($role === 'hcm')
    && ($jabatan->approval_flow_status ?? null) === 'waiting_hcm_confirmation'
    && $pendingVersion
    && !empty($pendingVersion->proposed_approved_at)
)
    <hr style="margin:22px 0;">

    <div class="approval-alert info">
        Approval dari pegawai sudah tercatat dan sekarang menunggu pengesahan final dari HCM.
    </div>

    <form method="POST"
          action="{{ route('jabatan.approval.confirm-final', ['jabatan' => $jabatan->id_jabatan, 'token' => $token]) }}"
          onsubmit="return confirm('Sahkan approval pegawai ini sebagai approval final?');">
        @csrf

        <label class="approval-form-label">
            Catatan Pengesahan HCM <span style="color:#98a2b3;">(opsional)</span>
        </label>

        <textarea name="hcm_confirmation_catatan"
                  class="approval-textarea"
                  placeholder="Tambahkan catatan pengesahan HCM jika diperlukan..."></textarea>

        <label class="approval-form-label" style="margin-top:14px;">
            Password HCM <span style="color:#dc2626;">*</span>
        </label>

        <input type="password"
               name="approval_password"
               class="approval-input"
               placeholder="Masukkan password akun HCM"
               required>

        <div class="approval-actions" style="margin-top:16px; justify-content:flex-end;">
            <button type="submit" class="approval-btn success">
                Sahkan Approval Final
            </button>
        </div>
    </form>
@endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmApprovalSubmit(form){
    const password = form.querySelector('[name="approval_password"]');
    if (!password || !password.value.trim()) {
        alert('Password wajib diisi untuk konfirmasi approval.');
        if (password) password.focus();
        return false;
    }

    const ok = confirm('Apakah Anda yakin ingin approve job description ini sebagai versi resmi terbaru? Versi lama akan diarsipkan.');
    if (!ok) return false;

    const btn = document.getElementById('approveSubmitBtn');
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Memproses Approval...';
    }

    return true;
}
</script>
@endpush
