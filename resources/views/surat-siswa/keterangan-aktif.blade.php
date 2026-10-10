<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Keterangan Aktif - {{ $surat->data['nama'] ?? '' }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12pt; line-height: 1.5; color: #111; }
        .kop { border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 18px; }
        .judul { text-align: center; margin: 0; font-size: 14pt; font-weight: bold; text-decoration: underline; }
        .nomor { text-align: center; margin: 2px 0 18px; }
        table.data { margin: 8px 0 8px 30px; border-collapse: collapse; }
        table.data td { padding: 2px 0; vertical-align: top; }
        .ttd { margin-top: 36px; margin-left: 55%; text-align: left; }
        p { margin: 0 0 10px; text-align: justify; }
    </style>
</head>
<body>
    <div class="kop">@include('partials.pdf-kop')</div>

    <p class="judul">SURAT KETERANGAN</p>
    <p class="nomor">Nomor: {{ $surat->nomor_surat }}</p>

    <p>Yang bertanda tangan di bawah ini, Kepala {{ $madrasah->nama_lengkap }}, menerangkan bahwa:</p>

    @php $d = $surat->data ?? []; @endphp
    <table class="data">
        <tr><td style="width: 150px;">Nama</td><td style="width: 12px;">:</td><td><strong>{{ $d['nama'] ?? '-' }}</strong></td></tr>
        <tr><td>Tempat, Tgl. Lahir</td><td>:</td><td>{{ $d['ttl'] ?? '-' }}</td></tr>
        <tr><td>NIS / NISN</td><td>:</td><td>{{ $d['nis'] ?? '-' }} / {{ $d['nisn'] ?? '-' }}</td></tr>
        <tr><td>Kelas</td><td>:</td><td>{{ $d['kelas'] ?? '-' }}</td></tr>
    </table>

    <p>adalah benar siswa aktif pada {{ $madrasah->nama_lengkap }}@if(!empty($d['tahun_pelajaran'])) pada tahun pelajaran {{ $d['tahun_pelajaran'] }}@endif.</p>
    <p>Surat keterangan ini dibuat untuk keperluan: {{ $surat->keperluan }}.</p>
    <p>Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p>

    <div class="ttd">
        {{ $surat->tanggal_kirim->translatedFormat('d F Y') }}<br>
        Kepala Madrasah,
        <br><br><br><br>
        <strong><u>{{ $madrasah->kepala_nama ?: '..............................' }}</u></strong>
        @if($madrasah->kepala_nip)<br>NIP. {{ $madrasah->kepala_nip }}@endif
    </div>
</body>
</html>
