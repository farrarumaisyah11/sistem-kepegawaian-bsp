<?php

namespace App\Http\Controllers;

use App\Models\Departemen;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class StrukturJabatanController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->check(), 403);

        $idDepartemen = $request->get('id_departemen');

        $departemenList = Departemen::query()
            ->where('is_active', 1)
            ->orderBy('level_departemen')
            ->orderBy('urutan')
            ->orderBy('nama_departemen')
            ->get();

        $selectedDepartemen = null;

        if ($idDepartemen) {
            $selectedDepartemen = Departemen::query()
                ->where('is_active', 1)
                ->where('id_departemen', $idDepartemen)
                ->first();
        }

        $sheetTitle = $selectedDepartemen
            ? strtoupper($selectedDepartemen->nama_departemen)
            : 'PT BUMI SIAK PUSAKO';

        $sheetCode = $selectedDepartemen
            ? (($selectedDepartemen->kode_departemen ?: $selectedDepartemen->singkatan ?: $selectedDepartemen->urutan) . ' - ' . $selectedDepartemen->id_departemen)
            : 'CORP - 1';

        /*
        |--------------------------------------------------------------------------
        | Ambil jabatan
        |--------------------------------------------------------------------------
        | Struktur organisasi tetap memakai fallback tb_jabatan jika belum ada versi
        | approved. Relasi parent tetap berdasarkan parent_jabatan.
        |--------------------------------------------------------------------------
        */
        $jabatans = Jabatan::query()
            ->with([
                'departemenMaster',
                'parent.departemenMaster',
                'parent.activeVersion',
                'parent.latestApprovedVersion',
                'children',
                'activeVersion.departemenMaster',
                'latestApprovedVersion.departemenMaster',
                'pemangkuSaatIni.departemenMaster',
                'riwayatPemangku.pegawai.departemenMaster',
                'riwayatPemangku.version',
            ])
            ->orderBy('id_departemen')
            ->orderByRaw('parent_jabatan IS NULL DESC')
            ->orderBy('parent_jabatan')
            ->orderBy('nama_jabatan')
            ->get();

        $allNodes = $this->mapJabatanToNodes($jabatans);
        $allNodesById = $allNodes->keyBy('id_jabatan');

        if ($selectedDepartemen) {
            $matchingNodes = $allNodes->filter(function ($node) use ($selectedDepartemen) {
                return (int) ($node->id_departemen ?? 0) === (int) $selectedDepartemen->id_departemen;
            })->values();

            $nodes = $this->filterNodesWithParents($allNodes, $allNodesById, $matchingNodes);
            $summaryBaseNodes = $matchingNodes;
        } else {
            $nodes = $allNodes;
            $summaryBaseNodes = $allNodes;
        }

        $struktur = $this->buildTree($nodes);
        $summary = $this->buildSummary($summaryBaseNodes, $nodes);

        /*
        |--------------------------------------------------------------------------
        | Data khusus print / save PDF
        |--------------------------------------------------------------------------
        | Jika tidak difilter, PDF akan punya halaman overview perusahaan dan halaman
        | lanjutan per departemen. Jika difilter, PDF hanya halaman departemen terpilih.
        |--------------------------------------------------------------------------
        */
        $printSections = $this->buildPrintSections(
            $departemenList,
            $allNodes,
            $allNodesById,
            $selectedDepartemen,
            $struktur,
            $summary
        );

        return view('struktur-jabatan.index', compact(
            'struktur',
            'departemenList',
            'selectedDepartemen',
            'idDepartemen',
            'sheetTitle',
            'sheetCode',
            'summary',
            'printSections'
        ));
    }

    public function show($id_jabatan)
    {
        abort_unless(auth()->check(), 403);

        $jabatan = Jabatan::query()
            ->with([
                'departemenMaster',
                'parent.departemenMaster',
                'parent.activeVersion',
                'parent.latestApprovedVersion',
                'children.departemenMaster',
                'children.activeVersion',
                'children.latestApprovedVersion',
                'activeVersion.departemenMaster',
                'latestApprovedVersion.departemenMaster',
                'versions' => function ($query) {
                    $query->orderByDesc('version_number');
                },
                'versions.departemenMaster',
                'pemangkuSaatIni.departemenMaster',
                'riwayatPemangku.pegawai.departemenMaster',
                'riwayatPemangku.version',
            ])
            ->where('id_jabatan', $id_jabatan)
            ->firstOrFail();

        $version = $jabatan->activeVersion ?: $jabatan->latestApprovedVersion;

        $parentId = $version->parent_jabatan
            ?? $jabatan->parent_jabatan
            ?? null;

        $parent = null;

        if ($parentId) {
            $parent = Jabatan::query()
                ->with(['departemenMaster', 'activeVersion', 'latestApprovedVersion'])
                ->where('id_jabatan', $parentId)
                ->first();
        }

        $pemangkuSaatIni = $jabatan->pemangkuSaatIni ?: collect();
        $riwayatPemangku = $jabatan->riwayatPemangku ?: collect();

        return view('struktur-jabatan.show', compact(
            'jabatan',
            'version',
            'parent',
            'pemangkuSaatIni',
            'riwayatPemangku'
        ));
    }

    private function mapJabatanToNodes(Collection $jabatans): Collection
    {
        return $jabatans->map(function (Jabatan $jabatan) {
            $version = $jabatan->activeVersion ?: $jabatan->latestApprovedVersion;

            $idDepartemen = $version->id_departemen
                ?? $jabatan->id_departemen
                ?? null;

            $departemenName = $version?->departemenMaster?->nama_departemen
                ?? $jabatan->departemenMaster?->nama_departemen
                ?? $version?->departemen
                ?? $jabatan->departemen
                ?? '-';

            $parentJabatan = $version->parent_jabatan
                ?? $jabatan->parent_jabatan
                ?? null;

            $pemangku = $jabatan->pemangkuSaatIni ?: collect();

            return (object) [
                'id_jabatan' => $jabatan->id_jabatan,
                'id_jabatan_version' => $version?->id_jabatan_version,
                'version_number' => $version?->version_number,

                'nama_jabatan' => $version->nama_jabatan
                    ?? $jabatan->nama_jabatan
                    ?? '-',

                'id_departemen' => $idDepartemen,
                'departemen' => $departemenName,

                'gol_jabatan' => $version->gol_jabatan
                    ?? $jabatan->gol_jabatan
                    ?? null,

                'home_base' => $version->home_base
                    ?? $jabatan->home_base
                    ?? null,

                'lokasi_kerja' => $version->lokasi_kerja
                    ?? $jabatan->lokasi_kerja
                    ?? null,

                'parent_jabatan' => $parentJabatan,
                'pemangkuSaatIni' => $pemangku,
                'riwayatPemangku' => $jabatan->riwayatPemangku ?: collect(),
                'children' => collect(),
            ];
        })->values();
    }

    private function filterNodesWithParents(Collection $allNodes, Collection $allNodesById, Collection $matchingNodes): Collection
    {
        $idsToKeep = collect();

        foreach ($matchingNodes as $node) {
            $idsToKeep->push($node->id_jabatan);

            $parentId = $node->parent_jabatan;
            $guard = 0;

            while ($parentId && $allNodesById->has($parentId) && $guard < 100) {
                $parentNode = $allNodesById->get($parentId);
                $idsToKeep->push($parentNode->id_jabatan);
                $parentId = $parentNode->parent_jabatan;
                $guard++;
            }
        }

        $idsToKeep = $idsToKeep->unique()->values();

        return $allNodes->filter(function ($node) use ($idsToKeep) {
            return $idsToKeep->contains($node->id_jabatan);
        })->values();
    }

    private function buildTree(Collection $nodes): Collection
    {
        $nodes = $nodes->map(function ($node) {
            $cloned = clone $node;
            $cloned->children = collect();
            return $cloned;
        })->values();

        $nodesById = $nodes->keyBy('id_jabatan');

        foreach ($nodes as $node) {
            if ($node->parent_jabatan && $nodesById->has($node->parent_jabatan)) {
                $nodesById->get($node->parent_jabatan)->children->push($node);
            }
        }

        foreach ($nodes as $node) {
            $node->children = $node->children
                ->sortBy([
                    ['id_departemen', 'asc'],
                    ['nama_jabatan', 'asc'],
                ])
                ->values();
        }

        return $nodes->filter(function ($node) use ($nodesById) {
            return empty($node->parent_jabatan) || !$nodesById->has($node->parent_jabatan);
        })->values();
    }

    private function buildSummary(Collection $summaryBaseNodes, ?Collection $shownNodes = null): array
    {
        $totalFormasi = $summaryBaseNodes->count();

        $filled = $summaryBaseNodes->filter(function ($node) {
            return $node->pemangkuSaatIni && $node->pemangkuSaatIni->count() > 0;
        })->count();

        $vacant = max(0, $totalFormasi - $filled);

        return [
            'filled' => $filled,
            'vacant' => $vacant,
            'total' => $totalFormasi,
            'shown' => $shownNodes ? $shownNodes->count() : $summaryBaseNodes->count(),
        ];
    }

    private function buildPrintSections(
        Collection $departemenList,
        Collection $allNodes,
        Collection $allNodesById,
        ?Departemen $selectedDepartemen,
        Collection $currentTree,
        array $currentSummary
    ): Collection {
        $sections = collect();

        if ($selectedDepartemen) {
            $sections->push([
                'type' => 'department',
                'title' => 'STRUKTUR ORGANISASI - ' . strtoupper($selectedDepartemen->nama_departemen),
                'subtitle' => 'PT BUMI SIAK PUSAKO',
                'code' => ($selectedDepartemen->kode_departemen ?: $selectedDepartemen->singkatan ?: $selectedDepartemen->urutan) . ' - ' . $selectedDepartemen->id_departemen,
                'struktur' => $currentTree,
                'summary' => $currentSummary,
            ]);

            return $sections;
        }

        $sections->push([
            'type' => 'overview',
            'title' => 'STRUKTUR ORGANISASI',
            'subtitle' => 'PT BUMI SIAK PUSAKO',
            'code' => 'CORP - 1',
            'struktur' => $currentTree,
            'summary' => $currentSummary,
        ]);

        foreach ($departemenList as $departemen) {
            $matchingNodes = $allNodes->filter(function ($node) use ($departemen) {
                return (int) ($node->id_departemen ?? 0) === (int) $departemen->id_departemen;
            })->values();

            if ($matchingNodes->isEmpty()) {
                continue;
            }

            $nodes = $this->filterNodesWithParents($allNodes, $allNodesById, $matchingNodes);
            $tree = $this->buildTree($nodes);
            $summary = $this->buildSummary($matchingNodes, $nodes);

            $sections->push([
                'type' => 'department',
                'title' => 'STRUKTUR ORGANISASI - ' . strtoupper($departemen->nama_departemen),
                'subtitle' => 'PT BUMI SIAK PUSAKO',
                'code' => ($departemen->kode_departemen ?: $departemen->singkatan ?: $departemen->urutan) . ' - ' . $departemen->id_departemen,
                'struktur' => $tree,
                'summary' => $summary,
            ]);
        }

        return $sections;
    }
}
