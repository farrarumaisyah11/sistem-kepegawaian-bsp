<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Exports\JabatanExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class JabatanController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $jabatans = Jabatan::query()
            ->withCount('pegawai')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($r) use ($search) {
                    $r->where('nama_jabatan', 'like', "%{$search}%")
                        ->orWhere('departemen', 'like', "%{$search}%")
                        ->orWhere('lokasi_kerja', 'like', "%{$search}%")
                        ->orWhere('home_base', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id_jabatan')
            ->paginate(10)
            ->appends(['search' => $search]);

        return view('jabatan.index', compact('jabatans', 'search'));
    }

    public function create()
    {
        return view('jabatan.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateJabatan($request);

        $data = $this->prepareJabatanData($request, $data);
        $data['approval_status'] = 'pending';
        $data['approval_token'] = (string) Str::uuid();

        if ($request->hasFile('struktur_file')) {
            $data['struktur_file'] = $request->file('struktur_file')->store('jabatan/struktur', 'public');
        }

        $jabatan = Jabatan::create($data);

        return redirect()
            ->route($this->routeName('jabatan.show'), $jabatan)
            ->with('success', 'Data jabatan berhasil disimpan.');
    }

    public function show(Jabatan $jabatan)
    {
        return view('jabatan.show', compact('jabatan'));
    }

    public function edit(Jabatan $jabatan)
    {
        return view('jabatan.edit', compact('jabatan'));
    }

    public function update(Request $request, Jabatan $jabatan)
    {
        $data = $this->validateJabatan($request);

        $data = $this->prepareJabatanData($request, $data);
        $data['approval_status'] = 'pending';
        $data['approval_token'] = $jabatan->approval_token ?: (string) Str::uuid();

        $data['approved_by'] = null;
        $data['approved_by_name'] = null;
        $data['approved_by_role'] = null;
        $data['approved_by_jabatan'] = null;
        $data['approved_by_departemen'] = null;
        $data['approved_at'] = null;
        $data['approval_catatan'] = null;

        if ($request->hasFile('struktur_file')) {
            if (!empty($jabatan->struktur_file) && Storage::disk('public')->exists($jabatan->struktur_file)) {
                Storage::disk('public')->delete($jabatan->struktur_file);
            }

            $data['struktur_file'] = $request->file('struktur_file')->store('jabatan/struktur', 'public');
        }

        $jabatan->update($data);

        $this->syncPegawaiByJabatan($jabatan);

        return redirect()
            ->route($this->routeName('jabatan.show'), $jabatan)
            ->with('success', 'Data jabatan berhasil diperbarui.');
    }

    public function destroy(Jabatan $jabatan)
    {
        $jumlahPegawai = Pegawai::where('id_jabatan', $jabatan->id_jabatan)->count();

        if ($jumlahPegawai > 0) {
            return back()->withErrors([
                'jabatan' => 'Jabatan tidak dapat dihapus karena masih digunakan oleh ' . $jumlahPegawai . ' pegawai.'
            ]);
        }

        if (!empty($jabatan->struktur_file) && Storage::disk('public')->exists($jabatan->struktur_file)) {
            Storage::disk('public')->delete($jabatan->struktur_file);
        }

        $jabatan->delete();

        return redirect()
            ->route($this->routeName('jabatan.index'))
            ->with('success', 'Data jabatan berhasil dihapus.');
    }

    public function print(Jabatan $jabatan)
    {
        return view('jabatan.show', compact('jabatan'));
    }

    public function exportExcel(Jabatan $jabatan)
    {
        return Excel::download(
            new JabatanExport($jabatan),
            'job-description-' . $jabatan->id_jabatan . '.xlsx'
        );
    }

    private function validateJabatan(Request $request): array
    {
        return $request->validate([
            'nama_jabatan'                    => 'required|string|max:100',
            'departemen'                      => 'nullable|string|max:100',
            'gol_jabatan'                     => 'nullable|string|max:50',
            'home_base'                       => 'nullable|string|max:100',
            'lokasi_kerja'                    => 'nullable|string|max:100',
            'tujuan_jabatan'                  => 'nullable|string',

            'tanggung_jawab'                  => 'nullable|array',
            'tanggung_jawab.*'                => 'nullable|string',

            'tantangan_jabatan'               => 'nullable|array',
            'tantangan_jabatan.*'             => 'nullable|string',

            'dim_keuangan'                    => 'nullable|string',
            'dim_nonkeuangan'                 => 'nullable|string',
            'bawahan_langsung'                => 'nullable|string',

            'internal_perusahaan'             => 'nullable|array',
            'internal_perusahaan.*'           => 'nullable|string',

            'external_perusahaan'             => 'nullable|array',
            'external_perusahaan.*'           => 'nullable|string',

            'finansial'                       => 'nullable|string',
            'non_finansial'                   => 'nullable|string',

            'pengetahuan_keterampilan'        => 'nullable|array',
            'pengetahuan_keterampilan.*'      => 'nullable|string',

            'kompetensi'                      => 'nullable|array',
            'kompetensi.*'                    => 'nullable|string',

            'syarat_kompetensi_jabatan'       => 'nullable|array',
            'syarat_kompetensi_jabatan.*'     => 'nullable|string',

            'parent_jabatan'                  => 'nullable|integer|exists:tb_jabatan,id_jabatan',
            'struktur_file'                   => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:2048',
        ]);
    }

    private function prepareJabatanData(Request $request, array $data): array
    {
        $data['tanggung_jawab'] = $this->joinArrayLines($request->input('tanggung_jawab', []));
        $data['tantangan_jabatan'] = $this->joinArrayLines($request->input('tantangan_jabatan', []));
        $data['internal_perusahaan'] = $this->joinArrayLines($request->input('internal_perusahaan', []));
        $data['external_perusahaan'] = $this->joinArrayLines($request->input('external_perusahaan', []));
        $data['pengetahuan_keterampilan'] = $this->joinArrayLines($request->input('pengetahuan_keterampilan', []));
        $data['kompetensi'] = $this->joinArrayLines($request->input('kompetensi', []));
        $data['syarat_kompetensi_jabatan'] = $this->joinArrayLines($request->input('syarat_kompetensi_jabatan', []));

        return $data;
    }

    private function syncPegawaiByJabatan(Jabatan $jabatan): void
    {
        Pegawai::where('id_jabatan', $jabatan->id_jabatan)
            ->update([
                'jabatan'      => $jabatan->nama_jabatan,
                'departemen'   => $jabatan->departemen,
                'gol_jabatan'  => is_numeric($jabatan->gol_jabatan) ? (int) $jabatan->gol_jabatan : null,
                'lokasi_kerja' => $jabatan->lokasi_kerja,
            ]);
    }

    private function joinArrayLines(array $items = []): ?string
    {
        $lines = [];

        foreach ($items as $item) {
            $item = trim((string) $item);

            if ($item === '') {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Penting:
            | Jika ada data lama yang sudah tergabung dalam 1 input dengan enter,
            | pecah lagi menjadi beberapa poin agar saat show/edit tetap 1,2,3.
            |--------------------------------------------------------------------------
            */
            $splitItems = preg_split('/\r\n|\r|\n/', $item);

            foreach ($splitItems as $line) {
                $line = trim((string) $line);

                if ($line !== '') {
                    $lines[] = $line;
                }
            }
        }

        return count($lines) ? implode("\n", $lines) : null;
    }
public function approvalPage(Jabatan $jabatan)
{
    abort_unless(in_array(auth()->user()->role, ['admin', 'hcm']), 403);

    $jabatan = $this->ensureApprovalToken($jabatan);

    return view('jabatan.approval-page', compact('jabatan'));
}

public function approvalQr(Jabatan $jabatan)
{
    abort_unless(in_array(auth()->user()->role, ['admin', 'hcm']), 403);

    $jabatan = $this->ensureApprovalToken($jabatan);

    /*
    |--------------------------------------------------------------------------
    | URL approval yang masuk ke QR
    |--------------------------------------------------------------------------
    | Pakai APP_URL agar QR bisa discan dari HP.
    | Kalau APP_URL masih 127.0.0.1, HP tidak akan bisa buka linknya.
    */
    $approvalPath = route('jabatan.approval.scan', [
        'jabatan' => $jabatan->id_jabatan,
        'token'   => $jabatan->approval_token,
    ], false);

    $approvalUrl = rtrim(config('app.url'), '/') . $approvalPath;

    $qrSvg = QrCode::format('svg')
        ->size(260)
        ->margin(2)
        ->errorCorrection('M')
        ->generate($approvalUrl);

    return response($qrSvg, 200)
        ->header('Content-Type', 'image/svg+xml')
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
}

private function ensureApprovalToken(Jabatan $jabatan): Jabatan
{
    if (empty($jabatan->approval_token)) {
        $jabatan->forceFill([
            'approval_token' => (string) Str::uuid(),
        ])->save();

        $jabatan->refresh();
    }

    return $jabatan;
}

public function approvalScan(Jabatan $jabatan, string $token)
{
    abort_unless(in_array(auth()->user()->role, ['admin', 'hcm']), 403);

    abort_unless(
        $jabatan->approval_token && hash_equals($jabatan->approval_token, $token),
        403,
        'Token approval tidak valid.'
    );

    $user = auth()->user();

    $nipUser = $user->nip ?? $user->username ?? null;

    $pegawaiApprover = $nipUser
        ? Pegawai::where('nip', $nipUser)->first()
        : null;

    return view('jabatan.approval-scan', compact('jabatan', 'user', 'pegawaiApprover', 'token'));
}

public function approvalApprove(Request $request, Jabatan $jabatan, string $token)
{
    abort_unless(in_array(auth()->user()->role, ['admin', 'hcm']), 403);

    abort_unless(
        $jabatan->approval_token && hash_equals($jabatan->approval_token, $token),
        403,
        'Token approval tidak valid.'
    );

    $request->validate([
        'approval_catatan' => ['nullable', 'string'],
    ]);

    $user = auth()->user();

    $nipUser = $user->nip ?? $user->username ?? null;

    $pegawaiApprover = $nipUser
        ? Pegawai::where('nip', $nipUser)->first()
        : null;

    $jabatan->update([
        'approval_status' => 'approved',
        'approved_by' => $user->getKey(),
        'approved_by_name' => $pegawaiApprover->nama ?? $user->username ?? 'Approver',
        'approved_by_role' => $user->role,
        'approved_by_jabatan' => $pegawaiApprover->jabatan ?? strtoupper($user->role),
        'approved_by_departemen' => $pegawaiApprover->departemen ?? '-',
        'approved_at' => now(),
        'approval_catatan' => $request->approval_catatan,
    ]);

    return redirect()
        ->route($this->routeName('jabatan.show'), $jabatan)
        ->with('success', 'Job description berhasil di-approve.');
}


    private function routeName(string $name): string
    {
        $role = auth()->check() ? auth()->user()->role : 'hcm';

        return match ($role) {
            'admin' => 'admin.' . $name,
            'hcm'   => 'hcm.' . $name,
            default => 'hcm.' . $name,
        };
    }
}