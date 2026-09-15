<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Masa QR Kodları</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: sans-serif; margin: 0; padding: 16px; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        .card {
            border: 1px dashed #999;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
            page-break-inside: avoid;
        }
        .card svg { width: 100%; height: auto; }
        .store-name { font-size: 12px; color: #666; margin: 0 0 4px; }
        .table-name { font-size: 18px; font-weight: bold; margin: 8px 0 0; }
        @media print {
            .card { border: 1px solid #ccc; }
        }
    </style>
</head>
<body>
    <div class="grid">
        @foreach ($tables as $item)
            <div class="card">
                <p class="store-name">{{ $item['table']->store->name }}</p>
                {!! $item['svg'] !!}
                <p class="table-name">{{ $item['table']->name }}</p>
            </div>
        @endforeach
    </div>
</body>
</html>
