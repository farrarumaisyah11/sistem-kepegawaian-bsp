@extends('layouts.app')

@section('title', 'Tambah Data Pegawai')

@section('content')
<div class="container pt-1 pb-4">

    @if (session('warning'))
        <div class="alert alert-warning">
            {{ session('warning') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('errors'))
        <div class="alert alert-danger">
            {{ session('errors')->first('error') }}
        </div>
    @endif

    {{-- ===== TAB NAVIGATION ===== --}}
    <div class="step-tabs mb-4">
        <ul class="nav nav-pills nav-fill">
            <li class="nav-item">
                <button type="button" class="nav-link active" onclick="goToSection(1)">Informasi Pribadi</button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" onclick="goToSection(2)">Pendidikan</button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" onclick="goToSection(3)">Kursus</button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" onclick="goToSection(4)">Pengalaman BSP</button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" onclick="goToSection(5)">Pengalaman Luar</button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" onclick="goToSection(6)">Keluarga</button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" onclick="goToSection(7)">Penilaian</button>
            </li>
        </ul>
    </div>

    @php
        $pegawaiPrefix = auth()->user()->role; // admin / hcm
        $pendidikans = old('pendidikan', [[]]);
        $kursus = old('kursus', [[]]);
        $bsp = old('peng_bsp', [[]]);
        $luar = old('peng_luar', [[]]);
        $keluarga = old('keluarga', [[]]);
        $penilaian = old('penilaian', [[]]);
        $jabatans = $jabatans ?? collect();
$selectedDepartemen = old('departemen');
$selectedIdJabatan = old('id_jabatan');
    @endphp

    <form action="{{ route($pegawaiPrefix.'.pegawai.store') }}" method="POST" enctype="multipart/form-data" id="form-karyawan">
        @csrf

       {{-- ================= SECTION 1 ================= --}}
<div class="section card p-4 shadow rounded" id="section1">
    <h4 class="mb-3">Informasi Pribadi</h4>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">NIP <span class="text-danger">*</span></label>
            <input type="number" name="nip" class="form-control" value="{{ old('nip') }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Nama <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Tempat Lahir</label>
            <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir') }}">
        </div>

        <div class="col-md-6">
            <label class="form-label">Tanggal Lahir</label>
            <input type="date" name="tgl_lahir" class="form-control" value="{{ old('tgl_lahir') }}">
        </div>

        <div class="col-md-6">
            <label class="form-label">Jenis Kelamin</label>
            <select name="jenkel" class="form-control">
                <option value="">Pilih</option>
                <option value="Laki-laki" {{ old('jenkel') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                <option value="Perempuan" {{ old('jenkel') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Agama</label>
            <select name="agama" class="form-control">
                <option value="">Pilih</option>
                <option value="Islam" {{ old('agama') == 'Islam' ? 'selected' : '' }}>Islam</option>
                <option value="Kristen" {{ old('agama') == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                <option value="Katolik" {{ old('agama') == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                <option value="Hindu" {{ old('agama') == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                <option value="Buddha" {{ old('agama') == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                <option value="Konghucu" {{ old('agama') == 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
            </select>
        </div>

        <div class="col-md-12">
            <label class="form-label">Alamat</label>
            <textarea name="alamat" class="form-control" rows="2">{{ old('alamat') }}</textarea>
        </div>

        <div class="col-md-6">
            <label class="form-label">Golongan Upah</label>
            <input type="number" name="gol_upah" class="form-control" min="1" max="20" value="{{ old('gol_upah') }}">
        </div>

        <div class="col-md-6">
            <label class="form-label">Golongan Jabatan</label>
            <input type="number" name="gol_jabatan" class="form-control" min="7" max="20" value="{{ old('gol_jabatan') }}">
        </div>

        <div class="col-md-6">
            <label class="form-label">TMT Golongan Jabatan</label>
            <input type="date" name="tmt_gol_jabatan" class="form-control" value="{{ old('tmt_gol_jabatan') }}">
        </div>

        <div class="col-md-6">
            <label class="form-label">TMT Golongan Upah</label>
            <input type="date" name="tmt_gol_upah" class="form-control" value="{{ old('tmt_gol_upah') }}">
        </div>

        @include('pegawai.partials.jabatan-dropdown', [
    'jabatans' => $jabatans,
    'selectedDepartemen' => old('departemen'),
    'selectedIdJabatan' => old('id_jabatan'),
])
        <div class="col-md-4">
            <label class="form-label">Hubungan Kerja</label>
            <select name="hubungan_kerja" class="form-control">
                <option value="">Pilih</option>
                <option value="PWT" {{ old('hubungan_kerja') == 'PWT' ? 'selected' : '' }}>PWT</option>
                <option value="PWTT" {{ old('hubungan_kerja') == 'PWTT' ? 'selected' : '' }}>PWTT</option>
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

        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
                <option value="">Pilih</option>
                <option value="Manajerial" {{ old('status') == 'Manajerial' ? 'selected' : '' }}>Manajerial</option>
                <option value="Staf Utama" {{ old('status') == 'Staf Utama' ? 'selected' : '' }}>Staf Utama</option>
                <option value="Staf Madya" {{ old('status') == 'Staf Madya' ? 'selected' : '' }}>Staf Madya</option>
                <option value="Staf Biasa" {{ old('status') == 'Staf Biasa' ? 'selected' : '' }}>Staf Biasa</option>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Tanggal Mulai Kerja</label>
            <input type="date" name="tgl_masuk" class="form-control" value="{{ old('tgl_masuk') }}">
        </div>

        <div class="col-md-6">
            <label class="form-label">Profesional</label>
            <select name="profesional" class="form-control">
                <option value="">Pilih</option>
                <option value="Core" {{ old('profesional') == 'Core' ? 'selected' : '' }}>Core</option>
                <option value="Subcore" {{ old('profesional') == 'Subcore' ? 'selected' : '' }}>Subcore</option>
                <option value="Support" {{ old('profesional') == 'Support' ? 'selected' : '' }}>Support</option>
            </select>
        </div>

        <div class="col-md-12">
            <label for="foto" class="form-label">Foto</label>
            <input type="file" name="foto" id="foto" class="form-control" accept="image/*">

            <div class="mt-3">
                <img id="preview-foto"
                     src=""
                     alt="Preview Foto"
                     style="display:none; width:130px; height:160px; object-fit:cover; border-radius:10px; border:1px solid #ddd;">
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mt-4">
        <button type="button" class="btn btn-warning" onclick="nextSection(2)">Selanjutnya</button>
    </div>
</div>

        {{-- ================= SECTION 2 ================= --}}
        <div class="section card p-4 shadow rounded d-none" id="section2">
            <h4 class="mb-3">Pendidikan</h4>
            <div id="pendidikan-wrapper">
                @foreach($pendidikans as $i => $p)
                <div class="item pendidikan-item border p-3 rounded mb-3">
                    <input type="hidden" name="pendidikan[{{ $i }}][id_pendidikan]" value="{{ $p['id_pendidikan'] ?? '' }}">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="pendidikan[{{ $i }}][pendidikan_mulai]" class="form-control" value="{{ $p['pendidikan_mulai'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label>Tanggal Selesai</label>
                            <input type="date" name="pendidikan[{{ $i }}][pendidikan_selesai]" class="form-control" value="{{ $p['pendidikan_selesai'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label>Jenjang Pendidikan</label>
                            <select name="pendidikan[{{ $i }}][jenjang_pendidikan]" class="form-control">
                                <option value="">Pilih</option>
                                <option value="SMA" {{ ($p['jenjang_pendidikan'] ?? '') === 'SMA' ? 'selected' : '' }}>SMA</option>
                                <option value="Diploma" {{ ($p['jenjang_pendidikan'] ?? '') === 'Diploma' ? 'selected' : '' }}>Diploma</option>
                                <option value="Sarjana" {{ ($p['jenjang_pendidikan'] ?? '') === 'Sarjana' ? 'selected' : '' }}>Sarjana</option>
                                <option value="Magister" {{ ($p['jenjang_pendidikan'] ?? '') === 'Magister' ? 'selected' : '' }}>Magister</option>
                                <option value="Doktor" {{ ($p['jenjang_pendidikan'] ?? '') === 'Doktor' ? 'selected' : '' }}>Doktor</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Nama Institusi</label>
                            <input type="text" name="pendidikan[{{ $i }}][nama_institusi]" class="form-control" value="{{ $p['nama_institusi'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label>Jurusan</label>
                            <input type="text" name="pendidikan[{{ $i }}][jurusan]" class="form-control" value="{{ $p['jurusan'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label>Lokasi Pendidikan</label>
                            <input type="text" name="pendidikan[{{ $i }}][lokasi_pendidikan]" class="form-control" value="{{ $p['lokasi_pendidikan'] ?? '' }}">
                        </div>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm mt-2 remove-item" {{ $i==0 ? 'style=display:none;' : '' }}>Hapus</button>
                </div>
                @endforeach
            </div>
            <div class="d-flex justify-content-between mt-2">
                <button type="button" class="btn btn-secondary" onclick="prevSection(1)">Sebelumnya</button>
                <div>
                    <button type="button" class="btn btn-primary" onclick="addItem('pendidikan-wrapper')">+ Tambah</button>
                    <button type="button" class="btn btn-warning" onclick="nextSection(3)">Selanjutnya</button>
                </div>
            </div>
        </div>

        {{-- ================= SECTION 3 ================= --}}
        <div class="section card p-4 shadow rounded d-none" id="section3">
            <h4 class="mb-3">Kursus & Pelatihan</h4>
            <div id="kursus-wrapper">
                @foreach($kursus as $i => $k)
                <div class="item kursus-item border p-3 rounded mb-3">
                    <input type="hidden" name="kursus[{{ $i }}][id_kursus]" value="{{ $k['id_kursus'] ?? '' }}">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="kursus[{{ $i }}][tanggal_mulai_kursus]" class="form-control" value="{{ $k['tanggal_mulai_kursus'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label>Tanggal Selesai</label>
                            <input type="date" name="kursus[{{ $i }}][tanggal_selesai_kursus]" class="form-control" value="{{ $k['tanggal_selesai_kursus'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label>Jenis Kursus</label>
                            <input type="text" name="kursus[{{ $i }}][jenis_kursus]" class="form-control" value="{{ $k['jenis_kursus'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label>Nama Kegiatan</label>
                            <input type="text" name="kursus[{{ $i }}][nama_kegiatan_kursus]" class="form-control" value="{{ $k['nama_kegiatan_kursus'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label>Masa Berlaku Mulai</label>
                            <input type="date" name="kursus[{{ $i }}][tanggal_mulai_berlaku]" class="form-control" value="{{ $k['tanggal_mulai_berlaku'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label>Masa Berlaku Selesai</label>
                            <input type="date" name="kursus[{{ $i }}][tanggal_selesai_berlaku]" class="form-control" value="{{ $k['tanggal_selesai_berlaku'] ?? '' }}">
                        </div>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm mt-2 remove-item" {{ $i==0 ? 'style=display:none;' : '' }}>Hapus</button>
                </div>
                @endforeach
            </div>
            <div class="d-flex justify-content-between mt-2">
                <button type="button" class="btn btn-secondary" onclick="prevSection(2)">Sebelumnya</button>
                <div>
                    <button type="button" class="btn btn-primary" onclick="addItem('kursus-wrapper')">+ Tambah</button>
                    <button type="button" class="btn btn-warning" onclick="nextSection(4)">Selanjutnya</button>
                </div>
            </div>
        </div>

        {{-- ================= SECTION 4 ================= --}}
        <div class="section card p-4 shadow rounded d-none" id="section4">
            <h4 class="mb-3">Pengalaman BSP</h4>
            <div id="bsp-wrapper">
                @foreach($bsp as $i => $b)
                <div class="item bsp-item border p-3 rounded mb-3">
                    <input type="hidden" name="peng_bsp[{{ $i }}][id_pengalaman_bsp]" value="{{ $b['id_pengalaman_bsp'] ?? '' }}">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="peng_bsp[{{ $i }}][pglmn_bsp_mulai]" class="form-control" value="{{ $b['pglmn_bsp_mulai'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label>Tanggal Selesai</label>
                            <input type="date" name="peng_bsp[{{ $i }}][pglmn_bsp_selesai]" class="form-control" value="{{ $b['pglmn_bsp_selesai'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label>Jabatan</label>
                            <input type="text" name="peng_bsp[{{ $i }}][pengalaman_jabatan]" class="form-control" value="{{ $b['pengalaman_jabatan'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label>Lokasi</label>
                            <input type="text" name="peng_bsp[{{ $i }}][pengalaman_lokasi]" class="form-control" value="{{ $b['pengalaman_lokasi'] ?? '' }}">
                        </div>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm mt-2 remove-item" {{ $i==0 ? 'style=display:none;' : '' }}>Hapus</button>
                </div>
                @endforeach
            </div>
            <div class="d-flex justify-content-between mt-2">
                <button type="button" class="btn btn-secondary" onclick="prevSection(3)">Sebelumnya</button>
                <div>
                    <button type="button" class="btn btn-primary" onclick="addItem('bsp-wrapper')">+ Tambah</button>
                    <button type="button" class="btn btn-warning" onclick="nextSection(5)">Selanjutnya</button>
                </div>
            </div>
        </div>

        {{-- ================= SECTION 5 ================= --}}
        <div class="section card p-4 shadow rounded d-none" id="section5">
            <h4 class="mb-3">Pengalaman Luar BSP</h4>
            <div id="luar-wrapper">
                @foreach($luar as $i => $l)
                <div class="item luar-item border p-3 rounded mb-3">
                    <input type="hidden" name="peng_luar[{{ $i }}][id_pengalaman_luar_bsp]" value="{{ $l['id_pengalaman_luar_bsp'] ?? '' }}">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="peng_luar[{{ $i }}][pglmn_luar_bsp_mulai]" class="form-control" value="{{ $l['pglmn_luar_bsp_mulai'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label>Tanggal Selesai</label>
                            <input type="date" name="peng_luar[{{ $i }}][pglmn_luar_bsp_selesai]" class="form-control" value="{{ $l['pglmn_luar_bsp_selesai'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label>Jabatan</label>
                            <input type="text" name="peng_luar[{{ $i }}][pengalaman_luar_jabatan]" class="form-control" value="{{ $l['pengalaman_luar_jabatan'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label>Lokasi</label>
                            <input type="text" name="peng_luar[{{ $i }}][pengalaman_luar_lokasi]" class="form-control" value="{{ $l['pengalaman_luar_lokasi'] ?? '' }}">
                        </div>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm mt-2 remove-item" {{ $i==0 ? 'style=display:none;' : '' }}>Hapus</button>
                </div>
                @endforeach
            </div>
            <div class="d-flex justify-content-between mt-2">
                <button type="button" class="btn btn-secondary" onclick="prevSection(4)">Sebelumnya</button>
                <div>
                    <button type="button" class="btn btn-primary" onclick="addItem('luar-wrapper')">+ Tambah</button>
                    <button type="button" class="btn btn-warning" onclick="nextSection(6)">Selanjutnya</button>
                </div>
            </div>
        </div>

        {{-- ================= SECTION 6 ================= --}}
        <div class="section card p-4 shadow rounded d-none" id="section6">
            <h4 class="mb-3">Data Keluarga</h4>
            <div id="keluarga-wrapper">
                @foreach($keluarga as $i => $f)
                <div class="item keluarga-item border p-3 rounded mb-3">
                    <input type="hidden" name="keluarga[{{ $i }}][id_keluarga]" value="{{ $f['id_keluarga'] ?? '' }}">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label>Nama</label>
                            <input type="text" name="keluarga[{{ $i }}][nama_keluarga]" class="form-control" value="{{ $f['nama_keluarga'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label>Tanggal Lahir</label>
                            <input type="date" name="keluarga[{{ $i }}][tanggal_keluarga]" class="form-control" value="{{ $f['tanggal_keluarga'] ?? '' }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Keterangan Keluarga</label>
                            <select name="keluarga[{{ $i }}][ket_keluarga]" class="form-control">
                                <option value="">Pilih</option>
                                <option value="Suami/Istri" {{ ($f['ket_keluarga'] ?? '') === 'Suami/Istri' ? 'selected' : '' }}>Suami/Istri</option>
                                <option value="Anak" {{ ($f['ket_keluarga'] ?? '') === 'Anak' ? 'selected' : '' }}>Anak</option>
                                <option value="Orang Tua" {{ ($f['ket_keluarga'] ?? '') === 'Orang Tua' ? 'selected' : '' }}>Orang Tua</option>
                            </select>
                        </div>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm mt-2 remove-item" {{ $i==0 ? 'style=display:none;' : '' }}>Hapus</button>
                </div>
                @endforeach
            </div>
            <div class="d-flex justify-content-between mt-2">
                <button type="button" class="btn btn-secondary" onclick="prevSection(5)">Sebelumnya</button>
                <div>
                    <button type="button" class="btn btn-primary" onclick="addItem('keluarga-wrapper')">+ Tambah</button>
                    <button type="button" class="btn btn-warning" onclick="nextSection(7)">Selanjutnya</button>
                </div>
            </div>
        </div>

        {{-- ================= SECTION 7 ================= --}}
        <div class="section card p-4 shadow rounded d-none" id="section7">
            <h4 class="mb-3">Penilaian / Kompetensi</h4>
            <div id="kompetensi-wrapper">
                @foreach($penilaian as $i => $pn)
                <div class="item kompetensi-item border p-3 rounded mb-3">
                    <input type="hidden" name="penilaian[{{ $i }}][id_penilaian]" value="{{ $pn['id_penilaian'] ?? '' }}">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label>Tahun Penilaian</label>
                            <input type="number" name="penilaian[{{ $i }}][tahun_penilaian]" class="form-control" min="1900" max="2100" value="{{ $pn['tahun_penilaian'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label>Nilai</label>
                            <input type="number" step="0.01" name="penilaian[{{ $i }}][nilai_penilaian]" class="form-control" value="{{ $pn['nilai_penilaian'] ?? '' }}">
                        </div>
                        <div class="col-md-12">
                            <label>Dasar Penilaian</label>
                            <textarea name="penilaian[{{ $i }}][dasar_penilaian]" class="form-control">{{ $pn['dasar_penilaian'] ?? '' }}</textarea>
                        </div>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm mt-2 remove-item" {{ $i==0 ? 'style=display:none;' : '' }}>Hapus</button>
                </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-between mt-2">
                <button type="button" class="btn btn-secondary" onclick="prevSection(6)">Sebelumnya</button>
                <button type="submit" class="btn btn-success" id="btn-submit">
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
    padding:10px 14px;
    margin:2px;
    color:#5f6b4b;
    background:transparent;
    transition:all .25s ease;
    position:relative;
    border:none;
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

.section{
    border-radius:18px;
    border:1px solid #e5e7eb;
    background:#fff;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
}

.section h4{
    font-weight:700;
    color:#374151;
    border-bottom:2px solid #e5e7eb;
    padding-bottom:12px;
    margin-bottom:22px;
}

.step-tabs .nav-link:not(.active){
    opacity:.75;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    showSection(1);

    const form = document.getElementById('form-karyawan');
    const submitBtn = document.getElementById('btn-submit');

    form.addEventListener('submit', function (e) {
        const nipInput = form.querySelector('input[name="nip"]');
        const namaInput = form.querySelector('input[name="nama"]');

        if (!nipInput.value.trim() || !namaInput.value.trim()) {
            e.preventDefault();

            showSection(1);

            setTimeout(() => {
                if (!nipInput.value.trim()) {
                    nipInput.focus();
                } else {
                    namaInput.focus();
                }
            }, 300);

            alert('NIP dan Nama wajib diisi sebelum menyimpan data pegawai.');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.querySelector('.btn-text').classList.add('d-none');
        submitBtn.querySelector('.btn-loading').classList.remove('d-none');
    });
});

function showSection(id){
    document.querySelectorAll('.section').forEach(s => s.classList.add('d-none'));

    const active = document.getElementById('section' + id);
    if (active) active.classList.remove('d-none');

    document.querySelectorAll('.step-tabs .nav-link').forEach(tab => tab.classList.remove('active'));

    const tabs = document.querySelectorAll('.step-tabs .nav-link');
    if (tabs[id - 1]) tabs[id - 1].classList.add('active');

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function nextSection(id){ showSection(id); }
function prevSection(id){ showSection(id); }
function goToSection(id){ showSection(id); }

function buildTemplate(wrapperId, i){
    if(wrapperId === 'pendidikan-wrapper'){
        return `
        <div class="item pendidikan-item border p-3 rounded mb-3">
            <input type="hidden" name="pendidikan[${i}][id_pendidikan]">
            <div class="row g-2">
                <div class="col-md-6">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="pendidikan[${i}][pendidikan_mulai]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label>Tanggal Selesai</label>
                    <input type="date" name="pendidikan[${i}][pendidikan_selesai]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label>Jenjang</label>
                    <select name="pendidikan[${i}][jenjang_pendidikan]" class="form-control">
                        <option value="">Pilih</option>
                        <option value="SMA">SMA</option>
                        <option value="Diploma">Diploma</option>
                        <option value="Sarjana">Sarjana</option>
                        <option value="Magister">Magister</option>
                        <option value="Doktor">Doktor</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Institusi</label>
                    <input type="text" name="pendidikan[${i}][nama_institusi]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label>Jurusan</label>
                    <input type="text" name="pendidikan[${i}][jurusan]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label>Lokasi</label>
                    <input type="text" name="pendidikan[${i}][lokasi_pendidikan]" class="form-control">
                </div>
            </div>
            <button type="button" class="btn btn-danger btn-sm mt-2 remove-item">Hapus</button>
        </div>`;
    }

    if(wrapperId === 'kursus-wrapper'){
        return `
        <div class="item kursus-item border p-3 rounded mb-3">
            <input type="hidden" name="kursus[${i}][id_kursus]">
            <div class="row g-2">
                <div class="col-md-6">
                    <label>Mulai</label>
                    <input type="date" name="kursus[${i}][tanggal_mulai_kursus]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label>Selesai</label>
                    <input type="date" name="kursus[${i}][tanggal_selesai_kursus]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label>Jenis</label>
                    <input type="text" name="kursus[${i}][jenis_kursus]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label>Kegiatan</label>
                    <input type="text" name="kursus[${i}][nama_kegiatan_kursus]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label>Masa Berlaku Mulai</label>
                    <input type="date" name="kursus[${i}][tanggal_mulai_berlaku]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label>Masa Berlaku Selesai</label>
                    <input type="date" name="kursus[${i}][tanggal_selesai_berlaku]" class="form-control">
                </div>
            </div>
            <button type="button" class="btn btn-danger btn-sm mt-2 remove-item">Hapus</button>
        </div>`;
    }

    if(wrapperId === 'bsp-wrapper'){
        return `
        <div class="item bsp-item border p-3 rounded mb-3">
            <input type="hidden" name="peng_bsp[${i}][id_pengalaman_bsp]">
            <div class="row g-2">
                <div class="col-md-6">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="peng_bsp[${i}][pglmn_bsp_mulai]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label>Tanggal Selesai</label>
                    <input type="date" name="peng_bsp[${i}][pglmn_bsp_selesai]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label>Jabatan</label>
                    <input type="text" name="peng_bsp[${i}][pengalaman_jabatan]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label>Lokasi</label>
                    <input type="text" name="peng_bsp[${i}][pengalaman_lokasi]" class="form-control">
                </div>
            </div>
            <button type="button" class="btn btn-danger btn-sm mt-2 remove-item">Hapus</button>
        </div>`;
    }

    if(wrapperId === 'luar-wrapper'){
        return `
        <div class="item luar-item border p-3 rounded mb-3">
            <input type="hidden" name="peng_luar[${i}][id_pengalaman_luar_bsp]">
            <div class="row g-2">
                <div class="col-md-6">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="peng_luar[${i}][pglmn_luar_bsp_mulai]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label>Tanggal Selesai</label>
                    <input type="date" name="peng_luar[${i}][pglmn_luar_bsp_selesai]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label>Jabatan</label>
                    <input type="text" name="peng_luar[${i}][pengalaman_luar_jabatan]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label>Lokasi</label>
                    <input type="text" name="peng_luar[${i}][pengalaman_luar_lokasi]" class="form-control">
                </div>
            </div>
            <button type="button" class="btn btn-danger btn-sm mt-2 remove-item">Hapus</button>
        </div>`;
    }

    if(wrapperId === 'keluarga-wrapper'){
        return `
        <div class="item keluarga-item border p-3 rounded mb-3">
            <input type="hidden" name="keluarga[${i}][id_keluarga]">
            <div class="row g-2">
                <div class="col-md-6">
                    <label>Nama</label>
                    <input type="text" name="keluarga[${i}][nama_keluarga]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label>Tanggal Lahir</label>
                    <input type="date" name="keluarga[${i}][tanggal_keluarga]" class="form-control">
                </div>
                <div class="col-md-12">
                    <label>Keterangan Keluarga</label>
                    <select name="keluarga[${i}][ket_keluarga]" class="form-control">
                        <option value="">Pilih</option>
                        <option value="Suami/Istri">Suami/Istri</option>
                        <option value="Anak">Anak</option>
                        <option value="Orang Tua">Orang Tua</option>
                    </select>
                </div>
            </div>
            <button type="button" class="btn btn-danger btn-sm mt-2 remove-item">Hapus</button>
        </div>`;
    }

    if(wrapperId === 'kompetensi-wrapper'){
        return `
        <div class="item kompetensi-item border p-3 rounded mb-3">
            <input type="hidden" name="penilaian[${i}][id_penilaian]">
            <div class="row g-2">
                <div class="col-md-6">
                    <label>Tahun Penilaian</label>
                    <input type="number" name="penilaian[${i}][tahun_penilaian]" class="form-control" min="1900" max="2100">
                </div>
                <div class="col-md-6">
                    <label>Nilai</label>
                    <input type="number" step="0.01" name="penilaian[${i}][nilai_penilaian]" class="form-control">
                </div>
                <div class="col-md-12">
                    <label>Dasar Penilaian</label>
                    <textarea name="penilaian[${i}][dasar_penilaian]" class="form-control"></textarea>
                </div>
            </div>
            <button type="button" class="btn btn-danger btn-sm mt-2 remove-item">Hapus</button>
        </div>`;
    }

    return '';
}

function addItem(wrapperId){
    const wrap = document.getElementById(wrapperId);
    const i = wrap.querySelectorAll('.item').length;
    wrap.insertAdjacentHTML('beforeend', buildTemplate(wrapperId, i));
}

document.addEventListener('click', function(e){
    if (e.target.classList.contains('remove-item')) {
        const item = e.target.closest('.item');
        const wrap = item.parentElement;
        if (wrap.children[0] === item) return;
        item.remove();
    }
});

const fotoInput = document.getElementById('foto');
const previewFoto = document.getElementById('preview-foto');

if (fotoInput && previewFoto) {
    fotoInput.addEventListener('change', function () {
        const file = this.files[0];

        if (file) {
            const reader = new FileReader();

            reader.onload = function (e) {
                previewFoto.src = e.target.result;
                previewFoto.style.display = 'block';
            };

            reader.readAsDataURL(file);
        }
    });
}
</script>
@endpush