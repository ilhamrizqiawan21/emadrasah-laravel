{{-- Warna utama hasil pengaturan. CSS dibuat dari nilai #rrggbb yang sudah divalidasi, bukan input mentah. --}}
@php($themeCss = $madrasah->themeCss())
@if ($themeCss !== '')
    <style id="em-theme">{!! $themeCss !!}</style>
@endif
