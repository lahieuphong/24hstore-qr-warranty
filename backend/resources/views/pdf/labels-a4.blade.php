<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 6mm; }
        body { margin: 0; padding: 0; font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        td { width: 20%; height: 38mm; padding: 0; text-align: center; vertical-align: middle; }
        img { width: 32mm; height: 32mm; }
        .page { page-break-after: always; }
        .page:last-child { page-break-after: auto; }
    </style>
</head>
<body>
@foreach ($labels->chunk(35) as $page)
    <div class="page">
        <table>
            @foreach ($page->chunk(5) as $row)
                <tr>
                    @foreach ($row as $label)
                        <td><img src="{{ $label['qr'] }}" alt="QR"></td>
                    @endforeach
                    @for ($i = $row->count(); $i < 5; $i++)
                        <td></td>
                    @endfor
                </tr>
            @endforeach
        </table>
    </div>
@endforeach
</body>
</html>
