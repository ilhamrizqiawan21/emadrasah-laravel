<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Raport {{ $siswa->nama_lengkap }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; }
        .header { margin-bottom: 20px; }
        .header h1 { margin-bottom: 4px; }
        .header p { margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #333; padding: 8px; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    @include('partials.pdf-kop')
    <div class="header">
        <h1>Raport Siswa</h1>
        <p>Nama: {{ $siswa->nama_lengkap }}</p>
        <p>NIS: {{ $siswa->nis }} | Kelas: {{ $siswa->kelas->nama_kelas ?? '-' }}</p>
        <p>Tahun Pelajaran: {{ $tahunPelajaran->kode ?? '-' }} | Semester: {{ $semester }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Mata Pelajaran</th>
                <th class="text-center">Nilai Akhir</th>
                <th>Capaian Kompetensi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mapels as $index => $mapel)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $mapel->nama_mapel ?? $mapel->nama }}</td>
                <td class="text-center">{{ optional($nilai[$mapel->id] ?? null)->nilai_akhir ?? '-' }}</td>
                <td>{{ optional($nilai[$mapel->id] ?? null)->deskripsi ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @include('partials.pdf-ttd')
</body>
</html>
