{{-- Satu kartu pelajar. Tabel dan gaya inline karena dompdf tidak mendukung flex. --}}
@php $warna = $madrasah->warnaUtama(); @endphp
<div class="kartu" style="width: 240pt; height: 149pt; border: 0.75pt solid {{ $warna }}; overflow: hidden; background: #fff;">
    <table style="width: 100%; border-collapse: collapse; background: {{ $warna }}; color: #fff;">
        <tr>
            <td style="width: 30pt; padding: 3pt 4pt;">@if($logo = $madrasah->logoDataUri())<img src="{{ $logo }}" style="height: 22pt;" alt="">@endif</td>
            <td style="padding: 3pt 0;">
                <div style="font-size: 7.5pt; font-weight: bold; text-transform: uppercase;">{{ $madrasah->nama_lengkap }}</div>
                <div style="font-size: 6pt; letter-spacing: 1pt;">KARTU PELAJAR</div>
            </td>
        </tr>
    </table>
    <table style="width: 100%; border-collapse: collapse; margin-top: 5pt;">
        <tr>
            <td style="width: 52pt; padding: 0 5pt; vertical-align: top;">
                @if($k['foto'])
                    <img src="{{ $k['foto'] }}" style="width: 48pt; height: 62pt;" alt="">
                @else
                    <div style="width: 48pt; height: 62pt; border: 0.5pt solid #bbb; text-align: center; font-size: 6pt; color: #999; padding-top: 28pt;">FOTO</div>
                @endif
            </td>
            <td style="vertical-align: top; font-size: 7pt; line-height: 1.35;">
                <div style="font-size: 8.5pt; font-weight: bold; margin-bottom: 2pt;">{{ $k['nama'] }}</div>
                <table style="border-collapse: collapse; font-size: 7pt;">
                    <tr><td style="width: 30pt;">NIS</td><td>: {{ $k['nis'] }}</td></tr>
                    <tr><td>NISN</td><td>: {{ $k['nisn'] ?: '-' }}</td></tr>
                    <tr><td>TTL</td><td>: {{ $k['ttl'] }}</td></tr>
                    <tr><td>Kelas</td><td>: {{ $k['kelas'] ?: '-' }}</td></tr>
                    <tr><td>T.P.</td><td>: {{ $k['tahun'] ?: '-' }}</td></tr>
                </table>
            </td>
        </tr>
    </table>
    @if($k['barcode'])
        <div style="text-align: center; margin-top: 4pt;"><img src="{{ $k['barcode'] }}" style="width: 120pt; height: 16pt;" alt="Barcode NIS {{ $k['nis'] }}"></div>
    @endif
</div>
