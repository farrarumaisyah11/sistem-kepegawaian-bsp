<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PegawaiTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new PegawaiUtamaSheet(),
            new PendidikanSheet(),
            new KursusSheet(),
            new PengalamanBspSheet(),
            new PengalamanLuarSheet(),
            new KeluargaSheet(),
            new PenilaianSheet(),
        ];
    }
}

class PegawaiUtamaSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'nip',
            'nama',
            'tempat_lahir',
            'tgl_lahir',
            'jenkel',
            'agama',
            'alamat',
            'gol_upah',
            'gol_jabatan',
            'tmt_gol_jabatan',
            'tmt_gol_upah',
            'jabatan',
            'departemen',
            'hubungan_kerja',
            'lokasi_kerja',
            'status',
            'tgl_masuk',
            'profesional',
            'foto',
        ];
    }

    public function array(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Pegawai';
    }
}

class PendidikanSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'nip',
            'pendidikan_mulai',
            'pendidikan_selesai',
            'jenjang_pendidikan',
            'nama_institusi',
            'jurusan',
            'lokasi_pendidikan',
        ];
    }

    public function array(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Pendidikan';
    }
}

class KursusSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'nip',
            'tanggal_mulai_kursus',
            'tanggal_selesai_kursus',
            'jenis_kursus',
            'nama_kegiatan_kursus',
            'tanggal_mulai_berlaku',
            'tanggal_selesai_berlaku',
        ];
    }

    public function array(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Kursus';
    }
}

class PengalamanBspSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'nip',
            'pglmn_bsp_mulai',
            'pglmn_bsp_selesai',
            'pengalaman_jabatan',
            'pengalaman_lokasi',
        ];
    }

    public function array(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Pengalaman_BSP';
    }
}

class PengalamanLuarSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'nip',
            'pglmn_luar_bsp_mulai',
            'pglmn_luar_bsp_selesai',
            'pengalaman_luar_jabatan',
            'pengalaman_luar_lokasi',
        ];
    }

    public function array(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Pengalaman_Luar';
    }
}

class KeluargaSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'nip',
            'nama_keluarga',
            'tanggal_keluarga',
            'ket_keluarga',
        ];
    }

    public function array(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Keluarga';
    }
}

class PenilaianSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'nip',
            'tahun_penilaian',
            'nilai_penilaian',
            'dasar_penilaian',
        ];
    }

    public function array(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Penilaian';
    }
}