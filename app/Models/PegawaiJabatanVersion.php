<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PegawaiJabatanVersion extends Model
{
    protected $table = 'tb_pegawai_jabatan_versions';
    protected $primaryKey = 'id_pegawai_jabatan_version';

    public $incrementing = true;
    protected $keyType = 'int';

    /*
    |--------------------------------------------------------------------------
    | Timestamp
    |--------------------------------------------------------------------------
    | Tabel tb_pegawai_jabatan_versions pada sistem ini memakai assigned_at,
    | ended_at, acknowledged_at, bukan created_at dan updated_at bawaan Laravel.
    |--------------------------------------------------------------------------
    */
    public $timestamps = false;

    protected $fillable = [
        'nip',
        'id_jabatan',
        'id_jabatan_version',

        'assigned_at',
        'assigned_by',
        'assigned_by_name',

        'ended_at',
        'ended_by',
        'ended_by_name',

        'is_current',

        'acknowledged_at',
        'acknowledged_ip',
        'acknowledged_user_agent',
    ];

    protected $casts = [
        'nip' => 'string',
        'id_jabatan' => 'integer',
        'id_jabatan_version' => 'integer',

        'assigned_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_current' => 'boolean',
        'acknowledged_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relasi Pegawai
    |--------------------------------------------------------------------------
    | Menghubungkan tb_pegawai_jabatan_versions.nip ke tb_karyawan.nip.
    | NIP diperlakukan sebagai string karena beberapa NIP memiliki angka 0 di depan.
    |--------------------------------------------------------------------------
    */
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nip');
    }

    /*
    |--------------------------------------------------------------------------
    | Alias Relasi Pegawai
    |--------------------------------------------------------------------------
    | Alias ini disediakan agar pemanggilan di view/controller lebih fleksibel.
    |--------------------------------------------------------------------------
    */
    public function pegawaiMaster()
    {
        return $this->pegawai();
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi Jabatan
    |--------------------------------------------------------------------------
    | Menghubungkan tb_pegawai_jabatan_versions.id_jabatan ke tb_jabatan.id_jabatan.
    |--------------------------------------------------------------------------
    */
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'id_jabatan', 'id_jabatan');
    }

    /*
    |--------------------------------------------------------------------------
    | Alias Relasi Jabatan
    |--------------------------------------------------------------------------
    */
    public function masterJabatan()
    {
        return $this->jabatan();
    }

    public function jabatanMaster()
    {
        return $this->jabatan();
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi Jabatan Version
    |--------------------------------------------------------------------------
    | Menghubungkan riwayat pegawai ke versi job description yang pernah berlaku.
    |--------------------------------------------------------------------------
    */
    public function version()
    {
        return $this->belongsTo(JabatanVersion::class, 'id_jabatan_version', 'id_jabatan_version');
    }

    /*
    |--------------------------------------------------------------------------
    | Alias Relasi Jabatan Version
    |--------------------------------------------------------------------------
    */
    public function jabatanVersion()
    {
        return $this->version();
    }

    public function jobdeskVersion()
    {
        return $this->version();
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi Departemen lewat Jabatan
    |--------------------------------------------------------------------------
    | Departemen dibaca melalui jabatan:
    | tb_pegawai_jabatan_versions.id_jabatan
    | -> tb_jabatan.id_jabatan
    | -> tb_jabatan.id_departemen
    | -> tb_departemen.id_departemen
    |--------------------------------------------------------------------------
    */
    public function departemenMaster()
    {
        return $this->hasOneThrough(
            Departemen::class,
            Jabatan::class,
            'id_jabatan',
            'id_departemen',
            'id_jabatan',
            'id_departemen'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scope Aktif
    |--------------------------------------------------------------------------
    | Memudahkan query riwayat pemangku jabatan yang masih aktif.
    |--------------------------------------------------------------------------
    */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', 1);
    }

    public function scopeAktif($query)
    {
        return $this->scopeCurrent($query);
    }

    /*
    |--------------------------------------------------------------------------
    | Scope Riwayat
    |--------------------------------------------------------------------------
    | Memudahkan query riwayat jabatan yang sudah tidak aktif.
    |--------------------------------------------------------------------------
    */
    public function scopeHistory($query)
    {
        return $query->where(function ($q) {
            $q->where('is_current', 0)
              ->orWhereNotNull('ended_at');
        });
    }

    public function scopeRiwayat($query)
    {
        return $this->scopeHistory($query);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor Status
    |--------------------------------------------------------------------------
    */
    public function getStatusLabelAttribute()
    {
        return $this->is_current ? 'Aktif' : 'Riwayat';
    }

    public function getStatusBadgeAttribute()
    {
        return $this->is_current ? 'success' : 'secondary';
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor Acknowledgement
    |--------------------------------------------------------------------------
    */
    public function getAcknowledgementLabelAttribute()
    {
        return $this->acknowledged_at ? 'Sudah Dibaca' : 'Belum Dibaca';
    }

    public function getAcknowledgementBadgeAttribute()
    {
        return $this->acknowledged_at ? 'success' : 'warning';
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor Periode Jabatan
    |--------------------------------------------------------------------------
    | Dipakai pada tampilan detail struktur jabatan agar riwayat lebih mudah dibaca.
    |--------------------------------------------------------------------------
    */
    public function getPeriodeLabelAttribute()
    {
        $mulai = $this->assigned_at
            ? $this->assigned_at->locale('id')->translatedFormat('d F Y')
            : '-';

        $selesai = $this->ended_at
            ? $this->ended_at->locale('id')->translatedFormat('d F Y')
            : 'Sekarang';

        return $mulai . ' - ' . $selesai;
    }
}