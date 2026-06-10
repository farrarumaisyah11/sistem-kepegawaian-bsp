@extends('layouts.approval')
@section('title', 'Approval Job Description')

@section('content')
@php
    $prefix = auth()->user()->role ?? 'hcm';

    $activeVersion = $jabatan->activeVersion;
    $pendingVersion = $jabatan->pendingVersion;

    $approvalStatus = $jabatan->approval_status ?? 'pending';
    $approvalFlowStatus = $jabatan->approval_flow_status ?? 'pending';

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

    $approvalDate = $formatTanggalIndonesia($jabatan->approved_at ?? null);
    $proposedApprovalDate = $formatTanggalIndonesia($jabatan->proposed_approved_at ?? null);
    $hcmConfirmedDate = $formatTanggalIndonesia($jabatan->hcm_confirmed_at ?? null);
    $lastUpdatedDate = $formatTanggalIndonesia($jabatan->jobdesk_updated_at ?? null);

    $approvalToken = $jabatan->approval_token ?? '';
    $qrUrl = $approvalToken ? route('jabatan.approval.qr', $jabatan->id_jabatan).'?v='.urlencode($approvalToken) : '';
@endphp

<div class="approval-panel">
    <div class="approval-panel-head">
        <div>
            <div class="approval-eyebrow">Job Description Approval</div>
            <h1 class="approval-title">Approval Job Description</h1>
            <p class="approval-desc">
                Halaman ini digunakan untuk melihat status approval, versi job description, QR approval, dan penerapan job description ke pegawai aktif.
            </p>
        </div>

        <div class="approval-actions">
            @if(in_array($prefix, ['admin', 'hcm'], true))
                <a href="{{ route($prefix.'.jabatan.show', $jabatan->id_jabatan) }}" class="approval-btn">
                    Kembali ke Detail Jabatan
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
                Link approval saat ini masih memakai alamat lokal/private. QR hanya bisa dibuka dari perangkat yang berada di jaringan yang sama.
                Agar bisa dibuka dari jaringan berbeda, gunakan domain publik, ngrok, Cloudflare Tunnel, atau server yang dapat diakses publik lalu isi <strong>APP_APPROVAL_URL</strong> di file <strong>.env</strong>.
            </div>
        @endif

        <div class="approval-alert info">
            Alur approval corporate: perubahan job description dibuat sebagai <strong>pending version</strong>.
            Jika approver login sebagai <strong>pegawai</strong>, approval akan dicatat terlebih dahulu dan menunggu pengesahan final dari HCM.
            Jika approver login sebagai <strong>HCM</strong>, approval langsung menjadi final.
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
                <div class="approval-card-title">Status Versi Job Description</div>
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
                            <th>Status Approval</th>
                            <td>
                                @if($approvalStatus === 'approved' && !$pendingVersion)
                                    <span class="approval-badge approved">Approved Final</span>
                                @elseif($approvalFlowStatus === 'waiting_hcm_confirmation')
                                    <span class="approval-badge pending">Menunggu Pengesahan HCM</span>
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
                            <th>Jabatan Approver</th>
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
                            <th>Waktu Pengesahan HCM</th>
                            <td>{{ $hcmConfirmedDate }}</td>
                        </tr>
                        <tr>
                            <th>Terakhir Diperbarui</th>
                            <td>{{ $lastUpdatedDate }}</td>
                        </tr>
                    </table>

                    @if($approvalFlowStatus === 'waiting_hcm_confirmation' && $pendingVersion && $prefix === 'hcm')
                        <form method="POST"
                              action="{{ route('jabatan.approval.confirm-final', ['jabatan' => $jabatan->id_jabatan, 'token' => $jabatan->approval_token]) }}"
                              style="margin-top:14px;"
                              onsubmit="return confirm('Sahkan approval pegawai ini sebagai approval final?');">
                            @csrf

                            <label class="approval-form-label">Catatan Pengesahan HCM <span style="color:#98a2b3;">(opsional)</span></label>
                            <textarea name="hcm_confirmation_catatan" class="approval-textarea" placeholder="Tambahkan catatan jika diperlukan..."></textarea>

                            <label class="approval-form-label" style="margin-top:14px;">Password HCM <span style="color:#dc2626;">*</span></label>
                            <input type="password"
                                   name="approval_password"
                                   class="approval-input"
                                   placeholder="Masukkan password HCM"
                                   autocomplete="current-password"
                                   required>

                            <button type="submit" class="approval-btn success" style="margin-top:14px;">
                                Sahkan Approval Final
                            </button>
                        </form>
                    @endif

                    @if($activeVersion && !$pendingVersion && in_array($prefix, ['admin', 'hcm'], true))
                        <form method="POST"
                              action="{{ route($prefix.'.jabatan.apply-approved-version', $jabatan->id_jabatan) }}"
                              style="margin-top:14px;">
                            @csrf
                            <button type="submit"
                                    class="approval-btn success"
                                    onclick="return confirm('Terapkan versi approved terbaru ke seluruh pegawai aktif yang memegang jabatan ini? Riwayat versi lama pegawai tetap disimpan.');">
                                Terapkan ke Pegawai Aktif
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="approval-grid-1" style="margin-top:18px;">
            <div class="approval-card">
                <div class="approval-card-title">
                    <span>QR Approval</span>
                    @if($approvalToken)
                        <a href="{{ route('jabatan.approval.qr', $jabatan->id_jabatan) }}" target="_blank" class="approval-btn" style="min-height:34px; padding:7px 12px; font-size:12px;">
                            Buka QR SVG
                        </a>
                    @endif
                </div>

                <div class="approval-card-body">
                    @if($approvalToken)
                        <div class="approval-qr-wrap">
                            <div class="approval-qr-box">
                                <img src="{{ $qrUrl }}" alt="QR Approval Job Description">
                            </div>

                            <div class="approval-note">
                                Scan QR ini menggunakan akun HCM atau pegawai yang berwenang.
                                Jika yang approve adalah pegawai, status akan menunggu pengesahan final dari HCM.
                            </div>

                            <div class="approval-link-row" style="width:100%; max-width:760px;">
                                <input type="text" id="approvalLinkInput" class="approval-input" value="{{ $approvalUrl }}" readonly>
                                <button type="button" class="approval-btn primary" onclick="copyApprovalLink()">Copy Link</button>
                            </div>
                        </div>
                    @else
                        <div class="approval-alert warning" style="margin-bottom:0;">
                            Token approval belum tersedia. Refresh halaman ini atau pastikan controller sudah membuat approval_token.
                        </div>
                    @endif
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
    if (!input) return;

    input.select();
    input.setSelectionRange(0, 99999);

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(input.value).then(function(){
            alert('Link approval berhasil disalin.');
        }).catch(function(){
            document.execCommand('copy');
            alert('Link approval berhasil disalin.');
        });
    } else {
        document.execCommand('copy');
        alert('Link approval berhasil disalin.');
    }
}
</script>
@endpush