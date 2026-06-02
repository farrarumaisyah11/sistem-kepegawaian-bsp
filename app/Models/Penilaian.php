<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    protected $table = 'tb_penilaian';
    protected $primaryKey = 'id_penilaian';
    public $timestamps = false;

    protected $fillable = [
        'nip',
        'tahun_penilaian',
        'nilai_penilaian',
        'dasar_penilaian',
    ];

    protected $casts = [
        'nip'              => 'integer',
        'tahun_penilaian'  => 'integer',
        'nilai_penilaian'  => 'float',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nip');
    }
}
