@extends('layouts.app')

@section('title', 'Surat Masuk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Surat Masuk</h1>
    <a href="{{ route('surat-masuk.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Surat Masuk
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">Daftar Surat Masuk</h5>
            </div>
            <div class="col-md-6">
                <form method="GET" action="{{ route('surat-masuk.index') }}" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nomor agenda/asal/perihal..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                    @if(request('search'))
                        <a href="{{ route('surat-masuk.index') }}" class="btn btn-sm btn-secondary">Reset</a>
                    @endif
                </form>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table data-hide-sm="1 3 4 6 8" class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th>Nomor Agenda</th>
                        <th>Asal Surat</th>
                        <th>Nomor Surat</th>
                        <th>Perihal</th>
                        <th>Tgl Terima</th>
                        <th>Status</th>
                        <th>File</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($surat as $index => $item)
                    <tr>
                        <td>{{ $index + 1 + ($surat->currentPage() - 1) * $surat->perPage() }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ $item->nomor_agenda }}</span>
                        </td>
                        <td>{{ $item->asal_surat }}</td>
                        <td>{{ $item->nomor_surat ?? '-' }}</td>
                        <td>{{ $item->perihal }}</td>
                        <td>{{ $item->tanggal_terima->format('d/m/Y') }}</td>
                        <td>
                            @php
                                $statusClass = [
                                    'diterima' => 'secondary',
                                    'diproses' => 'warning',
                                    'selesai' => 'success'
                                ][$item->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">{{ ucfirst($item->status) }}</span>
                        </td>
                        <td>
                            @if($item->file_scan)
                                <a href="{{ route('files.show', ['path' => $item->file_scan]) }}" target="_blank" class="btn btn-sm btn-info">
                                    <i class="fas fa-file-pdf"></i> Lihat
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('surat-masuk.show', $item) }}" class="btn btn-info" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('surat-masuk.edit', $item) }}" class="btn btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger" onclick="confirmDelete('{{ route('surat-masuk.destroy', $item) }}')" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">Belum ada data surat masuk</td>
                    </tr>
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
    if (confirm('Yakin ingin menghapus surat masuk ini?')) {
        const form = document.getElementById('deleteForm');
        form.action = url;
        form.submit();
    }
}
</script>
@endsection