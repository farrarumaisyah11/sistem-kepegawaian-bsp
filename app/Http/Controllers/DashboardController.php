<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Jabatan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
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

        /*
        |--------------------------------------------------------------------------
        | Hubungan Kerja
        |--------------------------------------------------------------------------
        | Menggunakan normalisasi TRIM + LOWER agar data tetap terbaca walaupun
        | di database ada spasi, misalnya "PWT " atau " pwtt".
        |--------------------------------------------------------------------------
        */
        $pwt  = $this->countByNormalizedColumn('hubungan_kerja', 'PWT');
        $pwtt = $this->countByNormalizedColumn('hubungan_kerja', 'PWTT');

        /*
        |--------------------------------------------------------------------------
        | Status Pegawai
        |--------------------------------------------------------------------------
        */
        $manajerial = $this->countByNormalizedColumn('status', 'Manajerial');
        $staffUtama = $this->countByNormalizedColumn('status', 'Staf Utama');
        $staffMadya = $this->countByNormalizedColumn('status', 'Staf Madya');
        $staffBiasa = $this->countByNormalizedColumn('status', 'Staf Biasa');

        /*
        |--------------------------------------------------------------------------
        | Profesional
        |--------------------------------------------------------------------------
        | Bagian ini dipakai oleh donut chart "Staf Profesional".
        |--------------------------------------------------------------------------
        */
        $profCore    = $this->countByNormalizedColumn('profesional', 'Core');
        $profSubcore = $this->countByNormalizedColumn('profesional', 'Subcore');
        $profSupport = $this->countByNormalizedColumn('profesional', 'Support');

        $staffProfesional = [
            'Core'    => $profCore,
            'Subcore' => $profSubcore,
            'Support' => $profSupport,
        ];

        /*
        |--------------------------------------------------------------------------
        | Lokasi Kerja
        |--------------------------------------------------------------------------
        */
        $lokasiKerja = [
            'Jakarta'   => $this->countByNormalizedColumn('lokasi_kerja', 'Jakarta'),
            'Pekanbaru' => $this->countByNormalizedColumn('lokasi_kerja', 'Pekanbaru'),
            'Zamrud'    => $this->countByNormalizedColumn('lokasi_kerja', 'Zamrud'),
            'Pedada'    => $this->countByNormalizedColumn('lokasi_kerja', 'Pedada'),
            'West Area' => $this->countByNormalizedColumn('lokasi_kerja', 'West Area'),
        ];

        /*
        |--------------------------------------------------------------------------
        | Jumlah Departemen
        |--------------------------------------------------------------------------
        */
        $tableName = (new Pegawai())->getTable();

        if (Schema::hasColumn($tableName, 'departemen')) {
            $jumlahDepartemen = (int) Pegawai::query()
                ->whereNotNull('departemen')
                ->whereRaw("TRIM(departemen) <> ''")
                ->selectRaw("COUNT(DISTINCT TRIM(departemen)) AS total")
                ->value('total');
        } else {
            $jumlahDepartemen = 0;
        }

        $jumlahLokasiAktif = collect($lokasiKerja)
            ->filter(fn ($jumlah) => (int) $jumlah > 0)
            ->count();

        $hubunganKerjaDominan = $pwt >= $pwtt ? 'PWT' : 'PWTT';
        $jumlahHubunganKerjaDominan = max($pwt, $pwtt);

        $statusPegawaiLabels = [
            'Manajerial',
            'Staf Utama',
            'Staf Madya',
            'Staf Biasa',
        ];

        $statusPegawaiData = [
            $manajerial,
            $staffUtama,
            $staffMadya,
            $staffBiasa,
        ];

        /*
        |--------------------------------------------------------------------------
        | Trend Pegawai
        |--------------------------------------------------------------------------
        | Tetap dipertahankan walaupun di view saat ini belum ditampilkan.
        |--------------------------------------------------------------------------
        */
        $pegawaiTrendLabels = [];
        $pegawaiTrendData   = [];

        for ($i = 5; $i >= 0; $i--) {
            $bulan = Carbon::now()->subMonths($i);

            $pegawaiTrendLabels[] = $bulan->translatedFormat('M');

            $pegawaiTrendData[] = Pegawai::query()
                ->whereNotNull('tgl_masuk')
                ->whereDate('tgl_masuk', '<=', $bulan->copy()->endOfMonth()->toDateString())
                ->count();
        }

        return [
            'totalPegawai'               => $totalPegawai,
            'totalJabatan'               => $totalJabatan,

            'pwt'                        => $pwt,
            'pwtt'                       => $pwtt,

            'manajerial'                 => $manajerial,
            'staffUtama'                 => $staffUtama,
            'staffMadya'                 => $staffMadya,
            'staffBiasa'                 => $staffBiasa,

            'profCore'                   => $profCore,
            'profSubcore'                => $profSubcore,
            'profSupport'                => $profSupport,
            'staffProfesional'           => $staffProfesional,

            'lokasiKerja'                => $lokasiKerja,

            'jumlahDepartemen'           => $jumlahDepartemen,
            'jumlahLokasiAktif'          => $jumlahLokasiAktif,
            'hubunganKerjaDominan'       => $hubunganKerjaDominan,
            'jumlahHubunganKerjaDominan' => $jumlahHubunganKerjaDominan,

            'statusPegawaiLabels'        => $statusPegawaiLabels,
            'statusPegawaiData'          => $statusPegawaiData,

            'pegawaiTrendLabels'         => $pegawaiTrendLabels,
            'pegawaiTrendData'           => $pegawaiTrendData,
        ];
    }

    private function countByNormalizedColumn(string $column, string $value): int
    {
        $allowedColumns = [
            'hubungan_kerja',
            'status',
            'profesional',
            'lokasi_kerja',
        ];

        if (!in_array($column, $allowedColumns, true)) {
            return 0;
        }

        return Pegawai::query()
            ->whereRaw("LOWER(TRIM(COALESCE({$column}, ''))) = ?", [
                strtolower($value),
            ])
            ->count();
    }
}