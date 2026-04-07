<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vade Takip Listesi</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: bold; }
        h2 { text-align: center; color: #333; text-transform: uppercase; }
    </style>
</head>
<body>
    <h2>{{ $baslik }} LİSTESİ</h2>
    <p>Oluşturulma Tarihi: {{ now()->format('d.m.Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Müvekkil</th>
                <th>Protokol No</th>
                <th>Borçlu</th>
                <th>Vade Tarihi</th>
                <th>Ödeme Tipi</th>
                <th>Banka / Seri No</th>
                <th>Tutar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($liste as $item)
                <tr>
                    <td>{{ $item['muvekkil_adi'] ?? '-' }}</td>
                    <td>{{ $item['protokol_no'] ?? '-' }}</td>
                    <td>{{ $item['borclu_adi'] ?? '-' }}</td>
                    <td>{{ $item['vade_tarihi'] ?? '-' }}</td>
                    <td style="text-transform: uppercase;">{{ $item['odeme_tipi'] ?? 'Taksit' }}</td>
                    <td>
                        @if(($item['odeme_tipi'] ?? '') !== 'taksit' && isset($item['evrak_detayi']))
                            {{ $item['evrak_detayi']['banka_adi'] ?? '-' }} / {{ $item['evrak_detayi']['seri_no'] ?? '-' }}
                        @else
                            -
                        @endif
                    </td>
                    <td style="text-align: right;">{{ number_format($item['kalan_tutar'], 2, ',', '.') }} TL</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">Bu listede kayıt bulunmamaktadır.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>