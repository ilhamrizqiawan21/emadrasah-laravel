@extends('layouts.app')

@section('title', 'Disiplin Siswa')

@section('content')
<x-page-header title="{{ $siswa->nama_lengkap }}">
    {{ $siswa->kelas?->nama_kelas }} · NIS {{ $siswa->nis }} · Total {{ $totalPoin }} poin
    @if($totalPoin >= $ambang)<span class="badge bg-danger-subtle text-danger-emphasis ms-1">Melewati ambang {{ $ambang }}</span>@endif
    <x-slot:actions>
        <a href="{{ route('kedisiplinan.index') }}" class="btn btn-light border">Kembali</a>
    </x-slot:actions>
</x-page-header>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white fw-semibold">Riwayat pelanggaran</div>
            <div class="table-responsive">
                <table class="table table-stack-sm align-middle mb-0">
                    <thead class="table-light"><tr><th>Tanggal</th><th>Pelanggaran</th><th class="text-end">Poin</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($siswa->pelanggaran as $p)
                            <tr>
                                <td data-label="Tanggal">{{ $p->tanggal->translatedFormat('d M Y') }}</td>
                                <td data-label="Pelanggaran">{{ $p->jenis->nama }}@if($p->keterangan)<div class="small text-muted">{{ $p->keterangan }}</div>@endif<div class="small text-muted">{{ $p->pencatat?->name }}</div></td>
                                <td data-label="Poin" class="text-end">{{ $p->poin }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('kedisiplinan.pelanggaran.destroy', $p) }}" onsubmit="return confirm('Hapus catatan ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <x-empty-row :colspan="4" icon="fa-circle-check">Belum ada pelanggaran.</x-empty-row>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-lock me-1 text-muted"></i> Catatan konseling BK <span class="small text-muted fw-normal">(rahasia, hanya staf)</span></div>
            <div class="list-group list-group-flush">
                @forelse($siswa->catatanBk as $c)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between gap-2">
                            <div><span class="fw-semibold">{{ $c->topik }}</span> <span class="small text-muted">· {{ $c->tanggal->translatedFormat('d M Y') }} · {{ $c->pencatat?->name }}</span></div>
                            <form method="POST" action="{{ route('kedisiplinan.bk.destroy', $c) }}" onsubmit="return confirm('Hapus catatan BK ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </div>
                        <div class="mt-1" style="white-space: pre-line">{{ $c->uraian }}</div>
                        @if($c->tindak_lanjut)<div class="mt-1 small"><strong>Tindak lanjut:</strong> {{ $c->tindak_lanjut }}</div>@endif
                    </div>
                @empty
                    <div class="list-group-item text-muted">Belum ada catatan BK.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white fw-semibold">Catat pelanggaran</div>
            <div class="card-body">
                @if($jenis->isEmpty())
                    <p class="mb-0 text-muted">Belum ada jenis pelanggaran. <a href="{{ route('kedisiplinan.jenis.index') }}">Tambahkan dulu</a>.</p>
                @else
                    <form method="POST" action="{{ route('kedisiplinan.pelanggaran.store', $siswa) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="jenis_id">Pelanggaran</label>
                            <select name="jenis_id" id="jenis_id" class="form-select @error('jenis_id') is-invalid @enderror" required>
                                @foreach($jenis as $j)<option value="{{ $j->id }}" @selected(old('jenis_id') == $j->id)>{{ $j->nama }} ({{ $j->poin }} poin)</option>@endforeach
                            </select>
                            @error('jenis_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="p-tanggal">Tanggal</label>
                            <input type="date" name="tanggal" id="p-tanggal" max="{{ today()->toDateString() }}" class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal', today()->toDateString()) }}" required>
                            @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="p-ket">Keterangan (opsional)</label>
                            <textarea name="keterangan" id="p-ket" rows="2" maxlength="500" class="form-control">{{ old('keterangan') }}</textarea>
                        </div>
                        <button class="btn btn-primary w-100">Simpan</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-semibold">Tambah catatan BK</div>
            <div class="card-body">
                <form method="POST" action="{{ route('kedisiplinan.bk.store', $siswa) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="b-tanggal">Tanggal</label>
                        <input type="date" name="tanggal" id="b-tanggal" max="{{ today()->toDateString() }}" class="form-control" value="{{ today()->toDateString() }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="topik">Topik</label>
                        <input type="text" name="topik" id="topik" maxlength="150" class="form-control @error('topik') is-invalid @enderror" required>
                        @error('topik')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="uraian">Uraian</label>
                        <textarea name="uraian" id="uraian" rows="4" maxlength="5000" class="form-control @error('uraian') is-invalid @enderror" required></textarea>
                        @error('uraian')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="tindak_lanjut">Tindak lanjut (opsional)</label>
                        <textarea name="tindak_lanjut" id="tindak_lanjut" rows="2" maxlength="2000" class="form-control"></textarea>
                    </div>
                    <button class="btn btn-outline-primary w-100">Simpan catatan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
