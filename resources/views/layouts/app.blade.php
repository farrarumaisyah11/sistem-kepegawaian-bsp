<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - BSP System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --olive-950: #3f4a32;
            --olive-700: #78845f;
            --accent: #f3c94b;
            --sidebar-width: 260px;
            --sidebar-mini: 75px;
            --header-height: 70px;
            --transition: all 0.3s cubic-bezier(.4, 0, .2, 1);
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            background-color: #f4f6f2;
            margin: 0;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            overflow-x: hidden;
            --sidebar-current-width: var(--sidebar-width);
        }

        body.sidebar-mini {
            --sidebar-current-width: var(--sidebar-mini);
        }

        #layout-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .main-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            margin-left: var(--sidebar-current-width);
            width: calc(100% - var(--sidebar-current-width));
            transition: var(--transition);
        }

        .page-content {
            padding: calc(var(--header-height) + 20px) 25px 80px;
            flex-grow: 1;
        }

        @media (max-width: 992px) {
            body {
                --sidebar-current-width: var(--sidebar-mini);
            }

            body.sidebar-mini {
                --sidebar-current-width: var(--sidebar-mini);
            }

            .main-content {
                margin-left: var(--sidebar-mini) !important;
                width: calc(100% - var(--sidebar-mini)) !important;
            }

            .page-content {
                padding-left: 18px;
                padding-right: 18px;
            }
        }

        @media (max-width: 576px) {
            .page-content {
                padding-left: 14px;
                padding-right: 14px;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <script>
    (function () {
        const mobileQuery = window.matchMedia('(max-width: 992px)');

        if (mobileQuery.matches) {
            document.body.classList.add('sidebar-mini');
            document.body.setAttribute('data-sidebar-mode', 'mobile-mini');
            return;
        }

        try {
            const savedMode = localStorage.getItem('bsp-sidebar-mode');

            if (savedMode === 'mini') {
                document.body.classList.add('sidebar-mini');
                document.body.setAttribute('data-sidebar-mode', 'desktop-mini');
            } else {
                document.body.classList.remove('sidebar-mini');
                document.body.setAttribute('data-sidebar-mode', 'desktop-full');
            }
        } catch (e) {
            document.body.classList.remove('sidebar-mini');
            document.body.setAttribute('data-sidebar-mode', 'desktop-full');
        }
    })();
    </script>

    <div id="layout-wrapper">
        @include('layouts.sidebar')

        <div class="main-content">
            @include('layouts.header')

            <div class="page-content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>

            <footer class="py-3 px-4 bg-white border-top text-center text-muted" style="font-size: 12px;">
                © 2025 PT Bumi Siak Pusako - Integrated System
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @if(session('success_auto'))
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        function showAutoSuccess() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: @json(session('success_auto')),
                timer: 1500,
                timerProgressBar: true,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false
            });
        }

        if (window.Swal) {
            showAutoSuccess();
        } else {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
            script.onload = showAutoSuccess;
            document.head.appendChild(script);
        }
    });
    </script>
    @endif

    @stack('scripts')
</body>
</html>