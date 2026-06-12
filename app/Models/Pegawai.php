<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $table = 'tb_karyawan';
    protected $primaryKey = 'nip';

    public $incrementing = false;
    protected $keyType = 'string';
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
        'id_departemen',
        'hubungan_kerja',
        'lokasi_kerja',
        'status',
        'tmt_gol_upah',
        'gol_upah',
        'tgl_masuk',
        'foto',
    ];

    protected $casts = [
        'nip' => 'string',
        'tgl_lahir' => 'date',
        'tmt_gol_jabatan' => 'date',
        'tmt_gol_upah' => 'date',
        'tgl_masuk' => 'date',
        'id_jabatan' => 'integer',
        'id_departemen' => 'integer',
        'gol_jabatan' => 'integer',
        'gol_upah' => 'integer',
    ];

    public function getRouteKeyName()
    {
        return 'nip';
    }

    public function masterJabatan()
    {
        return $this->belongsTo(Jabatan::class, 'id_jabatan', 'id_jabatan');
    }

    public function jabatanMaster()
    {
        return $this->masterJabatan();
    }

    public function departemenMaster()
    {
        return $this->belongsTo(Departemen::class, 'id_departemen', 'id_departemen');
    }

    public function pendidikan()
    {
        return $this->hasMany(Pendidikan::class, 'nip', 'nip')
            ->orderBy('pendidikan_mulai')
            ->orderBy('id_pendidikan');
    }

    public function kursus()
    {
        return $this->hasMany(Pelatihan::class, 'nip', 'nip')
            ->orderByDesc('tanggal_mulai_kursus')
            ->orderByDesc('id_kursus');
    }

    public function pelatihan()
    {
        return $this->kursus();
    }

    public function pengalamanBsp()
    {
        return $this->hasMany(PengalamanBsp::class, 'nip', 'nip')
            ->orderBy('pglmn_bsp_mulai')
            ->orderBy('id_pengalaman_bsp');
    }

    public function pengalamanLuarBsp()
    {
        return $this->hasMany(PengalamanLuarBsp::class, 'nip', 'nip')
            ->orderBy('pglmn_luar_bsp_mulai')
            ->orderBy('id_pengalaman_luar_bsp');
    }

    public function keluarga()
    {
        return $this->hasMany(Keluarga::class, 'nip', 'nip')
            ->orderByRaw("FIELD(ket_keluarga, 'Suami/Istri', 'Anak', 'Orang Tua')")
            ->orderBy('tanggal_keluarga')
            ->orderBy('id_keluarga');
    }

    public function penilaian()
    {
        return $this->hasMany(Penilaian::class, 'nip', 'nip')
            ->orderByDesc('tahun_penilaian')
            ->orderByDesc('id_penilaian');
    }

    public function peng_bsp()
    {
        return $this->pengalamanBsp();
    }

    public function peng_luar()
    {
        return $this->pengalamanLuarBsp();
    }

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

    public function riwayatJabatan()
    {
        return $this->jobdeskVersions();
    }

    public function jabatanAktifVersion()
    {
        return $this->currentJobdeskVersion();
    }

    public function getFotoUrlAttribute()
    {
        if (empty($this->foto)) {
            return null;
        }

        if (str_starts_with((string) $this->foto, 'http://') || str_starts_with((string) $this->foto, 'https://')) {
            return $this->foto;
        }

        return asset('storage/' . ltrim($this->foto, '/'));
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
            $pegawai->jobdeskVersions()->delete();
        });
    }
}
