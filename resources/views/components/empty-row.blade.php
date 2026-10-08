{{--
    Baris "data kosong" untuk tabel daftar, dipakai di @empty.
    <x-empty-row :colspan="7" icon="fa-archive">Belum ada arsip.</x-empty-row>
--}}
@props(['colspan', 'icon' => null])
<tr>
    <td colspan="{{ $colspan }}" class="text-center py-5 text-muted">
        @if ($icon)
            <i class="fas {{ $icon }} fa-3x mb-3 opacity-25"></i>
            <p>{{ $slot }}</p>
        @else
            {{ $slot }}
        @endif
    </td>
</tr>
