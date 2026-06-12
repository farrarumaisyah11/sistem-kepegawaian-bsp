<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\UserDaftar;
use App\Models\Pendidikan;
use App\Models\Pelatihan;
use App\Models\PengalamanBsp;
use App\Models\PengalamanLuarBsp;
use App\Models\Keluarga;
use App\Models\Penilaian;
use App\Models\Departemen;
use Illuminate\Http\Request;
use App\Models\Jabatan;
use App\Models\JabatanVersion;
use App\Models\PegawaiJabatanVersion;
use App\Exports\PegawaiTemplateExport;
use App\Imports\PegawaiPreviewImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\ValidationException;

class PegawaiController extends Controller
{
    /* ===================== INDEX ===================== */
    public function index(Request $request)
    {
        $pegawai = Pegawai::query()
            ->orderByDesc('tgl_masuk')
            ->get();

        return view('pegawai.index', compact('pegawai'));
    }

    /* ===================== DOWNLOAD TEMPLATE ===================== */
    public function downloadTemplate()
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'hcm']), 403);

        return Excel::download(new PegawaiTemplateExport, 'template_pegawai.xlsx');
    }

    /* ===================== CREATE ===================== */
    public function create()
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'hcm']), 403);

        $jabatans = $this->jabatanOptions();
        $departemenList = $this->departemenOptions();

        return view('pegawai.create', compact('jabatans', 'departemenList'));
    }

    /* ===================== STORE ===================== */
    public function store(Request $request)
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'hcm']), 403);

        $pegawaiPrefix = auth()->user()->role; // admin / hcm

        // NIP disimpan sebagai string agar angka 0 di depan tidak hilang.
        $request->merge([
            'nip' => trim((string) $request->input('nip')),
        ]);

        $request->validate([
            'nip'  => ['required', 'string', 'max:50'],
            'nama' => ['required', 'string', 'max:100'],
        ], [
            'nip.required'  => 'NIP wajib diisi.',
            'nama.required' => 'Nama wajib diisi.',
        ]);

        $pegawaiSudahAda = Pegawai::where('nip', $request->nip)
            ->orWhere('nama', $request->nama)
            ->first();

        if ($pegawaiSudahAda) {
            return redirect()
                ->route($pegawaiPrefix . '.pegawai.show', $pegawaiSudahAda->nip)
                ->with('warning', 'Data pegawai dengan NIP atau Nama tersebut sudah ada. Anda diarahkan ke detail pegawai terkait.');
        }

        $validated = $request->validate([
            'nip'             => ['required', 'string', 'max:50'],
            'nama'            => ['required', 'string', 'max:100'],
            'foto'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'tempat_lahir'    => ['nullable', 'string'],
            'tgl_lahir'       => ['nullable', 'date'],
            'jenkel'          => ['nullable', 'string'],
            'agama'           => ['nullable', 'string'],
            'alamat'          => ['nullable', 'string'],

            'gol_upah'        => ['nullable', 'integer'],
            'gol_jabatan'     => ['nullable', 'integer'],
            'id_jabatan'      => ['nullable', 'integer', 'exists:tb_jabatan,id_jabatan'],
            'id_departemen'   => ['nullable', 'integer', 'exists:tb_departemen,id_departemen'],

            'tmt_gol_jabatan' => ['nullable', 'date'],
            'tmt_gol_upah'    => ['nullable', 'date'],

            'jabatan'         => ['nullable', 'string'],
            'departemen'      => ['nullable', 'string'],
            'hubungan_kerja'  => ['nullable', 'string'],
            'lokasi_kerja'    => ['nullable', 'string'],
            'status'          => ['nullable', 'string'],
            'tgl_masuk'       => ['nullable', 'date'],
            'profesional'     => ['nullable', 'string'],

            'pendidikan'      => ['nullable', 'array'],
            'kursus'          => ['nullable', 'array'],
            'peng_bsp'        => ['nullable', 'array'],
            'peng_luar'       => ['nullable', 'array'],
            'keluarga'        => ['nullable', 'array'],
            'penilaian'       => ['nullable', 'array'],
        ]);

        $selectedJabatan = $this->resolveSelectedJabatan($request);

        DB::beginTransaction();

        try {
            $data = $this->preparePegawaiData(
                $validated,
                $request,
                false,
                null,
                $selectedJabatan
            );

            $pegawai = Pegawai::create($data);

            UserDaftar::firstOrCreate(
                ['nip' => $pegawai->nip],
                [
                    'username' => $pegawai->nip,
                    'password' => Hash::make($pegawai->nip),
                    'role'     => 'pegawai',
                    'status'   => 'aktif',
                ]
            );

            $this->storeChildren($pegawai, $request);

            /*
            |--------------------------------------------------------------------------
            | Jobdesk Versioning
            |--------------------------------------------------------------------------
            | Jika pegawai baru langsung memiliki id_jabatan, sistem mengikat pegawai
            | ke versi jobdesk aktif yang sudah berlaku. Jika jabatan belum punya versi
            | aktif, proses pegawai tetap berhasil dan jobdesk bisa diterapkan nanti.
            |--------------------------------------------------------------------------
            */
            $this->syncPegawaiJobdeskVersion($pegawai, $selectedJabatan);

            DB::commit();

            return redirect()
                ->route($pegawaiPrefix . '.pegawai.index')
                ->with('success_auto', 'Data pegawai berhasil disimpan');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Simpan gagal: ' . $e->getMessage()])
                ->withInput();
        }
    }

    // ===================== HELPER =====================
    protected function storeChildren(Pegawai $pegawai, Request $request): void
    {
        foreach ($this->childRelationMap() as $inputKey => $config) {
            $relationName = $config['relation'];
            $primaryKey   = $config['primary'];

            $rows = $request->input($inputKey, []);

            if (!is_array($rows)) {
                continue;
            }

            foreach ($rows as $row) {
                $row = $this->normalizeChildRow($row, $primaryKey);

                if ($row === null) {
                    continue;
                }

                unset($row[$primaryKey]);
                $row['nip'] = (string) $pegawai->nip;

                $pegawai->{$relationName}()->create($row);
            }
        }
    }

    private function childRelationMap(): array
    {
        return [
            'pendidikan' => ['relation' => 'pendidikan', 'primary' => 'id_pendidikan'],
            'kursus'     => ['relation' => 'kursus', 'primary' => 'id_kursus'],
            'peng_bsp'   => ['relation' => 'pengalamanBsp', 'primary' => 'id_pengalaman_bsp'],
            'peng_luar'  => ['relation' => 'pengalamanLuarBsp', 'primary' => 'id_pengalaman_luar_bsp'],
            'keluarga'   => ['relation' => 'keluarga', 'primary' => 'id_keluarga'],
            'penilaian'  => ['relation' => 'penilaian', 'primary' => 'id_penilaian'],
        ];
    }

    private function normalizeChildRow($row, string $primaryKey): ?array
    {
        if (!is_array($row)) {
            return null;
        }

        $row = collect($row)
            ->map(function ($value) {
                if (is_string($value)) {
                    $value = trim($value);
                }

                return $value === '' ? null : $value;
            })
            ->toArray();

        $isEmptyRow = collect($row)
            ->except([$primaryKey])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->isEmpty();

        return $isEmptyRow ? null : $row;
    }

    protected function normalizeFotoPath(?string $foto): ?string
    {
        if (!$foto) {
            return null;
        }

        $path = str_replace('\\', '/', trim($foto));
        $path = ltrim($path, '/');

        if (str_contains($path, 'storage/app/public/')) {
            $path = \Illuminate\Support\Str::after($path, 'storage/app/public/');
        } elseif (str_contains($path, 'public/storage/')) {
            $path = \Illuminate\Support\Str::after($path, 'public/storage/');
        } elseif (str_starts_with($path, 'storage/')) {
            $path = \Illuminate\Support\Str::after($path, 'storage/');
        } elseif (str_starts_with($path, 'public/')) {
            $path = \Illuminate\Support\Str::after($path, 'public/');
        }

        if (str_contains($path, 'karyawan/')) {
            $path = 'karyawan/' . \Illuminate\Support\Str::after($path, 'karyawan/');
        }

        return ltrim($path, '/');
    }

    /* ===================== SHOW ===================== */
    public function show(Pegawai $pegawai)
    {
        $user = auth()->user();

        if (in_array($user->role, ['admin', 'hcm'])) {
            $pegawai = $this->loadPegawaiProfile($pegawai);

            return view('pegawai.show', compact('pegawai'));
        }

        if ($user->role === 'pegawai') {
            $nipUser = (string) ($user->nip ?? $user->username ?? '');

            abort_unless($nipUser === (string) $pegawai->nip, 403, 'Anda tidak memiliki akses');

            $pegawai = $this->loadPegawaiProfile($pegawai);

            return view('pegawai.show', compact('pegawai'));
        }

        abort(403, 'Anda tidak memiliki akses');
    }

    private function pegawaiProfileRelations(): array
    {
        return [
            'pendidikan',
            'kursus',
            'pengalamanBsp',
            'pengalamanLuarBsp',
            'keluarga',
            'penilaian',
            'masterJabatan',
            'departemenMaster',
            'currentJobdeskVersion.version',
            'jobdeskVersions.version',
        ];
    }

    private function loadPegawaiProfile(Pegawai $pegawai): Pegawai
    {
        return Pegawai::query()
            ->with($this->pegawaiProfileRelations())
            ->where('nip', (string) $pegawai->nip)
            ->firstOrFail();
    }

    /* ===================== EDIT ===================== */
    public function edit(Pegawai $pegawai)
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'hcm']), 403);

        $pegawai = $this->loadPegawaiProfile($pegawai);

        $jabatans = $this->jabatanOptions();
        $departemenList = $this->departemenOptions();

        return view('pegawai.edit', compact('pegawai', 'jabatans', 'departemenList'));
    }

    /* ===================== UPDATE ===================== */
    public function update(Request $request, Pegawai $pegawai)
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'hcm']), 403);

        $pegawaiPrefix = auth()->user()->role; // admin / hcm

        // NIP tetap diperlakukan sebagai string agar angka 0 di depan aman.
        $request->merge([
            'nip' => trim((string) $request->input('nip', $pegawai->nip)),
        ]);

        $validated = $request->validate([
            'nip'             => ['required', 'string', 'max:50'],
            'nama'            => ['required', 'string', 'max:100'],
            'foto'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'tempat_lahir'    => ['nullable', 'string'],
            'tgl_lahir'       => ['nullable', 'date'],
            'jenkel'          => ['nullable', 'string'],
            'agama'           => ['nullable', 'string'],
            'alamat'          => ['nullable', 'string'],
            'gol_upah'        => ['nullable', 'integer'],
            'gol_jabatan'     => ['nullable', 'integer'],
            'id_jabatan'      => ['nullable', 'integer', 'exists:tb_jabatan,id_jabatan'],
            'id_departemen'   => ['nullable', 'integer', 'exists:tb_departemen,id_departemen'],
            'tmt_gol_jabatan' => ['nullable', 'date'],
            'tmt_gol_upah'    => ['nullable', 'date'],
            'jabatan'         => ['nullable', 'string'],
            'departemen'      => ['nullable', 'string'],
            'hubungan_kerja'  => ['nullable', 'string'],
            'lokasi_kerja'    => ['nullable', 'string'],
            'status'          => ['nullable', 'string'],
            'tgl_masuk'       => ['nullable', 'date'],
            'profesional'     => ['nullable', 'string'],

            'pendidikan'      => ['nullable', 'array'],
            'kursus'          => ['nullable', 'array'],
            'peng_bsp'        => ['nullable', 'array'],
            'peng_luar'       => ['nullable', 'array'],
            'keluarga'        => ['nullable', 'array'],
            'penilaian'       => ['nullable', 'array'],
        ]);

        $selectedJabatan = $this->resolveSelectedJabatan($request);
        $oldIdJabatan = $pegawai->id_jabatan;

        DB::beginTransaction();

        try {
            $data = $this->preparePegawaiData($validated, $request, true, $pegawai, $selectedJabatan);

            // NIP lama tetap dipakai supaya relasi anak tidak putus
            $data['nip'] = $pegawai->nip;

            $pegawai->update($data);

            // Simpan/update data section 2 sampai 7
            $this->syncChildren($pegawai, $request);

            /*
            |--------------------------------------------------------------------------
            | Jobdesk Versioning
            |--------------------------------------------------------------------------
            | Kalau jabatan pegawai berubah, tutup jobdesk aktif lama dan assign versi
            | aktif dari jabatan baru. Kalau jabatan sama, tidak mengubah riwayat.
            |--------------------------------------------------------------------------
            */
            if ((string) $oldIdJabatan !== (string) $pegawai->id_jabatan) {
                $this->syncPegawaiJobdeskVersion($pegawai->fresh(), $selectedJabatan);
            }

            DB::commit();

            return redirect()
                ->route($pegawaiPrefix . '.pegawai.index')
                ->with('success_auto', 'Data pegawai berhasil diperbaharui');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Update gagal: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /* ===================== DELETE ===================== */
    public function destroy($nip)
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'hcm']), 403);

        $pegawaiPrefix = auth()->user()->role; // admin / hcm

        $pegawai = Pegawai::where('nip', $nip)->firstOrFail();

        DB::beginTransaction();

        try {
            if ($pegawai->foto) {
                Storage::disk('public')->delete($pegawai->foto);
            }

            $pegawai->delete();

            DB::commit();

            return redirect()
                ->route($pegawaiPrefix . '.pegawai.index')
                ->with('success_auto', 'Data pegawai berhasil dihapus');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Hapus gagal: ' . $e->getMessage()]);
        }
    }

    /* ===================== HELPER ===================== */
    protected function preparePegawaiData(
        array $validated,
        Request $request,
        bool $isUpdate = false,
        ?Pegawai $pegawai = null,
        ?Jabatan $selectedJabatan = null
    ): array {
        $golJabatan = $validated['gol_jabatan'] ?? null;

        if ($selectedJabatan && $selectedJabatan->gol_jabatan !== null && $selectedJabatan->gol_jabatan !== '') {
            $golJabatan = is_numeric($selectedJabatan->gol_jabatan)
                ? (int) $selectedJabatan->gol_jabatan
                : $golJabatan;
        }

        /*
        |--------------------------------------------------------------------------
        | Departemen
        |--------------------------------------------------------------------------
        | Jika jabatan dipilih, departemen mengikuti master jabatan.
        | Jika jabatan kosong, departemen boleh dari dropdown departemen.
        |--------------------------------------------------------------------------
        */
        $idDepartemen = $selectedJabatan?->id_departemen
            ?? ($validated['id_departemen'] ?? null);

        $departemenMaster = null;

        if ($idDepartemen) {
            $departemenMaster = Departemen::where('id_departemen', $idDepartemen)->first();
        }

        $namaDepartemen = $departemenMaster?->nama_departemen
            ?? $selectedJabatan?->departemen
            ?? ($validated['departemen'] ?? null);

        $data = [
            'nip'             => $isUpdate ? (string) $pegawai->nip : (string) ($validated['nip'] ?? ''),
            'nama'            => $validated['nama'] ?? '',
            'tempat_lahir'    => $validated['tempat_lahir'] ?? null,
            'tgl_lahir'       => $validated['tgl_lahir'] ?? null,
            'jenkel'          => $validated['jenkel'] ?? null,
            'agama'           => $validated['agama'] ?? null,
            'alamat'          => $validated['alamat'] ?? null,
            'gol_upah'        => $validated['gol_upah'] ?? null,

            /*
            |--------------------------------------------------------------------------
            | Jabatan
            |--------------------------------------------------------------------------
            | Tidak ada pembatasan 1 jabatan hanya untuk 1 pegawai.
            | Banyak pegawai boleh memakai id_jabatan yang sama sesuai formasi RPTK.
            |--------------------------------------------------------------------------
            */
            'id_jabatan'      => $selectedJabatan?->id_jabatan,
            'jabatan'         => $selectedJabatan?->nama_jabatan ?? null,

            'id_departemen'   => $idDepartemen,
            'departemen'      => $namaDepartemen,

            'gol_jabatan'     => $golJabatan,

            // Lokasi dari form diprioritaskan, baru fallback ke master jabatan.
            'lokasi_kerja'    => $validated['lokasi_kerja']
                ?? $selectedJabatan?->lokasi_kerja
                ?? null,

            'tmt_gol_jabatan' => $validated['tmt_gol_jabatan'] ?? null,
            'tmt_gol_upah'    => $validated['tmt_gol_upah'] ?? null,
            'hubungan_kerja'  => $validated['hubungan_kerja'] ?? null,
            'status'          => $validated['status'] ?? null,
            'tgl_masuk'       => $validated['tgl_masuk'] ?? null,
            'profesional'     => $validated['profesional'] ?? null,
        ];

        if ($request->hasFile('foto')) {
            if ($isUpdate && $pegawai && $pegawai->foto) {
                Storage::disk('public')->delete($pegawai->foto);
            }

            $data['foto'] = $request->file('foto')->store('karyawan', 'public');
        }

        return $data;
    }

    private function jabatanOptions()
    {
        return Jabatan::query()
            ->with('departemenMaster')
            ->select(
                'id_jabatan',
                'nama_jabatan',
                'departemen',
                'id_departemen',
                'gol_jabatan',
                'lokasi_kerja'
            )
            ->whereNotNull('nama_jabatan')
            ->orderBy('id_departemen')
            ->orderBy('nama_jabatan')
            ->get();
    }

    private function departemenOptions()
    {
        return Departemen::query()
            ->where(function ($q) {
                $q->where('is_active', 1)
                    ->orWhereNull('is_active');
            })
            ->orderBy('level_departemen')
            ->orderBy('urutan')
            ->orderBy('nama_departemen')
            ->get();
    }

    private function resolveSelectedJabatan(Request $request): ?Jabatan
    {
        $idJabatan = $request->input('id_jabatan');

        if ($idJabatan === null || $idJabatan === '') {
            return null;
        }

        $jabatan = Jabatan::with('departemenMaster')->find($idJabatan);

        if (!$jabatan) {
            throw ValidationException::withMessages([
                'id_jabatan' => 'Jabatan yang dipilih tidak ditemukan.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi kesesuaian departemen
        |--------------------------------------------------------------------------
        | Ini bukan validasi pemangku. Jabatan tetap boleh dipakai banyak pegawai.
        | Cek ini hanya memastikan dropdown departemen dan jabatan tidak silang.
        |--------------------------------------------------------------------------
        */
        $idDepartemenInput = $request->input('id_departemen');

        if (
            $idDepartemenInput !== null &&
            $idDepartemenInput !== '' &&
            $jabatan->id_departemen !== null &&
            (string) $jabatan->id_departemen !== (string) $idDepartemenInput
        ) {
            throw ValidationException::withMessages([
                'id_jabatan' => 'Jabatan yang dipilih tidak sesuai dengan departemen.',
            ]);
        }

        return $jabatan;
    }

    // Sync related data (update existing or delete if no longer present)
    protected function syncChildren(Pegawai $pegawai, Request $request): void
    {
        foreach ($this->childRelationMap() as $inputKey => $config) {
            $relationName = $config['relation'];
            $primaryKey   = $config['primary'];

            // Jika section tidak ikut terkirim, jangan hapus data lama.
            // Ini aman untuk form bertahap/tab dan menjaga logika sistem lama.
            if (!array_key_exists($inputKey, $request->all())) {
                continue;
            }

            $submittedRows = $request->input($inputKey, []);

            if (!is_array($submittedRows)) {
                $submittedRows = [];
            }

            $keptIds = [];

            foreach ($submittedRows as $row) {
                $row = $this->normalizeChildRow($row, $primaryKey);

                if ($row === null) {
                    continue;
                }

                $id = $row[$primaryKey] ?? null;

                unset($row[$primaryKey]);
                $row['nip'] = (string) $pegawai->nip;

                if ($id !== null && $id !== '') {
                    $existing = $pegawai->{$relationName}()
                        ->where($primaryKey, $id)
                        ->first();

                    if ($existing) {
                        $existing->update($row);
                        $keptIds[] = $existing->{$primaryKey};
                    } else {
                        $new = $pegawai->{$relationName}()->create($row);
                        $keptIds[] = $new->{$primaryKey};
                    }
                } else {
                    $new = $pegawai->{$relationName}()->create($row);
                    $keptIds[] = $new->{$primaryKey};
                }
            }

            if (!empty($keptIds)) {
                $pegawai->{$relationName}()
                    ->whereNotIn($primaryKey, $keptIds)
                    ->delete();
            } else {
                $pegawai->{$relationName}()->delete();
            }
        }
    }

    public function foto(Pegawai $pegawai)
    {
        $user = auth()->user();

        if (in_array($user->role, ['admin', 'hcm'])) {
            // boleh akses
        } elseif ($user->role === 'pegawai') {
            $nipUser = (string) ($user->nip ?? $user->username ?? '');
            abort_unless($nipUser === (string) $pegawai->nip, 403, 'Anda tidak memiliki akses');
        } else {
            abort(403, 'Anda tidak memiliki akses');
        }

        $fotoPath = $this->normalizeFotoPath($pegawai->foto);

        abort_if(
            !$fotoPath || !Storage::disk('public')->exists($fotoPath),
            404,
            'Foto tidak ditemukan.'
        );

        return response()->file(Storage::disk('public')->path($fotoPath));
    }

    // Menampilkan form untuk ganti password
    public function showChangePasswordForm()
    {
        // Pastikan hanya pegawai yang bisa mengakses halaman ganti password
        abort_unless(auth()->user()->role === 'pegawai', 403);

        return view('auth.change-password');
    }

    public function jobDescription()
    {
        abort_unless(auth()->user()->role === 'pegawai', 403);

        $user = auth()->user();
        $nip = $user->nip ?? $user->username;

        $pegawai = Pegawai::with([
                'masterJabatan',
                'currentJobdeskVersion.version',
                'jobdeskVersions.version',
            ])
            ->where('nip', $nip)
            ->first();

        $currentAssignment = $pegawai?->currentJobdeskVersion;
        $currentVersion = $currentAssignment?->version;
        $jabatan = $pegawai?->masterJabatan;

        $jabatanNotFound = false;

        /*
        |--------------------------------------------------------------------------
        | Backward Compatible Fallback
        |--------------------------------------------------------------------------
        | View lama jabatan.show tetap dipakai. Jika sudah ada versioning, $jabatan
        | diisi dari masterJabatan dan view bisa menampilkan $currentVersion.
        | Jika belum ada versi, sistem tetap menampilkan master jabatan lama.
        |--------------------------------------------------------------------------
        */
        if (!$pegawai || !$jabatan) {
            $jabatan = new Jabatan();
            $jabatanNotFound = true;
        }

        return view('jabatan.show', compact(
            'jabatan',
            'pegawai',
            'jabatanNotFound',
            'currentAssignment',
            'currentVersion'
        ));
    }

    public function jobDescriptionDetail(PegawaiJabatanVersion $assignment)
    {
        abort_unless(auth()->user()->role === 'pegawai', 403);

        $user = auth()->user();
        $nipUser = (string) ($user->nip ?? $user->username ?? '');

        abort_unless((string) $assignment->nip === $nipUser, 403, 'Anda tidak memiliki akses');

        $assignment->load(['jabatan', 'version']);

        $pegawai = Pegawai::with('masterJabatan')
            ->where('nip', $nipUser)
            ->first();

        $jabatan = $assignment->jabatan ?? $pegawai?->masterJabatan ?? new Jabatan();
        $currentAssignment = $assignment;
        $currentVersion = $assignment->version;
        $jabatanNotFound = !$currentVersion;

        return view('jabatan.show', compact(
            'jabatan',
            'pegawai',
            'jabatanNotFound',
            'currentAssignment',
            'currentVersion'
        ));
    }

    public function acknowledgeJobDescription(Request $request, PegawaiJabatanVersion $assignment)
    {
        abort_unless(auth()->user()->role === 'pegawai', 403);

        $user = auth()->user();
        $nipUser = (string) ($user->nip ?? $user->username ?? '');

        abort_unless((string) $assignment->nip === $nipUser, 403, 'Anda tidak memiliki akses');

        if (!$assignment->acknowledged_at) {
            $assignment->update([
                'acknowledged_at' => now(),
                'acknowledged_ip' => $request->ip(),
                'acknowledged_user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]);
        }

        return back()->with('success_auto', 'Job description berhasil ditandai sudah dibaca.');
    }

    // Proses perubahan password
    public function changePassword(Request $request)
    {
        // Validasi password
        $validated = $request->validate([
            'password' => ['required', 'confirmed', 'min:8'],  // Pastikan password baru cukup kuat
        ]);

        $user = auth()->user();

        // Cek jika password yang baru berbeda dengan password lama
        if (Hash::check($validated['password'], $user->password)) {
            return back()->withErrors(['password' => 'Password baru tidak boleh sama dengan yang lama.']);
        }

        // Update password dengan password baru yang sudah di-hash
        $user->password = Hash::make($validated['password']);
        $user->save();

        // Redirect ke halaman profil atau dashboard setelah password berhasil diubah
        return redirect()->route('pegawai.saya')->with('success', 'Password berhasil diubah');
    }

    private function syncPegawaiJobdeskVersion(Pegawai $pegawai, ?Jabatan $selectedJabatan = null): void
    {
        if (!$pegawai->id_jabatan) {
            $this->closeCurrentJobdeskAssignment($pegawai);
            return;
        }

        $jabatan = $selectedJabatan ?: Jabatan::find($pegawai->id_jabatan);

        if (!$jabatan) {
            $this->closeCurrentJobdeskAssignment($pegawai);
            return;
        }

        $activeVersion = $this->resolveActiveJabatanVersion($jabatan);

        if (!$activeVersion) {
            return;
        }

        $currentAssignment = PegawaiJabatanVersion::where('nip', $pegawai->nip)
            ->where('is_current', 1)
            ->first();

        if (
            $currentAssignment &&
            (int) $currentAssignment->id_jabatan === (int) $jabatan->id_jabatan &&
            (int) $currentAssignment->id_jabatan_version === (int) $activeVersion->id_jabatan_version
        ) {
            return;
        }

        $this->closeCurrentJobdeskAssignment($pegawai);

        $user = auth()->user();

        PegawaiJabatanVersion::create([
            'nip' => $pegawai->nip,
            'id_jabatan' => $jabatan->id_jabatan,
            'id_jabatan_version' => $activeVersion->id_jabatan_version,
            'assigned_at' => now(),
            'assigned_by' => $user?->getKey(),
            'assigned_by_name' => $user->nama ?? $user->name ?? $user->username ?? null,
            'is_current' => 1,
        ]);
    }

    private function closeCurrentJobdeskAssignment(Pegawai $pegawai): void
    {
        $user = auth()->user();

        PegawaiJabatanVersion::where('nip', $pegawai->nip)
            ->where('is_current', 1)
            ->update([
                'is_current' => 0,
                'ended_at' => now(),
                'ended_by' => $user?->getKey(),
                'ended_by_name' => $user->nama ?? $user->name ?? $user->username ?? null,
            ]);
    }

    private function resolveActiveJabatanVersion(Jabatan $jabatan): ?JabatanVersion
    {
        if (!empty($jabatan->active_version_id)) {
            $version = JabatanVersion::where('id_jabatan_version', $jabatan->active_version_id)
                ->where('status', 'approved')
                ->first();

            if ($version) {
                return $version;
            }
        }

        return JabatanVersion::where('id_jabatan', $jabatan->id_jabatan)
            ->where('status', 'approved')
            ->orderByDesc('version_number')
            ->first();
    }
}
