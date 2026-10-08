@extends('layouts.app')

@section('title', 'Peminjaman Sarana')

@section('content')
<div class="mb-4">
    <a href="{{ route('sarana.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Daftar Sarana</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5>Detail Sarana: {{ $sarana->nama_sarana }} ({{ $sarana->kode_sarana }})</h5>
        <small>Stok: {{ $sarana->jumlah }} | Kondisi: {{ ucfirst(str_replace('_',' ',$sarana->kondisi)) }}</small>
    </div>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5>Form Peminjaman Baru</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('sarana.store-peminjaman', $sarana) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama Peminjam <span class="text-danger">*</span></label>
                        <input type="text" name="peminjam" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipe Peminjam</label>
                        <select name="tipe_peminjam" class="form-select">
                            <option value="guru">Guru</option>
                            <option value="siswa">Siswa</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Pinjam <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_pinjam" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-hand-holding"></i> Catat Peminjaman</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5>Riwayat Peminjaman</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Peminjam</th>
                                <th>Tipe</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th>Status</th>
                                <th>Denda</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($peminjaman as $pinjam)
                            <tr>
                                <td>{{ $pinjam->peminjam }}</td>
                                <td>{{ ucfirst($pinjam->tipe_peminjam) }}</td>
                                <td>{{ \Carbon\Carbon::parse($pinjam->tanggal_pinjam)->format('d/m/Y') }}</td>
                                <td>{{ $pinjam->tanggal_kembali ? \Carbon\Carbon::parse($pinjam->tanggal_kembali)->format('d/m/Y') : '-' }}</td>
                                <td>
                                    @if($pinjam->status == 'dipinjam')
                                        <span class="badge bg-warning text-dark">Dipinjam</span>
                                    @else
                                        <span class="badge bg-success">Dikembalikan</span>
                                    @endif
                                </td>
                                <td>Rp {{ number_format($pinjam->denda, 0, ',', '.') }}</td>
                                <td>
                                    @if($pinjam->status == 'dipinjam')
                                        <form method="POST" action="{{ route('sarana.kembalikan', $pinjam) }}" style="display:inline;">
                                            @csrf @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Kembalikan sarana?')"><i class="fas fa-undo"></i> Kembalikan</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-3">Belum ada riwayat peminjaman</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                {{ $peminjaman->links() }}
            </div>
        </div>
    </div>
</div>
@endsection