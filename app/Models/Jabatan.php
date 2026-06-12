<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    protected $table = 'tb_jabatan';
    protected $primaryKey = 'id_jabatan';

    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'active_version_id',
        'draft_version_id',
        'latest_version_number',
        'jobdesk_updated_at',
        'jobdesk_updated_by',
        'nama_jabatan',
        'departemen',
        'id_departemen',
        'gol_jabatan',
        'home_base',
        'lokasi_kerja',
        'parent_jabatan',
        'tujuan_jabatan',
        'tanggung_jawab',
        'tantangan_jabatan',
        'dim_keuangan',
        'dim_nonkeuangan',
        'bawahan_langsung',
        'internal_perusahaan',
        'external_perusahaan',
        'finansial',
        'non_finansial',
        'pengetahuan_keterampilan',
        'kompetensi',
        'syarat_kompetensi_jabatan',
        'struktur_file',
        'approval_status',
        'approval_token',
        'approved_by',
        'approved_by_name',
        'approved_by_role',
        'approved_by_jabatan',
        'approved_by_departemen',
        'approved_at',
        'approval_catatan',
        'approval_flow_status',
        'proposed_approved_by',
        'proposed_approved_by_name',
        'proposed_approved_by_role',
        'proposed_approved_by_jabatan',
        'proposed_approved_by_departemen',
        'proposed_approved_at',
        'proposed_approval_catatan',
        'hcm_confirmed_by',
        'hcm_confirmed_by_name',
        'hcm_confirmed_at',
        'hcm_confirmation_catatan',
    ];

    protected $casts = [
        'id_departemen' => 'integer',
        'parent_jabatan' => 'integer',
        'active_version_id' => 'integer',
        'draft_version_id' => 'integer',
        'approved_at' => 'datetime',
        'jobdesk_updated_at' => 'datetime',
        'proposed_approved_at' => 'datetime',
        'hcm_confirmed_at' => 'datetime',
    ];

    public function getRouteKeyName()
    {
        return 'id_jabatan';
    }

    public function departemenMaster()
    {
        return $this->belongsTo(Departemen::class, 'id_departemen', 'id_departemen');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_jabatan', 'id_jabatan');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_jabatan', 'id_jabatan')
            ->orderBy('id_departemen')
            ->orderBy('nama_jabatan');
    }

    public function pegawai()
    {
        return $this->hasMany(Pegawai::class, 'id_jabatan', 'id_jabatan');
    }

    public function pemangkuSaatIni()
    {
        return $this->hasMany(Pegawai::class, 'id_jabatan', 'id_jabatan')
            ->orderBy('nama');
    }

    public function riwayatPemangku()
    {
        return $this->hasMany(PegawaiJabatanVersion::class, 'id_jabatan', 'id_jabatan')
            ->orderByDesc('is_current')
            ->orderByDesc('assigned_at');
    }

    public function approver()
    {
        return $this->belongsTo(UserDaftar::class, 'approved_by', 'id_daftar');
    }

    public function approvalLogs()
    {
        return $this->hasMany(JabatanApprovalLog::class, 'id_jabatan', 'id_jabatan')
            ->orderByDesc('created_at');
    }

    public function versions()
    {
        return $this->hasMany(JabatanVersion::class, 'id_jabatan', 'id_jabatan')
            ->orderByDesc('version_number');
    }

    public function activeVersion()
    {
        return $this->belongsTo(JabatanVersion::class, 'active_version_id', 'id_jabatan_version');
    }

    public function pendingVersion()
    {
        return $this->belongsTo(JabatanVersion::class, 'draft_version_id', 'id_jabatan_version');
    }

    public function draftVersion()
    {
        return $this->pendingVersion();
    }

    public function latestApprovedVersion()
    {
        return $this->hasOne(JabatanVersion::class, 'id_jabatan', 'id_jabatan')
            ->where('status', 'approved')
            ->latestOfMany('version_number');
    }

    public function getEffectiveVersionAttribute()
    {
        return $this->activeVersion ?: $this->latestApprovedVersion;
    }

    public function effectiveVersion()
    {
        return $this->activeVersion ?: $this->latestApprovedVersion;
    }

    public function getEffectiveNamaJabatanAttribute()
    {
        return $this->effective_version->nama_jabatan ?? $this->nama_jabatan;
    }

    public function getEffectiveParentJabatanAttribute()
    {
        return $this->effective_version->parent_jabatan ?? $this->parent_jabatan;
    }

    public function getEffectiveDepartemenAttribute()
    {
        return $this->effective_version->departemen
            ?? $this->departemenMaster->nama_departemen
            ?? $this->departemen;
    }

    public function getIsApprovalFinalAttribute(): bool
    {
        return ($this->approval_status === 'approved')
            && ($this->approval_flow_status === 'approved_final')
            && empty($this->draft_version_id);
    }

    public function getIsWaitingHcmFinalAttribute(): bool
    {
        return ($this->approval_flow_status === 'waiting_hcm_confirmation')
            && !empty($this->draft_version_id);
    }

    public function getCanApprovalLinkActionAttribute(): bool
    {
        return !$this->is_approval_final
            && !$this->is_waiting_hcm_final
            && !empty($this->draft_version_id)
            && !empty($this->approval_token);
    }

    public function getCanHcmFinalApproveFromShowAttribute(): bool
    {
        return $this->is_waiting_hcm_final && !empty($this->draft_version_id);
    }

    public function getCanApplyApprovedVersionAttribute(): bool
    {
        return $this->is_approval_final && !empty($this->active_version_id) && empty($this->draft_version_id);
    }

    public function getApprovalStatusLabelAttribute()
    {
        if ($this->is_approval_final) {
            return 'Approved Final';
        }

        if ($this->is_waiting_hcm_final) {
            return 'Menunggu Approval Final HCM';
        }

        return match ($this->approval_status) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'pending' => 'Pending Approval',
            default => 'Pending Approval',
        };
    }

    public function getApprovalStatusBadgeAttribute()
    {
        if ($this->is_approval_final) {
            return 'success';
        }

        if ($this->is_waiting_hcm_final) {
            return 'warning';
        }

        return match ($this->approval_status) {
            'approved' => 'success',
            'rejected' => 'danger',
            'pending' => 'warning',
            default => 'warning',
        };
    }

    public function getTanggungJawabListAttribute()
    {
        return $this->stringToArray($this->tanggung_jawab);
    }

    public function getTantanganJabatanListAttribute()
    {
        return $this->stringToArray($this->tantangan_jabatan);
    }

    public function getInternalPerusahaanListAttribute()
    {
        return $this->stringToArray($this->internal_perusahaan);
    }

    public function getExternalPerusahaanListAttribute()
    {
        return $this->stringToArray($this->external_perusahaan);
    }

    public function getPengetahuanKeterampilanListAttribute()
    {
        return $this->stringToArray($this->pengetahuan_keterampilan);
    }

    public function getKompetensiListAttribute()
    {
        return $this->stringToArray($this->kompetensi);
    }

    public function getSyaratKompetensiJabatanListAttribute()
    {
        return $this->stringToArray($this->syarat_kompetensi_jabatan);
    }

    private function stringToArray($value): array
    {
        if (!$value) {
            return [];
        }

        if (is_array($value)) {
            $items = $value;
        } else {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $items = $decoded;
            } else {
                $items = preg_split('/\r\n|\r|\n/', (string) $value);
            }
        }

        $result = [];

        foreach ($items as $item) {
            $item = trim((string) $item);

            if ($item === '') {
                continue;
            }

            $splitItems = preg_split('/\r\n|\r|\n/', $item);

            foreach ($splitItems as $line) {
                $line = trim((string) $line);

                if ($line !== '') {
                    $result[] = $line;
                }
            }
        }

        return array_values($result);
    }
}
