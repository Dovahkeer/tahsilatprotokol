<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Mail Order Raporu</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1e293b; font-size: 11px; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin: 0 0 5px 0; color: #0f172a; }
        .header p { margin: 0; color: #64748b; font-size: 10px; }
        
        .pos-section { margin-bottom: 30px; }
        .pos-title { font-size: 14px; font-weight: bold; color: #b45309; margin-bottom: 8px; border-bottom: 1px solid #fcd34d; padding-bottom: 3px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; text-align: left; }
        th { background: #f8fafc; font-weight: bold; color: #475569; text-transform: uppercase; font-size: 9px; }
        .amount { text-align: right; font-weight: bold; }
        .text-center { text-align: center; }
        .badge-vekalet { background-color: #fef2f2; color: #991b1b; padding: 2px 4px; border-radius: 3px; font-size: 9px; border: 1px solid #fecaca; }
        .badge-vekil { background-color: #f0fdf4; color: #166534; padding: 2px 4px; border-radius: 3px; font-size: 9px; border: 1px solid #bbf7d0; }
        
        .total-row td { background-color: #fffbeb; font-weight: bold; font-size: 12px; color: #92400e; }
        .grand-total { margin-top: 30px; border: 2px solid #d97706; background-color: #fffbeb; padding: 15px; text-align: right; border-radius: 8px;}
        .grand-total span { font-size: 16px; font-weight: bold; color: #b45309; }
    </style>
</head>
<body>
    <div class="header">
        <h1>POS Aktarım Raporu (Mail Order)</h1>
        <p>İşlem Tarihi: <strong>{{ $hedefTarih->format('d.m.Y') }}</strong> | Rapor Üretilme: {{ $generatedAt->format('d.m.Y H:i') }}</p>
    </div>

    @php
        $gruplar = $rows->groupBy(function($item) {
            return $item->pos_cihazi ?: 'Belirtilmeyen POS';
        });
        $genelToplam = 0;
    @endphp

    @forelse($gruplar as $posAdi => $islemler)
        @php 
            $posToplam = $islemler->sum('tutar'); 
            $genelToplam += $posToplam;
        @endphp
        <div class="pos-section">
            <div class="pos-title">{{ $posAdi }} İşlemleri</div>
            <table>
                <thead>
                    <tr>
                        <th width="30%">Müvekkil</th>
                        <th width="25%">Borçlu</th>
                        <th width="15%">Hesap Türü</th>
                        <th width="15%">Durum</th>
                        <th width="15%" class="amount">Tutar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($islemler as $row)
                        <tr>
                            <td>{{ $row->muvekkil?->ad ?: '-' }}</td>
                            <td>{{ $row->borclu_adi }}</td>
                            <td class="text-center">
                                @if($row->tahsilat_yontemi == 'vekalet_ucreti_mail_order')
                                    <span class="badge-vekalet">Vekalet Ücreti</span>
                                @else
                                    <span class="badge-vekil">Vekil Hesabı</span>
                                @endif
                            </td>
                            <td class="text-center">{{ ucfirst($row->onay_durumu) }}</td>
                            <td class="amount">{{ number_format((float) $row->tutar, 2, ',', '.') }} TL</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="4" style="text-align: right;">{{ $posAdi }} Ara Toplam:</td>
                        <td class="amount">{{ number_format((float) $posToplam, 2, ',', '.') }} TL</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @empty
        <div style="text-align: center; padding: 40px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px;">
            <p style="font-size: 14px; font-weight: bold; color: #64748b;">Bu tarihe ait hiçbir POS (Mail Order) tahsilatı bulunamadı.</p>
        </div>
    @endforelse

    @if($genelToplam > 0)
        <div class="grand-total">
            Genel Rapor Toplamı: <br>
            <span>{{ number_format((float) $genelToplam, 2, ',', '.') }} TL</span>
        </div>
    @endif

</body>
</html>