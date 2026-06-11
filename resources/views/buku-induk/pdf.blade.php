<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Buku Induk - {{ $siswa->nama_lengkap }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11pt; line-height: 1.4; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h2 { margin: 0; text-transform: uppercase; }
        .header p { margin: 5px 0 0; font-size: 10pt; }
        .section-title { background: #f0f0f0; padding: 5px 10px; font-weight: bold; margin-top: 20px; border-left: 4px solid #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table td { vertical-align: top; padding: 4px 0; }
        .label { width: 35%; }
        .colon { width: 2%; }
        .value { width: 63%; font-weight: bold; }
        .photo-box { position: absolute; top: 120px; right: 0; width: 120px; height: 160px; border: 1px solid #ccc; text-align: center; line-height: 160px; color: #999; font-size: 9pt; }
        .footer { margin-top: 50px; text-align: right; }
        .signature { margin-top: 60px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Buku Induk Register Peserta Didik</h2>
        <p>Madrasah Tsanawiyah Al-Ihsan - Kurikulum Merdeka</p>
    </div>

    <div class="photo-box">
        PAS FOTO 3X4
    </div>

    <div class="section-title">A. KETERANGAN TENTANG DIRI PESERTA DIDIK</div>
    <table>
        <tr><td class="label">1. Nama Lengkap</td><td class="colon">:</td><td class="value">{{ $siswa->nama_lengkap }}</td></tr>
        <tr><td class="label">2. Nama Panggilan</td><td class="colon">:</td><td class="value">{{ $siswa->nama_panggilan ?? '-' }}</td></tr>
        <tr><td class="label">3. NIS / NISN</td><td class="colon">:</td><td class="value">{{ $siswa->nis }} / {{ $siswa->nisn ?? '-' }}</td></tr>
        <tr><td class="label">4. Tempat, Tanggal Lahir</td><td class="colon">:</td><td class="value">{{ $siswa->tempat_lahir ?? '-' }}, {{ $siswa->tanggal_lahir ? $siswa->tanggal_lahir->format('d-m-Y') : '-' }}</td></tr>
        <tr><td class="label">5. Jenis Kelamin</td><td class="colon">:</td><td class="value">{{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td></tr>
        <tr><td class="label">6. Agama</td><td class="colon">:</td><td class="value">{{ $siswa->agama ?? 'Islam' }}</td></tr>
        <tr><td class="label">7. NIK</td><td class="colon">:</td><td class="value">{{ $siswa->nik ?? '-' }}</td></tr>
    </table>

    <div class="section-title">B. KETERANGAN TEMPAT TINGGAL</div>
    <table>
        <tr><td class="label">8. Alamat Jalan</td><td class="colon">:</td><td class="value">{{ $siswa->alamat ?? '-' }}</td></tr>
        <tr><td class="label">9. RT / RW</td><td class="colon">:</td><td class="value">{{ $siswa->rt ?? '0' }} / {{ $siswa->rw ?? '0' }}</td></tr>
        <tr><td class="label">10. Desa / Kelurahan</td><td class="colon">:</td><td class="value">{{ $siswa->desa_kelurahan ?? '-' }}</td></tr>
        <tr><td class="label">11. Kecamatan</td><td class="colon">:</td><td class="value">{{ $siswa->kecamatan ?? '-' }}</td></tr>
        <tr><td class="label">12. Kabupaten / Kota</td><td class="colon">:</td><td class="value">{{ $siswa->kabupaten_kota ?? '-' }}</td></tr>
    </table>

    <div class="section-title">C. KETERANGAN TENTANG ORANG TUA KANDUNG</div>
    <table>
        <tr><td class="label">13. Nama Ayah</td><td class="colon">:</td><td class="value">{{ $siswa->orangTuaWali->nama_ayah ?? '-' }}</td></tr>
        <tr><td class="label">14. Pekerjaan Ayah</td><td class="colon">:</td><td class="value">{{ $siswa->orangTuaWali->pekerjaan_ayah ?? '-' }}</td></tr>
        <tr><td class="label">15. Nama Ibu</td><td class="colon">:</td><td class="value">{{ $siswa->orangTuaWali->nama_ibu ?? '-' }}</td></tr>
        <tr><td class="label">16. Pekerjaan Ibu</td><td class="colon">:</td><td class="value">{{ $siswa->orangTuaWali->pekerjaan_ibu ?? '-' }}</td></tr>
    </table>

    <div class="section-title">D. KETERANGAN PENDIDIKAN SEBELUMNYA</div>
    <table>
        <tr><td class="label">17. Sekolah Asal (SD/MI)</td><td class="colon">:</td><td class="value">{{ $siswa->perkembangan->asal_sekolah ?? '-' }}</td></tr>
        <tr><td class="label">18. Nomor Ijazah</td><td class="colon">:</td><td class="value">{{ $siswa->perkembangan->no_ijazah_asal ?? '-' }}</td></tr>
        <tr><td class="label">19. Diterima di Kelas</td><td class="colon">:</td><td class="value">{{ $siswa->kelas->nama_kelas ?? '-' }}</td></tr>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ date('d F Y') }}</p>
        <div class="signature">
            <p>Kepala Madrasah,</p>
            <br><br><br>
            <p><strong>Dra. Hj. Lina Nurhasanah</strong></p>
        </div>
    </div>
</body>
</html>
