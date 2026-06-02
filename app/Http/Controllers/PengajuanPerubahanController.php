<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\PengajuanPerubahan;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PengajuanPerubahanController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX REVIEWER: ADMIN / HCM
    |--------------------------------------------------------------------------
    */
    public function indexReviewer(Request $request)
    {
        $this->authorizeReviewer();

        $baseQuery = PengajuanPerubahan::with('pegawai')
            ->search($request->search);

        $pengajuanAktif = (clone $baseQuery)
            ->whereIn('status', ['diajukan', 'pending', 'belum_diolah', 'diproses'])
            ->latest('created_at')
            ->get();

        $riwayatPengajuan = (clone $baseQuery)
            ->whereIn('status', ['diterima', 'disetujui', 'ditolak'])
            ->latest('created_at')
            ->get();

        return view('pengajuan.reviewer.index', compact('pengajuanAktif', 'riwayatPengajuan'));
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW REVIEWER: ADMIN / HCM
    |--------------------------------------------------------------------------
    */
    public function showReviewer(PengajuanPerubahan $pengajuan)
    {
        $this->authorizeReviewer();

        if (in_array($pengajuan->status, ['diajukan', 'pending', 'belum_diolah'])) {
            $pengajuan->update([
                'status' => 'diproses',
                'dilihat_pada' => now(),
                'id_user_proses' => auth()->user()->getKey(),
                'role_pemroses' => auth()->user()->role,
            ]);
        }

        $pengajuan->load('pegawai');

        $payload = $pengajuan->payload ?? [];

        return view('pengajuan.reviewer.show', compact('pengajuan', 'payload'));
    }

    /*
    |--------------------------------------------------------------------------
    | TANDAI DIPROSES
    |--------------------------------------------------------------------------
    */
    public function proses(PengajuanPerubahan $pengajuan)
    {
        $this->authorizeReviewer();

        if (in_array($pengajuan->status, ['diterima', 'ditolak', 'disetujui'])) {
            return back()->withErrors([
                'status' => 'Pengajuan ini sudah selesai diproses.'
            ]);
        }

        $pengajuan->update([
            'status' => 'diproses',
            'dilihat_pada' => now(),
            'id_user_proses' => auth()->user()->getKey(),
            'role_pemroses' => auth()->user()->role,
        ]);

        return back()->with('success', 'Pengajuan berhasil ditandai sedang diproses.');
    }

    /*
    |--------------------------------------------------------------------------
    | TERIMA / APPROVE
    |--------------------------------------------------------------------------
    */
    public function terima(Request $request, PengajuanPerubahan $pengajuan)
    {
        $this->authorizeReviewer();

        if (in_array($pengajuan->status, ['diterima', 'ditolak', 'disetujui'])) {
            return back()->withErrors([
                'status' => 'Pengajuan ini sudah selesai diproses.'
            ]);
        }

        $request->validate([
            'catatan_reviewer' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $pengajuan) {
            $this->applyPayloadToPegawai($pengajuan);

            $pengajuan->update([
                'status' => 'diterima',
                'catatan_reviewer' => $request->catatan_reviewer,
                'diproses_pada' => now(),
                'id_user_proses' => auth()->user()->getKey(),
                'role_pemroses' => auth()->user()->role,
            ]);
        });

        return redirect()
            ->route(auth()->user()->role . '.pengajuan.index')
            ->with('success', 'Pengajuan diterima. Data pegawai berhasil diperbarui.');
    }

    /*
    |--------------------------------------------------------------------------
    | TOLAK
    |--------------------------------------------------------------------------
    */
    public function tolak(Request $request, PengajuanPerubahan $pengajuan)
    {
        $this->authorizeReviewer();

        if (in_array($pengajuan->status, ['diterima', 'ditolak', 'disetujui'])) {
            return back()->withErrors([
                'status' => 'Pengajuan ini sudah selesai diproses.'
            ]);
        }

        $request->validate([
            'catatan_reviewer' => 'required|string',
        ]);

        $pengajuan->update([
            'status' => 'ditolak',
            'catatan_reviewer' => $request->catatan_reviewer,
            'ditolak_pada' => now(),
            'id_user_proses' => auth()->user()->getKey(),
            'role_pemroses' => auth()->user()->role,
        ]);

        return redirect()
            ->route(auth()->user()->role . '.pengajuan.index')
            ->with('success', 'Pengajuan berhasil ditolak.');
    }

    /*
    |--------------------------------------------------------------------------
    | HAPUS PENGAJUAN REVIEWER
    |--------------------------------------------------------------------------
    */
    public function destroyReviewer(PengajuanPerubahan $pengajuan)
    {
        $this->authorizeReviewer();

        $pengajuan->delete();

        return back()->with('success', 'Pengajuan berhasil dihapus.');
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX PEGAWAI
    |--------------------------------------------------------------------------
    */
    public function indexPegawai(Request $request)
    {
        $user = auth()->user();

        $nip = $user->nip ?? $user->username;

        $list = PengajuanPerubahan::with('pegawai')
            ->where('nip', $nip)
            ->status($request->status)
            ->search($request->search)
            ->latest('created_at')
            ->get();

        return view('pengajuan.pegawai.index', compact('list'));
    }

    /*
    |--------------------------------------------------------------------------
    | FORM CREATE PENGAJUAN PEGAWAI
    |--------------------------------------------------------------------------
    */
    public function createPegawai()
    {
        $user = auth()->user();

        $nip = $user->nip ?? $user->username;

        $pegawai = Pegawai::with([
            'pendidikan',
            'kursus',
            'pengalamanBsp',
            'pengalamanLuarBsp',
            'keluarga',
            'penilaian',
            'masterJabatan',
        ])->where('nip', $nip)->firstOrFail();

        $jabatans = Jabatan::query()
            ->select('id_jabatan', 'nama_jabatan', 'departemen', 'gol_jabatan', 'lokasi_kerja')
            ->whereNotNull('nama_jabatan')
            ->orderBy('departemen')
            ->orderBy('nama_jabatan')
            ->get();

        return view('pengajuan.pegawai.create', compact('pegawai', 'jabatans'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE PENGAJUAN PEGAWAI
    |--------------------------------------------------------------------------
    */
    public function storePegawai(Request $request)
    {
        return $this->savePengajuan($request);
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW PEGAWAI
    |--------------------------------------------------------------------------
    */
    public function showPegawai(PengajuanPerubahan $pengajuan)
    {
        $user = auth()->user();

        $nip = $user->nip ?? $user->username;

        abort_unless($pengajuan->nip == $nip, 403);

        $pengajuan->load('pegawai');

        $payload = $pengajuan->payload ?? [];

        return view('pengajuan.pegawai.show', compact('pengajuan', 'payload'));
    }

    /*
    |--------------------------------------------------------------------------
    | ALIAS METHOD BIAR AMAN JIKA ADA ROUTE LAMA
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        if (in_array(auth()->user()->role, ['admin', 'hcm'])) {
            return $this->indexReviewer($request);
        }

        return $this->indexPegawai($request);
    }

    public function store(Request $request)
    {
        return $this->savePengajuan($request);
    }

    public function show(PengajuanPerubahan $pengajuan)
    {
        if (in_array(auth()->user()->role, ['admin', 'hcm'])) {
            return $this->showReviewer($pengajuan);
        }

        return $this->showPegawai($pengajuan);
    }

    public function approve(Request $request, PengajuanPerubahan $pengajuan)
    {
        return $this->terima($request, $pengajuan);
    }

    public function reject(Request $request, PengajuanPerubahan $pengajuan)
    {
        return $this->tolak($request, $pengajuan);
    }

    public function destroy(PengajuanPerubahan $pengajuan)
    {
        return $this->destroyReviewer($pengajuan);
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPAN PENGAJUAN KE DRAFT / TABEL PENGAJUAN
    |--------------------------------------------------------------------------
    */
    private function savePengajuan(Request $request)
    {
        $user = auth()->user();

        $nip = $user->role === 'pegawai'
            ? ($user->nip ?? $user->username)
            : $request->input('nip');

        if (!$nip) {
            return back()->withErrors([
                'nip' => 'NIP tidak ditemukan.'
            ]);
        }

        $pegawai = Pegawai::where('nip', $nip)->first();

        $adaPengajuanAktif = PengajuanPerubahan::where('nip', $nip)
            ->whereIn('status', ['diajukan', 'pending', 'belum_diolah', 'diproses'])
            ->exists();

        if ($adaPengajuanAktif) {
            return back()->withErrors([
                'pengajuan' => 'Masih ada pengajuan perubahan yang belum selesai diproses.'
            ]);
        }

        $request->validate([
            'nama'             => 'nullable|string|max:100',
            'tempat_lahir'     => 'nullable|string|max:100',
            'tgl_lahir'        => 'nullable|date',
            'jenkel'           => 'nullable|string|max:20',
            'agama'            => 'nullable|string|max:50',
            'alamat'           => 'nullable|string',
            'profesional'      => 'nullable|string|max:50',

            'tmt_gol_jabatan'  => 'nullable|date',
            'gol_jabatan'      => 'nullable|numeric',
            'id_jabatan'       => 'nullable|integer|exists:tb_jabatan,id_jabatan',
            'jabatan'          => 'nullable|string|max:100',
            'departemen'       => 'nullable|string|max:100',

            'hubungan_kerja'   => 'nullable|string|max:50',
            'lokasi_kerja'     => 'nullable|string|max:50',
            'status'           => 'nullable|string|max:50',
            'tmt_gol_upah'     => 'nullable|date',
            'gol_upah'         => 'nullable|numeric',
            'tgl_masuk'        => 'nullable|date',
            'foto'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'pendidikan'       => 'nullable|array',
            'kursus'           => 'nullable|array',
            'peng_bsp'         => 'nullable|array',
            'peng_luar'        => 'nullable|array',
            'keluarga'         => 'nullable|array',
            'penilaian'        => 'nullable|array',
            'catatan_pegawai'  => 'nullable|string',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Ambil data jabatan dari master tb_jabatan
        | Jika pegawai memilih id_jabatan, maka jabatan/departemen/gol/lokasi
        | disesuaikan dengan master jabatan agar data tidak beda-beda.
        |--------------------------------------------------------------------------
        */
        $selectedJabatan = $this->resolveSelectedJabatan($request);

        if ($selectedJabatan) {
            $request->merge([
                'id_jabatan'   => $selectedJabatan->id_jabatan,
                'jabatan'      => $selectedJabatan->nama_jabatan,
                'departemen'   => $selectedJabatan->departemen,
                'gol_jabatan'  => is_numeric($selectedJabatan->gol_jabatan)
                    ? (int) $selectedJabatan->gol_jabatan
                    : $request->input('gol_jabatan'),
                'lokasi_kerja' => $selectedJabatan->lokasi_kerja ?: $request->input('lokasi_kerja'),
            ]);
        }

        $pegawaiPayload = [];

        foreach ($this->pegawaiFields() as $field) {
            if ($request->has($field)) {
                $value = $request->input($field);
                $pegawaiPayload[$field] = $value === '' ? null : $value;
            }
        }

        if ($request->hasFile('foto')) {
            $pegawaiPayload['foto'] = $request->file('foto')->store('pengajuan/foto', 'public');
        }

        $payload = [
            'pegawai' => $pegawaiPayload,
        ];

        foreach ($this->sectionKeys() as $key) {
            if ($request->has($key)) {
                $payload[$key] = $this->cleanRows($request->input($key, []));
            }
        }

        PengajuanPerubahan::create([
            'nip'              => $nip,
            'jenis'            => 'perubahan_data',
            'nama_pegawai'     => $pegawaiPayload['nama'] ?? $pegawai->nama ?? null,
            'id_user_pengaju'  => $user->getKey(),
            'role_pengaju'     => $user->role,
            'status'           => 'diajukan',
            'payload'          => $payload,
            'catatan_pegawai'  => $request->catatan_pegawai,
        ]);

        return redirect()
            ->route('pegawai.pengajuan.index')
            ->with('success', 'Pengajuan perubahan berhasil dikirim dan menunggu persetujuan HCM/Admin.');
    }

    /*
    |--------------------------------------------------------------------------
    | APPLY DATA KE TABEL UTAMA SETELAH APPROVE
    |--------------------------------------------------------------------------
    */
    private function applyPayloadToPegawai(PengajuanPerubahan $pengajuan): void
    {
        $payload = $pengajuan->payload ?? [];

        $pegawaiData = $payload['pegawai'] ?? [];

        unset($pegawaiData['nip']);

        $pegawai = Pegawai::updateOrCreate(
            ['nip' => $pengajuan->nip],
            $pegawaiData
        );

        foreach ($this->childrenMap() as $payloadKey => $config) {
            if (!array_key_exists($payloadKey, $payload)) {
                continue;
            }

            $rows = $payload[$payloadKey] ?? [];

            $relation = $config['relation'];
            $primaryKey = $config['primary_key'];

            if (!method_exists($pegawai, $relation)) {
                continue;
            }

            $pegawai->{$relation}()->delete();

            foreach ($rows as $row) {
                unset($row[$primaryKey]);

                if ($this->hasRealData($row)) {
                    $pegawai->{$relation}()->create($row);
                }
            }
        }
    }

    private function pegawaiFields(): array
    {
        return [
            'nama',
            'tempat_lahir',
            'tgl_lahir',
            'jenkel',
            'agama',
            'alamat',
            'profesional',
            'tmt_gol_jabatan',
            'gol_jabatan',
            'id_jabatan',
            'jabatan',
            'departemen',
            'hubungan_kerja',
            'lokasi_kerja',
            'status',
            'tmt_gol_upah',
            'gol_upah',
            'tgl_masuk',
        ];
    }

    private function sectionKeys(): array
    {
        return [
            'pendidikan',
            'kursus',
            'peng_bsp',
            'peng_luar',
            'keluarga',
            'penilaian',
        ];
    }

    private function childrenMap(): array
    {
        return [
            'pendidikan' => [
                'relation' => 'pendidikan',
                'primary_key' => 'id_pendidikan',
            ],
            'kursus' => [
                'relation' => 'kursus',
                'primary_key' => 'id_kursus',
            ],
            'peng_bsp' => [
                'relation' => 'pengalamanBsp',
                'primary_key' => 'id_pengalaman_bsp',
            ],
            'peng_luar' => [
                'relation' => 'pengalamanLuarBsp',
                'primary_key' => 'id_pengalaman_luar_bsp',
            ],
            'keluarga' => [
                'relation' => 'keluarga',
                'primary_key' => 'id_keluarga',
            ],
            'penilaian' => [
                'relation' => 'penilaian',
                'primary_key' => 'id_penilaian',
            ],
        ];
    }

    private function cleanRows($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $cleaned = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $item = [];

            foreach ($row as $key => $value) {
                if ($value !== null && $value !== '') {
                    $item[$key] = $value;
                }
            }

            if (count($item) > 0) {
                $cleaned[] = $item;
            }
        }

        return $cleaned;
    }

    private function hasRealData(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && $value !== '') {
                return true;
            }
        }

        return false;
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

    private function authorizeReviewer(): void
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'hcm']), 403);
    }
}