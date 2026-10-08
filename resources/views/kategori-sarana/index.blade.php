@extends('layouts.app')

@section('title', 'Kategori Sarana')

@section('content')
<x-page-header title="Kategori Sarana Prasarana">
    <x-slot:actions>
        <a href="{{ route('kategori-sarana.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Kategori
        </a>
    </x-slot:actions>
</x-page-header>

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
                                <button type="button" class="btn btn-danger" onclick="confirmDelete('{{ route('kategori-sarana.destroy', $item->id) }}', {{ Js::from($item->nama_kategori) }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <x-empty-row :colspan="3" icon="fa-folder-open">Belum ada kategori</x-empty-row>
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