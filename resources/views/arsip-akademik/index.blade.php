@extends('layouts.app')

@section('title', 'Arsip Akademik')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="em-page-title">Arsip Administrasi Akademik</h2>
        <p class="text-muted">Penyimpanan digital untuk dokumen administrasi TU.</p>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="fas fa-upload me-2"></i>Upload Arsip Baru
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" style="width: 60px;">No</th>
                        <th>Nama Arsip / File</th>
                        <th>Tipe</th>
                        <th>Kelas / Semester</th>
                        <th>Tahun Pelajaran</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($arsip as $index => $item)
                    <tr>
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
                            <div class="btn-group">
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
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-archive fa-3x mb-3 opacity-25"></i>
                            <p>Belum ada arsip akademik yang diunggah.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
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
@endsection
