<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanPerubahan extends Model
{
    protected $table = 'tb_pengajuan_perubahan';
    protected $primaryKey = 'id_pengajuan';

    protected $fillable = [
        'nip',
        'jenis',
        'nama_pegawai',
        'id_user_pengaju',
        'role_pengaju',
        'status',
        'payload',
        'catatan_pegawai',
        'catatan_reviewer',
        'id_user_proses',
        'role_pemroses',
        'dilihat_pada',
        'diproses_pada',
        'ditolak_pada',
    ];

    protected $casts = [
        'payload' => 'array',
        'dilihat_pada' => 'datetime',
        'diproses_pada' => 'datetime',
        'ditolak_pada' => 'datetime',
    ];

    public function getRouteKeyName()
    {
        return 'id_pengajuan';
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nip');
    }

    public function getStatusLabelAttribute()
{
    return match ($this->status) {
        'diajukan', 'pending', 'belum_diolah' => 'Baru Masuk',
        'diproses'                           => 'Diproses',
        'diterima', 'disetujui'              => 'Diterima',
        'ditolak'                            => 'Ditolak',
        default                              => '-',
    };
}

   public function getStatusBadgeAttribute()
{
    return match ($this->status) {
        'diajukan', 'pending', 'belum_diolah' => 'secondary',
        'diproses'                           => 'warning',
        'diterima', 'disetujui'              => 'success',
        'ditolak'                            => 'danger',
        default                              => 'dark',
    };
}

    public function scopeStatus($query, $status)
{
    if (!$status) return $query;

    if (in_array($status, ['diajukan', 'pending', 'belum_diolah'])) {
        return $query->whereIn('status', ['diajukan', 'pending', 'belum_diolah']);
    }

    if (in_array($status, ['diterima', 'disetujui'])) {
        return $query->whereIn('status', ['diterima', 'disetujui']);
    }

    return $query->where('status', $status);
}

    public function scopeSearch($query, $q)
    {
        if (!$q) return $query;

        return $query->where(function ($sub) use ($q) {
            $sub->where('nip', 'like', "%{$q}%")
                ->orWhere('nama_pegawai', 'like', "%{$q}%")
                ->orWhereHas('pegawai', function ($p) use ($q) {
                    $p->where('nama', 'like', "%{$q}%");
                });
        });
    }

    public function getPegawaiPayload()
    {
        return $this->payload['pegawai'] ?? [];
    }

    public function getSection($key)
    {
        return $this->payload[$key] ?? [];
    }
}