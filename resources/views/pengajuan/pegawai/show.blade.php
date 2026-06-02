@extends('layouts.app')

@section('title', 'Detail Pengajuan Perubahan')

@section('content')
<style>
    /* Palette Warna Berdasarkan Moodboard */
    :root {
        --bsp-sage: #4A5D45;
        --bsp-sage-light: #5c6d58;
        --bsp-gold: #C5A059; /* Gold yang lebih elegan */
        --bsp-cream: #F8F9FA;
        --bsp-text: #2D3436;
    }

    body {
        background-color: #F4F6F3; /* Background abu-hijau sangat muda */
        color: var(--bsp-text);
        font-family: 'Inter', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    }

    .card-custom {
        border: none;
        border-top: 5px solid var(--bsp-gold); /* Aksen emas di atas */
        border-radius: 15px;
        overflow: hidden;
    }

    .section-divider {
        height: 2px;
        background: linear-gradient(to right, var(--bsp-gold), transparent);
        margin-bottom: 1.5rem;
        width: 100px;
    }

    .table-bsp thead {
        background-color: var(--bsp-sage);
        color: white;
    }

    .table-bsp th {
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.8rem;
        border: none;
    }

    .badge-status {
        font-weight: 600;
        padding: 0.6rem 1.2rem;
        border-radius: 50px;
        letter-spacing: 0.5px;
    }

    .label-muted {
        color: #888;
        font-size: 0.85rem;
        text-transform: uppercase;
        font-weight: 600;
    }

    .val-new { color: var(--bsp-sage); font-weight: 700; }
    .val-old { color: #A0AEC0; font-style: italic; }

    .btn-bsp-outline {
        border: 2px solid var(--bsp-sage);
        color: var(--bsp-sage);
        font-weight: 600;
        transition: 0.3s;
    }

    .btn-bsp-outline:hover {
        background-color: var(--bsp-sage);
        color: white;
    }
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0" style="color: var(--bsp-sage);">Informasi Detail Pengajuan</h3>
            <p class="text-muted">Kelola dan tinjau riwayat perubahan data Anda</p>
        </div>
        <a href="{{ route('pegawai.pengajuan.index') }}" class="btn btn-bsp-outline rounded-pill px-4">
            <i class="bi bi-chevron-left me-2"></i> Kembali
        </a>
    </div>

    <div class="card card-custom shadow-sm">
        <div class="card-body p-5">
            <div class="row align-items-center mb-5">
                <div class="col-md-7">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light p-3 rounded-3 me-3">
                            <i class="bi bi-file-earmark-text text-primary" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold">Pengajuan Perubahan Data</h5>
                            <span class="text-muted">ID: #{{ $pengajuan->id_pengajuan }} • Diajukan pada {{ $pengajuan->created_at->format('d M Y, H:i') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 text-md-end">
                    @php
                        $statusClass = match($pengajuan->status) {
                            'diterima' => 'bg-success text-white',
                            'ditolak' => 'bg-danger text-white',
                            'diproses' => 'bg-warning text-dark',
                            default => 'bg-secondary text-white'
                        };
                    @endphp
                    <span class="badge badge-status {{ $statusClass }} shadow-sm">
                        <i class="bi bi-dot me-1"></i> {{ strtoupper(str_replace('_', ' ', $pengajuan->status)) }}
                    </span>
                </div>
            </div>

            @if($pengajuan->catatan_admin)
            <div class="alert border-0 bg-light p-4 mb-5" style="border-left: 5px solid var(--bsp-gold) !important;">
                <h6 class="fw-bold" style="color: var(--bsp-sage);"><i class="bi bi-chat-left-dots me-2"></i> Tanggapan Admin/HCM:</h6>
                <p class="mb-0 text-dark">{{ $pengajuan->catatan_admin }}</p>
            </div>
            @endif

            @foreach($pengajuan->payload as $section => $items)
                @if(!empty($items))
                    <div class="mb-5">
                        <h6 class="fw-bold text-uppercase" style="color: var(--bsp-sage); letter-spacing: 1px;">
                            {{ str_replace('_', ' ', $section) }}
                        </h6>
                        <div class="section-divider"></div>

                        <div class="table-responsive">
                            <table class="table table-bsp align-middle border">
                                <thead>
                                    <tr>
                                        <th class="ps-4 py-3">Item / Judul</th>
                                        <th class="py-3">Field Data</th>
                                        <th class="pe-4 py-3">Perubahan Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $item)
                                        @php 
                                            $dataToLoop = $item['changes'] ?? $item['values'] ?? [];
                                            $isUpdate = isset($item['changes']);
                                        @endphp

                                        @foreach($dataToLoop as $field => $detail)
                                            <tr>
                                                <td class="ps-4">
                                                    <span class="fw-bold d-block">{{ $item['title'] ?? 'Data Baru' }}</span>
                                                    <span class="badge bg-light text-dark border small fw-normal">{{ $item['mode'] ?? 'update' }}</span>
                                                </td>
                                                <td class="text-muted small">
                                                    {{ $detail['label'] ?? $field }}
                                                </td>
                                                <td class="pe-4">
                                                    @if($isUpdate)
                                                        <div class="d-flex flex-column">
                                                            <span class="val-old text-decoration-line-through small">{{ $detail['old'] ?? '-' }}</span>
                                                            <span class="val-new"><i class="bi bi-arrow-return-right me-2"></i>{{ $detail['new'] ?? '-' }}</span>
                                                        </div>
                                                    @else
                                                        <span class="val-new">{{ $detail['value'] ?? '-' }}</span>
                                                        <span class="ms-2 badge bg-success-subtle text-success small">Entry Baru</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
@endsection