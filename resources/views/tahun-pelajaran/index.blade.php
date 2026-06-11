@extends('layouts.app')

@section('title', 'Tahun Pelajaran')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="em-page-title">Tahun Pelajaran</h2>
        <p class="text-muted">Kelola daftar tahun pelajaran untuk sistem rapor dan arsip.</p>
    </div>
    <a href="{{ route('tahun-pelajaran.create') }}" class="btn btn-primary">Tambah Tahun Pelajaran</a>
</div>

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
                    @foreach($tahunPelajaran as $item)
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
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $tahunPelajaran->links() }}
</div>
@endsection
