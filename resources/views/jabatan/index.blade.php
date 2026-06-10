@extends('layouts.app')

@section('title', 'Daftar Jabatan')

@section('content')
@php
    $prefix = auth()->user()->role; // admin / hcm

    $formatTanggalIndonesia = function ($date) {
        if (!$date) {
            return '-';
        }

        try {
            return \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('d F Y H:i');
        } catch (\Throwable $e) {
            return '-';
        }
    };
@endphp

<div class="approval-page pt-2 pb-4">

    @if(session('success'))
        <div class="alert alert-success custom-alert d-none">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger rounded-4 shadow-sm">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif 

    <div class="page-header-card mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <div class="eyebrow">DATA JABATAN</div>
                <h2 class="page-title">Daftar Jabatan</h2>
                <p class="page-subtitle mb-0">
                    Kelola data jabatan perusahaan dengan tabel yang rapi, searchable, sortable, dan responsive.
                </p>
            </div>

            <a href="{{ route($prefix.'.jabatan.create') }}" class="btn-add">
                <span>+</span> Tambah Jabatan
            </a>
        </div>
    </div>

    <div class="card table-card">
        <div class="card-body">
            <div class="table-responsive mt-3">
                <table id="datatable" class="table table-bordered w-100 approval-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Jabatan</th>
                            <th>Departemen</th>
                            <th>Lokasi Kerja</th>
                            <th>Home Base</th>
                            <th>Terakhir Update</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($jabatans as $i => $jabatan)
                            @php
                                $activeVersion = $jabatan->activeVersion ?? null;

                                $lastUpdateSource = $activeVersion->approved_at
                                    ?? $jabatan->jobdesk_updated_at
                                    ?? $jabatan->approved_at
                                    ?? null;
                            @endphp

                            <tr>
                                <td class="text-center">{{ $i + 1 }}</td>

                                <td class="name-cell">
                                    <a href="{{ route($prefix.'.jabatan.show', $jabatan->id_jabatan) }}" class="jabatan-link">
                                        {{ $jabatan->nama_jabatan ?? '-' }}
                                    </a>
                                </td>

                                <td class="text-center">
                                    {{ $jabatan->departemen ?? '-' }}
                                </td>

                                <td class="text-center">
                                    {{ $jabatan->lokasi_kerja ?? '-' }}
                                </td>

                                <td class="text-center">
                                    <span class="soft-badge badge-pending">
                                        {{ $jabatan->home_base ?? '-' }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    {{ $formatTanggalIndonesia($lastUpdateSource) }}
                                </td>

                                <td class="text-center">
                                    <div class="action-group">
                                        <a href="{{ route($prefix.'.jabatan.edit', $jabatan->id_jabatan) }}"
                                           class="icon-btn icon-edit"
                                           title="Edit Jabatan">
                                            <svg class="action-icon" viewBox="0 0 24 24" fill="none">
                                                <path d="M12 20h9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                            </svg>
                                        </a>

                                        <a href="{{ route($prefix.'.jabatan.show', $jabatan->id_jabatan) }}"
                                           class="icon-btn icon-view"
                                           title="Lihat Detail">
                                            <svg class="action-icon" viewBox="0 0 24 24" fill="none">
                                                <path d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12 18.25 18.75 12 18.75 2.25 12 2.25 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M12 15.25A3.25 3.25 0 1 0 12 8.75a3.25 3.25 0 0 0 0 6.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </a>

                                        <form action="{{ route($prefix.'.jabatan.destroy', $jabatan->id_jabatan) }}"
                                              method="POST"
                                              class="delete-form d-inline"
                                              data-nama="{{ $jabatan->nama_jabatan }}">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="icon-btn icon-delete"
                                                    title="Hapus Jabatan">
                                                <svg class="action-icon" viewBox="0 0 24 24" fill="none">
                                                    <path d="M4 7h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                    <path d="M10 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                    <path d="M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                    <path d="M6 7l1 14h10l1-14" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                                    <path d="M9 7V4h6v3" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

<style>
    .approval-page {
        width: 100%;
        max-width: none;
        padding-left: 0;
        padding-right: 0;
        margin-top: -20px;
    }

    .page-header-card {
        background: #fbfcfa;
        border: 1px solid #eef1ec;
        padding: 20px 25px;
    }

    .eyebrow {
        color: #6b775c;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 2px;
        margin-bottom: 8px;
    }

    .page-title {
        color: #273957;
        font-size: 32px;
        font-weight: 700;
        letter-spacing: -.3px;
        margin-bottom: 8px;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 15px;
        font-weight: 400;
    }

    .btn-add {
        min-height: 42px;
        padding: 10px 18px;
        border-radius: 14px;
        background: #6b775c;
        color: #fff;
        text-decoration: none;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-add:hover {
        background: #59664b;
        color: #fff;
    }

    .table-card {
        border: 1px solid #edf0ea;
        border-radius: 0;
    }

    .table-card .card-body {
        padding: 20px 22px;
    }

    .approval-table {
        border-color: #e8ece5 !important;
        margin-bottom: 0 !important;
    }

    .approval-table thead th {
        background: #6b775c;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        padding: 15px 12px;
        text-align: center !important;
        border-color: rgba(255,255,255,.12) !important;
        vertical-align: middle;
        white-space: nowrap;
    }

    .approval-table tbody td {
        color: #111827;
        font-size: 13.5px;
        padding: 15px 12px;
        vertical-align: middle;
        border-color: #e8ece5 !important;
    }

    .approval-table tbody tr:hover {
        background: #fbfcfa;
    }

    .name-cell {
        font-weight: 600;
        line-height: 1.35;
    }

    .jabatan-link {
        color: #273957;
        text-decoration: none;
        font-weight: 700;
    }

    .jabatan-link:hover {
        color: #6b775c;
        text-decoration: underline;
    }

    .soft-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 30px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .badge-pending {
        background: #eef2eb;
        color: #536044;
    }

    .action-group {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .icon-btn {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: .2s ease;
        cursor: pointer;
        line-height: 1;
        color: #fff !important;
    }

    .action-icon {
        width: 19px;
        height: 19px;
        display: block;
        color: #fff;
    }

    .icon-edit {
        background: #f4c542 !important;
        color: #fff !important;
    }

    .icon-edit:hover {
        background: #d9aa24 !important;
        transform: translateY(-1px);
    }

    .icon-view {
        background: #332da1 !important;
    }

    .icon-view:hover {
        background: #282383 !important;
        transform: translateY(-1px);
    }

    .icon-delete {
        background: #ef4444 !important;
    }

    .icon-delete:hover {
        background: #dc2626 !important;
        transform: translateY(-1px);
    }

    .custom-alert {
        border-radius: 14px;
        border: none;
    }

    .dataTables_wrapper .row:first-child {
        align-items: center;
        margin-bottom: 14px;
    }

    .dataTables_length label,
    .dataTables_filter label,
    .dataTables_info {
        color: #6b7280;
        font-size: 13px;
        font-weight: 500;
    }

    .dataTables_length select {
        border: 1px solid #dfe5d8;
        border-radius: 10px;
        padding: 6px 28px 6px 10px;
        color: #374151;
        outline: none;
    }

    .dataTables_filter input {
        border: 1px solid #dfe5d8;
        border-radius: 14px;
        padding: 8px 14px;
        color: #374151;
        outline: none;
        margin-left: 8px;
        min-width: 240px;
    }

    .dataTables_filter input:focus,
    .dataTables_length select:focus {
        border-color: #6b775c;
    }

    .page-item .page-link {
        border: none;
        color: #59664b;
        font-size: 13px;
        border-radius: 10px;
        margin: 0 2px;
    }

    .page-item.active .page-link {
        background: #6b775c;
        color: #fff;
    }

    .page-item.disabled .page-link {
        color: #a1a8b0;
        background: transparent;
    }

    table.dataTable > thead .sorting:before,
    table.dataTable > thead .sorting:after,
    table.dataTable > thead .sorting_asc:before,
    table.dataTable > thead .sorting_asc:after,
    table.dataTable > thead .sorting_desc:before,
    table.dataTable > thead .sorting_desc:after {
        color: #fff;
        opacity: .75;
    }

    @media (max-width: 768px) {
        .page-header-card {
            padding: 22px 20px;
        }

        .page-title {
            font-size: 27px;
        }

        .dataTables_filter input {
            min-width: 100%;
            margin-left: 0;
            margin-top: 6px;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: @json(session('success')),
            confirmButtonText: 'OK',
            confirmButtonColor: '#6b775c'
        });
    });
</script>
@endif

@if(session('success_auto'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: @json(session('success_auto')),
            timer: 3500,
            timerProgressBar: true,
            showConfirmButton: false,
            confirmButtonColor: '#6b775c'
        });
    });
</script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('#datatable').DataTable({
            responsive: false,
            scrollX: true,
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [[0, 'asc']],
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                zeroRecords: "Data tidak ditemukan",
                emptyTable: "Belum ada jabatan",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "›",
                    previous: "‹"
                }
            },
            columnDefs: [
                { orderable: false, targets: [6] },
                { searchable: false, targets: [0, 6] }
            ]
        });

        document.querySelectorAll('.delete-form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const nama = form.dataset.nama || 'jabatan ini';

                Swal.fire({
                    icon: 'warning',
                    title: 'Hapus Jabatan?',
                    html: `
                        <div style="font-size:14px;color:#6b7280;line-height:1.6;">
                            Data jabatan <b>${nama}</b><br>
                            akan dihapus permanen.
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b775c',
                    reverseButtons: true,
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menghapus...',
                            text: 'Mohon tunggu sebentar.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endpush
