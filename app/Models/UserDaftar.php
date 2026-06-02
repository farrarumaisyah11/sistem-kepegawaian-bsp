<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UserDaftar extends Authenticatable
{
    use Notifiable;

    /**
     * Nama tabel
     */
    protected $table = 'tb_daftar';

    /**
     * Primary key
     * (sesuaikan dengan struktur tabel kamu, biasanya id_daftar)
     */
    protected $primaryKey = 'id_daftar';

    /**
     * Primary key auto increment
     */
    public $incrementing = true;

    /**
     * Tipe primary key
     */
    protected $keyType = 'int';

    /**
     * Kolom yang boleh diisi mass assignment
     */
    protected $fillable = [
        'nip',
        'username',
        'password',
        'role',
        'status',
    ];

    /**
     * Kolom yang disembunyikan saat serialisasi
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Aktifkan timestamps (created_at & updated_at)
     */
    public $timestamps = true;

    /**
     * Relasi ke Pegawai (opsional tapi sangat disarankan)
     */
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nip');
    }
}
