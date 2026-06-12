<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengalamanLuarBsp extends Model
{
    protected $table = 'tb_pengalaman_luar_bsp';
    protected $primaryKey = 'id_pengalaman_luar_bsp';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nip',
        'pglmn_luar_bsp_mulai',
        'pglmn_luar_bsp_selesai',
        'pengalaman_luar_jabatan',
        'pengalaman_luar_lokasi',

    ];

    protected $casts = [
        'nip' => 'string',
        'pglmn_luar_bsp_mulai' => 'date',
        'pglmn_luar_bsp_selesai' => 'date',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nip');
    }
}
