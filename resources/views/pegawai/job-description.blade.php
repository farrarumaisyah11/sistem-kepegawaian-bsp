@extends('layouts.app')

@section('title', 'Job Description Saya')

@section('content')
<div class="container pt-2 pb-4">

    <div class="mb-4">
        <h3 class="mb-1">Job Description Saya</h3>
        <p class="text-muted mb-0">
            Halaman ini menampilkan job description berdasarkan jabatan yang terdaftar pada data pegawai.
        </p>
    </div>

    @if (!$pegawai)
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-5">
                <h5 class="mb-2">Data pegawai belum ditemukan</h5>
                <p class="text-muted mb-0">
                    Silakan hubungi HCM/Admin untuk melengkapi data pegawai Anda.
                </p>
            </div>
        </div>
    @elseif (!$jabatan)
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-5">
                <h5 class="mb-2">Belum memiliki jabatan</h5>
                <p class="text-muted mb-0">
                    Job description belum dapat ditampilkan karena jabatan Anda belum terhubung dengan master jabatan.
                </p>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <h4 class="mb-1">{{ $jabatan->nama_jabatan }}</h4>
                <div class="text-muted">
                    {{ $jabatan->departemen ?? '-' }} 
                    @if($jabatan->lokasi_kerja)
                        • {{ $jabatan->lokasi_kerja }}
                    @endif
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body">
                <h5 class="mb-3">Informasi Jabatan</h5>

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-muted small">Golongan Jabatan</div>
                        <div class="fw-semibold">{{ $jabatan->gol_jabatan ?? '-' }}</div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small">Home Base</div>
                        <div class="fw-semibold">{{ $jabatan->home_base ?? '-' }}</div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small">Lokasi Kerja</div>
                        <div class="fw-semibold">{{ $jabatan->lokasi_kerja ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body">
                <h5 class="mb-3">Tujuan Jabatan</h5>
                <p class="mb-0">{{ $jabatan->tujuan_jabatan ?: '-' }}</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body">
                <h5 class="mb-3">Tanggung Jawab</h5>

                @if(count($jabatan->tanggung_jawab_list))
                    <ol class="mb-0">
                        @foreach($jabatan->tanggung_jawab_list as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ol>
                @else
                    <p class="text-muted mb-0">Belum ada data tanggung jawab.</p>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body">
                <h5 class="mb-3">Tantangan Jabatan</h5>

                @if(count($jabatan->tantangan_jabatan_list))
                    <ol class="mb-0">
                        @foreach($jabatan->tantangan_jabatan_list as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ol>
                @else
                    <p class="text-muted mb-0">Belum ada data tantangan jabatan.</p>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body">
                <h5 class="mb-3">Dimensi Jabatan</h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Dimensi Keuangan</div>
                        <div>{{ $jabatan->dim_keuangan ?: '-' }}</div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">Dimensi Non Keuangan</div>
                        <div>{{ $jabatan->dim_nonkeuangan ?: '-' }}</div>
                    </div>

                    <div class="col-md-12">
                        <div class="text-muted small">Bawahan Langsung</div>
                        <div>{{ $jabatan->bawahan_langsung ?: '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body">
                <h5 class="mb-3">Hubungan Kerja Internal</h5>

                @if(count($jabatan->internal_perusahaan_list))
                    <ol class="mb-0">
                        @foreach($jabatan->internal_perusahaan_list as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ol>
                @else
                    <p class="text-muted mb-0">Belum ada data hubungan kerja internal.</p>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body">
                <h5 class="mb-3">Hubungan Kerja Eksternal</h5>

                @if(count($jabatan->external_perusahaan_list))
                    <ol class="mb-0">
                        @foreach($jabatan->external_perusahaan_list as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ol>
                @else
                    <p class="text-muted mb-0">Belum ada data hubungan kerja eksternal.</p>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body">
                <h5 class="mb-3">Wewenang</h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Finansial</div>
                        <div>{{ $jabatan->finansial ?: '-' }}</div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">Non Finansial</div>
                        <div>{{ $jabatan->non_finansial ?: '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body">
                <h5 class="mb-3">Pengetahuan & Keterampilan</h5>

                @if(count($jabatan->pengetahuan_keterampilan_list))
                    <ol class="mb-0">
                        @foreach($jabatan->pengetahuan_keterampilan_list as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ol>
                @else
                    <p class="text-muted mb-0">Belum ada data pengetahuan & keterampilan.</p>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body">
                <h5 class="mb-3">Kompetensi</h5>

                @if(count($jabatan->kompetensi_list))
                    <ol class="mb-0">
                        @foreach($jabatan->kompetensi_list as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ol>
                @else
                    <p class="text-muted mb-0">Belum ada data kompetensi.</p>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body">
                <h5 class="mb-3">Syarat Kompetensi Jabatan</h5>

                @if(count($jabatan->syarat_kompetensi_jabatan_list))
                    <ol class="mb-0">
                        @foreach($jabatan->syarat_kompetensi_jabatan_list as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ol>
                @else
                    <p class="text-muted mb-0">Belum ada data syarat kompetensi jabatan.</p>
                @endif
            </div>
        </div>

        @if($jabatan->struktur_file)
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <h5 class="mb-3">Struktur Organisasi</h5>

                    <a href="{{ asset('storage/' . $jabatan->struktur_file) }}"
                       target="_blank"
                       class="btn btn-outline-primary">
                        Lihat File Struktur
                    </a>
                </div>
            </div>
        @endif
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(!$jabatan)
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'warning',
        title: 'Belum memiliki jabatan',
        text: 'Job description belum dapat ditampilkan karena jabatan Anda belum terhubung dengan master jabatan.',
        confirmButtonText: 'Mengerti'
    });
});
</script>
@endif
@endpush