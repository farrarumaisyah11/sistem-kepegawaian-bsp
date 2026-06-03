@extends('layouts.app')

@section('title', 'Pengajuan Perubahan Data Pegawai')

@section('content')
@php
    \Carbon\Carbon::setLocale('id');
    $pengajuanAktif = $pengajuanAktif ?? collect();
    $riwayatPengajuan = $riwayatPengajuan ?? collect();

    $totalBaru = $pengajuanAktif->whereIn('status', ['diajukan', 'pending', 'belum_diolah'])->count();
    $totalDiproses = $pengajuanAktif->where('status', 'diproses')->count();
    $totalDiterima = $riwayatPengajuan->whereIn('status', ['diterima', 'disetujui'])->count();
    $totalDitolak = $riwayatPengajuan->where('status', 'ditolak')->count();
@endphp

<div class="approval-page pt-2 pb-4">

    @if(session('success'))
        <div class="alert alert-success custom-alert d-none">{{ session('success') }}</div>
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
                <div class="eyebrow">APPROVAL</div>
                <h2 class="page-title">Pengajuan Perubahan</h2>
                <p class="page-subtitle mb-0">
                    Kelola pengajuan perubahan data pegawai. Data baru akan masuk ke draft terlebih dahulu dan baru diterapkan ke tabel utama setelah disetujui.
                </p>
            </div>
        </div>
    </div>

    {{-- SUMMARY --}}
    <div class="summary-grid mb-4">
        <div class="summary-card">
            <div class="summary-label">Baru Masuk</div>
            <div class="summary-value">{{ $totalBaru }}</div>
        </div>

        <div class="summary-card warning">
            <div class="summary-label">Sedang Diproses</div>
            <div class="summary-value">{{ $totalDiproses }}</div>
        </div>

        <div class="summary-card success">
            <div class="summary-label">Diterima</div>
            <div class="summary-value">{{ $totalDiterima }}</div>
        </div>

        <div class="summary-card danger">
            <div class="summary-label">Ditolak</div>
            <div class="summary-value">{{ $totalDitolak }}</div>
        </div>
    </div>

    {{-- CARD TABLE --}}
    <div class="card table-card">
        <div class="card-body">

            <div class="table-title-wrap mb-3">
                <div>
                    <h5 class="table-title mb-1">Data Pengajuan</h5>
                    <p class="table-subtitle mb-0">
                        Pengajuan aktif dipisahkan dari riwayat agar data lebih rapi dan mudah dipantau.
                    </p>
                </div>
            </div>

            {{-- TAB NAV --}}
            <ul class="nav nav-pills approval-tabs mb-3" id="pengajuanTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active"
                            id="aktif-tab"
                            data-bs-toggle="pill"
                            data-bs-target="#aktif-pane"
                            type="button"
                            role="tab"
                            aria-controls="aktif-pane"
                            aria-selected="true">
                        Pengajuan Aktif
                        <span>{{ $pengajuanAktif->count() }}</span>
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link"
                            id="riwayat-tab"
                            data-bs-toggle="pill"
                            data-bs-target="#riwayat-pane"
                            type="button"
                            role="tab"
                            aria-controls="riwayat-pane"
                            aria-selected="false">
                        Riwayat Selesai
                        <span>{{ $riwayatPengajuan->count() }}</span>
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="pengajuanTabsContent">

                {{-- ================= TAB 1: PENGAJUAN AKTIF ================= --}}
                <div class="tab-pane fade show active" id="aktif-pane" role="tabpanel" aria-labelledby="aktif-tab">
                    <div class="section-note mb-3">
                        <strong>Catatan:</strong> Data pada bagian ini masih berupa draft pengajuan dan belum masuk ke tabel utama pegawai.
                    </div>

                    <div class="table-responsive">
                        <table id="datatable-aktif" class="table table-bordered w-100 approval-table nowrap">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIP</th>
                                    <th>Nama</th>
                                    <th>Status</th>
                                    <th>Tanggal Masuk</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($pengajuanAktif as $i => $item)
                                    @php
                                        $nama = $item->pegawai->nama
                                            ?? ($item->nama_pegawai ?? data_get($item->payload, 'pegawai.nama', '-'));

                                        $statusClass = match($item->status) {
                                            'diajukan', 'pending', 'belum_diolah' => 'badge-pending',
                                            'diproses' => 'badge-process',
                                            'diterima', 'disetujui' => 'badge-success-custom',
                                            'ditolak' => 'badge-reject',
                                            default => 'badge-default',
                                        };
                                    @endphp

                                    <tr>
                                        <td class="text-center">{{ $i + 1 }}</td>

                                        <td class="fw-semibold text-center">
                                            {{ $item->nip }}
                                        </td>

                                        <td class="name-cell">
                                            {{ $nama }}
                                        </td>

                                        <td class="text-center">
                                            <span class="soft-badge {{ $statusClass }}">
                                                {{ $item->status_label }}
                                            </span>
                                        </td>

                                        @php
                                            $createdAtWib = $item->created_at
                                                ? \Illuminate\Support\Carbon::parse($item->created_at)->timezone('Asia/Jakarta')->locale('id')
                                                : null;
                                        @endphp

                                        <td class="text-center" data-order="{{ $createdAtWib ? $createdAtWib->format('YmdHis') : '' }}">
                                            <div class="date-main">{{ $createdAtWib ? $createdAtWib->translatedFormat('d F Y') : '-' }}</div>
                                            <div class="date-sub">{{ $createdAtWib ? $createdAtWib->format('H:i') . ' WIB' : '--:-- WIB' }}</div>
                                        </td>

                                        <td class="text-center">
                                            <div class="action-group">
                                                <a href="{{ route(auth()->user()->role.'.pengajuan.show', $item) }}"
                                                   class="icon-btn icon-view"
                                                   title="Lihat Detail"
                                                   aria-label="Lihat Detail">
                                                    <svg class="action-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12 18.25 18.75 12 18.75 2.25 12 2.25 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M12 15.25A3.25 3.25 0 1 0 12 8.75a3.25 3.25 0 0 0 0 6.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </a>

                                                <form action="{{ route(auth()->user()->role.'.pengajuan.destroy', $item) }}"
                                                      method="POST"
                                                      class="delete-form d-inline"
                                                      data-nip="{{ $item->nip }}"
                                                      data-nama="{{ $nama }}">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                            class="icon-btn icon-delete"
                                                            title="Hapus Pengajuan"
                                                            aria-label="Hapus Pengajuan">
                                                        <svg class="action-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
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

                {{-- ================= TAB 2: RIWAYAT PENGAJUAN ================= --}}
                <div class="tab-pane fade" id="riwayat-pane" role="tabpanel" aria-labelledby="riwayat-tab">
                    <div class="section-note history mb-3">
                        <strong>Catatan:</strong> Data pada bagian ini sudah selesai diproses, baik diterima maupun ditolak.
                    </div>

                    <div class="table-responsive">
                        <table id="datatable-riwayat" class="table table-bordered w-100 approval-table nowrap">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIP</th>
                                    <th>Nama</th>
                                    <th>Status</th>
                                    <th>Tanggal Masuk</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($riwayatPengajuan as $i => $item)
                                    @php
                                        $nama = $item->pegawai->nama
                                            ?? ($item->nama_pegawai ?? data_get($item->payload, 'pegawai.nama', '-'));

                                        $statusClass = match($item->status) {
                                            'diajukan', 'pending', 'belum_diolah' => 'badge-pending',
                                            'diproses' => 'badge-process',
                                            'diterima', 'disetujui' => 'badge-success-custom',
                                            'ditolak' => 'badge-reject',
                                            default => 'badge-default',
                                        };
                                    @endphp

                                    <tr>
                                        <td class="text-center">{{ $i + 1 }}</td>

                                        <td class="fw-semibold text-center">
                                            {{ $item->nip }}
                                        </td>

                                        <td class="name-cell">
                                            {{ $nama }}
                                        </td>

                                        <td class="text-center">
                                            <span class="soft-badge {{ $statusClass }}">
                                                {{ $item->status_label }}
                                            </span>
                                        </td>

                                        @php
                                            $createdAtWib = $item->created_at
                                                ? \Illuminate\Support\Carbon::parse($item->created_at)->timezone('Asia/Jakarta')->locale('id')
                                                : null;
                                        @endphp

                                        <td class="text-center" data-order="{{ $createdAtWib ? $createdAtWib->format('YmdHis') : '' }}">
                                            <div class="date-main">{{ $createdAtWib ? $createdAtWib->translatedFormat('d F Y') : '-' }}</div>
                                            <div class="date-sub">{{ $createdAtWib ? $createdAtWib->format('H:i') . ' WIB' : '--:-- WIB' }}</div>
                                        </td>

                                        <td class="text-center">
                                            <div class="action-group">
                                                <a href="{{ route(auth()->user()->role.'.pengajuan.show', $item) }}"
                                                   class="icon-btn icon-view"
                                                   title="Lihat Detail"
                                                   aria-label="Lihat Detail">
                                                    <svg class="action-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12 18.25 18.75 12 18.75 2.25 12 2.25 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M12 15.25A3.25 3.25 0 1 0 12 8.75a3.25 3.25 0 0 0 0 6.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </a>

                                                <form action="{{ route(auth()->user()->role.'.pengajuan.destroy', $item) }}"
                                                      method="POST"
                                                      class="delete-form d-inline"
                                                      data-nip="{{ $item->nip }}"
                                                      data-nama="{{ $nama }}">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                            class="icon-btn icon-delete"
                                                            title="Hapus Pengajuan"
                                                            aria-label="Hapus Pengajuan">
                                                        <svg class="action-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
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
        color: #3f4a32;
        font-size: 32px;
        font-weight: 700;
        letter-spacing: -.3px;
        margin-bottom: 8px;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 15px;
        font-weight: 400;
        max-width: 860px;
        line-height: 1.6;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .summary-card {
        background: #ffffff;
        border: 1px solid #edf0ea;
        padding: 16px 18px;
        border-radius: 16px;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .04);
        border-left: 5px solid #6b775c;
    }

    .summary-card.warning {
        border-left-color: #f4c542;
    }

    .summary-card.success {
        border-left-color: #22c55e;
    }

    .summary-card.danger {
        border-left-color: #ef4444;
    }

    .summary-label {
        font-size: 12px;
        color: #6b7280;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .summary-value {
        font-size: 28px;
        line-height: 1;
        color: #3f4a32;
        font-weight: 800;
    }

    .table-card {
        border: 1px solid #edf0ea;
        border-radius: 0;
        overflow: hidden;
    }

    .table-card .card-body {
        padding: 20px 22px;
    }

    .table-title {
        color: #3f4a32;
        font-size: 18px;
        font-weight: 700;
    }

    .table-subtitle {
        color: #6b7280;
        font-size: 13px;
        line-height: 1.5;
    }

    .approval-tabs {
        background: #f4f6f2;
        border: 1px solid #e7ece3;
        border-radius: 16px;
        padding: 8px;
        gap: 8px;
        display: inline-flex;
        width: auto;
        max-width: 100%;
    }

    .approval-tabs .nav-link {
        border-radius: 12px;
        color: #59664b;
        font-size: 13px;
        font-weight: 700;
        padding: 10px 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none;
        background: transparent;
        white-space: nowrap;
    }

    .approval-tabs .nav-link span {
        min-width: 24px;
        height: 24px;
        padding: 0 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: rgba(107, 119, 92, .12);
        color: #59664b;
        font-size: 12px;
    }

    .approval-tabs .nav-link.active {
        background: #6b775c;
        color: #fff;
    }

    .approval-tabs .nav-link.active span {
        background: rgba(255,255,255,.2);
        color: #fff;
    }

    .section-note {
        border: 1px solid #fde68a;
        background: #fffbeb;
        color: #7b5a00;
        border-radius: 14px;
        padding: 12px 14px;
        font-size: 13px;
        line-height: 1.5;
    }

    .section-note.history {
        border-color: #dbe6d5;
        background: #f7faf5;
        color: #536044;
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
        white-space: nowrap;
    }

    .approval-table tbody tr:hover {
        background: #fbfcfa;
    }

    .name-cell {
        font-weight: 600;
        line-height: 1.35;
        white-space: normal !important;
        min-width: 220px;
    }

    .soft-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 30px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
        border: 1px solid transparent;
        letter-spacing: .02em;
    }

    /* Status dibuat lebih berbeda agar mudah dibaca */
    .badge-pending {
        background: #e8eef5;
        color: #273957;
        border-color: #b9c8dc;
    }

    .badge-process {
        background: #fff3c4;
        color: #7a5200;
        border-color: #f3c94b;
    }

    .badge-success-custom {
        background: #e7f6ec;
        color: #1f7a3a;
        border-color: #9bd6aa;
    }

    .badge-reject {
        background: #fde8e8;
        color: #b42318;
        border-color: #f3b3b0;
    }

    .badge-default {
        background: #f3f4f6;
        color: #374151;
        border-color: #d1d5db;
    }

    .date-main {
        font-weight: 700;
        color: #111827;
    }

    .date-sub {
        font-size: 12px;
        color: #6b7280;
        margin-top: 2px;
    }

    .action-group {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
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

    /* Icon aksi lihat detail dikembalikan ke navy seperti sebelumnya */
    .icon-view {
        background: #273957 !important;
    }

    .icon-view:hover {
        background: #1f2f49 !important;
        color: #fff !important;
        transform: translateY(-1px);
    }

    .icon-delete {
        background: #ef4444 !important;
    }

    .icon-delete:hover {
        background: #dc2626 !important;
        color: #fff !important;
        transform: translateY(-1px);
    }

    .custom-alert {
        border-radius: 14px;
        border: none;
    }

    .dataTables_wrapper {
        width: 100%;
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

    .dataTables_scrollHeadInner,
    .dataTables_scrollHeadInner table,
    .dataTables_scrollBody table {
        width: 100% !important;
    }

    .dataTables_scrollBody {
        border-bottom: 1px solid #e8ece5 !important;
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

        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
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

        .summary-grid {
            grid-template-columns: 1fr;
        }

        .approval-tabs {
            width: 100%;
            display: flex;
        }

        .approval-tabs .nav-item {
            flex: 1;
        }

        .approval-tabs .nav-link {
            width: 100%;
            justify-content: center;
            font-size: 12px;
            padding: 10px 8px;
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
            timer: 5000,
            timerProgressBar: true,
            showConfirmButton: false
        });
    });
</script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dataTableLanguage = {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Data tidak ditemukan",
            emptyTable: "Belum ada data",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "›",
                previous: "‹"
            }
        };

        const tableAktif = $('#datatable-aktif').DataTable({
            responsive: false,
            scrollX: true,
            autoWidth: false,
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [[4, 'desc']],
            language: {
                ...dataTableLanguage,
                emptyTable: "Belum ada pengajuan aktif"
            },
            columnDefs: [
                { orderable: false, targets: [5] },
                { searchable: false, targets: [0, 5] },
                { width: "60px", targets: 0 },
                { width: "120px", targets: 1 },
                { width: "240px", targets: 2 },
                { width: "150px", targets: 3 },
                { width: "150px", targets: 4 },
                { width: "120px", targets: 5 }
            ]
        });

        const tableRiwayat = $('#datatable-riwayat').DataTable({
            responsive: false,
            scrollX: true,
            autoWidth: false,
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [[4, 'desc']],
            language: {
                ...dataTableLanguage,
                emptyTable: "Belum ada riwayat pengajuan"
            },
            columnDefs: [
                { orderable: false, targets: [5] },
                { searchable: false, targets: [0, 5] },
                { width: "60px", targets: 0 },
                { width: "120px", targets: 1 },
                { width: "240px", targets: 2 },
                { width: "150px", targets: 3 },
                { width: "150px", targets: 4 },
                { width: "120px", targets: 5 }
            ]
        });

        document.querySelectorAll('button[data-bs-toggle="pill"]').forEach(function (tabButton) {
            tabButton.addEventListener('shown.bs.tab', function () {
                setTimeout(function () {
                    tableAktif.columns.adjust();
                    tableRiwayat.columns.adjust();
                }, 150);
            });
        });

        document.querySelectorAll('.delete-form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const nip = form.dataset.nip || '-';
                const nama = form.dataset.nama || 'pegawai ini';

                Swal.fire({
                    icon: 'warning',
                    title: 'Hapus Pengajuan?',
                    html: `
                        <div style="font-size:14px;color:#6b7280;line-height:1.6;">
                            Pengajuan perubahan data milik <b>${nama}</b><br>
                            dengan NIP <b>${nip}</b> akan dihapus permanen.
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#c5a059',
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
