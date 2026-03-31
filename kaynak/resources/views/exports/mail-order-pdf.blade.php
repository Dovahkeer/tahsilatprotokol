<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Mail Order Tahsilatlar</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        p { margin: 0 0 12px; color: #475569; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; text-align: left; }
        th { background: #f8fafc; }
        .amount { text-align: right; }
    </style>
</head>
<body>
    <h1>Mail Order Tahsilat Listesi</h1>
    <p>Üretilme zamanı: {{ $generatedAt->format('d.m.Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Tarih</th>
                <th>Müvekkil</th>
                <th>Borçlu</th>
                <th>Yöntem</th>
                <th>Durum</th>
                <th class="amount">Tutar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ optional($row->tahsilat_tarihi)->format('d.m.Y') }}</td>
                    <td>{{ $row->muvekkil?->ad }}</td>
                    <td>{{ $row->borclu_adi }}</td>
                    <td>{{ str_replace('_', ' ', $row->tahsilat_yontemi) }}</td>
                    <td>{{ $row->onay_durumu }}</td>
                    <td class="amount">{{ number_format((float) $row->tutar, 2, ',', '.') }} TL</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Bugün için mail order tahsilat bulunamadı.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
