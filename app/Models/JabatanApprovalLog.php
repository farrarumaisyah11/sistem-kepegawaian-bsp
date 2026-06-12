<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JabatanApprovalLog extends Model
{
    protected $table = 'tb_jabatan_approval_logs';
    protected $primaryKey = 'id_jabatan_approval_log';

    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_jabatan',
        'id_jabatan_version',
        'approval_token',
        'action',
        'actor_user_id',
        'actor_nip',
        'actor_name',
        'actor_role',
        'actor_jabatan',
        'actor_departemen',
        'actor_id_departemen',
        'ip_address',
        'user_agent',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'id_jabatan' => 'integer',
        'id_jabatan_version' => 'integer',
        'actor_user_id' => 'integer',
        'actor_id_departemen' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'id_jabatan', 'id_jabatan');
    }

    public function version()
    {
        return $this->belongsTo(JabatanVersion::class, 'id_jabatan_version', 'id_jabatan_version');
    }

    public function scopeForJabatan($query, $idJabatan)
    {
        return $query->where('id_jabatan', $idJabatan);
    }

    public function scopeForVersion($query, $idJabatanVersion)
    {
        return $query->where('id_jabatan_version', $idJabatanVersion);
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderByDesc('created_at')
            ->orderByDesc('id_jabatan_approval_log');
    }

    public function getActionLabelAttribute()
    {
        return match ($this->action) {
            'approval_page_opened' => 'Halaman Approval Dibuka',
            'approval_link_copied' => 'Link Approval Disalin',
            'approval_link_opened' => 'Link Approval Dibuka',
            'approval_detail_opened' => 'Detail Approval Dibuka',
            'approved_by_pegawai_waiting_hcm' => 'Approval Awal Pegawai, Menunggu HCM',
            'approved_final_by_hcm_direct' => 'Approved Final Langsung oleh HCM',
            'approved_final_by_hcm_from_show' => 'Approved Final oleh HCM dari Detail Jabatan',
            'jobdesc_applied_to_pegawai' => 'Job Description Diterapkan ke Pegawai',
            'draft_created' => 'Draft Job Description Dibuat',
            'draft_updated' => 'Draft Job Description Diperbarui',
            'jabatan_deleted' => 'Jabatan Dihapus',
            default => ucwords(str_replace('_', ' ', (string) $this->action)),
        };
    }
}
