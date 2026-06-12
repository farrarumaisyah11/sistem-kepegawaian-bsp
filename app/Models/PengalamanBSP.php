<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengalamanBsp extends Model
{
    protected $table = 'tb_pengalaman_bsp';
    protected $primaryKey = 'id_pengalaman_bsp';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nip',
        'pglmn_bsp_mulai',
        'pglmn_bsp_selesai',
        'jenjang_jabatan_bsp',
        'pengalaman_jabatan',
        'pengalaman_lokasi',

    ];

    protected $casts = [
        'nip' => 'string',
        'pglmn_bsp_mulai' => 'date',
        'pglmn_bsp_selesai' => 'date',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nip');
    }
}
