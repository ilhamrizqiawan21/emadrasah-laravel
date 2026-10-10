<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Raport {{ $kelas->nama_kelas }}</title>
    @include('raport.partials.gaya')
</head>
<body>
    @foreach($semuaRaport as $r)
        @include('raport.partials.isi', $r)
        @if(! $loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach
</body>
</html>
