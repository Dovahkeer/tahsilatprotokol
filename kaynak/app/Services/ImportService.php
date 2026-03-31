<?php

namespace App\Services;

use App\Models\IzlenceGecmisTahsilati;
use App\Models\User;
use App\Support\Money;
use App\Support\NameNormalizer;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportService
{
    public function __construct(
        private readonly LookupService $lookupService,
        private readonly ProtokolService $protokolService,
    ) {
    }

    public function protokolTemplate(): Response
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $headers = ['MUVEKKIL', 'TARIH', 'BORCLU ADI', 'TCKN/VKN', 'PESINAT', 'TOPLAM PROTOKOL BEDELI', 'PORTFOY', 'MUHATAP ADI', 'TELEFON'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray(['GSD Varlık', now()->toDateString(), 'Ali Veli', '12345678901', '50000', '50000', 'FİBA 01 - GSD', 'Mehmet Yetkili', '05550000000'], null, 'A2');

        return $this->spreadsheetResponse($spreadsheet, 'protokol-import-sablonu.xlsx');
    }

    public function izlenceTemplate(): Response
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['Tahsilat Tarihi', 'Tutar', 'Muvekkil/Unvan'], null, 'A1');
        $sheet->fromArray([now()->toDateString(), '125000', 'GSD Varlık'], null, 'A2');

        return $this->spreadsheetResponse($spreadsheet, 'izlence-gecmis-import-sablonu.xlsx');
    }

    public function importProtokoller(UploadedFile $file, User $user): array
    {
        $rows = $this->readRows($file);
        $result = ['inserted' => 0, 'skipped' => 0, 'errors' => []];

        foreach ($rows as $index => $row) {
            if ($this->rowEmpty($row)) {
                continue;
            }

            try {
                $mapped = $this->mapProtokolRow($row);

                if (Money::cmp($mapped['pesinat'], $mapped['toplam_protokol_tutari']) !== 0) {
                    throw new \RuntimeException('Taksit kolonu olmadığından peşinat ile toplam protokol bedeli eşit olmalıdır.');
                }

                $muvekkil = $this->lookupService->findOrCreateMuvekkil($mapped['muvekkil_ad']);
                $portfoy = ! empty($mapped['portfoy_ad'])
                    ? $this->lookupService->findOrCreatePortfoy($muvekkil, $mapped['portfoy_ad'])
                    : null;

                $this->protokolService->create([
                    'muvekkil_id' => $muvekkil->id,
                    'portfoy_id' => $portfoy?->id,
                    'protokol_tarihi' => $mapped['protokol_tarihi'],
                    'borclu_adi' => $mapped['borclu_adi'],
                    'borclu_tckn_vkn' => $mapped['borclu_tckn_vkn'],
                    'muhatap_adi' => $mapped['muhatap_adi'],
                    'muhatap_telefon' => $mapped['telefon'],
                    'pesinat' => $mapped['pesinat'],
                    'toplam_protokol_tutari' => $mapped['toplam_protokol_tutari'],
                    'hacizciler' => [],
                    'taksitler' => [],
                ], $user);

                $result['inserted']++;
            } catch (\Throwable $exception) {
                $result['skipped']++;
                $result['errors'][] = 'Satır '.($index + 2).': '.$exception->getMessage();
            }
        }

        return $result;
    }

    public function importIzlenceGecmis(UploadedFile $file): array
    {
        $rows = $this->readRows($file);
        $result = ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        foreach ($rows as $index => $row) {
            if ($this->rowEmpty($row)) {
                continue;
            }

            try {
                $mapped = $this->mapIzlenceRow($row);
                $muvekkil = $this->lookupService->findOrCreateMuvekkil($mapped['ham_muvekkil_adi']);

                $fingerprint = md5(
                    NameNormalizer::normalize($mapped['ham_muvekkil_adi']).'|'.$mapped['tahsilat_tarihi'].'|'.Money::normalize($mapped['tutar'])
                );

                $record = IzlenceGecmisTahsilati::query()->firstOrNew([
                    'source_fingerprint' => $fingerprint,
                ]);

                $isNew = ! $record->exists;
                $record->fill([
                    'muvekkil_id' => $muvekkil->id,
                    'ham_muvekkil_adi' => $mapped['ham_muvekkil_adi'],
                    'tahsilat_tarihi' => $mapped['tahsilat_tarihi'],
                    'tutar' => Money::normalize($mapped['tutar']),
                    'source_payload' => $row,
                ]);
                $record->save();

                $isNew ? $result['inserted']++ : $result['updated']++;
            } catch (\Throwable $exception) {
                $result['skipped']++;
                $result['errors'][] = 'Satır '.($index + 2).': '.$exception->getMessage();
            }
        }

        return $result;
    }

    private function mapProtokolRow(array $row): array
    {
        $headers = array_change_key_case($row, CASE_UPPER);

        foreach (['MUVEKKIL', 'TARIH', 'BORCLU_ADI', 'TCKN_VKN', 'PESINAT', 'TOPLAM_PROTOKOL_BEDELI'] as $required) {
            if (! array_key_exists($required, $headers) || trim((string) $headers[$required]) === '') {
                throw new \RuntimeException($required.' kolonu zorunludur.');
            }
        }

        return [
            'muvekkil_ad' => trim((string) $headers['MUVEKKIL']),
            'protokol_tarihi' => $this->parseDate($headers['TARIH']),
            'borclu_adi' => trim((string) $headers['BORCLU_ADI']),
            'borclu_tckn_vkn' => trim((string) $headers['TCKN_VKN']) ?: null,
            'pesinat' => Money::normalize($headers['PESINAT']),
            'toplam_protokol_tutari' => Money::normalize($headers['TOPLAM_PROTOKOL_BEDELI']),
            'portfoy_ad' => Arr::get($headers, 'PORTFOY'),
            'muhatap_adi' => Arr::get($headers, 'MUHATAP_ADI'),
            'telefon' => Arr::get($headers, 'TELEFON'),
        ];
    }

    private function mapIzlenceRow(array $row): array
    {
        $headers = array_change_key_case($row, CASE_UPPER);

        foreach (['TAHSILAT_TARIHI', 'TUTAR', 'MUVEKKIL_UNVAN'] as $required) {
            if (! array_key_exists($required, $headers) || trim((string) $headers[$required]) === '') {
                throw new \RuntimeException($required.' kolonu zorunludur.');
            }
        }

        return [
            'tahsilat_tarihi' => $this->parseDate($headers['TAHSILAT_TARIHI']),
            'tutar' => Money::normalize($headers['TUTAR']),
            'ham_muvekkil_adi' => trim((string) $headers['MUVEKKIL_UNVAN']),
        ];
    }

    private function readRows(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();
        $highestColumnIndex = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

        $headers = [];
        for ($column = 1; $column <= $highestColumnIndex; $column++) {
            $header = (string) $sheet->getCell([$column, 1])->getValue();
            $headers[$column] = $this->normalizeHeader($header);
        }

        $rows = [];
        for ($rowIndex = 2; $rowIndex <= $highestRow; $rowIndex++) {
            $row = [];
            for ($column = 1; $column <= $highestColumnIndex; $column++) {
                $value = $sheet->getCell([$column, $rowIndex])->getValue();
                $row[$headers[$column]] = is_string($value) ? trim($value) : $value;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    private function normalizeHeader(string $header): string
    {
        $header = strtoupper(NameNormalizer::normalize($header));
        $header = str_replace(' ', '_', $header);

        return match ($header) {
            'TCKN_VKN' => 'TCKN_VKN',
            'MUVEKKIL_UNVAN' => 'MUVEKKIL_UNVAN',
            default => $header,
        };
    }

    private function parseDate(mixed $value): string
    {
        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->toDateString();
        }

        return Carbon::parse((string) $value)->toDateString();
    }

    private function rowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function spreadsheetResponse(Spreadsheet $spreadsheet, string $filename): Response
    {
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $binary = ob_get_clean() ?: '';

        return response($binary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
