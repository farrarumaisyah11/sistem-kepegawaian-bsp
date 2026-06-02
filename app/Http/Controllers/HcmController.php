<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Jabatan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class HcmController extends Controller
{
    public function index()
    {
        return view('dashboard', $this->getDashboardData());
    }

    public function stats()
    {
        return response()->json($this->getDashboardData());
    }

    private function getDashboardData(): array
    {
        $totalPegawai = Pegawai::count();
        $totalJabatan = Jabatan::count();

        $pwt  = Pegawai::where('hubungan_kerja', 'PWT')->count();
        $pwtt = Pegawai::where('hubungan_kerja', 'PWTT')->count();

        $manajerial = Pegawai::where('status', 'Manajerial')->count();
        $staffUtama = Pegawai::where('status', 'Staf Utama')->count();
        $staffMadya = Pegawai::where('status', 'Staf Madya')->count();
        $staffBiasa = Pegawai::where('status', 'Staf Biasa')->count();

       // Profesional
$profCore = Pegawai::where('profesional', 'Core')->count();

$profSubcore = Pegawai::where('profesional', 'Subcore')->count();

$profSupport = Pegawai::where('profesional', 'Support')->count();

$staffProfesional = [
    'Core'    => $profCore,
    'Subcore' => $profSubcore,
    'Support' => $profSupport,
];

        $lokasiKerja = [
            'Jakarta'   => Pegawai::where('lokasi_kerja', 'Jakarta')->count(),
            'Pekanbaru' => Pegawai::where('lokasi_kerja', 'Pekanbaru')->count(),
            'Zamrud'    => Pegawai::where('lokasi_kerja', 'Zamrud')->count(),
            'Pedada'    => Pegawai::where('lokasi_kerja', 'Pedada')->count(),
            'West Area' => Pegawai::where('lokasi_kerja', 'West Area')->count(),
        ];

        $tableName = (new Pegawai())->getTable();

        if (Schema::hasColumn($tableName, 'departemen')) {
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

        $statusPegawaiLabels = ['Manajerial', 'Staf Utama', 'Staf Madya', 'Staf Biasa'];
        $statusPegawaiData   = [$manajerial, $staffUtama, $staffMadya, $staffBiasa];

        $pegawaiTrendLabels = [];
        $pegawaiTrendData   = [];

        for ($i = 5; $i >= 0; $i--) {
            $bulan = Carbon::now()->subMonths($i);

            $pegawaiTrendLabels[] = $bulan->translatedFormat('M');

            $pegawaiTrendData[] = Pegawai::whereDate('tgl_masuk', '<=', $bulan->copy()->endOfMonth())
                ->count();
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
            'pegawaiTrendLabels'          => $pegawaiTrendLabels,
            'pegawaiTrendData'            => $pegawaiTrendData,
        ];
    }
}