<?php

namespace App\Http\Controllers;

use App\Exports\JabatanExport;
use App\Models\Departemen;
use App\Models\Jabatan;
use App\Models\JabatanApprovalLog;
use App\Models\JabatanVersion;
use App\Models\Pegawai;
use App\Models\PegawaiJabatanVersion;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class JabatanController extends Controller
{
    public function index(Request $request)
    {
        $jabatans = Jabatan::query()
            ->with([
                'activeVersion',
                'pendingVersion',
                'departemenMaster',
                'parent',
            ])
            ->withCount('pegawai')
            ->orderBy('id_departemen')
            ->orderByRaw('parent_jabatan IS NULL DESC')
            ->orderBy('parent_jabatan')
            ->orderBy('nama_jabatan')
            ->get();

        return view('jabatan.index', compact('jabatans'));
    }

    public function create()
    {
        $departemenList = Departemen::query()
            ->where('is_active', 1)
            ->orderBy('level_departemen')
            ->orderBy('urutan')
            ->orderBy('nama_departemen')
            ->get();

        $parentOptions = Jabatan::query()
            ->with('departemenMaster')
            ->orderBy('id_departemen')
            ->orderBy('nama_jabatan')
            ->get();

        return view('jabatan.create', compact('departemenList', 'parentOptions'));
    }

    public function store(Request $request)
    {
        $data = $this->validateJabatan($request);
        $data = $this->prepareJabatanData($request, $data);

        $data['approval_status'] = 'pending';
        $data['approval_flow_status'] = 'pending';
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

            $this->recordApprovalLog($jabatan->fresh(), 'draft_created', [
                'id_jabatan_version' => $version->id_jabatan_version,
                'metadata' => ['version_number' => $version->version_number],
            ]);

            return $jabatan;
        });

        return redirect()
            ->route($this->routeName('jabatan.show'), $jabatan)
            ->with('success_auto', 'Data jabatan berhasil disimpan dan menunggu approval.');
    }

    public function show(Jabatan $jabatan)
    {
        $jabatan->load([
            'activeVersion',
            'pendingVersion',
            'departemenMaster',
            'parent.departemenMaster',
            'pegawai',
            'approvalLogs' => function ($query) use ($jabatan) {
                $query->where('id_jabatan', $jabatan->id_jabatan)
                    ->with('version')
                    ->orderByDesc('created_at')
                    ->orderByDesc('id_jabatan_approval_log');
            },
            'versions' => function ($q) {
                $q->orderByDesc('version_number');
            },
        ]);

        return view('jabatan.show', compact('jabatan'));
    }

    public function edit(Jabatan $jabatan)
    {
        $jabatan->load([
            'activeVersion',
            'pendingVersion',
            'departemenMaster',
            'parent',
        ]);

        $departemenList = Departemen::query()
            ->where('is_active', 1)
            ->orderBy('level_departemen')
            ->orderBy('urutan')
            ->orderBy('nama_departemen')
            ->get();

        $parentOptions = Jabatan::query()
            ->with('departemenMaster')
            ->where('id_jabatan', '!=', $jabatan->id_jabatan)
            ->orderBy('id_departemen')
            ->orderBy('nama_jabatan')
            ->get();

        return view('jabatan.edit', compact('jabatan', 'departemenList', 'parentOptions'));
    }

    public function update(Request $request, Jabatan $jabatan)
    {
        $data = $this->validateJabatan($request, $jabatan);
        $data = $this->prepareJabatanData($request, $data);

        $data['approval_status'] = 'pending';
        $data['approval_flow_status'] = 'pending';

        /*
        |--------------------------------------------------------------------------
        | Token selalu dibuat baru setiap ada pembaruan job description.
        | Token lama tidak dipakai ulang agar link approval versi sebelumnya tidak
        | menjadi pintu approval untuk versi baru.
        |--------------------------------------------------------------------------
        */
        $data['approval_token'] = (string) Str::uuid();

        $data['approved_by'] = null;
        $data['approved_by_name'] = null;
        $data['approved_by_role'] = null;
        $data['approved_by_jabatan'] = null;
        $data['approved_by_departemen'] = null;
        $data['approved_at'] = null;
        $data['approval_catatan'] = null;

        $data['proposed_approved_by'] = null;
        $data['proposed_approved_by_name'] = null;
        $data['proposed_approved_by_role'] = null;
        $data['proposed_approved_by_jabatan'] = null;
        $data['proposed_approved_by_departemen'] = null;
        $data['proposed_approved_at'] = null;
        $data['proposed_approval_catatan'] = null;

        $data['hcm_confirmed_by'] = null;
        $data['hcm_confirmed_by_name'] = null;
        $data['hcm_confirmed_at'] = null;
        $data['hcm_confirmation_catatan'] = null;

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

            $this->syncPegawaiByJabatan($jabatan->fresh());

            $this->recordApprovalLog($jabatan->fresh(), 'draft_updated', [
                'id_jabatan_version' => $version->id_jabatan_version,
                'metadata' => ['version_number' => $version->version_number],
            ]);
        });

        return redirect()
            ->route($this->routeName('jabatan.show'), $jabatan)
            ->with('success_auto', 'Data jabatan berhasil diperbarui dan menunggu approval ulang. Link approval baru sudah dibuat.');
    }

    public function destroy(Jabatan $jabatan)
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'hcm']), 403);

        $role = auth()->user()->role;
        $indexRoute = $role . '.jabatan.index';
        $pesanError = [];

        $pegawaiPemangku = Pegawai::query()
            ->where('id_jabatan', $jabatan->id_jabatan)
            ->orderBy('nama')
            ->get(['nip', 'nama']);

        if ($pegawaiPemangku->count() > 0) {
            $daftarPegawai = $pegawaiPemangku
                ->take(5)
                ->map(fn ($pegawai) => $pegawai->nama . ' - NIP ' . $pegawai->nip)
                ->implode(', ');

            $pesanError[] = 'Jabatan "' . $jabatan->nama_jabatan . '" tidak dapat dihapus karena masih dipangku oleh '
                . $pegawaiPemangku->count()
                . ' pegawai'
                . ($daftarPegawai ? ', yaitu: ' . $daftarPegawai . '.' : '.');
        }

        $jabatanBawahan = Jabatan::query()
            ->where('parent_jabatan', $jabatan->id_jabatan)
            ->orderBy('nama_jabatan')
            ->get(['id_jabatan', 'nama_jabatan']);

        if ($jabatanBawahan->count() > 0) {
            $daftarBawahan = $jabatanBawahan
                ->take(5)
                ->map(fn ($child) => $child->nama_jabatan)
                ->implode(', ');

            $pesanError[] = 'Jabatan "' . $jabatan->nama_jabatan . '" tidak dapat dihapus karena masih menjadi atasan/parent dari '
                . $jabatanBawahan->count()
                . ' jabatan bawahan'
                . ($daftarBawahan ? ', yaitu: ' . $daftarBawahan . '.' : '.');
        }

        if (!empty($pesanError)) {
            return redirect()->route($indexRoute)->with('delete_error', $pesanError);
        }

        try {
            $namaJabatan = $jabatan->nama_jabatan;
            $this->recordApprovalLog($jabatan, 'jabatan_deleted');
            $jabatan->delete();

            return redirect()
                ->route($indexRoute)
                ->with('success', 'Jabatan "' . $namaJabatan . '" berhasil dihapus.');
        } catch (QueryException $e) {
            return redirect()
                ->route($indexRoute)
                ->with('delete_error', [
                    'Jabatan "' . $jabatan->nama_jabatan . '" tidak dapat dihapus karena masih memiliki relasi dengan data lain dalam sistem.',
                ]);
        }
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

    public function versions(Jabatan $jabatan)
    {
        $jabatan->load(['versions', 'activeVersion', 'pendingVersion']);

        return view('jabatan.versions.index', compact('jabatan'));
    }

    public function showVersion(Jabatan $jabatan, JabatanVersion $version)
    {
        abort_unless((int) $version->id_jabatan === (int) $jabatan->id_jabatan, 404);

        $jabatan->load(['activeVersion', 'pendingVersion']);
        $version->load(['departemenMaster', 'parentJabatanMaster']);

        return view('jabatan.versions.show', compact('jabatan', 'version'));
    }

    private function validateJabatan(Request $request, ?Jabatan $jabatan = null): array
    {
        $data = $request->validate([
            'nama_jabatan' => 'required|string|max:100',
            'id_departemen' => 'nullable|integer|exists:tb_departemen,id_departemen',
            'departemen' => 'nullable|string|max:100',
            'gol_jabatan' => 'nullable|string|max:50',
            'home_base' => 'nullable|string|max:100',
            'lokasi_kerja' => 'nullable|string|max:100',
            'tujuan_jabatan' => 'nullable|string',
            'tanggung_jawab' => 'nullable|array',
            'tanggung_jawab.*' => 'nullable|string',
            'tantangan_jabatan' => 'nullable|array',
            'tantangan_jabatan.*' => 'nullable|string',
            'dim_keuangan' => 'nullable|string',
            'dim_nonkeuangan' => 'nullable|string',
            'bawahan_langsung' => 'nullable|string',
            'internal_perusahaan' => 'nullable|array',
            'internal_perusahaan.*' => 'nullable|string',
            'external_perusahaan' => 'nullable|array',
            'external_perusahaan.*' => 'nullable|string',
            'finansial' => 'nullable|string',
            'non_finansial' => 'nullable|string',
            'pengetahuan_keterampilan' => 'nullable|array',
            'pengetahuan_keterampilan.*' => 'nullable|string',
            'kompetensi' => 'nullable|array',
            'kompetensi.*' => 'nullable|string',
            'syarat_kompetensi_jabatan' => 'nullable|array',
            'syarat_kompetensi_jabatan.*' => 'nullable|string',
            'parent_jabatan' => 'nullable|integer|exists:tb_jabatan,id_jabatan',
            'struktur_file' => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:2048',
        ]);

        if (
            $jabatan &&
            !empty($data['parent_jabatan']) &&
            (string) $data['parent_jabatan'] === (string) $jabatan->id_jabatan
        ) {
            abort(422, 'Parent jabatan tidak boleh sama dengan jabatan itu sendiri.');
        }

        return $data;
    }

    private function prepareJabatanData(Request $request, array $data): array
    {
        if (!empty($data['id_departemen'])) {
            $departemen = Departemen::where('id_departemen', $data['id_departemen'])
                ->where('is_active', 1)
                ->first();

            if ($departemen) {
                $data['departemen'] = $departemen->nama_departemen;
                $data['id_departemen'] = $departemen->id_departemen;
            }
        } elseif (!empty($data['departemen'])) {
            $departemen = Departemen::query()
                ->whereRaw('LOWER(TRIM(nama_departemen)) = ?', [strtolower(trim($data['departemen']))])
                ->orWhereRaw('LOWER(TRIM(singkatan)) = ?', [strtolower(trim($data['departemen']))])
                ->first();

            if ($departemen) {
                $data['departemen'] = $departemen->nama_departemen;
                $data['id_departemen'] = $departemen->id_departemen;
            }
        }

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
            'id_departemen' => $data['id_departemen'] ?? $jabatan->id_departemen,
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
            'approval_flow_status' => $status === 'approved' ? 'approved_final' : 'pending',
        ]);
    }

    private function syncPegawaiByJabatan(Jabatan $jabatan): void
    {
        Pegawai::where('id_jabatan', $jabatan->id_jabatan)
            ->update([
                'jabatan' => $jabatan->nama_jabatan,
                'departemen' => $jabatan->departemen,
                'id_departemen' => $jabatan->id_departemen,
                'gol_jabatan' => is_numeric($jabatan->gol_jabatan) ? (int) $jabatan->gol_jabatan : null,
                'lokasi_kerja' => $jabatan->lokasi_kerja,
            ]);
    }

    public function applyApprovedVersionToPegawai(Jabatan $jabatan)
    {
        abort_unless(auth()->check() && in_array(auth()->user()->role, ['admin', 'hcm', 'manager'], true), 403);

        $jabatan->load('activeVersion');

        if (!$jabatan->is_approval_final || !$jabatan->activeVersion || $jabatan->pendingVersion) {
            return back()->withErrors([
                'jobdesk' => 'Job description hanya dapat diterapkan setelah status approved final dan tidak ada versi pending.',
            ]);
        }

        $pegawaiAktif = Pegawai::where('id_jabatan', $jabatan->id_jabatan)->get();

        if ($pegawaiAktif->isEmpty()) {
            return back()->withErrors([
                'jobdesk' => 'Belum ada pegawai aktif yang menggunakan jabatan ini.',
            ]);
        }

        $appliedCount = 0;

        DB::transaction(function () use ($jabatan, $pegawaiAktif, &$appliedCount) {
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

                $appliedCount++;
            }

            $this->recordApprovalLog($jabatan, 'jobdesc_applied_to_pegawai', [
                'id_jabatan_version' => $jabatan->activeVersion->id_jabatan_version,
                'metadata' => [
                    'applied_count' => $appliedCount,
                    'total_pegawai' => $pegawaiAktif->count(),
                ],
            ]);
        });

        return back()->with('success_auto', 'Versi job description approved final berhasil diterapkan ke pegawai. Jumlah pegawai diperbarui: ' . $appliedCount . '.');
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

        $jabatan->load(['activeVersion', 'pendingVersion']);

        if (!$jabatan->is_approval_final && !$jabatan->is_waiting_hcm_final) {
            $jabatan = $this->ensureApprovalToken($jabatan);
        }

        $jabatan->load([
            'approvalLogs' => function ($query) use ($jabatan) {
                $query->where('id_jabatan', $jabatan->id_jabatan)
                    ->with('version')
                    ->orderByDesc('created_at')
                    ->orderByDesc('id_jabatan_approval_log');
            },
        ]);

        $approvalUrl = $jabatan->approval_token
            ? $this->buildApprovalUrl($jabatan)
            : null;

        $isLocalApprovalUrl = $approvalUrl ? $this->isLocalApprovalUrl($approvalUrl) : false;

        $this->recordApprovalLog($jabatan, 'approval_page_opened', [
            'id_jabatan_version' => $jabatan->draft_version_id,
            'metadata' => [
                'scope' => 'single_jabatan',
                'id_jabatan' => $jabatan->id_jabatan,
            ],
        ]);

        $jabatan->load([
            'approvalLogs' => function ($query) use ($jabatan) {
                $query->where('id_jabatan', $jabatan->id_jabatan)
                    ->with('version')
                    ->orderByDesc('created_at')
                    ->orderByDesc('id_jabatan_approval_log');
            },
        ]);

        return view('jabatan.approval-page', compact('jabatan', 'approvalUrl', 'isLocalApprovalUrl'));
    }

    public function approvalQr(Jabatan $jabatan)
    {
        abort_unless(auth()->check() && in_array(auth()->user()->role, ['admin', 'hcm'], true), 403);

        if ($jabatan->is_approval_final || empty($jabatan->approval_token)) {
            abort(404, 'QR approval tidak tersedia untuk job description yang sudah approved final.');
        }

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

    public function approvalShortLink(string $token)
    {
        $jabatan = Jabatan::where('approval_token', $token)->firstOrFail();

        return redirect()->route('jabatan.approval.scan', [
            'jabatan' => $jabatan->id_jabatan,
            'token' => $token,
        ]);
    }

    public function recordApprovalLinkShare(Request $request, Jabatan $jabatan)
    {
        abort_unless(auth()->check() && in_array(auth()->user()->role, ['admin', 'hcm'], true), 403);

        $this->recordApprovalLog($jabatan, 'approval_link_copied', [
            'id_jabatan_version' => $jabatan->draft_version_id,
            'metadata' => [
                'source' => $request->input('source', 'unknown'),
            ],
        ]);

        return response()->json(['ok' => true]);
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
        $approvalPath = route('jabatan.approval.scan', [
            'jabatan' => $jabatan->id_jabatan,
            'token' => $jabatan->approval_token,
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

        $jabatan->load(['activeVersion', 'pendingVersion', 'parent']);
        $this->ensureUserCanOpenApprovalForJabatan($jabatan);

        $this->recordApprovalLog($jabatan, 'approval_link_opened', [
            'id_jabatan_version' => $jabatan->draft_version_id,
        ]);

        $user = auth()->user();
        $pegawaiApprover = $this->getPegawaiApprover();

        return view('jabatan.approval-scan', compact('jabatan', 'user', 'pegawaiApprover', 'token'));
    }

    public function approvalDetail(Jabatan $jabatan, string $token)
    {
        $this->validateApprovalToken($jabatan, $token);
        $this->ensureUserCanOpenApprovalForJabatan($jabatan);

        $jabatan->load(['activeVersion', 'pendingVersion', 'parent']);

        $this->recordApprovalLog($jabatan, 'approval_detail_opened', [
            'id_jabatan_version' => $jabatan->draft_version_id,
        ]);

        return view('jabatan.approval-detail', compact('jabatan', 'token'));
    }

    public function approvalApprove(Request $request, Jabatan $jabatan, string $token)
    {
        $this->validateApprovalToken($jabatan, $token);
        $this->ensureUserCanOpenApprovalForJabatan($jabatan);

        $request->validate([
            'approval_catatan' => ['nullable', 'string'],
        ]);

        $user = auth()->user();
        $pegawaiApprover = $this->getPegawaiApprover();

        if ($user->role === 'pegawai' && !$pegawaiApprover) {
            return back()->withErrors([
                'approval' => 'Data pegawai untuk akun ini tidak ditemukan. Approval tidak dapat diproses.',
            ]);
        }

        DB::transaction(function () use ($request, $jabatan, $user, $pegawaiApprover) {
            $jabatan->refresh();
            $jabatan->load(['activeVersion', 'pendingVersion']);

            if (!$this->isApprovalActionable($jabatan)) {
                throw new \RuntimeException($this->approvalClosedMessage($jabatan));
            }

            $pendingVersion = $jabatan->pendingVersion;

            if (!$pendingVersion) {
                throw new \RuntimeException('Tidak ada versi pending untuk di-approve.');
            }

            $approverData = $this->buildApproverData($user, $pegawaiApprover, $request->approval_catatan);

            if ($user->role === 'hcm') {
                $this->finalizeApproval($jabatan, $pendingVersion, $approverData, $request->approval_catatan, true, 'approved_final_by_hcm_direct');
            } else {
                $pendingVersion->update(array_merge($approverData, [
                    'approval_flow_status' => 'waiting_hcm_confirmation',
                    'status' => 'pending',
                ]));

                $jabatan->update(array_merge($approverData, [
                    'approval_status' => 'pending',
                    'approval_flow_status' => 'waiting_hcm_confirmation',
                ]));

                $this->recordApprovalLog($jabatan->fresh(), 'approved_by_pegawai_waiting_hcm', [
                    'id_jabatan_version' => $pendingVersion->id_jabatan_version,
                    'metadata' => [
                        'version_number' => $pendingVersion->version_number,
                    ],
                ]);
            }
        });

        return redirect()
            ->route('jabatan.approval.scan', [
                'jabatan' => $jabatan->id_jabatan,
                'token' => $token,
            ])
            ->with('success_auto', auth()->user()->role === 'hcm'
                ? 'Job description berhasil di-approve final oleh HCM.'
                : 'Approval awal berhasil dicatat dan menunggu pengesahan final HCM.');
    }

    public function approvalConfirmFinalFromShow(Request $request, Jabatan $jabatan)
    {
        abort_unless((auth()->user()->role ?? null) === 'hcm', 403, 'Hanya HCM yang dapat melakukan approval final.');

        $request->validate([
            'hcm_confirmation_catatan' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $jabatan) {
            $jabatan->refresh();
            $jabatan->load(['activeVersion', 'pendingVersion']);

            if (!$jabatan->is_waiting_hcm_final || !$jabatan->pendingVersion) {
                throw new \RuntimeException('Approval final HCM hanya dapat dilakukan setelah approval awal pegawai tercatat dan masih ada versi pending.');
            }

            $approverData = $this->buildApproverDataFromPendingVersion($jabatan->pendingVersion);

            $this->finalizeApproval(
                $jabatan,
                $jabatan->pendingVersion,
                $approverData,
                $request->hcm_confirmation_catatan,
                false,
                'approved_final_by_hcm_from_show'
            );
        });

        return back()->with('success_auto', 'Job description berhasil di-approve final oleh HCM.');
    }

    public function approvalConfirmFinal(Request $request, Jabatan $jabatan, string $token)
    {
        return redirect()
            ->route('hcm.jabatan.show', $jabatan->id_jabatan)
            ->withErrors([
                'approval' => 'Approval final HCM dilakukan dari halaman detail jabatan internal, bukan dari link approval.',
            ]);
    }

    private function finalizeApproval(
        Jabatan $jabatan,
        JabatanVersion $pendingVersion,
        array $approverData,
        ?string $hcmCatatan = null,
        bool $hcmDirectApprove = false,
        string $logAction = 'approved_final_by_hcm_direct'
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

        $this->syncMasterJabatanFromVersion($jabatan, $pendingVersion);

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
            'approval_token' => null,
            'jobdesk_updated_at' => now(),
            'jobdesk_updated_by' => $hcmUser?->getKey(),
        ]));

        $this->recordApprovalLog($jabatan->fresh(), $logAction, [
            'id_jabatan_version' => $pendingVersion->id_jabatan_version,
            'metadata' => [
                'version_number' => $pendingVersion->version_number,
                'hcm_direct_approve' => $hcmDirectApprove,
            ],
        ]);
    }

    private function syncMasterJabatanFromVersion(Jabatan $jabatan, JabatanVersion $version): void
    {
        $jabatan->forceFill([
            'nama_jabatan' => $version->nama_jabatan,
            'departemen' => $version->departemen,
            'id_departemen' => $version->id_departemen,
            'gol_jabatan' => $version->gol_jabatan,
            'home_base' => $version->home_base,
            'lokasi_kerja' => $version->lokasi_kerja,
            'parent_jabatan' => $version->parent_jabatan,
            'tujuan_jabatan' => $version->tujuan_jabatan,
            'tanggung_jawab' => $version->tanggung_jawab,
            'tantangan_jabatan' => $version->tantangan_jabatan,
            'dim_keuangan' => $version->dim_keuangan,
            'dim_nonkeuangan' => $version->dim_nonkeuangan,
            'bawahan_langsung' => $version->bawahan_langsung,
            'internal_perusahaan' => $version->internal_perusahaan,
            'external_perusahaan' => $version->external_perusahaan,
            'finansial' => $version->finansial,
            'non_finansial' => $version->non_finansial,
            'pengetahuan_keterampilan' => $version->pengetahuan_keterampilan,
            'kompetensi' => $version->kompetensi,
            'syarat_kompetensi_jabatan' => $version->syarat_kompetensi_jabatan,
        ])->save();

        $this->syncPegawaiByJabatan($jabatan->fresh());
    }

    private function buildApproverData($user, ?Pegawai $pegawaiApprover, ?string $catatan): array
    {
        return [
            'proposed_approved_by' => $user?->getKey(),
            'proposed_approved_by_name' => $pegawaiApprover->nama ?? $user->nama ?? $user->name ?? $user->username ?? 'Approver',
            'proposed_approved_by_role' => $user->role,
            'proposed_approved_by_jabatan' => $pegawaiApprover->jabatan ?? strtoupper((string) $user->role),
            'proposed_approved_by_departemen' => $pegawaiApprover->departemen ?? '-',
            'proposed_approved_at' => now(),
            'proposed_approval_catatan' => $catatan,
        ];
    }

    private function buildApproverDataFromPendingVersion(JabatanVersion $pendingVersion): array
    {
        return [
            'proposed_approved_by' => $pendingVersion->proposed_approved_by,
            'proposed_approved_by_name' => $pendingVersion->proposed_approved_by_name,
            'proposed_approved_by_role' => $pendingVersion->proposed_approved_by_role,
            'proposed_approved_by_jabatan' => $pendingVersion->proposed_approved_by_jabatan,
            'proposed_approved_by_departemen' => $pendingVersion->proposed_approved_by_departemen,
            'proposed_approved_at' => $pendingVersion->proposed_approved_at,
            'proposed_approval_catatan' => $pendingVersion->proposed_approval_catatan,
        ];
    }

    private function isApprovalActionable(Jabatan $jabatan): bool
    {
        return !$jabatan->is_approval_final
            && !$jabatan->is_waiting_hcm_final
            && !empty($jabatan->draft_version_id)
            && !empty($jabatan->approval_token);
    }

    private function approvalClosedMessage(Jabatan $jabatan): string
    {
        if ($jabatan->is_approval_final) {
            return 'Job description ini sudah approved final. Link approval tidak aktif lagi.';
        }

        if ($jabatan->is_waiting_hcm_final) {
            return 'Approval awal sudah tercatat. Dokumen sedang menunggu approval final HCM dari halaman detail jabatan.';
        }

        return 'Approval tidak dapat diproses untuk status dokumen saat ini.';
    }

    private function ensureUserCanOpenApprovalForJabatan(Jabatan $jabatan): void
    {
        $user = auth()->user();
        abort_unless($user, 403, 'Anda harus login untuk mengakses halaman approval.');

        if ($user->role === 'hcm') {
            return;
        }

        if ($user->role === 'pegawai') {
            $nipLogin = $user->nip ?? $user->username ?? session('nip') ?? session('login_nip') ?? null;
            $pegawaiLogin = Pegawai::where('nip', $nipLogin)->first();

            abort_unless($pegawaiLogin, 403, 'Data pegawai login tidak ditemukan.');

            $departemenPegawai = trim(strtolower((string) ($pegawaiLogin->departemen ?? '')));
            $departemenJabatan = trim(strtolower((string) (
                $jabatan->pendingVersion->departemen
                ?? $jabatan->activeVersion->departemen
                ?? $jabatan->departemen
                ?? ''
            )));

            abort_unless(
                $departemenPegawai !== '' &&
                $departemenJabatan !== '' &&
                $departemenPegawai === $departemenJabatan,
                403,
                'Anda tidak berwenang mengakses approval jabatan di luar departemen Anda.'
            );

            return;
        }

        abort(403, 'Anda tidak berwenang mengakses approval jabatan ini.');
    }

    private function validateApprovalToken(Jabatan $jabatan, string $token): void
    {
        abort_unless(
            !empty($jabatan->approval_token)
            && hash_equals((string) $jabatan->approval_token, (string) $token),
            403,
            'Token approval tidak valid atau sudah tidak aktif.'
        );
    }

    private function getPegawaiApprover(): ?Pegawai
    {
        $user = auth()->user();
        $nipUser = $user->nip ?? $user->username ?? null;

        return $nipUser ? Pegawai::where('nip', $nipUser)->first() : null;
    }

    private function recordApprovalLog(Jabatan $jabatan, string $action, array $options = []): void
    {
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();
        $pegawai = $this->getPegawaiApprover();

        JabatanApprovalLog::create([
            'id_jabatan' => $jabatan->id_jabatan,
            'id_jabatan_version' => $options['id_jabatan_version'] ?? $jabatan->draft_version_id ?? $jabatan->active_version_id,
            'approval_token' => $jabatan->approval_token,
            'action' => $action,
            'actor_user_id' => $user?->getKey(),
            'actor_nip' => $pegawai->nip ?? $user->nip ?? $user->username ?? null,
            'actor_name' => $pegawai->nama ?? $user->nama ?? $user->name ?? $user->username ?? null,
            'actor_role' => $user->role ?? null,
            'actor_jabatan' => $pegawai->jabatan ?? strtoupper((string) ($user->role ?? '')),
            'actor_departemen' => $pegawai->departemen ?? null,
            'actor_id_departemen' => $pegawai->id_departemen ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500),
            'metadata' => $options['metadata'] ?? null,
            'created_at' => now(),
        ]);
    }

    private function routeName(string $name): string
    {
        $role = auth()->check() ? auth()->user()->role : 'hcm';

        return match ($role) {
            'admin' => 'admin.' . $name,
            'hcm' => 'hcm.' . $name,
            default => 'hcm.' . $name,
        };
    }
}
