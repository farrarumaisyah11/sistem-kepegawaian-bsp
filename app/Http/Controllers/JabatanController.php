<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\JabatanVersion;
use App\Models\Pegawai;
use App\Models\PegawaiJabatanVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Exports\JabatanExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class JabatanController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $jabatans = Jabatan::query()
            ->with(['activeVersion', 'pendingVersion'])
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

        $jabatan = DB::transaction(function () use ($data) {
            $jabatan = Jabatan::create($data);

            $version = $this->createJobdeskVersion($jabatan, $data, 'pending');

            $jabatan->forceFill([
                'draft_version_id' => $version->id_jabatan_version,
                'active_version_id' => null,
                'latest_version_number' => $version->version_number,
                'jobdesk_updated_at' => null,
                'jobdesk_updated_by' => null,
            ])->save();

            return $jabatan;
        });

        return redirect()
            ->route($this->routeName('jabatan.show'), $jabatan)
            ->with('success_auto', 'Data jabatan berhasil disimpan dan menunggu approval.');
    }

    public function show(Jabatan $jabatan)
    {
        $jabatan->load(['activeVersion', 'pendingVersion', 'versions' => function ($q) {
            $q->orderByDesc('version_number');
        }]);

        return view('jabatan.show', compact('jabatan'));
    }

    public function edit(Jabatan $jabatan)
    {
        $jabatan->load(['activeVersion', 'pendingVersion']);

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

        DB::transaction(function () use ($jabatan, $data) {
            $jabatan->update($data);

            $version = $this->createJobdeskVersion($jabatan->fresh(), $data, 'pending');

            $jabatan->forceFill([
                'draft_version_id' => $version->id_jabatan_version,
                'latest_version_number' => $version->version_number,
            ])->save();

            $this->syncPegawaiByJabatan($jabatan);
        });

        return redirect()
            ->route($this->routeName('jabatan.show'), $jabatan)
            ->with('success_auto', 'Data jabatan berhasil diperbarui dan menunggu approval ulang.');
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
            ->with('success_auto', 'Data jabatan berhasil dihapus.');
    }

    public function print(Jabatan $jabatan)
    {
        $jabatan->load(['activeVersion', 'pendingVersion']);

        return view('jabatan.print', compact('jabatan'));
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

    private function createJobdeskVersion(Jabatan $jabatan, array $data, string $status = 'pending'): JabatanVersion
    {
        $nextVersionNumber = ((int) JabatanVersion::where('id_jabatan', $jabatan->id_jabatan)->max('version_number')) + 1;
        $user = auth()->user();

        return JabatanVersion::create([
            'id_jabatan' => $jabatan->id_jabatan,
            'version_number' => $nextVersionNumber,
            'status' => $status,

            'nama_jabatan' => $data['nama_jabatan'] ?? $jabatan->nama_jabatan,
            'departemen' => $data['departemen'] ?? $jabatan->departemen,
            'gol_jabatan' => $data['gol_jabatan'] ?? $jabatan->gol_jabatan,
            'home_base' => $data['home_base'] ?? $jabatan->home_base,
            'lokasi_kerja' => $data['lokasi_kerja'] ?? $jabatan->lokasi_kerja,
            'parent_jabatan' => $data['parent_jabatan'] ?? $jabatan->parent_jabatan,

            'tujuan_jabatan' => $data['tujuan_jabatan'] ?? null,
            'tanggung_jawab' => $data['tanggung_jawab'] ?? null,
            'tantangan_jabatan' => $data['tantangan_jabatan'] ?? null,
            'dim_keuangan' => $data['dim_keuangan'] ?? null,
            'dim_nonkeuangan' => $data['dim_nonkeuangan'] ?? null,
            'bawahan_langsung' => $data['bawahan_langsung'] ?? null,
            'internal_perusahaan' => $data['internal_perusahaan'] ?? null,
            'external_perusahaan' => $data['external_perusahaan'] ?? null,
            'finansial' => $data['finansial'] ?? null,
            'non_finansial' => $data['non_finansial'] ?? null,
            'pengetahuan_keterampilan' => $data['pengetahuan_keterampilan'] ?? null,
            'kompetensi' => $data['kompetensi'] ?? null,
            'syarat_kompetensi_jabatan' => $data['syarat_kompetensi_jabatan'] ?? null,
            'struktur_file' => $data['struktur_file'] ?? $jabatan->struktur_file ?? null,

            'created_by' => $user?->getKey(),
            'created_by_name' => $user->nama ?? $user->name ?? $user->username ?? null,
            'created_at' => now(),
        ]);
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

    public function applyApprovedVersionToPegawai(Jabatan $jabatan)
    {
        abort_unless(auth()->check() && in_array(auth()->user()->role, ['admin', 'hcm', 'manager'], true), 403);

        $jabatan->load('activeVersion');

        if (!$jabatan->activeVersion) {
            return back()->withErrors([
                'jobdesk' => 'Belum ada versi job description yang sudah approved untuk diterapkan.'
            ]);
        }

        $pegawaiAktif = Pegawai::where('id_jabatan', $jabatan->id_jabatan)->get();

        if ($pegawaiAktif->isEmpty()) {
            return back()->withErrors([
                'jobdesk' => 'Belum ada pegawai aktif yang menggunakan jabatan ini.'
            ]);
        }

        DB::transaction(function () use ($jabatan, $pegawaiAktif) {
            $user = auth()->user();

            foreach ($pegawaiAktif as $pegawai) {
                $alreadyCurrent = PegawaiJabatanVersion::where('nip', $pegawai->nip)
                    ->where('id_jabatan', $jabatan->id_jabatan)
                    ->where('id_jabatan_version', $jabatan->activeVersion->id_jabatan_version)
                    ->where('is_current', 1)
                    ->exists();

                if ($alreadyCurrent) {
                    continue;
                }

                PegawaiJabatanVersion::where('nip', $pegawai->nip)
                    ->where('is_current', 1)
                    ->update([
                        'is_current' => 0,
                        'ended_at' => now(),
                        'ended_by' => $user?->getKey(),
                        'ended_by_name' => $user->nama ?? $user->name ?? $user->username ?? null,
                    ]);

                PegawaiJabatanVersion::create([
                    'nip' => $pegawai->nip,
                    'id_jabatan' => $jabatan->id_jabatan,
                    'id_jabatan_version' => $jabatan->activeVersion->id_jabatan_version,
                    'assigned_at' => now(),
                    'assigned_by' => $user?->getKey(),
                    'assigned_by_name' => $user->nama ?? $user->name ?? $user->username ?? null,
                    'is_current' => 1,
                ]);
            }
        });

        return back()->with('success_auto', 'Versi job description approved berhasil diterapkan ke pegawai aktif. Riwayat versi sebelumnya tetap tersimpan.');
    }

    private function joinArrayLines(array $items = []): ?string
    {
        $lines = [];

        foreach ($items as $item) {
            $item = trim((string) $item);

            if ($item === '') {
                continue;
            }

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
        abort_unless(auth()->check() && in_array(auth()->user()->role, ['admin', 'hcm'], true), 403);

        $jabatan = $this->ensureApprovalToken($jabatan);
        $jabatan->load(['activeVersion', 'pendingVersion']);

        $approvalUrl = $this->buildApprovalUrl($jabatan);
        $isLocalApprovalUrl = $this->isLocalApprovalUrl($approvalUrl);

        return view('jabatan.approval-page', compact('jabatan', 'approvalUrl', 'isLocalApprovalUrl'));
    }

    public function approvalQr(Jabatan $jabatan)
    {
        abort_unless(auth()->check() && in_array(auth()->user()->role, ['admin', 'hcm'], true), 403);

        $jabatan = $this->ensureApprovalToken($jabatan);
        $approvalUrl = $this->buildApprovalUrl($jabatan);

        $qrSvg = QrCode::format('svg')
            ->size(360)
            ->margin(2)
            ->errorCorrection('H')
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

    private function buildApprovalUrl(Jabatan $jabatan): string
    {
        $jabatan = $this->ensureApprovalToken($jabatan);

        $approvalPath = route('jabatan.approval.scan', [
            'jabatan' => $jabatan->id_jabatan,
            'token'   => $jabatan->approval_token,
        ], false);

        $baseUrl = config('app.approval_url')
            ?: env('APP_APPROVAL_URL')
            ?: config('app.url')
            ?: request()->getSchemeAndHttpHost();

        return rtrim((string) $baseUrl, '/') . $approvalPath;
    }

    private function isLocalApprovalUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (!$host) {
            return true;
        }

        return in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || str_starts_with($host, '192.168.')
            || str_starts_with($host, '10.')
            || preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $host) === 1;
    }

    public function approvalScan(Jabatan $jabatan, string $token)
{
    $this->validateApprovalToken($jabatan, $token);
    $this->ensureUserCanOpenApproval();

    $jabatan->load(['activeVersion', 'pendingVersion']);

    $user = auth()->user();
    $pegawaiApprover = $this->getPegawaiApprover();

    return view('jabatan.approval-scan', compact('jabatan', 'user', 'pegawaiApprover', 'token'));
}

public function approvalDetail(Jabatan $jabatan, string $token)
{
    $this->validateApprovalToken($jabatan, $token);
    $this->ensureUserCanOpenApproval();

    $jabatan->load(['activeVersion', 'pendingVersion']);

    return view('jabatan.show', compact('jabatan'));
}

public function approvalApprove(Request $request, Jabatan $jabatan, string $token)
{
    $this->validateApprovalToken($jabatan, $token);
    $this->ensureUserCanOpenApproval();

    $request->validate([
        'approval_catatan' => ['nullable', 'string'],
        'approval_password' => ['required', 'string'],
    ], [
        'approval_password.required' => 'Password wajib diisi untuk konfirmasi approval.',
    ]);

    $user = auth()->user();

    if (!$user || !Hash::check((string) $request->approval_password, (string) $user->password)) {
        return back()
            ->withErrors(['approval_password' => 'Password yang Anda masukkan tidak sesuai.'])
            ->withInput($request->except('approval_password'));
    }

    $pegawaiApprover = $this->getPegawaiApprover();

    if ($user->role === 'pegawai' && !$pegawaiApprover) {
        return back()->withErrors([
            'approval' => 'Data pegawai untuk akun ini tidak ditemukan. Approval tidak dapat diproses.'
        ]);
    }

    DB::transaction(function () use ($request, $jabatan, $user, $pegawaiApprover) {
        $jabatan->refresh();
        $jabatan->load(['activeVersion', 'pendingVersion']);

        $pendingVersion = $jabatan->pendingVersion;

        if (!$pendingVersion) {
            $pendingVersion = $this->createJobdeskVersion($jabatan, $jabatan->toArray(), 'pending');

            $jabatan->forceFill([
                'draft_version_id' => $pendingVersion->id_jabatan_version,
                'latest_version_number' => $pendingVersion->version_number,
            ])->save();
        }

        $approverData = [
            'proposed_approved_by' => $user->getKey(),
            'proposed_approved_by_name' => $pegawaiApprover->nama ?? $user->nama ?? $user->name ?? $user->username ?? 'Approver',
            'proposed_approved_by_role' => $user->role,
            'proposed_approved_by_jabatan' => $pegawaiApprover->jabatan ?? strtoupper((string) $user->role),
            'proposed_approved_by_departemen' => $pegawaiApprover->departemen ?? '-',
            'proposed_approved_at' => now(),
            'proposed_approval_catatan' => $request->approval_catatan,
        ];

        if ($user->role === 'hcm') {
            $this->finalizeApproval($jabatan, $pendingVersion, $approverData, $request->approval_catatan, true);
        } else {
            $pendingVersion->update(array_merge($approverData, [
                'approval_flow_status' => 'waiting_hcm_confirmation',
                'status' => 'pending',
            ]));

            $jabatan->update(array_merge($approverData, [
                'approval_status' => 'pending',
                'approval_flow_status' => 'waiting_hcm_confirmation',
            ]));
        }
    });

    return redirect()
        ->route('jabatan.approval.scan', [
            'jabatan' => $jabatan->id_jabatan,
            'token' => $token,
        ])
        ->with('success_auto', auth()->user()->role === 'hcm'
            ? 'Job description berhasil di-approve final oleh HCM.'
            : 'Approval berhasil dicatat dan menunggu pengesahan HCM.');
}

public function approvalConfirmFinal(Request $request, Jabatan $jabatan, string $token)
{
    $this->validateApprovalToken($jabatan, $token);

    abort_unless((auth()->user()->role ?? null) === 'hcm', 403, 'Hanya HCM yang dapat mengesahkan approval final.');

    $request->validate([
        'hcm_confirmation_catatan' => ['nullable', 'string'],
        'approval_password' => ['required', 'string'],
    ], [
        'approval_password.required' => 'Password HCM wajib diisi.',
    ]);

    $user = auth()->user();

    if (!$user || !Hash::check((string) $request->approval_password, (string) $user->password)) {
        return back()->withErrors(['approval_password' => 'Password HCM tidak sesuai.']);
    }

    DB::transaction(function () use ($request, $jabatan, $user) {
        $jabatan->refresh();
        $jabatan->load(['activeVersion', 'pendingVersion']);

        $pendingVersion = $jabatan->pendingVersion;

        if (!$pendingVersion) {
            throw new \RuntimeException('Tidak ada versi pending untuk dikonfirmasi.');
        }

        if (!$pendingVersion->proposed_approved_at) {
            throw new \RuntimeException('Belum ada approval dari approver pegawai.');
        }

        $approverData = [
            'proposed_approved_by' => $pendingVersion->proposed_approved_by,
            'proposed_approved_by_name' => $pendingVersion->proposed_approved_by_name,
            'proposed_approved_by_role' => $pendingVersion->proposed_approved_by_role,
            'proposed_approved_by_jabatan' => $pendingVersion->proposed_approved_by_jabatan,
            'proposed_approved_by_departemen' => $pendingVersion->proposed_approved_by_departemen,
            'proposed_approved_at' => $pendingVersion->proposed_approved_at,
            'proposed_approval_catatan' => $pendingVersion->proposed_approval_catatan,
        ];

        $this->finalizeApproval($jabatan, $pendingVersion, $approverData, $request->hcm_confirmation_catatan, false);
    });

    return back()->with('success_auto', 'Approval pegawai berhasil disahkan final oleh HCM.');
}

private function finalizeApproval(
    Jabatan $jabatan,
    JabatanVersion $pendingVersion,
    array $approverData,
    ?string $hcmCatatan = null,
    bool $hcmDirectApprove = false
): void {
    $hcmUser = auth()->user();

    if ($jabatan->activeVersion) {
        $jabatan->activeVersion->update([
            'status' => 'archived',
            'effective_until' => now(),
        ]);
    }

    $pendingVersion->update(array_merge($approverData, [
        'status' => 'approved',
        'approval_flow_status' => 'approved_final',

        'approved_by' => $approverData['proposed_approved_by'],
        'approved_by_name' => $approverData['proposed_approved_by_name'],
        'approved_by_role' => $approverData['proposed_approved_by_role'],
        'approved_by_jabatan' => $approverData['proposed_approved_by_jabatan'],
        'approved_by_departemen' => $approverData['proposed_approved_by_departemen'],
        'approved_at' => $approverData['proposed_approved_at'],
        'approval_catatan' => $approverData['proposed_approval_catatan'],

        'hcm_confirmed_by' => $hcmUser?->getKey(),
        'hcm_confirmed_by_name' => $hcmUser->nama ?? $hcmUser->name ?? $hcmUser->username ?? 'HCM',
        'hcm_confirmed_at' => $hcmDirectApprove ? $approverData['proposed_approved_at'] : now(),
        'hcm_confirmation_catatan' => $hcmCatatan,

        'effective_from' => now(),
        'effective_until' => null,
    ]));

    $jabatan->update(array_merge($approverData, [
        'approval_status' => 'approved',
        'approval_flow_status' => 'approved_final',

        'approved_by' => $approverData['proposed_approved_by'],
        'approved_by_name' => $approverData['proposed_approved_by_name'],
        'approved_by_role' => $approverData['proposed_approved_by_role'],
        'approved_by_jabatan' => $approverData['proposed_approved_by_jabatan'],
        'approved_by_departemen' => $approverData['proposed_approved_by_departemen'],
        'approved_at' => $approverData['proposed_approved_at'],
        'approval_catatan' => $approverData['proposed_approval_catatan'],

        'hcm_confirmed_by' => $hcmUser?->getKey(),
        'hcm_confirmed_by_name' => $hcmUser->nama ?? $hcmUser->name ?? $hcmUser->username ?? 'HCM',
        'hcm_confirmed_at' => $hcmDirectApprove ? $approverData['proposed_approved_at'] : now(),
        'hcm_confirmation_catatan' => $hcmCatatan,

        'active_version_id' => $pendingVersion->id_jabatan_version,
        'draft_version_id' => null,
        'latest_version_number' => $pendingVersion->version_number,
        'jobdesk_updated_at' => now(),
        'jobdesk_updated_by' => $hcmUser?->getKey(),
    ]));
}

private function ensureUserCanOpenApproval(): void
{
    $role = auth()->user()->role ?? null;

    abort_unless(
        in_array($role, ['hcm', 'pegawai'], true),
        403,
        'Anda tidak memiliki akses untuk membuka halaman approval job description.'
    );
}

private function validateApprovalToken(Jabatan $jabatan, string $token): void
{
    abort_unless(
        !empty($jabatan->approval_token)
        && hash_equals((string) $jabatan->approval_token, (string) $token),
        403,
        'Token approval tidak valid.'
    );
} 

    private function getPegawaiApprover(): ?Pegawai
    {
        $user = auth()->user();
        $nipUser = $user->nip ?? $user->username ?? null;

        return $nipUser
            ? Pegawai::where('nip', $nipUser)->first()
            : null;
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
