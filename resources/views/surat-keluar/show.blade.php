@extends('layouts.app')

@section('title', 'Detail Surat Keluar')

@section('content')
<div class="mb-4">
    <a href="{{ route('surat-keluar.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
    <a href="{{ route('surat-keluar.edit', $suratKeluar) }}" class="btn btn-warning">
        <i class="fas fa-edit"></i> Edit
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <h5>Detail Surat Keluar</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="150">Nomor Surat</th>
                        <td>: <strong>{{ $suratKeluar->nomor_surat }}</strong></td>
                    </tr>
                    <tr>
                        <th>Tujuan</th>
                        <td>: {{ $suratKeluar->tujuan }}</td>
                    </tr>
                    <tr>
                        <th>Perihal</th>
                        <td>: {{ $suratKeluar->perihal }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Kirim</th>
                        <td>: {{ $suratKeluar->tanggal_kirim->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Lampiran</th>
                        <td>: {{ $suratKeluar->lampiran ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="150">File Draft</th>
                        <td>: 
                            @if($suratKeluar->file_draft)
                                <a href="{{ asset('storage/' . $suratKeluar->file_draft) }}" target="_blank" class="btn btn-sm btn-info">
                                    <i class="fas fa-file-word"></i> Lihat Draft
                                </a>
                            @else
                                Tidak ada file
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Dibuat pada</th>
                        <td>: {{ $suratKeluar->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Diperbarui</th>
                        <td>: {{ $suratKeluar->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection