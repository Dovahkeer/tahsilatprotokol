<?php

namespace App\Services;

use App\Enums\TahsilatOnayDurumu;
use App\Models\Tahsilat;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportService
{
    public function excel(): Response
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray([
            'Tarih',
            'Müvekkil',
            'Portföy',
            'Protokol',
            'Borçlu',
            'TCKN/VKN',
            'Tutar',
            'Yöntem',
            'Durum',
        ], null, 'A1');

        $rowIndex = 2;
        Tahsilat::query()
            ->with(['muvekkil', 'protokol.portfoy'])
            ->orderByDesc('tahsilat_tarihi')
            ->orderByDesc('id')
            ->get()
            ->each(function (Tahsilat $tahsilat) use ($sheet, &$rowIndex) {
                $sheet->fromArray([
                    optional($tahsilat->tahsilat_tarihi)->toDateString(),
                    $tahsilat->muvekkil?->ad,
                    $tahsilat->protokol?->portfoy?->ad,
                    $tahsilat->protokol?->protokol_no ?? ($tahsilat->protokolsuz ? 'Protokolsuz' : null),
                    $tahsilat->borclu_adi,
                    $tahsilat->borclu_tckn_vkn,
                    (float) $tahsilat->tutar,
                    $tahsilat->tahsilat_yontemi,
                    $tahsilat->onay_durumu,
                ], null, 'A'.$rowIndex);

                $rowIndex++;
            });

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $binary = ob_get_clean() ?: '';

        return response($binary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="tum-tahsilatlar.xlsx"',
        ]);
    }

    public function mailOrderPdf(): Response
    {
        $rows = Tahsilat::query()
            ->with(['muvekkil'])
            ->where('tahsilat_yontemi', 'like', '%mail_order%')
            ->whereDate('tahsilat_tarihi', now()->toDateString())
            ->where('onay_durumu', '!=', TahsilatOnayDurumu::Iptal->value)
            ->orderBy('tahsilat_tarihi')
            ->get();

        $pdf = Pdf::loadView('exports.mail-order-pdf', [
            'rows' => $rows,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('mail-order-tahsilatlar.pdf');
    }
}
