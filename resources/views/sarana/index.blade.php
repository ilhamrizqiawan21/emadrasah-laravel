@extends('layouts.app')

@section('title', 'Sarana Prasarana')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Sarana Prasarana</h1>
    <div>
        <a href="{{ route('kategori-sarana.index') }}" class="btn btn-info me-2">
            <i class="fas fa-tags"></i> Kelola Kategori
        </a>
        <a href="{{ route('sarana.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Sarana
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table data-hide-sm="1 2 4 5 7" class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama Sarana</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                        <th>Kondisi</th>
                        <th>Lokasi</th>
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
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('sarana.edit', $item) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                                <a href="{{ route('sarana.peminjaman', $item) }}" class="btn btn-info"><i class="fas fa-hand-holding"></i></a>
                                <button class="btn btn-danger" onclick="confirmDelete('{{ route('sarana.destroy', $item) }}', {{ Js::from($item->nama_sarana) }})"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">Belum ada data sarana</td>
                        </tr>
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