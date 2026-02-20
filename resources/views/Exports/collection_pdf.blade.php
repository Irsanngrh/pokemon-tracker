<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>My Pokémon Collection</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        h1 { text-align: center; color: #1a202c; margin-bottom: 5px; }
        p.subtitle { text-align: center; color: #718096; margin-top: 0; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; }
        th { background-color: #f7fafc; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { font-weight: bold; background-color: #edf2f7; }
    </style>
</head>
<body>
    <h1>My Pokémon Collection</h1>
    <p class="subtitle">Generated on {{ now()->format('d M Y, H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Card Name</th>
                <th>Number</th>
                <th>Expansion</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Unit Price (Rp)</th>
                <th class="text-right">Subtotal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->card->name }}</td>
                <td>{{ $item->card->card_number }}</td>
                <td>{{ $item->card->expansion->name }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->card->price->price ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->quantity * ($item->card->price->price ?? 0), 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="5" class="text-right">Total Estimated Value</td>
                <td class="text-right">Rp {{ number_format($totalValue, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>