@extends('layouts.app')

@section('title', 'Detail Tugas')

@section('content')
<div class="mb-4">
    <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-warning">
        <i class="fas fa-edit me-1"></i> Edit
    </a>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold">{{ $task->judul }}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="fw-semibold text-muted small">Status</label>
                        @php
                            $statusClass = $task->status == 'antrean' ? 'secondary' : ($task->status == 'proses' ? 'warning' : 'success');
                        @endphp
                        <div><span class="badge bg-{{ $statusClass }} px-3 py-2">{{ ucfirst($task->status) }}</span></div>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-semibold text-muted small">Prioritas</label>
                        @php
                            $priorityClass = $task->prioritas == 'tinggi' ? 'danger' : ($task->prioritas == 'sedang' ? 'warning' : 'info');
                        @endphp
                        <div><span class="badge bg-{{ $priorityClass }} px-3 py-2">{{ ucfirst($task->prioritas) }}</span></div>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-semibold text-muted small">Deadline</label>
                        <div class="{{ $task->deadline && $task->deadline->isPast() && $task->status != 'selesai' ? 'text-danger fw-bold' : '' }}">
                            {{ $task->deadline ? $task->deadline->format('d F Y') : '-' }}
                            @if($task->deadline && $task->deadline->isPast() && $task->status != 'selesai')
                            <i class="fas fa-exclamation-triangle ms-1"></i>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="fw-semibold text-muted small">Ditugaskan Kepada</label>
                    <div>{{ $task->assigned_to ? \App\Models\User::find($task->assigned_to)->name ?? '-' : 'Tidak ditugaskan' }}</div>
                </div>
                <div>
                    <label class="fw-semibold text-muted small">Deskripsi</label>
                    <div class="bg-light p-3 rounded mt-1">{{ $task->deskripsi ?? 'Tidak ada deskripsi.' }}</div>
                </div>
                <div class="mt-3 text-muted small">
                    <i class="fas fa-clock me-1"></i> Dibuat: {{ $task->created_at->format('d/m/Y H:i') }}<br>
                    <i class="fas fa-edit me-1"></i> Terakhir diperbarui: {{ $task->updated_at->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-history me-2 text-primary"></i> Log Aktivitas</h5>
            </div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                @if($logs->count())
                    <div class="list-group list-group-flush">
                        @foreach($logs as $log)
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $log->action }}</h6>
                                <small class="text-muted">{{ $log->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                            <p class="mb-1 small text-muted">
                                <i class="fas fa-user-circle me-1"></i> {{ $log->user->name ?? 'Sistem' }}
                            </p>
                            @if($log->keterangan)
                            <small class="text-secondary">{{ $log->keterangan }}</small>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>Belum ada aktivitas.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection