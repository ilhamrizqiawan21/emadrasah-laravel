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

    <table>
        <thead>
            <tr><th width="6%">No</th><th>Ekstrakurikuler</th><th width="14%">Nilai</th><th>Keterangan</th></tr>
        </thead>
        <tbody>
            @forelse($ekskul as $i => $e)
            <tr><td>{{ $i + 1 }}</td><td>{{ $e->nama_ekskul }}</td><td>{{ $e->nilai ?: '-' }}</td><td>{{ $e->keterangan ?: '-' }}</td></tr>
            @empty
            <tr><td colspan="4">Tidak ada data ekstrakurikuler.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table style="width: 50%;">
        <thead><tr><th colspan="2">Ketidakhadiran</th></tr></thead>
        <tbody>
            <tr><td>Sakit</td><td>{{ $kehadiran['sakit'] }} hari</td></tr>
            <tr><td>Izin</td><td>{{ $kehadiran['ijin'] }} hari</td></tr>
            <tr><td>Tanpa keterangan</td><td>{{ $kehadiran['tanpa_keterangan'] }} hari</td></tr>
        </tbody>
    </table>

    <table>
        <thead><tr><th>Catatan Wali Kelas</th></tr></thead>
        <tbody><tr><td style="height: 36px;">{{ $catatan ?: '-' }}</td></tr></tbody>
    </table>

    <div style="page-break-inside: avoid;">
        @include('partials.pdf-ttd')
    </div>
