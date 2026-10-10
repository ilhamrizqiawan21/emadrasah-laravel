{{-- Tiga pengumuman aktif terbaru untuk dashboard dan portal wali. --}}
@if($pengumuman->isNotEmpty())
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold"><i class="fas fa-bullhorn me-2 text-primary" aria-hidden="true"></i>Pengumuman</h5>
        <a href="{{ route('pengumuman.index') }}" class="small">Lihat semua</a>
    </div>
    <div class="list-group list-group-flush">
        @foreach($pengumuman as $p)
            <a href="{{ route('pengumuman.index') }}" class="list-group-item list-group-item-action">
                <div class="fw-semibold">@if($p->disematkan)<i class="fas fa-thumbtack me-1 text-warning" aria-label="Disematkan"></i>@endif{{ $p->judul }}</div>
                <div class="small text-muted">{{ $p->terbit_pada->translatedFormat('d F Y') }} · {{ \Illuminate\Support\Str::limit($p->isi, 110) }}</div>
            </a>
        @endforeach
    </div>
</div>
@endif
