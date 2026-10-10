<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kuitansi {{ $pembayaran->nomorKuitansi() }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11pt; line-height: 1.45; color: #111; }
        .kop { border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 10px; }
        .judul { text-align: center; font-size: 14pt; font-weight: bold; letter-spacing: 2px; margin: 0; }
        .nomor { text-align: center; margin: 0 0 10px; font-size: 10pt; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data td { padding: 3px 0; vertical-align: top; }
        .jumlah { border: 2px solid #000; display: inline-block; padding: 4px 14px; font-weight: bold; font-size: 13pt; margin-top: 8px; }
        .ttd { margin-left: 60%; margin-top: 14px; }
    </style>
</head>
<body>
    @php $t = $pembayaran->tagihan; $s = $t->siswa; @endphp
    <div class="kop">@include('partials.pdf-kop')</div>
    <p class="judul">KUITANSI</p>
    <p class="nomor">No. {{ $pembayaran->nomorKuitansi() }}</p>

    <table class="data">
        <tr><td style="width: 150px;">Telah terima dari</td><td style="width: 12px;">:</td><td>Wali/orang tua dari <strong>{{ $s->nama_lengkap }}</strong> (NIS {{ $s->nis }}, kelas {{ $s->kelas?->nama_kelas ?? '-' }})</td></tr>
        <tr><td>Uang sejumlah</td><td>:</td><td><em>{{ \App\Support\Terbilang::rupiah($pembayaran->jumlah) }}</em></td></tr>
        <tr><td>Untuk pembayaran</td><td>:</td><td>{{ $t->jenis }}@if($t->periode) {{ $t->namaPeriode() }}@endif @if($t->keterangan)({{ $t->keterangan }})@endif</td></tr>
        <tr><td>Metode</td><td>:</td><td>{{ \App\Models\Pembayaran::METODE[$pembayaran->metode] ?? $pembayaran->metode }}</td></tr>
    </table>

    <div class="jumlah">Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}</div>

    <div class="ttd">
        {{ $pembayaran->tanggal->translatedFormat('d F Y') }}<br>
        Penerima,
        <br><br><br>
        <strong><u>{{ $pembayaran->pencatat?->name ?? '..............................' }}</u></strong>
    </div>
</body>
</html>
