@extends('layouts.app')

@section('title', 'Tahun Pelajaran')

@section('content')
<x-page-header
    title="Tahun Pelajaran"
    subtitle="Kelola daftar tahun pelajaran untuk rapor, jadwal, dan arsip."
    :create-route="route('tahun-pelajaran.create')"
    create-label="Tambah Tahun Pelajaran"
/>

<form method="GET" action="{{ route('tahun-pelajaran.index') }}" class="em-filter-bar">
    <input type="search" name="search" value="{{ request('search') }}" class="form-control em-filter-search" placeholder="Cari kode atau nama tahun pelajaran">
    <button type="submit" class="btn btn-outline-primary">
        <i class="fas fa-search"></i>
        <span>Cari</span>
    </button>
    @if (request('search'))
        <a href="{{ route('tahun-pelajaran.index') }}" class="btn btn-outline-secondary">Reset</a>
    @endif
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tahunPelajaran as $item)
                    <tr>
                        <td>{{ $item->kode }}</td>
                        <td>{{ $item->nama }}</td>
                        <td>{{ $item->is_aktif ? 'Aktif' : 'Tidak aktif' }}</td>
                        <td class="text-end">
                            <a href="{{ route('tahun-pelajaran.edit', $item) }}" class="btn btn-sm btn-secondary me-2">Ubah</a>
                            <form action="{{ route('tahun-pelajaran.destroy', $item) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Hapus tahun pelajaran ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                        <x-empty-state colspan="4" title="Belum ada tahun pelajaran" description="Tahun pelajaran yang cocok dengan filter akan tampil di sini." icon="fa-calendar" />
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $tahunPelajaran->links() }}
</div>
@endsection
