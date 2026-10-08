@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<x-page-header title="Manajemen User">
    <x-slot:actions>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah User
        </a>
    </x-slot:actions>
</x-page-header>

<div class="card shadow-sm">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">Daftar User</h5>
            </div>
            <div class="col-md-6">
                <form method="GET" action="{{ route('users.index') }}" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama atau email..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                    @if(request('search'))
                        <a href="{{ route('users.index') }}" class="btn btn-sm btn-secondary">Reset</a>
                    @endif
                </form>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table data-hide-sm="1 3 5" class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Terdaftar</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                    <tr>
                        <td>{{ $index + 1 + ($users->currentPage() - 1) * $users->perPage() }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ $user->role ?? 'user' }}</span>
                        </td>
                        <td>{{ $user->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                                <button type="button" class="btn btn-danger" onclick="confirmDelete('{{ route('users.destroy', $user) }}', {{ Js::from($user->name) }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <x-empty-row :colspan="6" icon="fa-folder-open">Belum ada user</x-empty-row>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        {{ $users->appends(request()->query())->links() }}
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