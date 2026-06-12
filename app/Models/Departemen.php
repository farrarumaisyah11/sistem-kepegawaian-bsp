<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departemen extends Model
{
    protected $table = 'tb_departemen';
    protected $primaryKey = 'id_departemen';

    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'kode_departemen',
        'nama_departemen',
        'singkatan',
        'parent_id_departemen',
        'level_departemen',
        'strukturdata',
        'manager_nip',
        'urutan',
        'keterangan',
        'is_active',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id_departemen', 'id_departemen');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id_departemen', 'id_departemen')
            ->orderBy('urutan')
            ->orderBy('nama_departemen');
    }

    public function manager()
    {
        return $this->belongsTo(Pegawai::class, 'manager_nip', 'nip');
    }

    public function jabatans()
    {
        return $this->hasMany(Jabatan::class, 'id_departemen', 'id_departemen')
            ->orderBy('nama_jabatan');
    }

    public function jabatanVersions()
    {
        return $this->hasMany(JabatanVersion::class, 'id_departemen', 'id_departemen');
    }

    public function pegawai()
    {
        return $this->hasMany(Pegawai::class, 'id_departemen', 'id_departemen')
            ->orderBy('nama');
    }
}
