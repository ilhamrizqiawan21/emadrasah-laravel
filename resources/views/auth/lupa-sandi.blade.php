@extends('layouts.auth')

@section('title', 'Lupa Kata Sandi')

@section('content')
<h2 class="login-heading">Lupa Kata Sandi</h2>
<p class="login-lead">Masukkan email akun Anda. Kami kirim tautan untuk membuat kata sandi baru.</p>

@if(session('status'))
    <div class="alert em-alert em-alert-success mb-4" role="alert">
        <div class="em-alert-icon"><i class="fas fa-circle-check"></i></div>
        <div class="em-alert-content">{{ session('status') }}</div>
    </div>
@endif
@if($errors->any())
    <div class="alert em-alert em-alert-danger mb-4" role="alert">
        <div class="em-alert-icon"><i class="fas fa-circle-exclamation"></i></div>
        <div class="em-alert-content">{{ $errors->first() }}</div>
    </div>
@endif

<form action="{{ route('password.email') }}" method="POST">
    @csrf
    <div class="form-floating mb-4">
        <input type="email" name="email" class="form-control" id="email" placeholder="nama@email.com" autocomplete="username" required autofocus value="{{ old('email') }}">
        <label for="email"><i class="fas fa-envelope me-2"></i>Email</label>
    </div>
    <button type="submit" class="btn btn-primary btn-login w-100">Kirim Tautan <i class="fas fa-paper-plane ms-2"></i></button>
</form>
<p class="mt-4 mb-0"><a href="{{ route('login') }}" class="small"><i class="fas fa-arrow-left me-1"></i>Kembali ke halaman masuk</a></p>
@endsection
