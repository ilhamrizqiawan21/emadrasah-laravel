@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')
<x-page-header title="Audit Log">
    Jejak perubahan data penting: siapa mengubah apa, kapan, beserta nilai lama dan barunya.
</x-page-header>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('audit-log.index') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-bold text-uppercase text-muted" for="jenis">Data</label>
                <select name="jenis" id="jenis" class="form-select">
                    <option value="">Semua</option>
                    @foreach($jenisList as $label)<option value="{{ $label }}" @selected(request('jenis') === $label)>{{ $label }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-uppercase text-muted" for="aksi">Aksi</label>
                <select name="aksi" id="aksi" class="form-select">
                    <option value="">Semua</option>
                    @foreach(\App\Models\AuditLog::AKSI as $a)<option value="{{ $a }}" @selected(request('aksi') === $a)>{{ ucfirst($a) }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-uppercase text-muted" for="user_id">Pengguna</label>
                <select name="user_id" id="user_id" class="form-select">
                    <option value="">Semua</option>
                    @foreach($users as $u)<option value="{{ $u->id }}" @selected((string) request('user_id') === (string) $u->id)>{{ $u->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-uppercase text-muted" for="dari">Dari</label>
                <input type="date" name="dari" id="dari" class="form-control" value="{{ request('dari') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-uppercase text-muted" for="sampai">Sampai</label>
                <input type="date" name="sampai" id="sampai" class="form-control" value="{{ request('sampai') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-uppercase text-muted" for="cari">Cari</label>
                <input type="text" name="cari" id="cari" class="form-control" placeholder="Nama data / pengguna" value="{{ request('cari') }}">
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i> Terapkan</button>
                <a href="{{ route('audit-log.index') }}" class="btn btn-light border">Atur ulang</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-stack-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th width="160">Waktu</th><th>Pengguna</th><th width="110">Aksi</th><th>Data</th><th>Perubahan</th></tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td data-label="Waktu" class="small">{{ $log->created_at->translatedFormat('d M Y H:i') }}</td>
                        <td data-label="Pengguna">{{ $log->user_nama ?? '—' }}@if($log->ip)<div class="small text-muted">{{ $log->ip }}</div>@endif</td>
                        <td data-label="Aksi"><span class="badge bg-{{ ['dibuat' => 'success', 'diubah' => 'primary', 'dihapus' => 'danger', 'dipulihkan' => 'info'][$log->aksi] ?? 'secondary' }}">{{ ucfirst($log->aksi) }}</span></td>
                        <td data-label="Data"><span class="text-muted small">{{ $log->jenis() }}</span><div>{{ $log->label }}</div></td>
                        <td data-label="Perubahan" class="small">{{ $log->ringkasan() ?: '—' }}</td>
                    </tr>
                    @empty
                    <x-empty-row :colspan="5" icon="fa-clock-rotate-left">Belum ada catatan yang cocok.</x-empty-row>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-0 py-3">{{ $logs->links() }}</div>
</div>
@endsection
