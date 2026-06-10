<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Approval') - {{ config('app.name', 'Sistem SDM') }}</title>

    <style>
        :root{
            --apv-primary:#59684a;
            --apv-primary-dark:#3f4d35;
            --apv-primary-soft:#eef2ea;
            --apv-border:#d7dfcc;
            --apv-border-soft:#e5e7eb;
            --apv-text:#111827;
            --apv-muted:#667085;
            --apv-bg:#ffffff;
            --apv-page:#f8faf7;
            --apv-success:#166534;
            --apv-success-bg:#dcfce7;
            --apv-warning:#92400e;
            --apv-warning-bg:#fef3c7;
            --apv-danger:#b42318;
            --apv-danger-bg:#fee4e2;
        }

        *{ box-sizing:border-box; }

        html, body{
            margin:0;
            padding:0;
            min-height:100%;
            background:var(--apv-page);
            color:var(--apv-text);
            font-family:"Inter", "Segoe UI", Arial, sans-serif;
            line-height:1.5;
        }

        a{ color:inherit; }

        .approval-shell{
            min-height:100vh;
            display:flex;
            flex-direction:column;
            background:var(--apv-page);
        }

        .approval-topbar{
            background:#ffffff;
            border-bottom:1px solid var(--apv-border);
        }

        .approval-topbar-inner{
            width:min(1120px, calc(100% - 32px));
            margin:0 auto;
            padding:14px 0;
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:16px;
        }

        .approval-brand{
            display:flex;
            align-items:center;
            gap:12px;
            min-width:0;
        }

        .approval-brand-mark{
            width:40px;
            height:40px;
            border-radius:12px;
            background:var(--apv-primary-soft);
            border:1px solid var(--apv-border);
            display:flex;
            align-items:center;
            justify-content:center;
            color:var(--apv-primary-dark);
            font-weight:900;
            letter-spacing:.04em;
            flex:0 0 auto;
        }

        .approval-brand-title{
            font-size:14px;
            font-weight:800;
            color:var(--apv-primary-dark);
            text-transform:uppercase;
            letter-spacing:.04em;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        .approval-brand-subtitle{
            font-size:12px;
            color:var(--apv-muted);
            margin-top:2px;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        .approval-topbar-info{
            color:var(--apv-muted);
            font-size:12px;
            text-align:right;
            white-space:nowrap;
        }

        .approval-main{
            width:min(1120px, calc(100% - 32px));
            margin:0 auto;
            padding:28px 0 44px;
            flex:1;
        }

        .approval-panel{
            background:#ffffff;
            border:1px solid var(--apv-border);
            border-radius:20px;
            box-shadow:0 14px 34px rgba(30,41,59,.06);
            overflow:hidden;
        }

        .approval-panel-head{
            padding:22px 24px;
            border-bottom:1px solid var(--apv-border);
            background:#ffffff;
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            gap:16px;
            flex-wrap:wrap;
        }

        .approval-eyebrow{
            color:var(--apv-primary);
            font-size:11px;
            font-weight:900;
            letter-spacing:.16em;
            text-transform:uppercase;
            margin-bottom:8px;
        }

        .approval-title{
            margin:0;
            color:var(--apv-primary-dark);
            font-size:24px;
            line-height:1.2;
            font-weight:900;
        }

        .approval-desc{
            margin:8px 0 0;
            color:var(--apv-muted);
            font-size:14px;
            max-width:680px;
        }

        .approval-panel-body{
            padding:24px;
        }

        .approval-grid-2{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:18px;
        }

        .approval-grid-1{
            display:grid;
            grid-template-columns:1fr;
            gap:18px;
        }

        .approval-card{
            background:#ffffff;
            border:1px solid var(--apv-border);
            border-radius:16px;
            overflow:hidden;
        }

        .approval-card-title{
            padding:13px 16px;
            background:var(--apv-primary-soft);
            border-bottom:1px solid var(--apv-border);
            color:var(--apv-primary-dark);
            font-size:13px;
            font-weight:900;
            display:flex;
            justify-content:space-between;
            gap:12px;
            align-items:center;
        }

        .approval-card-body{
            padding:16px;
        }

        .approval-table{
            width:100%;
            border-collapse:collapse;
            table-layout:fixed;
        }

        .approval-table th,
        .approval-table td{
            border:1px solid var(--apv-border);
            padding:10px 12px;
            vertical-align:top;
            font-size:13px;
            word-break:break-word;
        }

        .approval-table th{
            width:38%;
            background:#f7f9f2;
            color:#344054;
            font-weight:900;
            text-align:left;
        }

        .approval-table td{
            color:var(--apv-text);
            font-weight:650;
        }

        .approval-badge{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:6px 12px;
            border-radius:999px;
            font-size:12px;
            font-weight:900;
            border:1px solid transparent;
        }

        .approval-badge.approved{
            color:var(--apv-success);
            background:var(--apv-success-bg);
            border-color:#86efac;
        }

        .approval-badge.pending{
            color:var(--apv-warning);
            background:var(--apv-warning-bg);
            border-color:#fde68a;
        }

        .approval-badge.danger{
            color:var(--apv-danger);
            background:var(--apv-danger-bg);
            border-color:#fda29b;
        }

        .approval-btn{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:8px;
            padding:10px 16px;
            border-radius:12px;
            border:1px solid var(--apv-border);
            background:#ffffff;
            color:#344054;
            font-weight:800;
            font-size:13px;
            text-decoration:none;
            cursor:pointer;
            min-height:42px;
        }

        .approval-btn:hover{
            background:#f8faf7;
        }

        .approval-btn.primary{
            border-color:var(--apv-primary-dark);
            background:var(--apv-primary-dark);
            color:#ffffff;
        }

        .approval-btn.primary:hover{
            background:#34402d;
        }

        .approval-btn.success{
            border-color:#15803d;
            background:#15803d;
            color:#ffffff;
        }

        .approval-btn.success:hover{
            background:#166534;
        }

        .approval-btn:disabled{
            cursor:not-allowed;
            opacity:.65;
        }

        .approval-actions{
            display:flex;
            gap:10px;
            align-items:center;
            flex-wrap:wrap;
        }

        .approval-alert{
            border-radius:14px;
            padding:12px 14px;
            font-size:13px;
            margin-bottom:16px;
            border:1px solid var(--apv-border);
        }

        .approval-alert.success{
            color:var(--apv-success);
            background:var(--apv-success-bg);
            border-color:#86efac;
        }

        .approval-alert.warning{
            color:var(--apv-warning);
            background:var(--apv-warning-bg);
            border-color:#fde68a;
        }

        .approval-alert.danger{
            color:var(--apv-danger);
            background:var(--apv-danger-bg);
            border-color:#fda29b;
        }

        .approval-form-label{
            display:block;
            margin-bottom:8px;
            color:#344054;
            font-size:13px;
            font-weight:900;
        }

        .approval-textarea,
        .approval-input{
            width:100%;
            border:1px solid var(--apv-border);
            border-radius:12px;
            padding:12px 14px;
            font:inherit;
            font-size:13px;
            color:var(--apv-text);
            background:#ffffff;
            outline:none;
        }

        .approval-textarea{
            min-height:110px;
            resize:vertical;
        }

        .approval-textarea:focus,
        .approval-input:focus{
            border-color:var(--apv-primary);
            box-shadow:0 0 0 3px rgba(89,104,74,.14);
        }

        .approval-link-row{
            display:flex;
            gap:10px;
            align-items:center;
        }

        .approval-link-row .approval-input{
            flex:1;
            min-width:0;
        }

        .approval-qr-wrap{
            display:flex;
            align-items:center;
            justify-content:center;
            flex-direction:column;
            gap:14px;
            text-align:center;
        }

        .approval-qr-box{
            width:296px;
            height:296px;
            border:1px solid var(--apv-border);
            border-radius:20px;
            display:flex;
            align-items:center;
            justify-content:center;
            background:#ffffff;
            padding:14px;
        }

        .approval-qr-box img{
            width:100%;
            height:100%;
            object-fit:contain;
            display:block;
        }

        .approval-note{
            color:var(--apv-muted);
            font-size:13px;
            line-height:1.6;
            max-width:620px;
        }

        .approval-footer{
            padding:16px 0 24px;
            text-align:center;
            color:#98a2b3;
            font-size:12px;
        }

        @media(max-width: 768px){
            .approval-topbar-inner,
            .approval-main{
                width:min(100% - 20px, 1120px);
            }

            .approval-topbar-inner{
                align-items:flex-start;
            }

            .approval-topbar-info{
                display:none;
            }

            .approval-panel-head,
            .approval-panel-body{
                padding:18px;
            }

            .approval-grid-2{
                grid-template-columns:1fr;
            }

            .approval-title{
                font-size:21px;
            }

            .approval-table th,
            .approval-table td{
                display:block;
                width:100%;
            }

            .approval-table th{
                border-bottom:none;
            }

            .approval-link-row{
                flex-direction:column;
                align-items:stretch;
            }

            .approval-qr-box{
                width:240px;
                height:240px;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="approval-shell">
        <header class="approval-topbar">
            <div class="approval-topbar-inner">
                <div class="approval-brand">
                    <div class="approval-brand-mark">BSP</div>
                    <div>
                        <div class="approval-brand-title">PT. Bumi Siak Pusako</div>
                        <div class="approval-brand-subtitle">Job Description Approval System</div>
                    </div>
                </div>

                <div class="approval-topbar-info">
                    Sistem Informasi SDM<br>
                    Approval Access
                </div>
            </div>
        </header>

        <main class="approval-main">
            @if(session('success_auto'))
                <div class="approval-alert success">{{ session('success_auto') }}</div>
            @endif

            @if(session('warning'))
                <div class="approval-alert warning">{{ session('warning') }}</div>
            @endif

            @if($errors->any())
                <div class="approval-alert danger">
                    <strong>Terjadi kesalahan:</strong>
                    <ul style="margin:8px 0 0; padding-left:18px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="approval-footer">
            Dokumen approval ini diproses melalui Sistem Informasi SDM PT. Bumi Siak Pusako.
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
