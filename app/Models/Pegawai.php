<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $table = 'tb_karyawan';
    protected $primaryKey = 'nip';

    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nip',
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
        'foto',
    ];

    public function getRouteKeyName()
    {
        return 'nip';
    }

    public function masterJabatan()
    {
        return $this->belongsTo(Jabatan::class, 'id_jabatan', 'id_jabatan');
    }

    public function pendidikan()
    {
        return $this->hasMany(Pendidikan::class, 'nip', 'nip');
    }

    public function kursus()
    {
        return $this->hasMany(Pelatihan::class, 'nip', 'nip');
    }

    public function pelatihan()
    {
        return $this->kursus();
    }

    public function pengalamanBsp()
    {
        return $this->hasMany(PengalamanBsp::class, 'nip', 'nip');
    }

    public function pengalamanLuarBsp()
    {
        return $this->hasMany(PengalamanLuarBsp::class, 'nip', 'nip');
    }

    public function keluarga()
    {
        return $this->hasMany(Keluarga::class, 'nip', 'nip');
    }

    public function penilaian()
    {
        return $this->hasMany(Penilaian::class, 'nip', 'nip');
    }

    public function peng_bsp()
    {
        return $this->pengalamanBsp();
    }

    public function peng_luar()
    {
        return $this->pengalamanLuarBsp();
    }

    /*
    |--------------------------------------------------------------------------
    | Job Description Versioning
    |--------------------------------------------------------------------------
    | Menyimpan riwayat job description yang pernah diampu pegawai.
    | currentJobdeskVersion = jobdesk aktif pegawai saat ini.
    |--------------------------------------------------------------------------
    */
    public function jobdeskVersions()
    {
        return $this->hasMany(PegawaiJabatanVersion::class, 'nip', 'nip')
            ->orderByDesc('assigned_at');
    }

    public function currentJobdeskVersion()
    {
        return $this->hasOne(PegawaiJabatanVersion::class, 'nip', 'nip')
            ->where('is_current', 1)
            ->latest('assigned_at');
    }

    protected static function booted()
    {
        static::deleting(function ($pegawai) {
            $pegawai->pendidikan()->delete();
            $pegawai->kursus()->delete();
            $pegawai->pengalamanBsp()->delete();
            $pegawai->pengalamanLuarBsp()->delete();
            $pegawai->keluarga()->delete();
            $pegawai->penilaian()->delete();

            /*
            |--------------------------------------------------------------------------
            | Jobdesk Versioning
            |--------------------------------------------------------------------------
            | Riwayat jobdesk ikut dihapus ketika pegawai dihapus agar tidak menjadi
            | orphan record. Jika perusahaan ingin arsip tetap tersimpan walaupun pegawai
            | dihapus, bagian ini bisa diubah menjadi tidak menghapus.
            |--------------------------------------------------------------------------
            */
            $pegawai->jobdeskVersions()->delete();
        });
    }
}
