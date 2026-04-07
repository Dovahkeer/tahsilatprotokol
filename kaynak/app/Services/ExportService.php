<?php

namespace App\Services;

use App\Enums\TahsilatOnayDurumu;
use App\Models\Tahsilat;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportService
{
    // Verileri çekebilmek için ProtokolService'i dahil ediyoruz
    public function __construct(
        private readonly ProtokolService $protokolService,
    ) {
    }

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

    // YENİ: VADE TAKİP ÇIKTI MOTORU
    public function vadeTakip(Request $request)
    {
        $tip = $request->query('tip'); // gecikmis, bugun, yaklasan
        $format = $request->query('format'); // pdf, excel

        $tumVeriler = $this->protokolService->vadeTakipListesi();
        $veriAnahtari = $tip === 'yaklasan' ? 'yaklasanlar' : $tip;
        $liste = $tumVeriler[$veriAnahtari] ?? [];

        $basliklar = [
            'gecikmis' => '🔴 Gecikmiş Ödemeler',
            'bugun' => '🟡 Bugün Beklenen Ödemeler',
            'yaklasanlar' => '🔵 Yaklaşan (7 Gün) Ödemeler'
        ];
        $baslik = $basliklar[$veriAnahtari] ?? 'Vade Takip';

        // 1. GERÇEK PDF ÇIKTISI (DomPDF)
        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.vade-takip-export', [
                'liste' => $liste,
                'baslik' => $baslik
            ])->setPaper('a4', 'landscape'); // Tablo sığsın diye yatay (landscape) yapıldı

            return $pdf->download("Vade_Takip_{$tip}.pdf");
        }

        // 2. GERÇEK EXCEL ÇIKTISI (PhpSpreadsheet)
        if ($format === 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Excel Başlıkları
            $sheet->fromArray(['Müvekkil', 'Protokol No', 'Borçlu', 'Vade Tarihi', 'Ödeme Tipi', 'Banka / Seri No', 'Kalan Tutar'], null, 'A1');

            // Excel Verileri
            $rowIndex = 2;
            foreach ($liste as $item) {
                $bankaSeri = '-';
                if (($item['odeme_tipi'] ?? '') !== 'taksit' && !empty($item['evrak_detayi'])) {
                    $bankaSeri = ($item['evrak_detayi']['banka_adi'] ?? '-') . ' / ' . ($item['evrak_detayi']['seri_no'] ?? '-');
                }

                $sheet->fromArray([
                    $item['muvekkil_adi'] ?? '-',
                    $item['protokol_no'] ?? '-',
                    $item['borclu_adi'] ?? '-',
                    $item['vade_tarihi'] ?? '-',
                    mb_strtoupper($item['odeme_tipi'] ?? 'Taksit', 'UTF-8'),
                    $bankaSeri,
                    (float) ($item['kalan_tutar'] ?? 0) // Excel'in formül yapabilmesi için gerçek sayı formatı
                ], null, 'A'.$rowIndex);

                $rowIndex++;
            }

            $writer = new Xlsx($spreadsheet);
            ob_start();
            $writer->save('php://output');
            $binary = ob_get_clean() ?: '';

            return response($binary, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="Vade_Takip_' . $tip . '.xlsx"',
            ]);
        }

        abort(400, 'Geçersiz format talebi.');
    }
}