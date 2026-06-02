<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keluarga extends Model
{
    protected $table = 'tb_keluarga';
    protected $primaryKey = 'id_keluarga';
    public $timestamps = false;

    protected $fillable = [
        'nip','tanggal_keluarga','nama_keluarga','ket_keluarga',
    ];

    protected $casts = [
        'nip' => 'integer',
        'tanggal_keluarga' => 'date',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nip');
    }
}
