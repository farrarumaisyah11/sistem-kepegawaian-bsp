@extends('layouts.app')
@section('title', 'Approval Job Description')

@section('content')
@php
    $prefix = auth()->user()->role;
    $approvalStatus = $jabatan->approval_status ?? 'pending';

    $approvalDate = '-';
    if (!empty($jabatan->approved_at)) {
        try {
            $approvalDate = \Illuminate\Support\Carbon::parse($jabatan->approved_at)->format('d-m-Y H:i');
        } catch (\Throwable $e) {
            $approvalDate = $jabatan->approved_at;
        }
    }

    $approvalToken = $jabatan->approval_token ?? '';

    /*
        Link ini yang akan masuk ke QR.
        Kalau mau discan dari HP, nanti APP_URL di .env harus pakai IP laptop,
        bukan 127.0.0.1.
    */
    $approvalUrl = $approvalToken
        ? url('/jabatan/'.$jabatan->id_jabatan.'/approval/'.$approvalToken)
        : '';
@endphp

<div class="approval-page">
    <div class="container py-4">

        <div class="approval-header mb-4">
            <div>
                <div class="approval-eyebrow">JOB DESCRIPTION APPROVAL</div>
                <h3 class="approval-title mb-1">Approval Job Description</h3>
                <p class="text-muted mb-0">
                    Halaman ini digunakan untuk melihat status approval dan QR approval dokumen jabatan.
                </p>
            </div>

            <a href="{{ route($prefix.'.jabatan.show', $jabatan->id_jabatan) }}"
               class="btn btn-outline-secondary rounded-4">
                <i class="bi bi-arrow-left"></i> Kembali ke Detail Jabatan
            </a>
        </div>

        <div class="row g-4">
            {{-- DATA JABATAN --}}
            <div class="col-lg-6">
                <div class="approval-card">
                    <div class="approval-card-header">
                        <i class="bi bi-briefcase"></i>
                        Data Jabatan
                    </div>

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
            </div>

            {{-- STATUS APPROVAL --}}
            <div class="col-lg-6">
                <div class="approval-card">
                    <div class="approval-card-header">
                        <i class="bi bi-shield-check"></i>
                        Status Approval
                    </div>

                    <div class="approval-card-body">
                        <table class="approval-table">
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if($approvalStatus === 'approved')
                                        <span class="approval-badge approved">Approved</span>
                                    @else
                                        <span class="approval-badge pending">Pending Approval</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Disetujui Oleh</th>
                                <td>{{ $jabatan->approved_by_name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Role Approver</th>
                                <td>{{ $jabatan->approved_by_role ? strtoupper($jabatan->approved_by_role) : '-' }}</td>
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
                                <th>Tanggal Approval</th>
                                <td>{{ $approvalDate }}</td>
                            </tr>
                            <tr>
                                <th>Catatan</th>
                                <td>{{ $jabatan->approval_catatan ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- QR APPROVAL --}}
            <div class="col-lg-12">
                <div class="approval-card">
                    <div class="approval-card-header">
                        <i class="bi bi-qr-code"></i>
                        QR Approval
                    </div>

                    <div class="approval-qr-body">
                        <div class="approval-qr-box">

                            @if($approvalToken)
                                <div id="approvalQrCode" class="approval-qr-img"></div>

                                <div class="approval-qr-note">
                                    Scan QR ini menggunakan handphone approver. Setelah scan,
                                    sistem akan membuka halaman approval.
                                </div>

                                <div class="approval-qr-warning">
                                    QR ini hanya berisi link token approval, bukan data sensitif pegawai atau jabatan.
                                </div>

                                <div class="approval-link-box mt-3">
                                    <div class="small text-muted mb-1">Link Approval:</div>
                                    <input type="text"
                                           class="form-control approval-link-input"
                                           value="{{ $approvalUrl }}"
                                           readonly>
                                </div>
                            @else
                                <div class="approval-empty-qr">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <h5>Token approval belum tersedia</h5>
                                    <p>Refresh halaman ini atau pastikan controller sudah membuat approval_token.</p>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.approval-page{
    min-height:100vh;
    background:#f6f8f4;
}

.approval-header{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:16px;
    flex-wrap:wrap;
}

.approval-eyebrow{
    color:#6b775c;
    font-size:11px;
    font-weight:800;
    letter-spacing:2px;
    margin-bottom:8px;
}

.approval-title{
    font-weight:800;
    color:#27351e;
}

.approval-card{
    background:#fff;
    border:1px solid #d7dfcc;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 12px 30px rgba(30,41,59,.06);
}

.approval-card-header{
    background:#e7eddc;
    color:#27351e;
    font-weight:800;
    padding:14px 18px;
    display:flex;
    align-items:center;
    gap:8px;
    border-bottom:1px solid #d7dfcc;
}

.approval-card-body{
    padding:18px;
}

.approval-table{
    width:100%;
    border-collapse:collapse;
}

.approval-table th,
.approval-table td{
    border:1px solid #d7dfcc;
    padding:12px 14px;
    vertical-align:top;
    font-size:14px;
}

.approval-table th{
    width:38%;
    background:#f7f9f2;
    color:#344054;
    font-weight:800;
}

.approval-table td{
    font-weight:600;
    color:#111827;
}

.approval-badge{
    display:inline-flex;
    padding:6px 14px;
    border-radius:999px;
    font-size:12px;
    font-weight:800;
}

.approval-badge.approved{
    background:#dcfce7;
    color:#166534;
    border:1px solid #86efac;
}

.approval-badge.pending{
    background:#fef3c7;
    color:#92400e;
    border:1px solid #fde68a;
}

.approval-qr-body{
    padding:32px 18px;
    display:flex;
    justify-content:center;
}

.approval-qr-box{
    max-width:520px;
    width:100%;
    text-align:center;
}

.approval-qr-img{
    width:260px;
    height:260px;
    margin:0 auto;
    padding:14px;
    background:#fff;
    border:1px solid #d7dfcc;
    border-radius:18px;
    display:flex;
    align-items:center;
    justify-content:center;
}

.approval-qr-img canvas,
.approval-qr-img img{
    max-width:100%;
    max-height:100%;
}

.approval-qr-note{
    margin-top:16px;
    font-size:14px;
    color:#475467;
    line-height:1.6;
}

.approval-qr-warning{
    margin:14px auto 0;
    font-size:12px;
    color:#667085;
    background:#f7f9f2;
    border:1px solid #d7dfcc;
    border-radius:12px;
    padding:10px 12px;
    max-width:420px;
}

.approval-link-box{
    max-width:520px;
    margin:0 auto;
}

.approval-link-input{
    border-radius:12px;
    font-size:13px;
    text-align:center;
}

.approval-empty-qr{
    border:1px dashed #d7dfcc;
    border-radius:18px;
    padding:28px 20px;
    background:#fbfcf8;
    color:#667085;
}

.approval-empty-qr i{
    font-size:36px;
    color:#92400e;
    margin-bottom:10px;
}

.approval-empty-qr h5{
    font-weight:800;
    color:#27351e;
    margin-bottom:6px;
}

.approval-empty-qr p{
    margin-bottom:0;
}

@media(max-width:768px){
    .approval-header{
        flex-direction:column;
    }

    .approval-table th,
    .approval-table td{
        display:block;
        width:100%;
    }

    .approval-table th{
        border-bottom:none;
    }

    .approval-qr-img{
        width:220px;
        height:220px;
    }
}
</style>

@if($approvalToken)
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const qrTarget = document.getElementById('approvalQrCode');
        const approvalUrl = @json($approvalUrl);

        if (qrTarget && approvalUrl) {
            qrTarget.innerHTML = '';

            new QRCode(qrTarget, {
                text: approvalUrl,
                width: 220,
                height: 220,
                colorDark: '#111827',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
        }
    });
    </script>
@endif
@endsection