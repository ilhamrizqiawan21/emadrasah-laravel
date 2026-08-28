@extends('layouts.app')

@section('title', 'Sarana Prasarana')

@section('content')
<x-page-header
    title="Sarana Prasarana"
    subtitle="Kelola inventaris, kondisi, lokasi, dan stok tersedia."
>
    <div>
        <a href="{{ route('kategori-sarana.index') }}" class="btn btn-info me-2">
            <i class="fas fa-tags"></i> Kelola Kategori
        </a>
        <a href="{{ route('sarana.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Sarana
        </a>
    </div>
</x-page-header>

<form method="GET" action="{{ route('sarana.index') }}" class="em-filter-bar">
    <input type="search" name="search" class="form-control em-filter-search" placeholder="Cari kode, nama, kategori, atau lokasi" value="{{ request('search') }}">
    <select name="kategori_id" class="form-select" style="max-width: 200px;">
        <option value="">Semua Kategori</option>
        @foreach ($kategori as $item)
            <option value="{{ $item->id }}" @selected((string) request('kategori_id') === (string) $item->id)>{{ $item->nama_kategori }}</option>
        @endforeach
    </select>
    <select name="kondisi" class="form-select" style="max-width: 200px;">
        <option value="">Semua Kondisi</option>
        @foreach (['baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat', 'hilang' => 'Hilang'] as $value => $label)
            <option value="{{ $value }}" @selected(request('kondisi') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-outline-primary">
        <i class="fas fa-filter"></i>
        <span>Filter</span>
    </button>
    @if (request()->hasAny(['search', 'kategori_id', 'kondisi', 'sort']))
        <a href="{{ route('sarana.index') }}" class="btn btn-outline-secondary">Reset</a>
    @endif
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th><x-sort-link column="kode" label="Kode" /></th>
                        <th><x-sort-link column="nama" label="Nama Sarana" /></th>
                        <th><x-sort-link column="kategori" label="Kategori" /></th>
                        <th><x-sort-link column="jumlah" label="Jumlah" /></th>
                        <th><x-sort-link column="kondisi" label="Kondisi" /></th>
                        <th><x-sort-link column="lokasi" label="Lokasi" /></th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sarana as $index => $item)
                    <tr>
                        <td>{{ $index + 1 + ($sarana->currentPage() - 1) * $sarana->perPage() }}</td>
                        <td>{{ $item->kode_sarana }}</td>
                        <td>{{ $item->nama_sarana }}</td>
                        <td>{{ $item->kategori->nama_kategori ?? '-' }}</td>
                        <td>{{ $item->jumlah }}</td>
                        <td>
                            @php
                                $kondisiClass = [
                                    'baik' => 'success',
                                    'rusak_ringan' => 'warning',
                                    'rusak_berat' => 'danger',
                                    'hilang' => 'dark'
                                ][$item->kondisi] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $kondisiClass }}">{{ ucfirst(str_replace('_', ' ', $item->kondisi)) }}</span>
                        </td>
                        <td>{{ $item->lokasi_ruang ?? '-' }}</td>
                        <td>
                            <div class="em-table-actions justify-content-start">
                                <a href="{{ route('sarana.edit', $item) }}" class="btn btn-sm btn-outline-warning" title="Edit" aria-label="Edit sarana {{ $item->nama_sarana }}"><i class="fas fa-edit"></i></a>
                                <a href="{{ route('sarana.peminjaman', $item) }}" class="btn btn-sm btn-outline-info" title="Peminjaman" aria-label="Peminjaman sarana {{ $item->nama_sarana }}"><i class="fas fa-hand-holding"></i></a>
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ route('sarana.destroy', $item) }}', '{{ $item->nama_sarana }}')" title="Hapus" aria-label="Hapus sarana {{ $item->nama_sarana }}"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <x-empty-state colspan="8" title="Belum ada data sarana" description="Sarana yang cocok dengan filter akan tampil di sini." icon="fa-warehouse" />
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        {{ $sarana->links() }}
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf @method('DELETE')
</form>
<script>
function confirmDelete(url, name) {
    if (confirm('Yakin ingin menghapus sarana "' + name + '"?')) {
        const form = document.getElementById('deleteForm');
        form.action = url;
        form.submit();
    }
}
</script>
@endsection
