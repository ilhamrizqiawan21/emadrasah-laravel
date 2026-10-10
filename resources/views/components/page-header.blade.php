{{--
    Judul halaman. Slot bawaan = subjudul, slot "actions" = tombol di kanan.
    <x-page-header title="Data Siswa">
        Kelola data seluruh siswa.
        <x-slot:actions><a class="btn btn-primary">Tambah</a></x-slot:actions>
    </x-page-header>
    "cols" = lebar kolom kiri (1-11); kolom kanan mengisi sisanya.
--}}
@props(['title', 'cols' => 6])
<div class="row mb-4 align-items-center">
    <div class="col-md-{{ $cols }}">
        <h2 class="em-page-title">{{ $title }}</h2>
        @if (trim((string) $slot) !== '')
            <p class="text-muted">{{ $slot }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="col-md-{{ 12 - $cols }} text-end">
            {{ $actions }}
        </div>
    @endisset
</div>
