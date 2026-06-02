<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class PegawaiPreviewImport implements ToCollection
{
    public array $rows = [];

    public function collection(Collection $collection)
    {
        $header = [];

        foreach ($collection as $index => $row) {
            if ($index === 0) {
                $header = $row->map(fn ($item) => trim((string) $item))->toArray();
                continue;
            }

            $values = $row->toArray();

            if (count(array_filter($values, fn ($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }

            $assoc = [];
            foreach ($header as $i => $key) {
                $assoc[$key] = $values[$i] ?? null;
            }

            $this->rows[] = $assoc;
        }
    }
}