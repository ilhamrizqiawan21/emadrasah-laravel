@extends('layouts.app')

@section('title', 'Surat Keluar')

@section('content')
<x-page-header
    title="Surat Keluar"
    subtitle="Kelola penomoran, tujuan, dan draft surat keluar."
    :create-route="route('surat-keluar.create')"
    create-label="Tambah Surat Keluar"
/>

<form method="GET" action="{{ route('surat-keluar.index') }}" class="em-filter-bar">
    <input type="search" name="search" class="form-control em-filter-search" placeholder="Cari nomor surat, tujuan, atau perihal" value="{{ request('search') }}">
    <button type="submit" class="btn btn-outline-primary">
        <i class="fas fa-filter"></i>
        <span>Filter</span>
    </button>
    @if (request()->hasAny(['search', 'sort']))
        <a href="{{ route('surat-keluar.index') }}" class="btn btn-outline-secondary">Reset</a>
    @endif
</form>

<div class="card shadow-sm">
    <form id="bulkForm" method="POST" action="{{ route('surat-keluar.bulk-destroy') }}">
        @csrf
        @method('DELETE')
        <div class="em-bulk-bar">
            <span class="text-muted small fw-semibold">{{ $surat->total() }} surat keluar</span>
            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirmBulkDelete('surat keluar')">
                <i class="fas fa-trash"></i>
                <span>Hapus Terpilih</span>
            </button>
        </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40"><input type="checkbox" class="form-check-input" data-check-all></th>
                        <th width="50">No</th>
                        <th><x-sort-link column="nomor" label="Nomor Surat" /></th>
                        <th><x-sort-link column="tujuan" label="Tujuan" /></th>
                        <th>Perihal</th>
                        <th><x-sort-link column="tanggal" label="Tgl Kirim" /></th>
                        <th>Lampiran</th>
                        <th>File</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($surat as $index => $item)
                    <tr>
                        <td><input type="checkbox" class="form-check-input" name="ids[]" value="{{ $item->id }}"></td>
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
                                <a href="{{ asset('storage/' . $item->file_draft) }}" target="_blank" class="btn btn-sm btn-outline-info" title="Lihat draft">
                                    <i class="fas fa-file-word"></i>
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="em-table-actions">
                                <a href="{{ route('surat-keluar.show', $item) }}" class="btn btn-sm btn-outline-info" title="Detail" aria-label="Detail surat keluar {{ $item->nomor_surat }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('surat-keluar.edit', $item) }}" class="btn btn-sm btn-outline-warning" title="Edit" aria-label="Edit surat keluar {{ $item->nomor_surat }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ route('surat-keluar.destroy', $item) }}')" title="Hapus" aria-label="Hapus surat keluar {{ $item->nomor_surat }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <x-empty-state colspan="9" title="Belum ada data surat keluar" description="Surat keluar yang cocok dengan filter akan tampil di sini." icon="fa-paper-plane" />
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </form>
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

function confirmBulkDelete(label) {
    const checked = document.querySelectorAll('#bulkForm input[name="ids[]"]:checked').length;
    if (!checked) {
        alert('Pilih data yang akan dihapus.');
        return false;
    }

    return confirm('Yakin ingin menghapus ' + checked + ' ' + label + ' terpilih?');
}

document.querySelector('[data-check-all]')?.addEventListener('change', function () {
    document.querySelectorAll('#bulkForm input[name="ids[]"]').forEach((checkbox) => {
        checkbox.checked = this.checked;
    });
});
</script>
@endsection
