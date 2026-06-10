<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PegawaiJabatanVersion extends Model
{
    protected $table = 'tb_pegawai_jabatan_versions';
    protected $primaryKey = 'id_pegawai_jabatan_version';

    public $incrementing = true;
    protected $keyType = 'int';

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
        'assigned_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_current' => 'boolean',
        'acknowledged_at' => 'datetime',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nip');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'id_jabatan', 'id_jabatan');
    }

    public function version()
    {
        return $this->belongsTo(JabatanVersion::class, 'id_jabatan_version', 'id_jabatan_version');
    }

    public function getStatusLabelAttribute()
    {
        return $this->is_current ? 'Aktif' : 'Riwayat';
    }

    public function getAcknowledgementLabelAttribute()
    {
        return $this->acknowledged_at ? 'Sudah Dibaca' : 'Belum Dibaca';
    }
}
