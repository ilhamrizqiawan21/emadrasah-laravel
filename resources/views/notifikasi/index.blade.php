@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<x-page-header title="Notifikasi">
    Kabar terbaru untuk akun Anda.
    <x-slot:actions>
        @if(auth()->user()->unreadNotifications()->exists())
            <form method="POST" action="{{ route('notifikasi.baca-semua') }}">
                @csrf
                <button type="submit" class="btn btn-light border"><i class="fas fa-check-double me-1"></i> Tandai semua dibaca</button>
            </form>
        @endif
    </x-slot:actions>
</x-page-header>

<div class="card shadow-sm border-0">
    <div class="list-group list-group-flush">
        @forelse($notifikasi as $n)
            <form method="POST" action="{{ route('notifikasi.baca', $n->id) }}" class="m-0">
                @csrf
                <button type="submit" class="list-group-item list-group-item-action text-start border-0 py-3 {{ $n->read_at ? '' : 'bg-primary-subtle' }}">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <div class="fw-semibold">
                                @unless($n->read_at)<span class="badge bg-primary me-1">Baru</span>@endunless
                                {{ $n->data['judul'] ?? 'Notifikasi' }}
                            </div>
                            <div class="text-muted">{{ $n->data['pesan'] ?? '' }}</div>
                        </div>
                        <small class="text-muted text-nowrap">{{ $n->created_at->diffForHumans() }}</small>
                    </div>
                </button>
            </form>
        @empty
            <div class="text-center text-muted py-5">
                <i class="fas fa-bell-slash fa-2x mb-3"></i>
                <div>Belum ada notifikasi.</div>
            </div>
        @endforelse
    </div>
    @if($notifikasi->hasPages())
        <div class="card-footer bg-white">{{ $notifikasi->links() }}</div>
    @endif
</div>
@endsection
