@extends('layouts.app')
@section('title', 'Approval Job Description')

@section('content')
@php
    $namaApprover = $pegawaiApprover->nama ?? $user->username ?? 'Approver';
    $jabatanApprover = $pegawaiApprover->jabatan ?? strtoupper($user->role);
    $departemenApprover = $pegawaiApprover->departemen ?? '-';
@endphp

<div class="container py-4">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <div class="mb-4">
                <h4 class="fw-bold mb-1">Approval Job Description</h4>
                <p class="text-muted mb-0">
                    Pastikan data jabatan sudah sesuai sebelum melakukan approval.
                </p>
            </div>

            <div class="alert alert-info rounded-4">
                Approval akan tersimpan otomatis berdasarkan akun yang sedang login.
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="border rounded-4 p-3 h-100">
                        <h6 class="fw-bold mb-3">Data Jabatan</h6>

                        <div class="mb-2">
                            <small class="text-muted">Nama Jabatan</small>
                            <div class="fw-semibold">{{ $jabatan->nama_jabatan ?? '-' }}</div>
                        </div>

                        <div class="mb-2">
                            <small class="text-muted">Departemen</small>
                            <div class="fw-semibold">{{ $jabatan->departemen ?? '-' }}</div>
                        </div>

                        <div class="mb-2">
                            <small class="text-muted">Golongan Jabatan</small>
                            <div class="fw-semibold">{{ $jabatan->gol_jabatan ?? '-' }}</div>
                        </div>

                        <div class="mb-2">
                            <small class="text-muted">Lokasi Kerja</small>
                            <div class="fw-semibold">{{ $jabatan->lokasi_kerja ?? '-' }}</div>
                        </div>

                        <div>
                            <small class="text-muted">Status Approval</small>
                            <div>
                                @if($jabatan->approval_status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pending Approval</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded-4 p-3 h-100">
                        <h6 class="fw-bold mb-3">Data Approver</h6>

                        <div class="mb-2">
                            <small class="text-muted">Nama Approver</small>
                            <div class="fw-semibold">{{ $namaApprover }}</div>
                        </div>

                        <div class="mb-2">
                            <small class="text-muted">Role</small>
                            <div class="fw-semibold">{{ strtoupper($user->role) }}</div>
                        </div>

                        <div class="mb-2">
                            <small class="text-muted">Jabatan Approver</small>
                            <div class="fw-semibold">{{ $jabatanApprover }}</div>
                        </div>

                        <div>
                            <small class="text-muted">Departemen Approver</small>
                            <div class="fw-semibold">{{ $departemenApprover }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if($jabatan->approval_status === 'approved')
                <div class="alert alert-success rounded-4">
                    Job description ini sudah di-approve oleh
                    <strong>{{ $jabatan->approved_by_name }}</strong>
                    pada
                    <strong>{{ optional($jabatan->approved_at)->format('d-m-Y H:i') }}</strong>.
                </div>
            @else
                <form action="{{ route('jabatan.approval.approve', [$jabatan->id_jabatan, $token]) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan Approval</label>
                        <textarea name="approval_catatan"
                                  class="form-control rounded-4"
                                  rows="3"
                                  placeholder="Opsional, tuliskan catatan approval jika diperlukan.">{{ old('approval_catatan') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary rounded-4">
                            Batal
                        </a>

                        <button type="submit" class="btn btn-success rounded-4">
                            Approve Job Description
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection