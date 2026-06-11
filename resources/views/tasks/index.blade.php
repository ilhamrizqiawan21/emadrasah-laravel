@extends('layouts.app')

@section('title', 'Manajemen Tugas - Kanban')

@section('styles')
<style>
    .kanban-column {
        background: #f8fafc;
        border-radius: 1rem;
        transition: all 0.2s;
    }
    .kanban-column .card-header {
        border-radius: 1rem 1rem 0 0;
        padding: 0.75rem 1rem;
    }
    .task-card {
        transition: all 0.2s ease;
        border-left: 4px solid transparent;
    }
    .task-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    .task-card.deadline-warning {
        border-left-color: #ef4444;
        background: #fef2f2;
    }
    .task-card.deadline-approaching {
        border-left-color: #f59e0b;
    }
    .priority-badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.5rem;
        border-radius: 20px;
    }
    .task-footer {
        border-top: 1px solid #e2e8f0;
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        font-size: 0.7rem;
        color: #64748b;
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 fw-bold"><i class="fas fa-tasks me-2 text-primary"></i> Manajemen Tugas</h1>
        <p class="text-muted">Drag & drop untuk mengubah status</p>
    </div>
    <a href="{{ route('tasks.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Tugas Baru
    </a>
</div>

<div class="row g-4" id="kanbanBoard">
    @php
        $statuses = ['antrean' => 'Antrean', 'proses' => 'Dalam Proses', 'selesai' => 'Selesai'];
        $statusColors = ['antrean' => 'secondary', 'proses' => 'warning', 'selesai' => 'success'];
        $bgColors = ['antrean' => '#f1f5f9', 'proses' => '#fffbeb', 'selesai' => '#f0fdf4'];
    @endphp

    @foreach($statuses as $statusKey => $statusLabel)
    <div class="col-md-4">
        <div class="kanban-column h-100">
            <div class="card-header bg-{{ $statusColors[$statusKey] }} text-white py-2 px-3 rounded-top">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $statusLabel }}</h5>
                    <span class="badge bg-light text-dark">{{ $tasks->where('status', $statusKey)->count() }}</span>
                </div>
            </div>
            <div class="card-body p-2" data-status="{{ $statusKey }}" id="column-{{ $statusKey }}" style="min-height: 500px; max-height: 70vh; overflow-y: auto;">
                @foreach($tasks->where('status', $statusKey) as $task)
                <div class="card mb-2 task-card task-card-{{ $task->id }} {{ $task->deadline && $task->deadline->isPast() && $task->status != 'selesai' ? 'deadline-warning' : '' }}"
                     data-id="{{ $task->id }}" draggable="true">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <h6 class="card-title mb-1 fw-semibold">{{ $task->judul }}</h6>
                            <span class="priority-badge bg-{{ $task->prioritas == 'tinggi' ? 'danger' : ($task->prioritas == 'sedang' ? 'warning' : 'info') }} text-white">
                                {{ ucfirst($task->prioritas) }}
                            </span>
                        </div>
                        <p class="card-text small text-muted mb-1">
                            <i class="fas fa-user-circle me-1"></i> {{ $task->assigned_to ? \App\Models\User::find($task->assigned_to)->name ?? '-' : '-' }}
                        </p>
                        @if($task->deadline)
                        <p class="card-text small mb-1">
                            <i class="fas fa-calendar-alt me-1"></i> Deadline: 
                            <span class="{{ $task->deadline->isPast() && $task->status != 'selesai' ? 'text-danger fw-bold' : 'text-muted' }}">
                                {{ $task->deadline->format('d/m/Y') }}
                                @if($task->deadline->isPast() && $task->status != 'selesai')
                                <i class="fas fa-exclamation-triangle ms-1"></i>
                                @endif
                            </span>
                        </p>
                        @endif
                        <div class="task-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <small><i class="fas fa-history"></i> {{ $task->created_at->diffForHumans() }}</small>
                                <div>
                                    <a href="{{ route('tasks.show', $task) }}" class="text-primary me-2" title="Detail"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('tasks.edit', $task) }}" class="text-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                @if($tasks->where('status', $statusKey)->count() == 0)
                <div class="text-center text-muted py-4">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <p>Tidak ada tugas</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

<form id="updateStatusForm" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
</form>

<form method="GET" class="row g-2 mb-3">
    <div class="col-auto">
        <input type="text" name="search" class="form-control" placeholder="Cari judul..." value="{{ request('search') }}">
    </div>
    <div class="col-auto">
        <select name="status" class="form-select">
            <option value="">Semua Status</option>
            <option value="antrean" {{ request('status')=='antrean' ? 'selected' : '' }}>Antrean</option>
            <option value="proses" {{ request('status')=='proses' ? 'selected' : '' }}>Proses</option>
            <option value="selesai" {{ request('status')=='selesai' ? 'selected' : '' }}>Selesai</option>
        </select>
    </div>
    <div class="col-auto">
        <select name="prioritas" class="form-select">
            <option value="">Semua Prioritas</option>
            <option value="rendah" {{ request('prioritas')=='rendah' ? 'selected' : '' }}>Rendah</option>
            <option value="sedang" {{ request('prioritas')=='sedang' ? 'selected' : '' }}>Sedang</option>
            <option value="tinggi" {{ request('prioritas')=='tinggi' ? 'selected' : '' }}>Tinggi</option>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="{{ route('tasks.index') }}" class="btn btn-secondary">Reset</a>
    </div>
</form>

<script>
    let dragSrc = null;
    document.querySelectorAll('.task-card').forEach(card => {
        card.addEventListener('dragstart', dragStart);
        card.addEventListener('dragend', dragEnd);
    });
    function dragStart(e) {
        dragSrc = this;
        e.dataTransfer.setData('text/plain', this.dataset.id);
        this.style.opacity = '0.5';
    }
    function dragEnd(e) {
        this.style.opacity = '';
        dragSrc = null;
    }
    document.querySelectorAll('[data-status]').forEach(column => {
        column.addEventListener('dragover', e => e.preventDefault());
        column.addEventListener('drop', e => {
            e.preventDefault();
            const taskId = e.dataTransfer.getData('text/plain');
            const newStatus = column.dataset.status;
            if (taskId && newStatus) {
                const form = document.getElementById('updateStatusForm');
                form.action = `/tasks/${taskId}/status`;
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'status';
                input.value = newStatus;
                form.appendChild(input);
                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({ _method: 'PATCH', status: newStatus })
                }).then(response => response.json()).then(data => {
                    if (data.success) location.reload();
                    else alert('Gagal mengupdate status: ' + data.message);
                }).catch(() => alert('Terjadi kesalahan.'));
                form.removeChild(input);
            }
        });
    });
</script>
@endsection