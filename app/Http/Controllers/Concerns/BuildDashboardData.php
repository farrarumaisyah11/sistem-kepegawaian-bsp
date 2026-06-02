<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Pegawai;
use App\Models\Jabatan;
use Illuminate\Support\Facades\Schema;

trait BuildDashboardData
{
    protected function getDashboardData(): array
    {
        $totalPegawai = Pegawai::count();
        $totalJabatan = Jabatan::count();

        // Hubungan kerja
        $pwt  = Pegawai::where('hubungan_kerja', 'PWT')->count();
        $pwtt = Pegawai::where('hubungan_kerja', 'PWTT')->count();

        // Status pegawai
        $manajerial = Pegawai::where('status', 'Manajerial')->count();
        $staffUtama = Pegawai::where('status', 'Staf Utama')->count();
        $staffMadya = Pegawai::where('status', 'Staf Madya')->count();
        $staffBiasa = Pegawai::where('status', 'Staf Biasa')->count();

      // Profesional berdasarkan input field profesional
$profCore = Pegawai::where('profesional', 'Core')->count();

$profSubcore = Pegawai::where('profesional', 'Subcore')->count();

$profSupport = Pegawai::where('profesional', 'Support')->count();

        // Lokasi kerja
        $lokasiKerja = [
            'Jakarta'   => Pegawai::where('lokasi_kerja', 'Jakarta')->count(),
            'Pekanbaru' => Pegawai::where('lokasi_kerja', 'Pekanbaru')->count(),
            'Zamrud'    => Pegawai::where('lokasi_kerja', 'Zamrud')->count(),
            'Pedada'    => Pegawai::where('lokasi_kerja', 'Pedada')->count(),
            'West Area' => Pegawai::where('lokasi_kerja', 'West Area')->count(),
        ];

        // Departemen unik dari tb_karyawan
        $pegawaiTable = (new Pegawai())->getTable();
        $jabatanTable = (new Jabatan())->getTable();

        if (Schema::hasColumn($pegawaiTable, 'departemen')) {
            $jumlahDepartemen = Pegawai::whereNotNull('departemen')
                ->where('departemen', '!=', '')
                ->distinct()
                ->count('departemen');
        } else {
            $jumlahDepartemen = 0;
        }

        $jumlahLokasiAktif = collect($lokasiKerja)
            ->filter(fn ($jumlah) => $jumlah > 0)
            ->count();

        $hubunganKerjaDominan = $pwt >= $pwtt ? 'PWT' : 'PWTT';
        $jumlahHubunganKerjaDominan = max($pwt, $pwtt);

        // Status chart
        $statusPegawaiLabels = ['Manajerial', 'Staf Utama', 'Staf Madya', 'Staf Biasa'];
        $statusPegawaiData   = [$manajerial, $staffUtama, $staffMadya, $staffBiasa];

        // Jabatan per departemen
        $jabatanPerDepartemenLabels = [];
        $jabatanPerDepartemenData = [];

        if (Schema::hasColumn($jabatanTable, 'departemen')) {
            $jabatanPerDepartemen = Jabatan::selectRaw('departemen, COUNT(*) as total')
                ->whereNotNull('departemen')
                ->where('departemen', '!=', '')
                ->groupBy('departemen')
                ->orderBy('departemen')
                ->get();

            $jabatanPerDepartemenLabels = $jabatanPerDepartemen->pluck('departemen')->values()->toArray();
            $jabatanPerDepartemenData   = $jabatanPerDepartemen->pluck('total')->values()->toArray();
        }

        return [
            'totalPegawai'                => $totalPegawai,
            'totalJabatan'                => $totalJabatan,
            'pwt'                         => $pwt,
            'pwtt'                        => $pwtt,
            'manajerial'                  => $manajerial,
            'staffUtama'                  => $staffUtama,
            'staffMadya'                  => $staffMadya,
            'staffBiasa'                  => $staffBiasa,
            'profCore'                    => $profCore,
            'profSubcore'                 => $profSubcore,
            'profSupport'                 => $profSupport,
            'staffProfesional'            => $staffProfesional,
            'lokasiKerja'                 => $lokasiKerja,
            'jumlahDepartemen'            => $jumlahDepartemen,
            'jumlahLokasiAktif'           => $jumlahLokasiAktif,
            'hubunganKerjaDominan'        => $hubunganKerjaDominan,
            'jumlahHubunganKerjaDominan'  => $jumlahHubunganKerjaDominan,
            'statusPegawaiLabels'         => $statusPegawaiLabels,
            'statusPegawaiData'           => $statusPegawaiData,
            'jabatanPerDepartemenLabels'  => $jabatanPerDepartemenLabels,
            'jabatanPerDepartemenData'    => $jabatanPerDepartemenData,
        ];
    }
}