{{-- Kop dokumen cetak: logo, nama resmi, alamat, dan kontak dari menu Pengaturan. Gaya inline karena dompdf. --}}
<div style="text-align: center; margin-bottom: 12px;">
    @if ($logo = $madrasah->logoDataUri())
        <img src="{{ $logo }}" alt="Logo" style="height: 56px; margin-bottom: 4px;">
    @endif
    <div style="font-size: 13pt; font-weight: bold; text-transform: uppercase;">{{ $madrasah->nama_lengkap }}</div>
    @if ($madrasah->alamat)
        <div style="font-size: 9pt;">{{ $madrasah->alamat }}</div>
    @endif
    @php
        $kontak = array_filter([
            $madrasah->npsn ? 'NPSN: '.$madrasah->npsn : null,
            $madrasah->telepon ? 'Telp. '.$madrasah->telepon : null,
            $madrasah->email,
            $madrasah->website,
        ]);
    @endphp
    @if ($kontak)
        <div style="font-size: 9pt;">{{ implode('  |  ', $kontak) }}</div>
    @endif
</div>
