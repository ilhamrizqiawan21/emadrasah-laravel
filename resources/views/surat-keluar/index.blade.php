@extends('layouts.app')

@section('title', 'Surat Keluar')

@section('content')
<x-page-header title="Surat Keluar">
    <x-slot:actions>
        <a href="{{ route('surat-keluar.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Surat Keluar
        </a>
    </x-slot:actions>
</x-page-header>

<div class="card shadow-sm">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">Daftar Surat Keluar</h5>
            </div>
            <div class="col-md-6">
                <form method="GET" action="{{ route('surat-keluar.index') }}" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nomor surat/tujuan/perihal..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                    @if(request('search'))
                        <a href="{{ route('surat-keluar.index') }}" class="btn btn-sm btn-secondary">Reset</a>
                    @endif
                </form>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table data-hide-sm="1 3 5 6 7" class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th>Nomor Surat</th>
                        <th>Tujuan</th>
                        <th>Perihal</th>
                        <th>Tgl Kirim</th>
                        <th>Lampiran</th>
                        <th>File</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($surat as $index => $item)
                    <tr>
                        <td>{{ $index + 1 + ($surat->currentPage() - 1) * $surat->perPage() }}</td>
                        <td>
                            <span class="badge bg-primary">{{ $item->nomor_surat }}</span>
                        </td>
                        <td>{{ $item->tujuan }}</td>
                        <td>{{ $item->perihal }}</td>
                        <td>{{ $item->tanggal_kirim->format('d/m/Y') }}</td>
                        <td>{{ $item->lampiran ?? '-' }}</td>
                        <td>
                            @if($item->file_draft)
                                <a href="{{ route('files.show', ['path' => $item->file_draft]) }}" target="_blank" class="btn btn-sm btn-info">
                                    <i class="fas fa-file-word"></i> Draft
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('surat-keluar.show', $item) }}" class="btn btn-info" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('surat-keluar.edit', $item) }}" class="btn btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger" onclick="confirmDelete('{{ route('surat-keluar.destroy', $item) }}')" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <x-empty-row :colspan="8" icon="fa-folder-open">Belum ada data surat keluar</x-empty-row>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        {{ $surat->appends(request()->query())->links() }}
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf @method('DELETE')
</form>

<script>
function confirmDelete(url) {
    if (confirm('Yakin ingin menghapus surat keluar ini?')) {
        const form = document.getElementById('deleteForm');
        form.action = url;
        form.submit();
    }
}
</script>
@endsection