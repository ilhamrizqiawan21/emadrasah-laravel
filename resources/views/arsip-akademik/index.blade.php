@extends('layouts.app')

@section('title', 'Arsip Akademik')

@section('content')
<x-page-header
    title="Arsip Akademik"
    subtitle="Penyimpanan digital untuk dokumen administrasi TU."
>
    <div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="fas fa-upload me-2"></i>Upload Arsip Baru
        </button>
    </div>
</x-page-header>

<form method="GET" action="{{ route('arsip-akademik.index') }}" class="em-filter-bar">
    <input type="search" name="search" value="{{ request('search') }}" class="form-control em-filter-search" placeholder="Cari nama arsip">
    <select name="tipe" class="form-select" style="max-width: 170px;">
        <option value="">Semua Tipe</option>
        @foreach (['Leger', 'RDM', 'Lainnya'] as $tipe)
            <option value="{{ $tipe }}" @selected(request('tipe') === $tipe)>{{ $tipe }}</option>
        @endforeach
    </select>
    <select name="kelas_id" class="form-select" style="max-width: 180px;">
        <option value="">Semua Kelas</option>
        @foreach ($kelas as $item)
            <option value="{{ $item->id }}" @selected((string) request('kelas_id') === (string) $item->id)>{{ $item->nama_kelas }}</option>
        @endforeach
    </select>
    <select name="tahun_pelajaran_id" class="form-select" style="max-width: 210px;">
        <option value="">Semua Tahun</option>
        @foreach ($tahunPelajaran as $item)
            <option value="{{ $item->id }}" @selected((string) request('tahun_pelajaran_id') === (string) $item->id)>{{ $item->kode }}</option>
        @endforeach
    </select>
    <select name="semester" class="form-select" style="max-width: 170px;">
        <option value="">Semua Semester</option>
        <option value="1" @selected(request('semester') === '1')>Semester 1</option>
        <option value="2" @selected(request('semester') === '2')>Semester 2</option>
    </select>
    <button type="submit" class="btn btn-outline-primary">
        <i class="fas fa-filter"></i>
        <span>Filter</span>
    </button>
    @if (request()->hasAny(['search', 'tipe', 'kelas_id', 'tahun_pelajaran_id', 'semester', 'sort']))
        <a href="{{ route('arsip-akademik.index') }}" class="btn btn-outline-secondary">Reset</a>
    @endif
</form>

<div class="card border-0 shadow-sm">
    <form id="bulkForm" method="POST" action="{{ route('arsip-akademik.bulk-destroy') }}">
        @csrf
        @method('DELETE')
        <div class="em-bulk-bar">
            <span class="text-muted small fw-semibold">{{ $arsip->total() }} arsip akademik</span>
            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirmBulkDelete('arsip')">
                <i class="fas fa-trash"></i>
                <span>Hapus Terpilih</span>
            </button>
        </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" style="width: 44px;"><input type="checkbox" class="form-check-input" data-check-all></th>
                        <th class="ps-4" style="width: 60px;">No</th>
                        <th><x-sort-link column="nama" label="Nama Arsip / File" /></th>
                        <th><x-sort-link column="tipe" label="Tipe" /></th>
                        <th><x-sort-link column="kelas" label="Kelas / Semester" /></th>
                        <th><x-sort-link column="tahun" label="Tahun Pelajaran" /></th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($arsip as $index => $item)
                    <tr>
                        <td class="ps-4"><input type="checkbox" class="form-check-input" name="ids[]" value="{{ $item->id }}"></td>
                        <td class="ps-4 text-muted small">{{ $arsip->firstItem() + $index }}</td>
                        <td>
                            <div class="fw-bold">{{ $item->nama_arsip }}</div>
                            <div class="small text-muted text-truncate" style="max-width: 250px;">
                                <i class="fas fa-file-alt me-1"></i> {{ basename($item->file_path) }}
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $item->tipe == 'Leger' ? 'bg-primary' : ($item->tipe == 'RDM' ? 'bg-success' : 'bg-secondary') }} bg-opacity-10 {{ $item->tipe == 'Leger' ? 'text-primary' : ($item->tipe == 'RDM' ? 'text-success' : 'text-secondary') }}">
                                {{ $item->tipe }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $item->kelas->nama_kelas }}</div>
                            <div class="small text-muted">Semester {{ $item->semester == 1 ? '1 (Ganjil)' : '2 (Genap)' }}</div>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $item->tahunPelajaran->kode }}</span></td>
                        <td class="text-end pe-4">
                            <div class="em-table-actions">
                                <a href="{{ asset('storage/' . $item->file_path) }}" target="_blank" class="btn btn-sm btn-outline-info" title="Download / Lihat">
                                    <i class="fas fa-download"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ route('arsip-akademik.destroy', $item) }}', '{{ $item->nama_arsip }}')" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <x-empty-state colspan="7" title="Belum ada arsip akademik" description="Arsip yang cocok dengan filter akan tampil di sini." icon="fa-archive" />
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </form>
    <div class="card-footer bg-white border-0 py-3">
        {{ $arsip->links() }}
    </div>
</div>

<!-- Modal Upload -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('arsip-akademik.store') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-primary">Upload Arsip Akademik</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Arsip</label>
                    <input type="text" name="nama_arsip" class="form-control" placeholder="Contoh: Leger Kelas 7A Ganjil" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Tahun Pelajaran</label>
                        <select name="tahun_pelajaran_id" class="form-select" required>
                            @foreach($tahunPelajaran as $tp)
                            <option value="{{ $tp->id }}" {{ $tp->is_aktif ? 'selected' : '' }}>{{ $tp->kode }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Kelas</label>
                        <select name="kelas_id" class="form-select" required>
                            @foreach($kelas as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Semester</label>
                        <select name="semester" class="form-select" required>
                            <option value="1">1 (Ganjil)</option>
                            <option value="2">2 (Genap)</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Tipe File</label>
                        <select name="tipe" class="form-select" required>
                            <option value="Leger">Leger</option>
                            <option value="RDM">RDM (Raport Digital)</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold">Pilih File (PDF/Excel/ZIP)</label>
                    <input type="file" name="file_arsip" class="form-control" required>
                    <small class="text-muted mt-1 d-block">Maksimal ukuran file: 5MB</small>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 p-4">
                <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Arsip</button>
            </div>
        </form>
    </div>
</div>

<script>
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
