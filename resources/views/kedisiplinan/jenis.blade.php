@extends('layouts.app')

@section('title', 'Jenis Pelanggaran')

@section('content')
<x-page-header title="Jenis Pelanggaran">
    Daftar pelanggaran beserta poinnya. Poin disalin ke catatan saat dicatat, jadi mengubah daftar tidak mengubah riwayat.
    <x-slot:actions>
        <a href="{{ route('kedisiplinan.index') }}" class="btn btn-light border">Kembali</a>
    </x-slot:actions>
</x-page-header>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-stack-sm align-middle mb-0">
                    <thead class="table-light"><tr><th>Pelanggaran</th><th>Kategori</th><th class="text-end">Poin</th><th class="text-end">Dipakai</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($jenis as $j)
                            <tr>
                                <td data-label="Pelanggaran" class="fw-semibold">{{ $j->nama }}</td>
                                <td data-label="Kategori">{{ \App\Models\PelanggaranJenis::KATEGORI[$j->kategori] ?? $j->kategori }}</td>
                                <td data-label="Poin" class="text-end">{{ $j->poin }}</td>
                                <td data-label="Dipakai" class="text-end">{{ $j->pelanggaran_count }}×</td>
                                <td class="text-end">
                                    @if($j->pelanggaran_count === 0)
                                        <form method="POST" action="{{ route('kedisiplinan.jenis.destroy', $j) }}" onsubmit="return confirm('Hapus jenis ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <x-empty-row :colspan="5" icon="fa-list">Belum ada jenis pelanggaran. Tambahkan lewat formulir di samping.</x-empty-row>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-semibold">Tambah jenis</div>
            <div class="card-body">
                <form method="POST" action="{{ route('kedisiplinan.jenis.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="nama">Nama</label>
                        <input type="text" name="nama" id="nama" maxlength="100" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" required>
                        @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        <div class="col-7 mb-3">
                            <label class="form-label" for="kategori">Kategori</label>
                            <select name="kategori" id="kategori" class="form-select">
                                @foreach(\App\Models\PelanggaranJenis::KATEGORI as $k => $n)<option value="{{ $k }}" @selected(old('kategori') === $k)>{{ $n }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-5 mb-3">
                            <label class="form-label" for="poin">Poin</label>
                            <input type="number" name="poin" id="poin" min="1" max="1000" class="form-control @error('poin') is-invalid @enderror" value="{{ old('poin') }}" required>
                            @error('poin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <button class="btn btn-primary w-100">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
