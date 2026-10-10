{{--
    Avatar inisial untuk kolom nama di tabel. Warnanya ditentukan nama (stabil, 6 pilihan pastel),
    dan gelar di depan nama (Dr., Hj., Ust., dst.) dilewati saat mengambil inisial.
    <x-avatar :name="$siswa->nama_lengkap" />
--}}
@props(['name'])
@php
    $titles = ['dr', 'dra', 'drs', 'h', 'hj', 'ir', 'prof', 'ust', 'ustadz', 'ustadzah', 'kh', 'nyai'];
    $words = preg_split('/[\s,]+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);
    while (count($words) > 1 && in_array(strtolower(rtrim($words[0], '.')), $titles, true)) {
        array_shift($words);
    }
    $initials = mb_strtoupper(mb_substr($words[0] ?? '?', 0, 1).(isset($words[1]) ? mb_substr($words[1], 0, 1) : ''));
    $variant = (crc32((string) $name) % 6) + 1;
@endphp
<span {{ $attributes->class(['em-avatar', "em-avatar--{$variant}"]) }} aria-hidden="true">{{ $initials }}</span>
