@extends('layouts.app')

@section('title', 'Detail Surat Masuk')

@section('content')
<div class="mb-4">
    <a href="{{ route('surat-masuk.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
    <a href="{{ route('surat-masuk.edit', $suratMasuk) }}" class="btn btn-warning">
        <i class="fas fa-edit"></i> Edit
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <h5>Detail Surat Masuk</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="150">Nomor Agenda</th>
                        <td>: <strong>{{ $suratMasuk->nomor_agenda }}</strong></td>
                    </tr>
                    <tr>
                        <th>Asal Surat</th>
                        <td>: {{ $suratMasuk->asal_surat }}</td>
                    </tr>
                    <tr>
                        <th>Nomor Surat</th>
                        <td>: {{ $suratMasuk->nomor_surat ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Perihal</th>
                        <td>: {{ $suratMasuk->perihal }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Terima</th>
                        <td>: {{ $suratMasuk->tanggal_terima->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Surat</th>
                        <td>: {{ $suratMasuk->tanggal_surat ? $suratMasuk->tanggal_surat->format('d/m/Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>: 
                            @php
                                $statusClass = [
                                    'diterima' => 'secondary',
                                    'diproses' => 'warning',
                                    'selesai' => 'success'
                                ][$suratMasuk->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">{{ ucfirst($suratMasuk->status) }}</span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="150">File Scan</th>
                        <td>: 
                            @if($suratMasuk->file_scan)
                                <a href="{{ route('files.show', ['path' => $suratMasuk->file_scan]) }}" target="_blank" class="btn btn-sm btn-info">
                                    <i class="fas fa-file-pdf"></i> Lihat File
                                </a>
                            @else
                                Tidak ada file
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Dibuat pada</th>
                        <td>: {{ $suratMasuk->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Diperbarui</th>
                        <td>: {{ $suratMasuk->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <hr>
        <h6>Disposisi</h6>
        <div class="p-3 bg-light rounded">
            {{ $suratMasuk->disposisi ?? 'Tidak ada disposisi.' }}
        </div>
    </div>
</div>
@endsection