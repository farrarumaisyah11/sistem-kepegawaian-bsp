<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pendidikan extends Model
{
    protected $table = 'tb_pendidikan';
    protected $primaryKey = 'id_pendidikan';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nip',
        'pendidikan_mulai',
        'pendidikan_selesai',
        'jenjang_pendidikan',
        'nama_institusi',
        'jurusan',
        'lokasi_pendidikan',

    ];

    protected $casts = [
        'nip' => 'string',
        'pendidikan_mulai' => 'date',
        'pendidikan_selesai' => 'date',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nip');
    }
}
