<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        html, body { margin: 0; padding: 0; width: 40mm; height: 40mm; }
        .label { width: 40mm; height: 40mm; text-align: center; vertical-align: middle; }
        .label img { width: 34mm; height: 34mm; margin-top: 3mm; }
    </style>
</head>
<body>
    <div class="label"><img src="{{ $label['qr'] }}" alt="QR"></div>
</body>
</html>
