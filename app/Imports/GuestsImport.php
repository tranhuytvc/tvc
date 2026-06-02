<?php

namespace App\Imports;

use App\Models\Guest;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GuestsImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;
    public int $skipped  = 0;
    public array $errors  = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $name = trim($row['ten'] ?? '');
            if ($name === '') {
                $this->skipped++;
                continue;
            }

            $scanMode = $this->parseScanMode($row['che_do_quet'] ?? '');

            try {
                Guest::create([
                    'name'              => $name,
                    'email'             => trim($row['email'] ?? '') ?: null,
                    'media_type'        => $this->parseMediaType($row['file_media'] ?? ''),
                    'import_media_name' => trim($row['file_media'] ?? '') ?: null,
                    'scan_mode'         => $scanMode,
                    'max_scan_count'    => $scanMode === 'max_scans' ? max(1, (int)($row['so_lan_toi_da'] ?? 1)) : 2,
                ]);
                $this->imported++;
            } catch (\Throwable $e) {
                $this->skipped++;
                $this->errors[] = "Dòng " . ($index + 2) . ": " . $e->getMessage();
            }
        }
    }

    private function parseScanMode(string $val): string
    {
        return match(strtolower(trim($val))) {
            'one_time', '1 lần', '1 lan', 'mot lan', 'một lần' => 'one_time',
            'checkin_checkout', 'vao ra', 'vào ra', 'in out'    => 'checkin_checkout',
            'max_scans', 'toi da', 'tối đa', 'n lan', 'n lần'  => 'max_scans',
            default                                              => 'unlimited',
        };
    }

    private function parseMediaType(string $filename): string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, ['mp4', 'webm', 'mov', 'avi']) ? 'video' : 'image';
    }
}
