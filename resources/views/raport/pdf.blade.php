<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Raport {{ $siswa->nama_lengkap }}</title>
    @include('raport.partials.gaya')
</head>
<body>
    @include('raport.partials.isi')
</body>
</html>
