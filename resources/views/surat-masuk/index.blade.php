@extends('layouts.app')

@section('title', 'Surat Masuk')

@section('content')
<x-page-header
    title="Surat Masuk"
    subtitle="Kelola agenda, disposisi, dan arsip surat masuk."
    :create-route="route('surat-masuk.create')"
    create-label="Tambah Surat Masuk"
/>

<form method="GET" action="{{ route('surat-masuk.index') }}" class="em-filter-bar">
    <input type="search" name="search" class="form-control em-filter-search" placeholder="Cari agenda, asal, nomor surat, atau perihal" value="{{ request('search') }}">
    <select name="status" class="form-select" style="max-width: 180px;">
        <option value="">Semua Status</option>
        @foreach (['diterima' => 'Diterima', 'diproses' => 'Diproses', 'selesai' => 'Selesai'] as $value => $label)
            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-outline-primary">
        <i class="fas fa-filter"></i>
        <span>Filter</span>
    </button>
    @if (request()->hasAny(['search', 'status', 'sort']))
        <a href="{{ route('surat-masuk.index') }}" class="btn btn-outline-secondary">Reset</a>
    @endif
</form>

<div class="card shadow-sm">
    <form id="bulkForm" method="POST" action="{{ route('surat-masuk.bulk-destroy') }}">
        @csrf
        @method('DELETE')
        <div class="em-bulk-bar">
            <span class="text-muted small fw-semibold">{{ $surat->total() }} surat masuk</span>
            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirmBulkDelete('surat masuk')">
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
                        <th><x-sort-link column="agenda" label="Nomor Agenda" /></th>
                        <th><x-sort-link column="asal" label="Asal Surat" /></th>
                        <th>Nomor Surat</th>
                        <th>Perihal</th>
                        <th><x-sort-link column="tanggal" label="Tgl Terima" /></th>
                        <th><x-sort-link column="status" label="Status" /></th>
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
                                <a href="{{ asset('storage/' . $item->file_scan) }}" target="_blank" class="btn btn-sm btn-outline-info" title="Lihat file">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="em-table-actions">
                                <a href="{{ route('surat-masuk.show', $item) }}" class="btn btn-sm btn-outline-info" title="Detail" aria-label="Detail surat masuk {{ $item->nomor_agenda }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('surat-masuk.edit', $item) }}" class="btn btn-sm btn-outline-warning" title="Edit" aria-label="Edit surat masuk {{ $item->nomor_agenda }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ route('surat-masuk.destroy', $item) }}')" title="Hapus" aria-label="Hapus surat masuk {{ $item->nomor_agenda }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <x-empty-state colspan="10" title="Belum ada data surat masuk" description="Surat masuk yang cocok dengan filter akan tampil di sini." icon="fa-envelope-open-text" />
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
    if (confirm('Yakin ingin menghapus surat masuk ini?')) {
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
