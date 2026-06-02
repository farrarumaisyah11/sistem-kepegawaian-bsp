<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelatihan extends Model
{
    protected $table = 'tb_kursus';
    protected $primaryKey = 'id_kursus';
    public $timestamps = false;

    protected $fillable = [
        'nip',
        'tanggal_mulai_kursus',
        'tanggal_selesai_kursus',
        'nama_kegiatan_kursus',
        'jenis_kursus',
        'tanggal_mulai_berlaku',
        'tanggal_selesai_berlaku',
    ];

    protected $casts = [
        'nip'                       => 'integer',
        'tanggal_mulai_kursus'      => 'date',
        'tanggal_selesai_kursus'    => 'date',
        'tanggal_mulai_berlaku'     => 'date',
        'tanggal_selesai_berlaku'   => 'date',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nip');
    }
}
