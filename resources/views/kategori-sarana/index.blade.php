@extends('layouts.app')

@section('title', 'Kategori Sarana')

@section('content')
<x-page-header
    title="Kategori Sarana"
    subtitle="Kelola kategori untuk inventaris sarana prasarana."
    :create-route="route('kategori-sarana.create')"
    create-label="Tambah Kategori"
/>

<form method="GET" action="{{ route('kategori-sarana.index') }}" class="em-filter-bar">
    <input type="search" name="search" value="{{ request('search') }}" class="form-control em-filter-search" placeholder="Cari kategori sarana">
    <button type="submit" class="btn btn-outline-primary">
        <i class="fas fa-search"></i>
        <span>Cari</span>
    </button>
    @if (request('search'))
        <a href="{{ route('kategori-sarana.index') }}" class="btn btn-outline-secondary">Reset</a>
    @endif
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th>Nama Kategori</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kategori as $index => $item)
                    <tr>
                        <td>{{ $index + 1 + ($kategori->currentPage() - 1) * $kategori->perPage() }}</td>
                        <td>{{ $item->nama_kategori }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('kategori-sarana.edit', $item->id) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger" onclick="confirmDelete('{{ route('kategori-sarana.destroy', $item->id) }}', '{{ $item->nama_kategori }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <x-empty-state colspan="3" title="Belum ada kategori" description="Kategori sarana yang cocok dengan filter akan tampil di sini." icon="fa-tags" />
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        {{ $kategori->links() }}
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf @method('DELETE')
</form>
<script>
function confirmDelete(url, name) {
    if (confirm('Yakin ingin menghapus kategori "' + name + '"?')) {
        const form = document.getElementById('deleteForm');
        form.action = url;
        form.submit();
    }
}
</script>
@endsection
