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

        'struktur_file',

        /*
        |--------------------------------------------------------------------------
        | Approval Job Description
        |--------------------------------------------------------------------------
        */
        'approval_status',
        'approval_token',
        'approved_by',
        'approved_by_name',
        'approved_by_role',
        'approved_by_jabatan',
        'approved_by_departemen',
        'approved_at',
        'approval_catatan',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function getRouteKeyName()
    {
        return 'id_jabatan';
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_jabatan', 'id_jabatan');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_jabatan', 'id_jabatan');
    }

    public function pegawai()
    {
        return $this->hasMany(Pegawai::class, 'id_jabatan', 'id_jabatan');
    }

    public function approver()
    {
        return $this->belongsTo(UserDaftar::class, 'approved_by', 'id');
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

    public function getApprovalStatusLabelAttribute()
    {
        return match ($this->approval_status) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'pending' => 'Pending Approval',
            default => 'Pending Approval',
        };
    }

    public function getApprovalStatusBadgeAttribute()
    {
        return match ($this->approval_status) {
            'approved' => 'success',
            'rejected' => 'danger',
            'pending' => 'warning',
            default => 'warning',
        };
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