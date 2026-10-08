@extends('layouts.app')

@section('title', 'Manajemen Tugas - Daftar')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 fw-bold"><i class="fas fa-list-check me-2 text-primary"></i> Daftar Tugas</h1>
        <p class="text-muted mb-0">Tampilan tabel daftar tugas administrasi TU</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('tasks.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-columns me-1"></i> Tampilan Kanban
        </a>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Tugas Baru
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-list-ul me-2 text-primary"></i> Semua Tugas</h5>
            </div>
            <div class="col-md-8">
                <form method="GET" action="{{ route('tasks.index') }}" class="row g-2">
                    <input type="hidden" name="view" value="list">
                    <div class="col-auto">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="antrean" {{ request('status') == 'antrean' ? 'selected' : '' }}>Antrean</option>
                            <option value="proses" {{ request('status') == 'proses' ? 'selected' : '' }}>Dalam Proses</option>
                            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>
                    <div class="col-auto flex-grow-1">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari judul..." value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                    </div>
                    @if(request('search') || request('status'))
                    <div class="col-auto">
                        <a href="{{ route('tasks.index', ['view' => 'list']) }}" class="btn btn-sm btn-secondary">Reset</a>
                    </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table data-hide-sm="1 3 5" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Judul</th>
                        <th>Ditugaskan Kepada</th>
                        <th>Prioritas</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $index => $task)
                    <tr>
                        <td class="text-muted">{{ $index + 1 + ($tasks->currentPage() - 1) * $tasks->perPage() }}</td>
                        <td class="fw-semibold">{{ $task->judul }}</td>
                        <td>
                            @if($task->assigned_to)
                            <i class="fas fa-user-circle me-1 text-muted"></i> {{ $task->assignedTo->name ?? '-' }}
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $priorityColor = $task->prioritas == 'tinggi' ? 'danger' : ($task->prioritas == 'sedang' ? 'warning' : 'info');
                            @endphp
                            <span class="badge bg-{{ $priorityColor }}">{{ ucfirst($task->prioritas) }}</span>
                        </td>
                        <td class="{{ $task->deadline && $task->deadline->isPast() && $task->status != 'selesai' ? 'text-danger fw-bold' : '' }}">
                            {{ $task->deadline ? $task->deadline->format('d/m/Y') : '-' }}
                            @if($task->deadline && $task->deadline->isPast() && $task->status != 'selesai')
                            <i class="fas fa-exclamation-triangle ms-1 text-danger"></i>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusColor = $task->status == 'antrean' ? 'secondary' : ($task->status == 'proses' ? 'warning' : 'success');
                            @endphp
                            <span class="badge bg-{{ $statusColor }} px-3 py-1">{{ ucfirst($task->status) }}</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('tasks.show', $task) }}" class="btn btn-outline-primary" title="Detail"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-outline-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete('{{ route('tasks.destroy', $task) }}', {{ Js::from($task->judul) }})" title="Hapus"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5">Belum ada tugas. <a href="{{ route('tasks.create') }}">Buat tugas baru</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        {{ $tasks->appends(request()->query())->links() }}
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