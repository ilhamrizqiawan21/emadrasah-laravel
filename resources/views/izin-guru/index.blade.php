@extends('layouts.app')

@section('title', 'Izin dan Cuti Guru')

@section('content')
<x-page-header title="Izin & Cuti Guru">
    {{ $bolehMengajukan ? 'Ajukan izin, sakit, cuti, atau dinas luar dan pantau statusnya.' : 'Tinjau pengajuan guru. Persetujuan mengisi absensi guru otomatis.' }}
    <x-slot:actions>
        @if($bolehMengajukan)
            <a href="{{ route('izin-guru.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Ajukan</a>
        @endif
    </x-slot:actions>
</x-page-header>

<div class="mb-3 d-flex flex-wrap gap-2">
    @foreach([null => 'Semua', 'menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak'] as $nilai => $label)
        <a href="{{ route('izin-guru.index', array_filter(['status' => $nilai])) }}"
           class="btn btn-sm {{ request('status') == $nilai ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-stack-sm align-middle mb-0">
            <thead class="table-light">
                <tr><th>Guru</th><th>Jenis</th><th>Tanggal</th><th>Alasan</th><th>Status</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($izin as $i)
                    <tr>
                        <td data-label="Guru" class="fw-semibold">{{ $i->guru->nama }}</td>
                        <td data-label="Jenis">{{ \App\Models\IzinGuru::JENIS[$i->jenis] ?? $i->jenis }}</td>
                        <td data-label="Tanggal">{{ $i->rentang() }}</td>
                        <td data-label="Alasan">{{ $i->alasan }}</td>
                        <td data-label="Status">
                            @php
                                $warna = ['menunggu' => 'bg-warning-subtle text-warning-emphasis', 'disetujui' => 'bg-success-subtle text-success-emphasis', 'ditolak' => 'bg-danger-subtle text-danger-emphasis'][$i->status] ?? 'bg-secondary-subtle';
                            @endphp
                            <span class="badge {{ $warna }}">{{ ucfirst($i->status) }}</span>
                            @if($i->catatan_keputusan)<div class="small text-muted mt-1">{{ $i->catatan_keputusan }}</div>@endif
                        </td>
                        <td class="text-end">
                            @if($i->status === 'menunggu')
                                @if($bolehMengajukan)
                                    <form method="POST" action="{{ route('izin-guru.destroy', $i) }}" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Batalkan</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('persetujuan-izin.putuskan', $i) }}" class="d-flex flex-wrap gap-2 justify-content-end">
                                        @csrf
                                        <input type="text" name="catatan" class="form-control form-control-sm w-auto" placeholder="Catatan (opsional)" maxlength="255" aria-label="Catatan keputusan">
                                        <button type="submit" name="keputusan" value="setuju" class="btn btn-sm btn-success">Setujui</button>
                                        <button type="submit" name="keputusan" value="tolak" class="btn btn-sm btn-outline-danger">Tolak</button>
                                    </form>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <x-empty-row :colspan="6" icon="fa-calendar-check">Belum ada pengajuan.</x-empty-row>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($izin->hasPages())
        <div class="card-footer bg-white">{{ $izin->links() }}</div>
    @endif
</div>
@endsection
