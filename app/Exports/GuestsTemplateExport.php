<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GuestsTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    public function headings(): array
    {
        return ['ten', 'email', 'file_media', 'che_do_quet', 'so_lan_toi_da'];
    }

    public function array(): array
    {
        return [
            ['Nguyễn Văn An',  'an@example.com', 'nguyen-van-an.jpg',  'unlimited',        ''],
            ['Trần Thị Bình',  'binh@example.com','tran-thi-binh.mp4', 'one_time',         ''],
            ['Lê Minh Cường',  '',               'le-minh-cuong.jpg',  'checkin_checkout',  ''],
            ['Phạm Thị Dung',  '',               'pham-thi-dung.jpg',  'max_scans',         '3'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '7C3AED'],
            ], 'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25, 'B' => 28, 'C' => 30, 'D' => 22, 'E' => 16,
        ];
    }
}
