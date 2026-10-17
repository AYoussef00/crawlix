<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Str;

class ExcelExporter
{
    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array{path: string, filename: string, relative: string}
     */
    public function export(array $rows, string $query, string $sector = '', string $governorate = ''): array
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Results');

        $sheet->fromArray(
            ['الاسم', 'الإيميل', 'رقم الهاتف', 'المحافظة', 'القطاع', 'الحالة'],
            null,
            'A1'
        );
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        $rowNum = 2;
        foreach ($rows as $row) {
            $sheet->setCellValue('A'.$rowNum, $row['name'] ?? '');
            $sheet->setCellValue('B'.$rowNum, $row['email'] ?? '');
            $sheet->setCellValueExplicit(
                'C'.$rowNum,
                (string) ($row['phone'] ?? ''),
                DataType::TYPE_STRING
            );
            $sheet->setCellValue('D'.$rowNum, $row['governorate'] ?? $governorate);
            $sheet->setCellValue('E'.$rowNum, $row['sector'] ?? $sector);
            $status = ($row['status'] ?? '') === 'new' ? 'جديدة' : (($row['status'] ?? '') === 'existing' ? 'موجودة مسبقًا' : '');
            $sheet->setCellValue('F'.$rowNum, $status);
            $rowNum++;
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $slug = Str::slug(Str::limit($query, 40, ''), '_') ?: 'search';
        $filename = $slug.'_'.now()->format('Ymd_His').'.xlsx';
        $relative = 'exports/'.$filename;
        $absolute = storage_path('app/public/'.$relative);

        if (! is_dir(dirname($absolute))) {
            mkdir(dirname($absolute), 0755, true);
        }

        (new Xlsx($spreadsheet))->save($absolute);
        $spreadsheet->disconnectWorksheets();

        return [
            'path' => $absolute,
            'filename' => $filename,
            'relative' => $relative,
        ];
    }
}
