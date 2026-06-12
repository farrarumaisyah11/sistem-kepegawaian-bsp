@extends('layouts.app')
@section('title', 'Tambah Jabatan')
@php
    $prefix = auth()->user()->role;
@endphp

@section('content')
<div class="container pt-2 pb-4">

    @if ($errors->any())
        <div class="alert alert-danger rounded-4 shadow-sm">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="page-header-jabatan mb-4">
        <div>
            <h3 class="mb-1">Tambah Data Jabatan</h3>
            <p class="text-muted mb-0">Lengkapi informasi job description, hubungan kerja, dan persyaratan jabatan.</p>
        </div>
    </div>

    {{-- ===== TAB NAVIGATION ===== --}}
    <div class="step-tabs mb-4">
        <ul class="nav nav-pills nav-fill flex-column flex-md-row gap-2 gap-md-0">
            <li class="nav-item">
                <button type="button" class="nav-link active" onclick="goToSection(1)">
                    Job Description
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" onclick="goToSection(2)">
                    Hubungan Kerja & Wewenang
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" onclick="goToSection(3)">
                    Persyaratan Jabatan
                </button>
            </li>
        </ul>
    </div>

<form action="{{ route($prefix.'.jabatan.store') }}" method="POST" enctype="multipart/form-data" id="form-jabatan">
            @csrf

        {{-- ================= SECTION 1 ================= --}}
        <div class="section card p-4 shadow rounded" id="section1">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
                    <input type="text"
                           name="nama_jabatan"
                           class="form-control"
                           value="{{ old('nama_jabatan') }}"
                           placeholder="Masukkan nama jabatan">
                </div>

                <div class="col-md-6">
    <label class="form-label">Departemen</label>

    <select name="id_departemen" class="form-control">
        <option value="">Pilih Departemen</option>

        @foreach($departemenList as $dep)
            <option value="{{ $dep->id_departemen }}"
                {{ (string) old('id_departemen') === (string) $dep->id_departemen ? 'selected' : '' }}>
                {{ str_repeat('— ', max(0, ($dep->level_departemen ?? 1) - 1)) }}
                {{ $dep->nama_departemen }}
                @if($dep->singkatan)
                    ({{ $dep->singkatan }})
                @endif
            </option>
        @endforeach
    </select>
</div>
<div class="col-md-6">
    <label class="form-label">Atasan Langsung / Parent Jabatan</label>

    <select name="parent_jabatan" class="form-control">
        <option value="">Root / Tidak Ada Atasan</option>

        @foreach($parentOptions as $parent)
            <option value="{{ $parent->id_jabatan }}"
                {{ (string) old('parent_jabatan') === (string) $parent->id_jabatan ? 'selected' : '' }}>
                {{ $parent->nama_jabatan }}
                @if($parent->departemenMaster)
                    - {{ $parent->departemenMaster->nama_departemen }}
                @elseif($parent->departemen)
                    - {{ $parent->departemen }}
                @endif
            </option>
        @endforeach
    </select>

    <small class="text-muted">
        Pilih jabatan atasan langsung agar struktur organisasi dapat terbentuk otomatis.
    </small>
</div>

                <div class="col-md-4">
                    <label class="form-label">Golongan Jabatan</label>
                    <input type="number"
                           name="gol_jabatan"
                           class="form-control"
                           value="{{ old('gol_jabatan') }}"
                           min="1"
                           placeholder="Masukkan golongan jabatan">
                </div>

               <div class="col-md-4">
                <label class="form-label">Home Base</label>
                <select name="home_base" class="form-control">
                    <option value="">Pilih</option>
                    <option value="Jakarta" {{ old('home_base') == 'Jakarta' ? 'selected' : '' }}>Jakarta</option>
                    <option value="Pekanbaru" {{ old('home_base') == 'Pekanbaru' ? 'selected' : '' }}>Pekanbaru</option>
                    <option value="Zamrud" {{ old('home_base') == 'Zamrud' ? 'selected' : '' }}>Zamrud</option>
                    <option value="Pedada" {{ old('home_base') == 'Pedada' ? 'selected' : '' }}>Pedada</option>
                    <option value="West Area" {{ old('home_base') == 'West Area' ? 'selected' : '' }}>West Area</option>
                </select>
            </div>
                <div class="col-md-4">
                    <label class="form-label">Lokasi Kerja</label>
                    <select name="lokasi_kerja" class="form-control">
                        <option value="">Pilih</option>
                        <option value="Jakarta" {{ old('lokasi_kerja') == 'Jakarta' ? 'selected' : '' }}>Jakarta</option>
                        <option value="Pekanbaru" {{ old('lokasi_kerja') == 'Pekanbaru' ? 'selected' : '' }}>Pekanbaru</option>
                        <option value="Zamrud" {{ old('lokasi_kerja') == 'Zamrud' ? 'selected' : '' }}>Zamrud</option>
                        <option value="Pedada" {{ old('lokasi_kerja') == 'Pedada' ? 'selected' : '' }}>Pedada</option>
                        <option value="West Area" {{ old('lokasi_kerja') == 'West Area' ? 'selected' : '' }}>West Area</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Tujuan Jabatan</label>
                    <textarea name="tujuan_jabatan"
                              rows="4"
                              class="form-control"
                              placeholder="Masukkan tujuan jabatan">{{ old('tujuan_jabatan') }}</textarea>
                </div>
            </div>

            <div class="subsection-title mt-4 mb-3">Tanggung Jawab</div>
            <div class="multi-group" id="tanggung_jawab_wrapper">
                @php
                    $oldTanggungJawab = old('tanggung_jawab', ['']);
                @endphp

                @foreach($oldTanggungJawab as $item)
                    <div class="input-group multi-item mb-2">
                        <input type="text"
                               name="tanggung_jawab[]"
                               class="form-control"
                               value="{{ $item }}"
                               placeholder="Masukkan poin tanggung jawab">
                        <button type="button" class="btn btn-outline-danger remove-item">Hapus</button>
                    </div>
                @endforeach
            </div>

            <button type="button"
                    class="btn btn-sm btn-outline-primary mt-2"
                    onclick="addMultiItem('tanggung_jawab_wrapper', 'tanggung_jawab[]', 'Masukkan poin tanggung jawab')">
                + Tambah Poin
            </button>

            <div class="subsection-title mt-4 mb-3">Tantangan Jabatan</div>
            <div class="multi-group" id="tantangan_jabatan_wrapper">
                @php
                    $oldTantangan = old('tantangan_jabatan', ['']);
                @endphp

                @foreach($oldTantangan as $item)
                    <div class="input-group multi-item mb-2">
                        <input type="text"
                               name="tantangan_jabatan[]"
                               class="form-control"
                               value="{{ $item }}"
                               placeholder="Masukkan poin tantangan jabatan">
                        <button type="button" class="btn btn-outline-danger remove-item">Hapus</button>
                    </div>
                @endforeach
            </div>

            <button type="button"
                    class="btn btn-sm btn-outline-primary mt-2"
                    onclick="addMultiItem('tantangan_jabatan_wrapper', 'tantangan_jabatan[]', 'Masukkan poin tantangan jabatan')">
                + Tambah Poin
            </button>

            <div class="subsection-title mt-4 mb-3">Dimensi Jabatan</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Dimensi Keuangan</label>
                    <input type="text"
                           name="dim_keuangan"
                           class="form-control"
                           value="{{ old('dim_keuangan') }}"
                           placeholder="Contoh: Budget, approval limit, dll">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Dimensi Non Keuangan</label>
                    <input type="text"
                           name="dim_nonkeuangan"
                           class="form-control"
                           value="{{ old('dim_nonkeuangan') }}"
                           placeholder="Contoh: aset, manpower, area kerja, dll">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Bawahan Langsung</label>
                    <input type="text"
                           name="bawahan_langsung"
                           class="form-control"
                           value="{{ old('bawahan_langsung') }}"
                           placeholder="Masukkan jabatan bawahan langsung">
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="button" class="btn btn-warning px-4" onclick="nextSection(2)">Selanjutnya</button>
            </div>
        </div>

        {{-- ================= SECTION 2 ================= --}}
        <div class="section card p-4 shadow rounded d-none" id="section2">
            <div class="subsection-title mb-3">Hubungan Kerja Internal</div>
            <div class="multi-group" id="internal_perusahaan_wrapper">
                @php
                    $oldInternal = old('internal_perusahaan', ['']);
                @endphp

                @foreach($oldInternal as $item)
                    <div class="input-group multi-item mb-2">
                        <input type="text"
                               name="internal_perusahaan[]"
                               class="form-control"
                               value="{{ $item }}"
                               placeholder="Masukkan poin hubungan internal">
                        <button type="button" class="btn btn-outline-danger remove-item">Hapus</button>
                    </div>
                @endforeach
            </div>

            <button type="button"
                    class="btn btn-sm btn-outline-primary mt-2"
                    onclick="addMultiItem('internal_perusahaan_wrapper', 'internal_perusahaan[]', 'Masukkan poin hubungan internal')">
                + Tambah Poin
            </button>

            <div class="subsection-title mt-4 mb-3">Hubungan Kerja Eksternal</div>
            <div class="multi-group" id="external_perusahaan_wrapper">
                @php
                    $oldExternal = old('external_perusahaan', ['']);
                @endphp

                @foreach($oldExternal as $item)
                    <div class="input-group multi-item mb-2">
                        <input type="text"
                               name="external_perusahaan[]"
                               class="form-control"
                               value="{{ $item }}"
                               placeholder="Masukkan poin hubungan eksternal">
                        <button type="button" class="btn btn-outline-danger remove-item">Hapus</button>
                    </div>
                @endforeach
            </div>

            <button type="button"
                    class="btn btn-sm btn-outline-primary mt-2"
                    onclick="addMultiItem('external_perusahaan_wrapper', 'external_perusahaan[]', 'Masukkan poin hubungan eksternal')">
                + Tambah Poin
            </button>

            <div class="subsection-title mt-4 mb-3">Wewenang</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Finansial</label>
                    <input type="text"
                           name="finansial"
                           class="form-control"
                           value="{{ old('finansial') }}"
                           placeholder="Masukkan wewenang finansial">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Non Finansial</label>
                    <input type="text"
                           name="non_finansial"
                           class="form-control"
                           value="{{ old('non_finansial') }}"
                           placeholder="Masukkan wewenang non finansial">
                </div>
            </div>

            <div class="subsection-title mt-4 mb-3">Syarat Kompetensi Jabatan</div>
            <div class="multi-group" id="syarat_kompetensi_jabatan_wrapper">
                @php
                    $oldSyarat = old('syarat_kompetensi_jabatan', ['']);
                @endphp

                @foreach($oldSyarat as $item)
                    <div class="input-group multi-item mb-2">
                        <input type="text"
                               name="syarat_kompetensi_jabatan[]"
                               class="form-control"
                               value="{{ $item }}"
                               placeholder="Masukkan poin syarat kompetensi jabatan">
                        <button type="button" class="btn btn-outline-danger remove-item">Hapus</button>
                    </div>
                @endforeach
            </div>

            <button type="button"
                    class="btn btn-sm btn-outline-primary mt-2"
                    onclick="addMultiItem('syarat_kompetensi_jabatan_wrapper', 'syarat_kompetensi_jabatan[]', 'Masukkan poin syarat kompetensi jabatan')">
                + Tambah Poin
            </button>

            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-secondary px-4" onclick="prevSection(1)">Sebelumnya</button>
                <button type="button" class="btn btn-warning px-4" onclick="nextSection(3)">Selanjutnya</button>
            </div>
        </div>

        {{-- ================= SECTION 3 ================= --}}
        <div class="section card p-4 shadow rounded d-none" id="section3">
            <div class="subsection-title mb-3">Pengetahuan & Keterampilan</div>
            <div class="multi-group" id="pengetahuan_keterampilan_wrapper">
                @php
                    $oldPengetahuan = old('pengetahuan_keterampilan', ['']);
                @endphp

                @foreach($oldPengetahuan as $item)
                    <div class="input-group multi-item mb-2">
                        <input type="text"
                               name="pengetahuan_keterampilan[]"
                               class="form-control"
                               value="{{ $item }}"
                               placeholder="Masukkan poin pengetahuan & keterampilan">
                        <button type="button" class="btn btn-outline-danger remove-item">Hapus</button>
                    </div>
                @endforeach
            </div>

            <button type="button"
                    class="btn btn-sm btn-outline-primary mt-2"
                    onclick="addMultiItem('pengetahuan_keterampilan_wrapper', 'pengetahuan_keterampilan[]', 'Masukkan poin pengetahuan & keterampilan')">
                + Tambah Poin
            </button>

            <div class="subsection-title mt-4 mb-3">Kompetensi</div>
            <div class="multi-group" id="kompetensi_wrapper">
                @php
                    $oldKompetensi = old('kompetensi', ['']);
                @endphp

                @foreach($oldKompetensi as $item)
                    <div class="input-group multi-item mb-2">
                        <input type="text"
                               name="kompetensi[]"
                               class="form-control"
                               value="{{ $item }}"
                               placeholder="Masukkan poin kompetensi">
                        <button type="button" class="btn btn-outline-danger remove-item">Hapus</button>
                    </div>
                @endforeach
            </div>

            <button type="button"
                    class="btn btn-sm btn-outline-primary mt-2"
                    onclick="addMultiItem('kompetensi_wrapper', 'kompetensi[]', 'Masukkan poin kompetensi')">
                + Tambah Poin
            </button>

            <div class="subsection-title mt-4 mb-3">Struktur Organisasi</div>
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">Upload File Struktur</label>
                    <input type="file" name="struktur_file" class="form-control">
                    <small class="text-muted">Format: PDF, PNG, JPG, JPEG. Maksimal 2 MB.</small>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-secondary px-4" onclick="prevSection(2)">Sebelumnya</button>
                <button type="submit" class="btn btn-success px-4" id="btn-submit">
                    <span class="btn-text">Simpan</span>
                    <span class="btn-loading d-none">Menyimpan...</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    body{
        background:#f6f8f4;
    }

    .page-header-jabatan h3{
        font-weight:700;
        color:#374151;
    }

    .step-tabs{
        background:#f4f6f2;
        border-radius:18px;
        padding:10px;
        box-shadow:inset 0 0 0 1px #e5e7eb;
    }

    .step-tabs .nav-link{
        border-radius:14px;
        font-weight:600;
        font-size:14px;
        padding:12px 14px;
        margin:2px;
        color:#5f6b4b;
        background:transparent;
        transition:all .25s ease;
        position:relative;
        border:none;
        width:100%;
    }

    .step-tabs .nav-link:hover{
        background:rgba(95,107,75,.08);
        color:#3f4a32;
    }

    .step-tabs .nav-link.active{
        background:linear-gradient(135deg,#6b775c,#505a45);
        color:#fff;
    }

    .step-tabs .nav-link.active::after{
        content:'';
        position:absolute;
        bottom:-6px;
        left:50%;
        transform:translateX(-50%);
        width:22px;
        height:4px;
        background:#f3c94b;
        border-radius:6px;
    }

    .step-tabs .nav-link:not(.active){
        opacity:.8;
    }

    .section{
        border-radius:20px;
        border:1px solid #e5e7eb;
        background:#fff;
        box-shadow:0 10px 30px rgba(0,0,0,.06);
    }

    .subsection-title{
        font-weight:700;
        color:#4b5563;
        font-size:15px;
        padding:10px 14px;
        background:#f8faf7;
        border:1px solid #e5e7eb;
        border-radius:12px;
    }

    .form-label{
        font-weight:600;
        color:#4b5563;
        margin-bottom:6px;
    }

    .form-control{
        border-radius:12px;
        min-height:46px;
        border:1px solid #dbe1d6;
        box-shadow:none;
    }

    textarea.form-control{
        min-height:auto;
    }

    .form-control:focus{
        border-color:#7a866a;
        box-shadow:0 0 0 .2rem rgba(107,119,92,.15);
    }

    .btn{
        border-radius:12px;
        font-weight:600;
        min-width:120px;
    }

    .multi-item .btn{
        min-width:auto;
        border-top-left-radius:0;
        border-bottom-left-radius:0;
    }

    .multi-item .form-control{
        border-top-right-radius:0;
        border-bottom-right-radius:0;
    }
    .multi-group{
    counter-reset: point-counter;
}

.multi-item{
    counter-increment: point-counter;
    align-items: stretch;
}

.multi-item::before{
    content: counter(point-counter);
    width: 44px;
    min-width: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #eef1ea;
    border: 1px solid #dbe1d6;
    border-right: none;
    color: #3f4a32;
    font-weight: 700;
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
}

.multi-item .form-control{
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
}

    @media (max-width: 768px){
        .section{
            padding:1.25rem !important;
        }

        .step-tabs .nav-link.active::after{
            display:none;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    showSection(1);

    const form = document.getElementById('form-jabatan');
    const submitBtn = document.getElementById('btn-submit');

    form.addEventListener('submit', function () {
        submitBtn.disabled = true;
        submitBtn.querySelector('.btn-text').classList.add('d-none');
        submitBtn.querySelector('.btn-loading').classList.remove('d-none');
    });
});

function showSection(id){
    document.querySelectorAll('.section').forEach(section => {
        section.classList.add('d-none');
    });

    const activeSection = document.getElementById('section' + id);
    if (activeSection) {
        activeSection.classList.remove('d-none');
    }

    document.querySelectorAll('.step-tabs .nav-link').forEach(tab => {
        tab.classList.remove('active');
    });

    const tabs = document.querySelectorAll('.step-tabs .nav-link');
    if (tabs[id - 1]) {
        tabs[id - 1].classList.add('active');
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function nextSection(id){
    showSection(id);
}

function prevSection(id){
    showSection(id);
}

function goToSection(id){
    showSection(id);
}

function addMultiItem(wrapperId, inputName, placeholder = 'Masukkan data'){
    const wrapper = document.getElementById(wrapperId);
    const html = `
        <div class="input-group multi-item mb-2">
            <input type="text" name="${inputName}" class="form-control" placeholder="${placeholder}">
            <button type="button" class="btn btn-outline-danger remove-item">Hapus</button>
        </div>
    `;
    wrapper.insertAdjacentHTML('beforeend', html);
}

document.addEventListener('click', function(e){
    if (e.target.classList.contains('remove-item')) {
        const item = e.target.closest('.multi-item');
        const wrapper = item.parentElement;

        if (wrapper.querySelectorAll('.multi-item').length > 1) {
            item.remove();
        } else {
            const input = item.querySelector('input');
            if (input) input.value = '';
        }
    }
});
</script>
@endpush