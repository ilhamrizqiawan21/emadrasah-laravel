@extends('layouts.app')

@section('title', 'Template Surat')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Template Surat</h1>
    <a href="{{ route('template-surat.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Template
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th>Nama Template</th>
                        <th>Konten (Preview)</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $index => $item)
                    <tr>
                        <td>{{ $index + 1 + ($templates->currentPage() - 1) * $templates->perPage() }}</td>
                        <td><strong>{{ $item->nama_template }}</strong></td>
                        <td>
                            <div class="text-truncate" style="max-width: 400px;">
                                {{ Str::limit(strip_tags($item->konten), 100) }}
                            </div>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('template-surat.edit', $item) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger" onclick="confirmDelete('{{ route('template-surat.destroy', $item) }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4">Belum ada template surat</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        {{ $templates->links() }}
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf @method('DELETE')
</form>

<script>
function confirmDelete(url) {
    if (confirm('Yakin ingin menghapus template ini?')) {
        const form = document.getElementById('deleteForm');
        form.action = url;
        form.submit();
    }
}
</script>
@endsection