@extends('layouts.app')

@section('title', 'Daftar Jabatan')

@section('content')
@php
    $prefix = auth()->user()->role;
@endphp

<div class="approval-page pt-2 pb-4">

    @if(session('success'))
        <div class="alert alert-success custom-alert d-none">{{ session('success') }}</div>
    @endif

    @if(session('success_auto'))
        <div class="alert alert-success custom-alert d-none">{{ session('success_auto') }}</div>
    @endif

    @if(session('delete_error'))
        <div class="alert alert-warning custom-alert d-none">
            @if(is_array(session('delete_error')))
                {{ implode(' ', session('delete_error')) }}
            @else
                {{ session('delete_error') }}
            @endif
        </div>
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
            <div class="table-wrap mt-3">
                <table id="datatable-jabatan" class="table table-bordered w-100 approval-table">
                    <thead>
                        <tr>
                            <th data-priority="1" class="col-no">No</th>
                            <th data-priority="2" class="col-jabatan">Nama Jabatan</th>
                            <th data-priority="3" class="col-departemen">Departemen</th>
                            <th data-priority="5" class="col-lokasi">Lokasi Kerja</th>
                            <th data-priority="6" class="col-home">Home Base</th>
                            <th data-priority="4" class="col-aksi">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($jabatans as $i => $jabatan)
                            @php
                                $departemenNama = $jabatan->departemenMaster->nama_departemen
                                    ?? $jabatan->departemen
                                    ?? '-';
                            @endphp

                            <tr>
                                <td class="text-center col-no">{{ $i + 1 }}</td>

                                <td class="name-cell col-jabatan">
                                    <a href="{{ route($prefix.'.jabatan.show', $jabatan->id_jabatan) }}" class="jabatan-link">
                                        {{ $jabatan->nama_jabatan ?? '-' }}
                                    </a>
                                </td>

                                <td class="text-center col-departemen">
                                    {{ $departemenNama }}
                                </td>

                                <td class="text-center col-lokasi">
                                    {{ $jabatan->lokasi_kerja ?? '-' }}
                                </td>

                                <td class="text-center col-home">
                                    <span class="soft-badge badge-pending">
                                        {{ $jabatan->home_base ?? '-' }}
                                    </span>
                                </td>

                                <td class="text-center col-aksi">
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
        max-width: 100%;
        padding-left: 0;
        padding-right: 0;
        margin-top: -20px;
        overflow-x: hidden;
    }

    .page-header-card {
        background: #fbfcfa;
        border: 1px solid #eef1ec;
        padding: clamp(16px, 2vw, 25px);
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
        font-size: clamp(24px, 3vw, 32px);
        font-weight: 700;
        letter-spacing: -.3px;
        margin-bottom: 8px;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: clamp(13px, 1.4vw, 15px);
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
        white-space: nowrap;
    }

    .btn-add:hover {
        background: #59664b;
        color: #fff;
    }

    .table-card {
        border: 1px solid #edf0ea;
        border-radius: 0;
        overflow: visible;
    }

    .table-card .card-body {
        padding: clamp(12px, 2vw, 22px);
        overflow: visible;
    }

    .table-wrap {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
        overflow-y: visible;
    }

    .approval-table {
        width: 100% !important;
        max-width: 100% !important;
        table-layout: fixed;
        border-color: #e8ece5 !important;
        margin-bottom: 0 !important;
    }

    .approval-table thead th {
        background: #6b775c;
        color: #fff;
        font-size: clamp(11px, 1vw, 13px);
        font-weight: 600;
        padding: clamp(9px, 1.3vw, 15px) clamp(6px, 1vw, 12px);
        text-align: center !important;
        border-color: rgba(255,255,255,.12) !important;
        vertical-align: middle;
        white-space: normal;
        line-height: 1.25;
    }

    .approval-table tbody td {
        color: #111827;
        font-size: clamp(11.5px, 1vw, 13.5px);
        padding: clamp(9px, 1.3vw, 15px) clamp(6px, 1vw, 12px);
        vertical-align: middle;
        border-color: #e8ece5 !important;
        white-space: normal;
        word-break: normal;
        overflow-wrap: anywhere;
        line-height: 1.35;
    }

    .approval-table tbody tr:hover {
        background: #fbfcfa;
    }

    .col-no {
        width: 5%;
    }

    .col-jabatan {
        width: 28%;
    }

    .col-departemen {
        width: 23%;
    }

    .col-lokasi {
        width: 12%;
    }

    .col-home {
        width: 11%;
    }

    .col-aksi {
        width: 21%;
    }

    .name-cell {
        font-weight: 600;
        line-height: 1.35;
    }

    .jabatan-link {
        color: #273957;
        text-decoration: none;
        font-weight: 700;
        display: inline;
        line-height: 1.35;
    }

    .jabatan-link:hover {
        color: #6b775c;
        text-decoration: underline;
    }

    .soft-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: clamp(10.5px, 1vw, 12px);
        font-weight: 600;
        white-space: normal;
        line-height: 1.2;
        max-width: 100%;
        text-align: center;
    }

    .badge-pending {
        background: #eef2eb;
        color: #536044;
    }

    .action-group {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: clamp(5px, .8vw, 10px);
        flex-wrap: wrap;
        max-width: 100%;
    }

    .icon-btn {
        width: clamp(30px, 3vw, 38px);
        height: clamp(30px, 3vw, 38px);
        min-width: clamp(30px, 3vw, 38px);
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
        padding: 0;
    }

    .action-icon {
        width: clamp(15px, 1.5vw, 19px);
        height: clamp(15px, 1.5vw, 19px);
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

    .dataTables_wrapper {
        width: 100%;
        max-width: 100%;
        position: relative;
        z-index: 2;
        overflow-x: hidden;
    }

    .dataTables_wrapper .row:first-child,
    .dataTables_wrapper .row:last-child {
        width: 100%;
        max-width: 100%;
        margin-left: 0;
        margin-right: 0;
        align-items: center;
    }

    .dataTables_wrapper .row:first-child {
        margin-bottom: 14px;
    }

    .dataTables_wrapper .row:last-child {
        margin-top: 18px;
        position: relative;
        z-index: 5;
    }

    .dataTables_length,
    .dataTables_filter,
    .dataTables_info,
    .dataTables_paginate {
        max-width: 100%;
    }

    .dataTables_length label,
    .dataTables_filter label,
    .dataTables_info {
        color: #6b7280;
        font-size: clamp(11.5px, 1vw, 13px);
        font-weight: 500;
    }

    .dataTables_length select {
        border: 1px solid #dfe5d8;
        border-radius: 10px;
        padding: 6px 28px 6px 10px;
        color: #374151;
        outline: none;
    }

    .dataTables_filter {
        text-align: right;
    }

    .dataTables_filter input {
        border: 1px solid #dfe5d8;
        border-radius: 14px;
        padding: 8px 14px;
        color: #374151;
        outline: none;
        margin-left: 8px;
        width: min(240px, 100%);
        max-width: 100%;
    }

    .dataTables_filter input:focus,
    .dataTables_length select:focus {
        border-color: #6b775c;
    }

    .dataTables_paginate {
        position: relative;
        z-index: 10;
        pointer-events: auto;
    }

    .dataTables_paginate .pagination {
        margin-bottom: 0;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 4px;
    }

    .dataTables_paginate .page-item,
    .dataTables_paginate .page-link {
        pointer-events: auto;
        cursor: pointer;
    }

    .page-item .page-link {
        border: none;
        color: #59664b;
        font-size: clamp(11.5px, 1vw, 13px);
        border-radius: 10px;
        margin: 0 2px;
        min-width: 32px;
        text-align: center;
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

    table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control:before,
    table.dataTable.dtr-inline.collapsed > tbody > tr > th.dtr-control:before {
        background-color: #6b775c;
        top: 50%;
        transform: translateY(-50%);
    }

    @media (max-width: 1200px) {
        .col-no {
            width: 8%;
        }

        .col-jabatan {
            width: 33%;
        }

        .col-departemen {
            width: 25%;
        }

        .col-lokasi {
            width: 12%;
        }

        .col-home {
            width: 10%;
        }

        .col-aksi {
            width: 12%;
        }
    }

    @media (max-width: 992px) {
        .approval-table {
            table-layout: auto;
        }

        .table-wrap {
            overflow-x: hidden;
        }

        .dataTables_filter {
            text-align: left;
            margin-top: 10px;
        }

        .dataTables_filter input {
            margin-left: 0;
            margin-top: 6px;
            width: 100%;
        }
    }

    @media (max-width: 768px) {
        .page-header-card {
            padding: 20px 16px;
        }

        .btn-add {
            width: 100%;
            justify-content: center;
        }

        .dataTables_wrapper .row:first-child > div,
        .dataTables_wrapper .row:last-child > div {
            width: 100%;
            text-align: left;
            margin-bottom: 10px;
        }

        .dataTables_paginate .pagination {
            justify-content: center;
        }

        .dataTables_info {
            text-align: center;
            display: block;
        }

        .approval-table tbody td {
            font-size: 12px;
        }

        .action-group {
            justify-content: flex-start;
        }
    }

    @media (max-width: 576px) {
        .table-card .card-body {
            padding: 12px;
        }

        .approval-table thead th,
        .approval-table tbody td {
            padding: 9px 7px;
        }

        .icon-btn {
            width: 30px;
            height: 30px;
            min-width: 30px;
        }

        .action-icon {
            width: 15px;
            height: 15px;
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

@if(session('delete_error'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const messages = @json(session('delete_error'));

        Swal.fire({
            icon: 'warning',
            title: 'Jabatan Tidak Dapat Dihapus',
            html: Array.isArray(messages)
                ? messages.join('<br><br>')
                : messages,
            confirmButtonText: 'OK',
            confirmButtonColor: '#6b775c'
        });
    });
</script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableSelector = '#datatable-jabatan';

        if ($.fn.DataTable.isDataTable(tableSelector)) {
            $(tableSelector).DataTable().clear().destroy();
        }

        const table = $(tableSelector).DataTable({
            paging: true,
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "Semua"]
            ],
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false,
            scrollX: false,
            responsive: {
                details: {
                    type: 'inline',
                    target: 'tr'
                }
            },
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: [5] },
                { searchable: false, targets: [0, 5] },
                { responsivePriority: 1, targets: 1 },
                { responsivePriority: 2, targets: 5 },
                { responsivePriority: 3, targets: 2 },
                { responsivePriority: 4, targets: 0 },
                { responsivePriority: 5, targets: 3 },
                { responsivePriority: 6, targets: 4 }
            ],
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
            }
        });

        setTimeout(function () {
            table.columns.adjust().responsive.recalc();
        }, 150);

        window.addEventListener('resize', function () {
            table.columns.adjust().responsive.recalc();
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