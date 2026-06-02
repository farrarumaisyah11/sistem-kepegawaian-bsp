@extends('layouts.app')

@section('content')
    <div class="login-container" style="background-image: url('{{ asset('images/bg login.png') }}');">
        <div class="login-box">
            <!-- Logo -->
            <img src="{{ asset('images/logo bsp.png') }}" alt="Logo BSP" class="logo">

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label for="username">Nama Pengguna</label>
                    <input type="text" name="username" id="username" required class="form-control">
                </div>
                <div class="form-group">
                    <label for="password">Kata Sandi</label>
                    <input type="password" name="password" id="password" required class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Masuk</button>
            </form>
        </div>
    </div>
@endsection
