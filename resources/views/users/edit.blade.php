@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="mb-4">
    <a href="{{ route('users.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5>Edit User: {{ $user->name }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('users.update', $user) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password <span class="text-muted">(kosongkan jika tidak diubah)</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror">
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="operator" {{ old('role', $user->role) == 'operator' ? 'selected' : '' }}>Staf TU / Operator</option>
                                <option value="guru" {{ old('role', $user->role) == 'guru' ? 'selected' : '' }}>Guru</option>
                                <option value="wali_murid" {{ old('role', $user->role) == 'wali_murid' ? 'selected' : '' }}>Wali Murid</option>
                                <option value="siswa" {{ old('role', $user->role) == 'siswa' ? 'selected' : '' }}>Siswa</option>
                            </select>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger bg-opacity-10 border-danger">
            <div class="card-body">
                <h6 class="text-danger"><i class="fas fa-exclamation-triangle"></i> Zona Berbahaya</h6>
                <p class="small">Menghapus user akan menghapus semua data terkait (log aktivitas, tugas yang dibuat, dll).</p>
                <button class="btn btn-danger btn-sm" onclick="confirmDelete('{{ route('users.destroy', $user) }}', {{ Js::from($user->name) }})">
                    <i class="fas fa-trash"></i> Hapus User Ini
                </button>
            </div>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf @method('DELETE')
</form>
<script>
function confirmDelete(url, name) {
    if (confirm('Yakin ingin menghapus user "' + name + '"?')) {
        const form = document.getElementById('deleteForm');
        form.action = url;
        form.submit();
    }
}
</script>
@endsection