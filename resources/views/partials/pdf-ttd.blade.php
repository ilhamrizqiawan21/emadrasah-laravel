{{-- Tanda tangan kepala madrasah untuk dokumen cetak (nama dan NIP dari menu Pengaturan). --}}
<div style="margin-top: 40px; text-align: right;">
    <div style="display: inline-block; min-width: 220px; text-align: center;">
        <div>Kepala Madrasah,</div>
        <div style="height: 64px;"></div>
        <div style="font-weight: bold;">{{ $madrasah->kepala_nama ?: '..............................' }}</div>
        @if ($madrasah->kepala_nip)
            <div>NIP. {{ $madrasah->kepala_nip }}</div>
        @endif
    </div>
</div>
