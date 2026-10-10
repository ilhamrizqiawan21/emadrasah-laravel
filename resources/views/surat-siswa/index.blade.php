@extends('layouts.app')

@section('title', 'Surat Keterangan Aktif')

@section('content')
<x-page-header title="Surat Keterangan Aktif">
    {{ $siswa->nama_lengkap }} · {{ $siswa->kelas?->nama_kelas }} · NIS {{ $siswa->nis }}
    <x-slot:actions>
        <a href="{{ route('siswa.show', $siswa) }}" class="btn btn-light border">Kembali</a>
    </x-slot:actions>
</x-page-header>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-semibold">Surat yang pernah diterbitkan</div>
            <div class="table-responsive">
                <table class="table table-stack-sm align-middle mb-0">
                    <thead class="table-light"><tr><th>Nomor</th><th>Tanggal</th><th>Keperluan</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($surat as $s)
                            <tr>
                                <td data-label="Nomor" class="fw-semibold">{{ $s->nomor_surat }}</td>
                                <td data-label="Tanggal">{{ $s->tanggal_kirim->translatedFormat('d M Y') }}</td>
                                <td data-label="Keperluan">{{ $s->keperluan }}</td>
                                <td class="text-end"><a href="{{ route('surat-siswa.cetak', $s) }}" target="_blank" class="btn btn-sm btn-outline-danger"><i class="fas fa-file-pdf me-1"></i> Cetak</a></td>
                            </tr>
                        @empty
                            <x-empty-row :colspan="4" icon="fa-envelope-open-text">Belum ada surat.</x-empty-row>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-semibold">Terbitkan surat baru</div>
            <div class="card-body">
                @if($siswa->status !== 'Aktif')
                    <p class="mb-0 text-muted">Siswa berstatus {{ $siswa->status }}, jadi surat keterangan aktif tidak dapat diterbitkan.</p>
                @else
                    <form method="POST" action="{{ route('surat-siswa.store', $siswa) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="keperluan">Keperluan</label>
                            <input type="text" name="keperluan" id="keperluan" maxlength="255" class="form-control @error('keperluan') is-invalid @enderror" value="{{ old('keperluan') }}" placeholder="mis. persyaratan beasiswa" required>
                            @error('keperluan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <p class="small text-muted">Nomor diberikan otomatis dan surat tercatat di Surat Keluar. Data siswa disalin saat terbit, jadi cetak ulang selalu sama.</p>
                        <button class="btn btn-primary w-100"><i class="fas fa-stamp me-1"></i> Terbitkan</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
