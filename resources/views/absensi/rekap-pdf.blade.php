<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi Guru</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; }
        h1, h2, h3 { margin: 0; }
        .header { text-align: center; margin-bottom: 20px; }
        .meta { margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 8px; }
        th { background: #f2f2f2; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rekap Absensi Guru</h1>
        <p>Bulan: {{ \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Nama Guru</th>
                <th class="text-center">Hadir</th>
                <th class="text-center">Izin</th>
                <th class="text-center">Sakit</th>
                <th class="text-center">Alpha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekap as $index => $data)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $data['guru']->nama }}</td>
                <td class="text-center">{{ $data['hadir'] }}</td>
                <td class="text-center">{{ $data['izin'] }}</td>
                <td class="text-center">{{ $data['sakit'] }}</td>
                <td class="text-center">{{ $data['alpha'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
