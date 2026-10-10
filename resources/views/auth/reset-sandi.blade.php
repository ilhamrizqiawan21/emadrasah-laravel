@extends('layouts.auth')

@section('title', 'Atur Ulang Kata Sandi')

@section('content')
<h2 class="login-heading">Kata Sandi Baru</h2>
<p class="login-lead">Minimal 10 karakter dan tidak mudah ditebak.</p>

@if($errors->any())
    <div class="alert em-alert em-alert-danger mb-4" role="alert">
        <div class="em-alert-icon"><i class="fas fa-circle-exclamation"></i></div>
        <div class="em-alert-content">{{ $errors->first() }}</div>
    </div>
@endif

<form action="{{ route('password.update') }}" method="POST">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div class="form-floating mb-3">
        <input type="email" name="email" class="form-control" id="email" placeholder="nama@email.com" autocomplete="username" required value="{{ old('email', $email) }}" readonly>
        <label for="email"><i class="fas fa-envelope me-2"></i>Email</label>
    </div>
    <div class="form-floating mb-3">
        <input type="password" name="password" class="form-control" id="password" placeholder="Kata sandi baru" autocomplete="new-password" required autofocus minlength="10">
        <label for="password"><i class="fas fa-lock me-2"></i>Kata sandi baru</label>
    </div>
    <div class="form-floating mb-4">
        <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" placeholder="Ulangi kata sandi" autocomplete="new-password" required minlength="10">
        <label for="password_confirmation"><i class="fas fa-lock me-2"></i>Ulangi kata sandi</label>
    </div>
    <button type="submit" class="btn btn-primary btn-login w-100">Simpan Kata Sandi <i class="fas fa-check ms-2"></i></button>
</form>
@endsection
