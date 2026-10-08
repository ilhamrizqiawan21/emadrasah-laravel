@extends('layouts.app')

@section('title', 'Pemeliharaan Sarana')

@section('content')
<div class="mb-4">
    <a href="{{ route('sarana.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Daftar Sarana</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5>Detail Sarana: {{ $sarana->nama_sarana }} ({{ $sarana->kode_sarana }})</h5>
    </div>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5>Catat Pemeliharaan / Perbaikan</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('sarana.store-pemeliharaan', $sarana) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Tanggal Pemeliharaan</label>
                        <input type="date" name="tanggal_pemeliharaan" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Biaya (Rp)</label>
                        <input type="number" name="biaya" class="form-control" step="1000" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teknisi / Pelaksana</label>
                        <input type="text" name="teknisi" class="form-control" placeholder="Nama teknisi atau pihak">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Jenis perbaikan, bagian yang diganti, dll"></textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5>Riwayat Pemeliharaan</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tgl Pemeliharaan</th>
                                <th>Biaya</th>
                                <th>Teknisi</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pemeliharaan as $p)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($p->tanggal_pemeliharaan)->format('d/m/Y') }}</td>
                                <td>Rp {{ number_format($p->biaya, 0, ',', '.') }}</td>
                                <td>{{ $p->teknisi ?? '-' }}</td>
                                <td>{{ $p->keterangan ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-3">Belum ada catatan pemeliharaan</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                {{ $pemeliharaan->links() }}
            </div>
        </div>
    </div>
</div>
@endsection