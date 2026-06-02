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
use Illuminate\Http\Request;
use App\Models\Jabatan;
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

        return view('pegawai.create', compact('jabatans'));
    }

    /* ===================== STORE ===================== */
 public function store(Request $request)
{
    abort_unless(in_array(auth()->user()->role, ['admin', 'hcm']), 403);

    $pegawaiPrefix = auth()->user()->role; // admin / hcm

    $request->validate([
        'nip'  => ['required', 'integer'],
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
        'nip'             => ['required', 'integer'],
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

        DB::commit();

        return redirect()
            ->route($pegawaiPrefix . '.pegawai.index')
            ->with('success', 'Data pegawai berhasil disimpan');

    } catch (\Throwable $e) {
        DB::rollBack();

        return back()
            ->withErrors(['error' => 'Simpan gagal: ' . $e->getMessage()])
            ->withInput();
    }
}

// ===================== HELPER =====================
    protected function storeChildren(Pegawai $pegawai, Request $request)
    {
        $map = [
            'pendidikan' => ['relation' => 'pendidikan', 'primary' => 'id_pendidikan'],
            'kursus'     => ['relation' => 'kursus', 'primary' => 'id_kursus'],
            'peng_bsp'   => ['relation' => 'pengalamanBsp', 'primary' => 'id_pengalaman_bsp'],
            'peng_luar'  => ['relation' => 'pengalamanLuarBsp', 'primary' => 'id_pengalaman_luar_bsp'],
            'keluarga'   => ['relation' => 'keluarga', 'primary' => 'id_keluarga'],
            'penilaian'  => ['relation' => 'penilaian', 'primary' => 'id_penilaian'],
        ];

        foreach ($map as $inputKey => $config) {
            $relationName = $config['relation'];
            $primaryKey   = $config['primary'];

            foreach ($request->input($inputKey, []) as $row) {
                if (collect($row)->except([$primaryKey])->filter(fn($v) => $v !== null && $v !== '')->isEmpty()) {
                    continue;
                }

                unset($row[$primaryKey]);
                $row['nip'] = $pegawai->nip;

                $pegawai->{$relationName}()->create($row);
            }
        }
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

    // Admin or HCM roles can view all employee data
    if (in_array($user->role, ['admin', 'hcm'])) {
        $pegawai->load([
            'pendidikan',
            'kursus',
            'pengalamanBsp',
            'pengalamanLuarBsp',
            'keluarga',
            'penilaian',
        ]);

        return view('pegawai.show', compact('pegawai'));
    }

    // For "pegawai" role, only allow access to their own data
    if ($user->role === 'pegawai') {
        $nipUser = (string) ($user->nip ?? $user->username ?? '');

        abort_unless($nipUser === (string) $pegawai->nip, 403, 'Anda tidak memiliki akses');

        $pegawai->load([
            'pendidikan',
            'kursus',
            'pengalamanBsp',
            'pengalamanLuarBsp',
            'keluarga',
            'penilaian',
        ]);

        return view('pegawai.show', compact('pegawai'));
    }

    abort(403, 'Anda tidak memiliki akses');
}

   /* ===================== EDIT ===================== */
public function edit(Pegawai $pegawai)
{
    abort_unless(in_array(auth()->user()->role, ['admin', 'hcm']), 403);

    $pegawai->load([
        'pendidikan',
        'kursus',
        'pengalamanBsp',
        'pengalamanLuarBsp',
        'keluarga',
        'penilaian',
        'masterJabatan',
    ]);

    $jabatans = $this->jabatanOptions();

    return view('pegawai.edit', compact('pegawai', 'jabatans'));
}
   /* ===================== UPDATE ===================== */
public function update(Request $request, Pegawai $pegawai)
{
    abort_unless(in_array(auth()->user()->role, ['admin', 'hcm']), 403);

    $pegawaiPrefix = auth()->user()->role; // admin / hcm

    $validated = $request->validate([
        'nip'             => ['required', 'integer'],
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
        $data = $this->preparePegawaiData($validated, $request, true, $pegawai, $selectedJabatan);
        // NIP lama tetap dipakai supaya relasi anak tidak putus
        $data['nip'] = $pegawai->nip;

        $pegawai->update($data);

        // Simpan/update data section 2 sampai 7
        $this->syncChildren($pegawai, $request);

        DB::commit();

        return redirect()
            ->route($pegawaiPrefix . '.pegawai.index')
            ->with('success', 'Data pegawai berhasil diperbaharui');

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
            ->with('success', 'Data pegawai berhasil dihapus');

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

    $data = [
        'nip'             => $isUpdate ? $pegawai->nip : ($validated['nip'] ?? null),
        'nama'            => $validated['nama'] ?? '',
        'tempat_lahir'    => $validated['tempat_lahir'] ?? null,
        'tgl_lahir'       => $validated['tgl_lahir'] ?? null,
        'jenkel'          => $validated['jenkel'] ?? null,
        'agama'           => $validated['agama'] ?? null,
        'alamat'          => $validated['alamat'] ?? null,
        'gol_upah'        => $validated['gol_upah'] ?? null,

        'id_jabatan'      => $selectedJabatan?->id_jabatan,
        'jabatan'         => $selectedJabatan?->nama_jabatan ?? null,
        'departemen'      => $selectedJabatan?->departemen ?? ($validated['departemen'] ?? null),
        'gol_jabatan'     => $golJabatan,
        'lokasi_kerja'    => $selectedJabatan?->lokasi_kerja ?? ($validated['lokasi_kerja'] ?? null),

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
        ->select('id_jabatan', 'nama_jabatan', 'departemen', 'gol_jabatan', 'lokasi_kerja')
        ->whereNotNull('nama_jabatan')
        ->orderBy('departemen')
        ->orderBy('nama_jabatan')
        ->get();
}

private function resolveSelectedJabatan(Request $request): ?Jabatan
{
    $idJabatan = $request->input('id_jabatan');

    if ($idJabatan === null || $idJabatan === '') {
        return null;
    }

    $jabatan = Jabatan::find($idJabatan);

    if (!$jabatan) {
        throw ValidationException::withMessages([
            'id_jabatan' => 'Jabatan yang dipilih tidak ditemukan.',
        ]);
    }

    $departemen = $request->input('departemen');

    if ($departemen && $jabatan->departemen !== $departemen) {
        throw ValidationException::withMessages([
            'id_jabatan' => 'Jabatan yang dipilih tidak sesuai dengan departemen.',
        ]);
    }

    return $jabatan;
}

// Sync related data (update existing or delete if no longer present)
protected function syncChildren(Pegawai $pegawai, Request $request)
{
    $map = [
        'pendidikan' => ['relation' => 'pendidikan', 'primary' => 'id_pendidikan'],
        'kursus'     => ['relation' => 'kursus', 'primary' => 'id_kursus'],
        'peng_bsp'   => ['relation' => 'pengalamanBsp', 'primary' => 'id_pengalaman_bsp'],
        'peng_luar'  => ['relation' => 'pengalamanLuarBsp', 'primary' => 'id_pengalaman_luar_bsp'],
        'keluarga'   => ['relation' => 'keluarga', 'primary' => 'id_keluarga'],
        'penilaian'  => ['relation' => 'penilaian', 'primary' => 'id_penilaian'],
    ];

    foreach ($map as $inputKey => $config) {
        $relationName = $config['relation'];
        $primaryKey   = $config['primary'];

        // PENTING:
        // Kalau input section tidak ikut terkirim, jangan hapus data lama.
        // Ini mencegah kasus data section 2-7 hilang saat hanya edit section 1.
        $allInput = $request->all();
        if (!array_key_exists($inputKey, $allInput)) {
            continue;
        }

        $submittedRows = $request->input($inputKey, []);
        $keptIds = [];

        if (!is_array($submittedRows)) {
            $submittedRows = [];
        }

        foreach ($submittedRows as $row) {
            if (!is_array($row)) {
                continue;
            }

            // Ubah string kosong menjadi null agar aman untuk kolom nullable/date.
            $row = collect($row)->map(function ($value) {
                return $value === '' ? null : $value;
            })->toArray();

            // Lewati baris yang benar-benar kosong, kecuali primary key.
            $isEmptyRow = collect($row)
                ->except([$primaryKey])
                ->filter(function ($value) {
                    return $value !== null && $value !== '';
                })
                ->isEmpty();

            if ($isEmptyRow) {
                continue;
            }

            $id = $row[$primaryKey] ?? null;

            unset($row[$primaryKey]);
            $row['nip'] = $pegawai->nip;

            if (!empty($id)) {
                $existing = $pegawai->{$relationName}()
                    ->where($primaryKey, $id)
                    ->first();

                if ($existing) {
                    $existing->update($row);
                    $keptIds[] = $id;
                } else {
                    $new = $pegawai->{$relationName}()->create($row);
                    $keptIds[] = $new->{$primaryKey};
                }
            } else {
                $new = $pegawai->{$relationName}()->create($row);
                $keptIds[] = $new->{$primaryKey};
            }
        }

        // Hapus data lama yang memang tidak ada lagi di form.
        // Kalau form section dikirim tapi semua baris kosong, artinya section itu dikosongkan.
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

    $pegawai = Pegawai::with('masterJabatan')
        ->where('nip', $nip)
        ->first();

    $jabatan = $pegawai?->masterJabatan;

    $jabatanNotFound = false;

    if (!$pegawai || !$jabatan) {
        $jabatan = new Jabatan();
        $jabatanNotFound = true;
    }

    return view('jabatan.show', compact('jabatan', 'pegawai', 'jabatanNotFound'));
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
   
}