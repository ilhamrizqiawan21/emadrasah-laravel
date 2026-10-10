@extends('layouts.app')

@section('title', 'Ganti Kata Sandi')

@section('content')
<x-page-header title="Ganti Kata Sandi">
    Ubah kata sandi akun Anda ({{ auth()->user()->email }}).
</x-page-header>

<div class="card shadow-sm border-0" style="max-width: 560px;">
    <div class="card-body">
        <form method="POST" action="{{ route('akun.sandi.update') }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="current_password" class="form-label fw-semibold">Kata sandi saat ini</label>
                <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password" required>
                @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Kata sandi baru</label>
                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" minlength="10" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Minimal 10 karakter dan tidak mudah ditebak.</div>
            </div>
            <div class="mb-4">
                <label for="password_confirmation" class="form-label fw-semibold">Ulangi kata sandi baru</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" autocomplete="new-password" minlength="10" required>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Simpan Kata Sandi</button>
        </form>
    </div>
</div>
@endsection
