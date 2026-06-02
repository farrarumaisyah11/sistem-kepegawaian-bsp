<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons (INI YANG BENAR) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: url('{{ asset('images/bg login.png') }}') no-repeat center center/cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
        }

        .login-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            padding: 40px 50px;
            width: 420px;
            text-align: center;
        }

        .login-card img {
            width: 90px;
            margin-bottom: 15px;
        }

        .login-card h4 {
            font-weight: 700;
            margin-bottom: 5px;
        }

        .login-card p {
            font-size: 14px;
        }

        .form-group {
            position: relative;
            margin-bottom: 20px;
        }

        .form-control {
            border-radius: 12px;
            border: 1px solid #b5b5b5;
            padding: 12px 15px 12px 42px;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #777;
            font-size: 18px;
        }

        .btn-login {
            background-color: #5A6621;
            border: none;
            color: white;
            font-weight: 600;
            width: 100%;
            border-radius: 12px;
            padding: 12px;
            transition: 0.2s ease;
        }

        .btn-login:hover {
            background-color: #46521A;
        }

        small {
            display: block;
            margin-top: 15px;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <img src="{{ asset('images/logo bsp.png') }}" alt="Logo">

        <h4>Login</h4>
        <p class="text-muted">Silahkan masukkan kredensial Anda</p>

        @if(session('error'))
            <div class="alert alert-danger py-2">
                {{ session('error') }}
            </div>
        @endif

<form method="POST" action="{{ route('login.post') }}">
                @csrf

            <div class="form-group">
                <i class="bi bi-person input-icon"></i>
                <input
                    type="text"
                    name="username"
                    class="form-control"
                    placeholder="Nama Pengguna"
                    required
                >
            </div>

            <div class="form-group">
                <i class="bi bi-lock input-icon"></i>
                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Kata Sandi"
                    required
                >
            </div>

            <button type="submit" class="btn-login">
                Masuk
            </button>
        </form>

        <small>
            <a href="#" class="text-secondary text-decoration-none">
                Lupa Kata Sandi?
            </a>
        </small>
    </div>

</body>
</html>
