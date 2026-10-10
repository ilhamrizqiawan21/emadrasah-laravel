@extends('layouts.app')

@section('title', 'Pengumuman')

@section('content')
<x-page-header title="Pengumuman">
    {{ $kelola ? 'Kelola pengumuman untuk guru, wali murid, dan siswa.' : 'Informasi terbaru dari madrasah.' }}
    <x-slot:actions>
        @if($kelola)<a href="{{ route('pengumuman.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Buat Pengumuman</a>@endif
    </x-slot:actions>
</x-page-header>

@forelse($pengumuman as $p)
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                <div>
                    <h5 class="fw-semibold mb-1">@if($p->disematkan)<i class="fas fa-thumbtack me-1 text-warning" aria-label="Disematkan"></i>@endif{{ $p->judul }}</h5>
                    <div class="small text-muted">
                        {{ $p->terbit_pada->translatedFormat('d F Y') }}@if($p->berakhir_pada) · sampai {{ $p->berakhir_pada->translatedFormat('d F Y') }}@endif
                        @if($kelola) · untuk {{ \App\Models\Pengumuman::TARGET[$p->target] }}@endif
                    </div>
                </div>
                @if($kelola)
                    @php $st = $p->status(); @endphp
                    <span class="badge {{ ['aktif' => 'bg-success-subtle text-success-emphasis', 'terjadwal' => 'bg-info-subtle text-info-emphasis', 'berakhir' => 'bg-secondary-subtle text-secondary-emphasis'][$st] }}">{{ ucfirst($st) }}</span>
                @endif
            </div>
            <div class="mt-2" style="white-space: pre-line">{{ $p->isi }}</div>
            @if($kelola)
                <div class="mt-3 d-flex gap-2">
                    <a href="{{ route('pengumuman.edit', $p) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                    <form method="POST" action="{{ route('pengumuman.destroy', $p) }}" onsubmit="return confirm('Hapus pengumuman ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@empty
    <div class="card shadow-sm border-0"><div class="card-body text-muted">Belum ada pengumuman.</div></div>
@endforelse

@if($pengumuman->hasPages())<div>{{ $pengumuman->links() }}</div>@endif
@endsection
