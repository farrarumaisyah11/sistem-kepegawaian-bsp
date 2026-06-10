<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JabatanVersion extends Model
{
    protected $table = 'tb_jabatan_versions';
    protected $primaryKey = 'id_jabatan_version';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_jabatan',
        'version_number',

        'nama_jabatan',
        'departemen',
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

        'min_pendidikan',
        'min_pengalaman',
        'min_nilai',

        'struktur_file',
        'status',

        'created_by',
        'created_by_name',
        'created_at',

        'approved_by',
        'approved_by_name',
        'approved_by_role',
        'approved_by_jabatan',
        'approved_by_departemen',
        'approved_at',
        'approval_catatan',

        'effective_from',
        'effective_until',

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
        'created_at' => 'datetime',
        'approved_at' => 'datetime',
        'effective_from' => 'datetime',
        'effective_until' => 'datetime',
        'proposed_approved_at' => 'datetime',
        'hcm_confirmed_at' => 'datetime',
    ];

    public function getRouteKeyName()
    {
        return 'id_jabatan_version';
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'id_jabatan', 'id_jabatan');
    }

    public function pegawaiAssignments()
    {
        return $this->hasMany(PegawaiJabatanVersion::class, 'id_jabatan_version', 'id_jabatan_version');
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'archived' => 'Archived',
            'draft' => 'Draft',
            'pending' => 'Pending Approval',
            default => 'Pending Approval',
        };
    }

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            'archived' => 'secondary',
            'draft' => 'info',
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

    private function stringToArray($value)
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