@extends('layouts.app')

@section('title', 'Edit Tugas')

@section('content')
<div class="mb-4">
    <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-semibold"><i class="fas fa-edit me-2 text-primary"></i> Edit Tugas: {{ $task->judul }}</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('tasks.update', $task) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Judul Tugas <span class="text-danger">*</span></label>
                    <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul', $task->judul) }}" required>
                    @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="4">{{ old('deskripsi', $task->deskripsi) }}</textarea>
                    @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Ditugaskan Kepada</label>
                    <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                        <option value="">-- Pilih Staf/Guru --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('assigned_to', $task->assigned_to) == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Prioritas</label>
                    <select name="prioritas" class="form-select @error('prioritas') is-invalid @enderror">
                        <option value="rendah" {{ old('prioritas', $task->prioritas) == 'rendah' ? 'selected' : '' }}>Rendah</option>
                        <option value="sedang" {{ old('prioritas', $task->prioritas) == 'sedang' ? 'selected' : '' }}>Sedang</option>
                        <option value="tinggi" {{ old('prioritas', $task->prioritas) == 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                    </select>
                    @error('prioritas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Deadline</label>
                    <input type="date" name="deadline" class="form-control @error('deadline') is-invalid @enderror" value="{{ old('deadline', $task->deadline ? $task->deadline->format('Y-m-d') : '') }}">
                    @error('deadline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Kategori</label>
                    <input type="text" name="kategori" class="form-control @error('kategori') is-invalid @enderror" value="{{ old('kategori', $task->kategori) }}" placeholder="Contoh: Administrasi, Kurikulum">
                    @error('kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="antrean" {{ old('status', $task->status) == 'antrean' ? 'selected' : '' }}>Antrean</option>
                        <option value="proses" {{ old('status', $task->status) == 'proses' ? 'selected' : '' }}>Dalam Proses</option>
                        <option value="selesai" {{ old('status', $task->status) == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Progress (%)</label>
                    <input type="number" name="progress_persen" class="form-control @error('progress_persen') is-invalid @enderror" value="{{ old('progress_persen', $task->progress_persen ?? 0) }}" min="0" max="100">
                    @error('progress_persen')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Lampiran (File)</label>
                    <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror">
                    @if($task->attachment)
                        <div class="mt-1 small">
                            <a href="{{ asset('storage/' . $task->attachment) }}" target="_blank" class="text-primary"><i class="fas fa-file-download me-1"></i> Lihat file saat ini</a>
                        </div>
                    @endif
                    @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-1"></i> Update Tugas
                </button>
            </div>
        </form>
    </div>
</div>

<div class="mt-4">
    <div class="card bg-danger bg-opacity-10 border-0">
        <div class="card-body">
            <h6 class="text-danger fw-semibold"><i class="fas fa-exclamation-triangle me-1"></i> Zona Berbahaya</h6>
            <p class="small text-muted">Menghapus tugas akan menghapus semua log aktivitas terkait.</p>
            <button class="btn btn-danger btn-sm" onclick="confirmDelete('{{ route('tasks.destroy', $task) }}', '{{ $task->judul }}')">
                <i class="fas fa-trash me-1"></i> Hapus Tugas Ini
            </button>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf @method('DELETE')
</form>
<script>
function confirmDelete(url, name) {
    if (confirm('Yakin ingin menghapus tugas "' + name + '"?')) {
        const form = document.getElementById('deleteForm');
        form.action = url;
        form.submit();
    }
}
</script>
@endsection