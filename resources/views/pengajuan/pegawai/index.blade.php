@extends('layouts.app')

@section('title', 'Riwayat Pengajuan Perubahan')

@section('content')
@php
    /*
        Aman untuk Collection maupun Paginator.
        Kalau controller pakai get(), DataTables akan bekerja penuh.
        Kalau controller pakai paginate(), nomor tetap aman.
    */
    $isPaginator = method_exists($list, 'hasPages');

    $startNumber = ($isPaginator && method_exists($list, 'firstItem') && $list->firstItem())
        ? $list->firstItem()
        : 1;
@endphp

<div class="approval-page pt-2 pb-4">

    @if(session('success'))
        <div class="alert alert-success custom-alert d-none">{{ session('success') }}</div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning custom-alert">
            {{ session('warning') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger custom-alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- HEADER --}}
    <div class="page-header-card mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <div class="eyebrow">RIWAYAT PENGAJUAN</div>
                <h2 class="page-title">Pengajuan Perubahan Data</h2>
                <p class="page-subtitle mb-0">
                    Kelola riwayat permintaan perubahan data pegawai Anda dengan tabel yang rapi, searchable, sortable, dan responsive.
                </p>
            </div>

            <a href="{{ route('pegawai.pengajuan.create') }}" class="btn-add">
                <span>+</span> Tambah Pengajuan
            </a>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="card table-card">
        <div class="card-body">
            <div class="table-title-wrap">
                <div>
                    <h5 class="table-title mb-1">Riwayat Pengajuan</h5>
                    <p class="table-subtitle mb-0">
                        Gunakan search bawaan DataTables untuk mencari status, tanggal, atau rincian perubahan.
                    </p>
                </div>
            </div>

            <div class="table-responsive mt-3">
                <table id="datatable" class="table table-bordered w-100 approval-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Rincian Perubahan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($list as $index => $row)
                            @php
                                $statusClass = match($row->status) {
                                    'diterima', 'disetujui' => 'badge-success-custom',
                                    'ditolak'               => 'badge-reject',
                                    'diproses'              => 'badge-process',
                                    'pending', 'belum_diolah', 'diajukan' => 'badge-pending',
                                    default                 => 'badge-default',
                                };

                                $statusLabel = $row->status_label ?? match($row->status) {
                                    'pending', 'belum_diolah', 'diajukan' => 'Baru Masuk',
                                    'diproses'                            => 'Diproses',
                                    'diterima', 'disetujui'               => 'Diterima',
                                    'ditolak'                             => 'Ditolak',
                                    default                               => strtoupper(str_replace('_', ' ', $row->status)),
                                };

                                $payload = $row->payload;

                                if (is_string($payload)) {
                                    $payload = json_decode($payload, true) ?: [];
                                }

                                if (!is_array($payload)) {
                                    $payload = [];
                                }

                                $sectionLabels = [
                                    'pegawai'    => 'Informasi Pribadi',
                                    'pendidikan' => 'Pendidikan',
                                    'kursus'     => 'Kursus',
                                    'peng_bsp'   => 'Pengalaman BSP',
                                    'peng_luar'  => 'Pengalaman Luar',
                                    'keluarga'   => 'Keluarga',
                                    'penilaian'  => 'Penilaian',
                                ];

                                $filledSections = [];

                                foreach ($sectionLabels as $key => $label) {
                                    if (!empty($payload[$key])) {
                                        $filledSections[] = $label;
                                    }
                                }
                            @endphp

                            <tr>
                                <td class="text-center">
                                    {{ $startNumber + $index }}
                                </td>

                                <td class="text-center" data-order="{{ optional($row->created_at)->format('YmdHis') }}">
                                    <div class="date-main">
                                        {{ optional($row->created_at)->format('d/m/Y') ?? '-' }}
                                    </div>
                                    <div class="date-sub">
                                        {{ optional($row->created_at)->format('H:i') ?? '--:--' }} WIB
                                    </div>
                                </td>

                                <td class="text-center">
                                    <span class="soft-badge {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td>
                                    @if(count($filledSections) > 0)
                                        <div class="change-wrap">
                                            @foreach($filledSections as $section)
                                                <span class="change-tag">
                                                    {{ $section }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted small fst-italic">
                                            Tidak ada rincian data
                                        </span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <a href="{{ route('pegawai.pengajuan.show', $row->getKey()) }}"
                                       class="icon-btn icon-view"
                                       title="Lihat Detail"
                                       aria-label="Lihat Detail">
                                        <svg class="action-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12 18.25 18.75 12 18.75 2.25 12 2.25 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M12 15.25A3.25 3.25 0 1 0 12 8.75a3.25 3.25 0 0 0 0 6.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($isPaginator && $list->hasPages())
                <div class="pagination-card mt-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="text-muted small">
                            Menampilkan {{ $list->firstItem() }} sampai {{ $list->lastItem() }} dari {{ $list->total() }} data
                        </div>

                        <div>
                            {{ $list->links() }}
                        </div>
                    </div>
                </div>
            @endif
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
        line-height: 1.6;
        max-width: 850px;
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
    }

    .table-card .card-body {
        padding: 20px 22px;
    }

    .table-title {
        color: #273957;
        font-size: 18px;
        font-weight: 700;
    }

    .table-subtitle {
        color: #6b7280;
        font-size: 13px;
        line-height: 1.5;
    }

    .approval-table {
        border-color: #e8ece5 !important;
        margin-bottom: 0 !important;
        width: 100% !important;
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

    .date-main {
        font-weight: 700;
        color: #111827;
        white-space: nowrap;
    }

    .date-sub {
        font-size: 12px;
        color: #6b7280;
        margin-top: 2px;
        white-space: nowrap;
    }

    .soft-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 30px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .badge-pending {
        background: #eef2eb;
        color: #536044;
    }

    .badge-process {
        background: #fff2c6;
        color: #7b5a00;
    }

    .badge-success-custom {
        background: #eaf5e7;
        color: #2f6b32;
    }

    .badge-reject {
        background: #ffe5e5;
        color: #b91c1c;
    }

    .badge-default {
        background: #f3f4f6;
        color: #374151;
    }

    .change-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .change-tag {
        background: #eef2eb;
        color: #536044;
        font-size: 12px;
        padding: 6px 10px;
        border-radius: 999px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
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
        padding: 0;
    }

    .action-icon {
        width: 19px;
        height: 19px;
        display: block;
        color: #fff;
    }

    .icon-view {
        background: #332da1 !important;
    }

    .icon-view:hover {
        background: #282383 !important;
        color: #fff !important;
        transform: translateY(-1px);
    }

    .custom-alert {
        border-radius: 14px;
        border: none;
    }

    .pagination-card {
        background: #fff;
        border-top: 1px solid #edf2f7;
        padding-top: 14px;
    }

    .dataTables_wrapper .row:first-child {
        align-items: center;
        margin-bottom: 14px;
    }

    .dataTables_wrapper .row:last-child {
        align-items: center;
        margin-top: 14px;
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
        min-height: 38px;
    }

    .dataTables_filter input {
        border: 1px solid #dfe5d8;
        border-radius: 14px;
        padding: 8px 14px;
        color: #374151;
        outline: none;
        margin-left: 8px;
        min-width: 240px;
        min-height: 38px;
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

    @media (max-width: 1200px) {
        .approval-page {
            padding-left: 24px;
            padding-right: 24px;
        }
    }

    @media (max-width: 768px) {
        .approval-page {
            padding-left: 12px;
            padding-right: 12px;
            margin-top: -12px;
        }

        .page-header-card {
            padding: 22px 18px;
        }

        .page-title {
            font-size: 27px;
        }

        .btn-add {
            width: 100%;
            justify-content: center;
        }

        .table-card .card-body {
            padding: 16px;
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('#datatable').DataTable({
            responsive: false,
            scrollX: true,
            autoWidth: false,

            // Kalau $list masih dari paginate(), pagination Laravel tetap muncul.
            // Kalau controller pakai get(), DataTables akan pakai pagination/search bawaan.
            paging: @json(!$isPaginator),
            searching: @json(!$isPaginator),
            info: @json(!$isPaginator),
            lengthChange: @json(!$isPaginator),

            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [[1, 'desc']],
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                zeroRecords: "Data tidak ditemukan",
                emptyTable: "Belum ada riwayat pengajuan",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "›",
                    previous: "‹"
                }
            },
            columnDefs: [
                { orderable: false, targets: [4] },
                { searchable: false, targets: [0, 4] },
                { width: "60px", targets: 0 },
                { width: "160px", targets: 1 },
                { width: "150px", targets: 2 },
                { width: "420px", targets: 3 },
                { width: "100px", targets: 4 }
            ]
        });
    });
</script>
@endpush