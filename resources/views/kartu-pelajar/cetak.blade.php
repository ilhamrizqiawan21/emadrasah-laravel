<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kartu Pelajar</title>
    <style>
        @if($satu)
        @page { margin: 0; }
        @else
        @page { margin: 24pt; }
        @endif
        body { font-family: sans-serif; color: #111; margin: 0; }
        table.lembar { border-collapse: separate; border-spacing: 10pt; }
    </style>
</head>
<body>
@if($satu)
    @php $k = $kartu[0]; @endphp
    @include('kartu-pelajar.kartu')
@else
    <table class="lembar">
        @foreach(array_chunk($kartu, 2) as $baris)
            <tr>
                @foreach($baris as $k)
                    <td>@include('kartu-pelajar.kartu')</td>
                @endforeach
            </tr>
        @endforeach
    </table>
@endif
</body>
</html>
