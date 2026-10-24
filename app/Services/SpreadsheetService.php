<?php

namespace App\Services;

use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SpreadsheetService
{
    /**
     * Hasilkan berkas template spreadsheet (.xlsx) lengkap dengan header berformat dan baris contoh.
     *
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, mixed>>  $sampleRows
     * @param  array<string, mixed>  $options
     */
    public function template(string $filename, array $headers, array $sampleRows = [], array $options = []): StreamedResponse
    {
        $options['is_template'] = true;

        return $this->export($filename, $headers, $sampleRows, $options);
    }

    /**
     * Ekspor kumpulan data menjadi berkas spreadsheet (.xlsx atau .csv) secara streaming.
     *
     * @param  array<int, string>  $headers
     * @param  iterable<int, array<int, mixed>>  $rows
     * @param  array<string, mixed>  $options
     */
    public function export(string $filename, array $headers, iterable $rows, array $options = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $sheetTitle = (string) ($options['sheet_title'] ?? 'Data');
        $sheet->setTitle(substr(preg_replace('/[:\\\\\\/?*\\[\\]]/', '', $sheetTitle) ?: 'Data', 0, 31));

        $format = strtolower((string) ($options['format'] ?? 'xlsx'));
        $textColumns = (array) ($options['text_columns'] ?? []);
        $isTemplate = (bool) ($options['is_template'] ?? false);

        // 1. Tulis Baris Header
        $colCount = count($headers);
        for ($col = 1; $col <= $colCount; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $headerText = (string) ($headers[$col - 1] ?? '');
            $sheet->setCellValueExplicit("{$colLetter}1", $headerText, DataType::TYPE_STRING);
        }

        // Styling Baris Header (Royal Blue dengan Teks Putih Tebal)
        if ($colCount > 0) {
            $lastColLetter = Coordinate::stringFromColumnIndex($colCount);
            $headerRange = "A1:{$lastColLetter}1";

            $sheet->getStyle($headerRange)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size' => 10.5,
                    'name' => 'Segoe UI',
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF2563EB'], // Royal Blue
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => [
                    'bottom' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['argb' => 'FF1D4ED8'],
                    ],
                ],
            ]);

            $sheet->getRowDimension(1)->setRowHeight(28);
            $sheet->freezePane('A2');
        }

        // 2. Tulis Baris-Baris Data
        $currentRow = 2;
        foreach ($rows as $row) {
            $colIndex = 1;
            foreach ($row as $cellKey => $cellValue) {
                if ($colIndex > $colCount) {
                    break;
                }

                $colLetter = Coordinate::stringFromColumnIndex($colIndex);
                $isTextCol = in_array($colIndex, $textColumns, true)
                    || in_array($cellKey, $textColumns, true)
                    || in_array($headers[$colIndex - 1] ?? '', $textColumns, true);

                $formattedValue = $cellValue !== null ? (string) $cellValue : '';

                if ($isTextCol) {
                    $sheet->setCellValueExplicit("{$colLetter}{$currentRow}", $formattedValue, DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue("{$colLetter}{$currentRow}", $cellValue);
                }

                $colIndex++;
            }

            // Styling baris data
            $rowRange = "A{$currentRow}:".Coordinate::stringFromColumnIndex($colCount).$currentRow;
            $rowStyle = [
                'font' => [
                    'size' => 10,
                    'name' => 'Segoe UI',
                    'italic' => $isTemplate,
                    'color' => ['argb' => $isTemplate ? 'FF64748B' : 'FF0F172A'],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'bottom' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFE2E8F0'],
                    ],
                ],
            ];

            if ($currentRow % 2 === 1 && ! $isTemplate) {
                $rowStyle['fill'] = [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF8FAFC'],
                ];
            }

            $sheet->getStyle($rowRange)->applyFromArray($rowStyle);
            $sheet->getRowDimension($currentRow)->setRowHeight(22);
            $currentRow++;
        }

        // 3. Tambahkan Data Validation (Dropdown) jika didefinisikan
        $validations = (array) ($options['validations'] ?? []);
        if (! empty($validations) && $format === 'xlsx') {
            foreach ($validations as $headerTarget => $validationConfig) {
                $targetCol = null;
                for ($col = 1; $col <= $colCount; $col++) {
                    if (($headers[$col - 1] ?? '') === $headerTarget) {
                        $targetCol = $col;
                        break;
                    }
                }

                if (! $targetCol) {
                    continue;
                }

                $colLetter = Coordinate::stringFromColumnIndex($targetCol);
                $optionsList = is_array($validationConfig) && isset($validationConfig['options'])
                    ? (array) $validationConfig['options']
                    : (array) $validationConfig;

                if (empty($optionsList)) {
                    continue;
                }

                $validation = new DataValidation;
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_STOP);
                $validation->setAllowBlank(true);
                $validation->setShowDropDown(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowInputMessage(true);

                $title = is_array($validationConfig) ? ($validationConfig['title'] ?? 'Pilihan Valid') : 'Pilihan Valid';
                $prompt = is_array($validationConfig) ? ($validationConfig['prompt'] ?? 'Pilih salah satu opsi dari daftar') : 'Pilih salah satu opsi dari daftar';

                $validation->setPromptTitle($title);
                $validation->setPrompt($prompt);
                $validation->setErrorTitle('Pilihan Tidak Valid');
                $validation->setError('Nilai harus dipilih dari daftar yang tersedia.');
                $validation->setFormula1('"'.implode(',', $optionsList).'"');

                $maxValidationRow = (int) ($options['max_validation_row'] ?? 500);
                $sheet->setDataValidation("{$colLetter}2:{$colLetter}{$maxValidationRow}", $validation);
            }
        }

        // 4. Tambahkan Sheet Referensi jika didefinisikan
        $referenceSheetData = (array) ($options['reference_sheet'] ?? []);
        if (! empty($referenceSheetData) && $format === 'xlsx') {
            $refSheet = $spreadsheet->createSheet();
            $refSheetTitle = (string) ($referenceSheetData['title'] ?? 'Panduan & Referensi');
            $refSheet->setTitle(substr(preg_replace('/[:\\\\\\/?*\\[\\]]/', '', $refSheetTitle) ?: 'Panduan', 0, 31));

            $refColumns = (array) ($referenceSheetData['columns'] ?? []);
            $refColIndex = 1;

            foreach ($refColumns as $refHeader => $refValues) {
                $refColLetter = Coordinate::stringFromColumnIndex($refColIndex);

                // Tulis header kolom referensi
                $refSheet->setCellValueExplicit("{$refColLetter}1", (string) $refHeader, DataType::TYPE_STRING);
                $refSheet->getStyle("{$refColLetter}1")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF'],
                        'size' => 10.5,
                        'name' => 'Segoe UI',
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF059669'], // Emerald Green
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Tulis baris nilai referensi
                $refRowIndex = 2;
                foreach ((array) $refValues as $val) {
                    $refSheet->setCellValueExplicit("{$refColLetter}{$refRowIndex}", (string) $val, DataType::TYPE_STRING);
                    $refSheet->getStyle("{$refColLetter}{$refRowIndex}")->applyFromArray([
                        'font' => ['size' => 10, 'name' => 'Segoe UI'],
                        'borders' => [
                            'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']],
                        ],
                    ]);
                    $refRowIndex++;
                }

                $refSheet->getColumnDimension($refColLetter)->setAutoSize(true);
                $refColIndex++;
            }

            $refSheet->getRowDimension(1)->setRowHeight(26);
            $refSheet->freezePane('A2');

            // Kembalikan lembar kerja aktif ke Sheet 1
            $spreadsheet->setActiveSheetIndex(0);
        }

        // 5. Auto-fit Lebar Kolom Lembar Kerja Utama
        for ($col = 1; $col <= $colCount; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // 6. Siapkan Streamed Response
        $cleanFilename = str_ends_with(strtolower($filename), ".{$format}") ? $filename : "{$filename}.{$format}";

        $contentType = $format === 'csv'
            ? 'text/csv; charset=UTF-8'
            : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        return new StreamedResponse(
            function () use ($spreadsheet, $format): void {
                if ($format === 'csv') {
                    $writer = new Csv($spreadsheet);
                    $writer->setUseBOM(true);
                    $writer->setDelimiter(',');
                } else {
                    $writer = new Xlsx($spreadsheet);
                }

                $writer->save('php://output');
                $spreadsheet->disconnectWorksheets();
            },
            200,
            [
                'Content-Type' => $contentType,
                'Content-Disposition' => 'attachment; filename="'.rawurlencode($cleanFilename).'"',
                'Cache-Control' => 'max-age=0, must-revalidate',
                'Pragma' => 'public',
            ]
        );
    }

    /**
     * Baca baris-baris data dari berkas spreadsheet (.xlsx, .xls, .csv).
     *
     * @param  (Closure(string): string)|null  $keyNormalizer
     * @return array<int, array<string|int, mixed>>
     */
    public function readRows(
        UploadedFile|string $file,
        bool $headerAsKeys = true,
        int $startRow = 2,
        ?Closure $keyNormalizer = null
    ): array {
        $filePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if (! is_string($filePath) || ! file_exists($filePath)) {
            return [];
        }

        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getSheet(0);

        $highestRow = $sheet->getHighestDataRow();
        $highestColumn = $sheet->getHighestDataColumn();
        $highestColIndex = Coordinate::columnIndexFromString($highestColumn);

        // Ambil Baris Header jika diminta
        $headerKeys = [];
        if ($headerAsKeys) {
            for ($col = 1; $col <= $highestColIndex; $col++) {
                $colLetter = Coordinate::stringFromColumnIndex($col);
                $rawHeader = (string) $sheet->getCell("{$colLetter}1")->getValue();
                $cleanHeader = trim($rawHeader);

                if ($keyNormalizer instanceof Closure) {
                    $headerKeys[$col] = $keyNormalizer($cleanHeader);
                } else {
                    $headerKeys[$col] = $this->normalizeHeaderKey($cleanHeader, $col);
                }
            }
        }

        $results = [];
        for ($row = $startRow; $row <= $highestRow; $row++) {
            $rowData = [];
            $hasData = false;

            for ($col = 1; $col <= $highestColIndex; $col++) {
                $colLetter = Coordinate::stringFromColumnIndex($col);
                $rawVal = $sheet->getCell("{$colLetter}{$row}")->getValue();

                $cellVal = $this->sanitizeCellValue($rawVal);

                if ($cellVal !== null && $cellVal !== '') {
                    $hasData = true;
                }

                $key = $headerAsKeys ? ($headerKeys[$col] ?? $col) : $col;
                $rowData[$key] = $cellVal;
            }

            // Lewati baris yang kosong total
            if ($hasData) {
                $results[] = $rowData;
            }
        }

        $spreadsheet->disconnectWorksheets();

        return $results;
    }

    /**
     * Normalisasi string header kolom menjadi format snake_case bersih untuk key array.
     */
    public function normalizeHeaderKey(string $header, int $fallbackIndex): string
    {
        $cleaned = trim($header);

        // Hapus tanda bintang wajib pengisian (misal 'Nama Lengkap*' -> 'Nama Lengkap')
        $cleaned = rtrim($cleaned, '* ');

        // Hapus keterangan kurung (misal 'Jenis Kelamin (L/P)' -> 'Jenis Kelamin')
        $cleaned = preg_replace('/\\s*\\([^)]*\\)/', '', $cleaned);

        $slug = Str::slug($cleaned ?: "col_{$fallbackIndex}", '_');

        return $slug ?: "col_{$fallbackIndex}";
    }

    /**
     * Sanitasi nilai sel untuk mencegah Formula Injection (CSV injection) dan pembersihan spasi.
     */
    protected function sanitizeCellValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            if ($trimmed === '') {
                return null;
            }

            // Proteksi formula injection: jika diawali =, +, -, @, netralkan
            if (in_array(substr($trimmed, 0, 1), ['=', '+', '-', '@'], true)) {
                $trimmed = "'".$trimmed;
            }

            return $trimmed;
        }

        return $value;
    }
}
