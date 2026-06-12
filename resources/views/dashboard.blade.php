@extends('layouts.app')
@section('title','Dashboard')

@push('styles')
<style>
    :root{
        --navy:#0F172A;
        --soft-green:#6f7f59;
        --soft-green-2:#87956f;
        --soft-gold:#f3c94b;
        --soft-red:#d66b6b;
        --soft-olive:#9aa67a;
        --card-grad-1:#d3d9c7;
        --card-grad-2:#c4ccb6;
        --shadow:0 12px 30px rgba(15,23,42,.08);
        --ink:#334027;
        --muted:#4f5e41;
        --grid:rgba(51,64,39,.10);
        --panel:#ffffff;
        --panel-soft:#f8faf6;
        --border:#e3e8dc;
    }

    .dashboard-page{
        padding:8px 2px 22px;
    }

    .dashboard-head{
        display:flex;
        justify-content:space-between;
        align-items:flex-end;
        gap:18px;
        flex-wrap:wrap;
        margin-bottom:22px;
    }

    .section-title{
        font-weight:800;
        color:var(--navy);
        margin:0;
        letter-spacing:.2px;
        font-size:30px;
    }

    .section-subtitle{
        margin:6px 0 0;
        color:var(--muted);
        font-size:14px;
        font-weight:500;
    }

    .sync-badge{
        display:inline-flex;
        align-items:center;
        gap:10px;
        padding:10px 14px;
        border-radius:14px;
        background:#fff;
        border:1px solid var(--border);
        box-shadow:var(--shadow);
        font-size:13px;
        font-weight:700;
        color:var(--ink);
    }

    .sync-dot{
        width:10px;
        height:10px;
        border-radius:50%;
        background:var(--soft-green);
        box-shadow:0 0 0 6px rgba(111,127,89,.12);
    }

    .hero-card{
        background:linear-gradient(160deg,var(--card-grad-1),var(--card-grad-2));
        border:1px solid rgba(255,255,255,.45);
        border-radius:24px;
        padding:24px;
        box-shadow:var(--shadow);
        position:relative;
        overflow:hidden;
        margin-bottom:22px;
    }

    .hero-card::before{
        content:"";
        position:absolute;
        top:-40px;
        right:-40px;
        width:180px;
        height:180px;
        background:radial-gradient(circle, rgba(255,255,255,.20) 0%, rgba(255,255,255,0) 72%);
        pointer-events:none;
    }

    .dashboard-hero-title{
        position:relative;
        z-index:2;
        margin-bottom:18px;
    }

    .dashboard-hero-title h4{
        margin:0;
        font-size:15px;
        font-weight:800;
        color:var(--ink);
        text-transform:uppercase;
        letter-spacing:.08em;
    }

    .dashboard-hero-title p{
        margin:8px 0 0;
        font-size:14px;
        line-height:1.7;
        color:var(--muted);
        font-weight:600;
        max-width:760px;
    }

    .hero-summary-grid{
        position:relative;
        z-index:2;
        display:grid;
        grid-template-columns:repeat(3, 1fr);
        gap:16px;
    }

    .hero-summary-card{
        background:rgba(255,255,255,.24);
        border:1px solid rgba(255,255,255,.42);
        border-radius:18px;
        padding:18px 18px 16px;
        min-height:150px;
        display:flex;
        flex-direction:column;
        justify-content:space-between;
    }

    .hero-summary-label{
        font-size:12px;
        font-weight:800;
        color:var(--muted);
        text-transform:uppercase;
        letter-spacing:.06em;
        margin-bottom:12px;
        line-height:1.5;
    }

    .hero-summary-value{
        font-size:54px;
        font-weight:800;
        line-height:1;
        color:#1f2b16;
        margin-bottom:10px;
    }

    .hero-summary-note{
        font-size:13px;
        color:#000;
        line-height:1.65;
        font-weight:600;
    }

    .chart-box{
        background:#fff;
        border:1px solid var(--border);
        border-radius:22px;
        box-shadow:var(--shadow);
        height:100%;
        overflow:hidden;
    }

    .chart-box-head{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:14px;
        padding:18px 20px 14px;
        background:linear-gradient(180deg,#fcfdfb 0%, var(--panel-soft) 100%);
        border-bottom:1px solid #edf1e8;
    }

    .chart-box-title{
        margin:0;
        font-size:16px;
        font-weight:800;
        color:var(--navy);
        letter-spacing:.2px;
    }

    .chart-box-desc{
        margin:4px 0 0;
        font-size:12px;
        color:var(--muted);
        font-weight:600;
        line-height:1.5;
    }

    .chart-box-body{
        padding:18px 20px 20px;
    }

    .chart-wrap-main{
        position:relative;
        height:320px;
        width:100%;
    }

    .chart-wrap-detail{
        position:relative;
        height:340px;
        width:100%;
    }

    @media (max-width: 991px){
        .hero-summary-grid{
            grid-template-columns:1fr;
        }

        .hero-summary-value{
            font-size:44px;
        }

        .chart-wrap-main{
            height:290px;
        }

        .chart-wrap-detail{
            height:300px;
        }
    }

    @media (max-width: 767px){
        .section-title{
            font-size:24px;
        }

        .hero-card{
            padding:18px;
        }

        .hero-summary-card{
            min-height:auto;
        }
    }
</style>
@endpush

@section('content')
@php
    $role = auth()->user()->role ?? null;

    $dashboardStatsRoute = $role === 'admin'
        ? route('admin.dashboard.stats')
        : route('hcm.dashboard.stats');

    $safeStaffProfesional = $staffProfesional ?? [
        'Core' => 0,
        'Subcore' => 0,
        'Support' => 0,
    ];

    $safeLokasiKerja = $lokasiKerja ?? [
        'Jakarta' => 0,
        'Pekanbaru' => 0,
        'Zamrud' => 0,
        'Pedada' => 0,
        'West Area' => 0,
    ];

    $dashboardInitialData = [
        'totalPegawai' => (int) ($totalPegawai ?? 0),
        'totalJabatan' => (int) ($totalJabatan ?? 0),
        'jumlahDepartemen' => (int) ($jumlahDepartemen ?? 0),
        'jumlahLokasiAktif' => (int) ($jumlahLokasiAktif ?? 0),

        'hubunganKerjaDominan' => $hubunganKerjaDominan ?? '-',
        'jumlahHubunganKerjaDominan' => (int) ($jumlahHubunganKerjaDominan ?? 0),

        'pwt' => (int) ($pwt ?? 0),
        'pwtt' => (int) ($pwtt ?? 0),

        'manajerial' => (int) ($manajerial ?? 0),
        'staffUtama' => (int) ($staffUtama ?? 0),
        'staffMadya' => (int) ($staffMadya ?? 0),
        'staffBiasa' => (int) ($staffBiasa ?? 0),

        'profCore' => (int) ($profCore ?? ($safeStaffProfesional['Core'] ?? 0)),
        'profSubcore' => (int) ($profSubcore ?? ($safeStaffProfesional['Subcore'] ?? 0)),
        'profSupport' => (int) ($profSupport ?? ($safeStaffProfesional['Support'] ?? 0)),

        'staffProfesional' => [
            'Core' => (int) ($safeStaffProfesional['Core'] ?? 0),
            'Subcore' => (int) ($safeStaffProfesional['Subcore'] ?? 0),
            'Support' => (int) ($safeStaffProfesional['Support'] ?? 0),
        ],

        'statusPegawaiLabels' => $statusPegawaiLabels ?? [
            'Manajerial',
            'Staf Utama',
            'Staf Madya',
            'Staf Biasa',
        ],

        'statusPegawaiData' => $statusPegawaiData ?? [
            (int) ($manajerial ?? 0),
            (int) ($staffUtama ?? 0),
            (int) ($staffMadya ?? 0),
            (int) ($staffBiasa ?? 0),
        ],

        'lokasiKerja' => [
            'Jakarta' => (int) ($safeLokasiKerja['Jakarta'] ?? 0),
            'Pekanbaru' => (int) ($safeLokasiKerja['Pekanbaru'] ?? 0),
            'Zamrud' => (int) ($safeLokasiKerja['Zamrud'] ?? 0),
            'Pedada' => (int) ($safeLokasiKerja['Pedada'] ?? 0),
            'West Area' => (int) ($safeLokasiKerja['West Area'] ?? 0),
        ],
    ];
@endphp

<div class="container-fluid px-0 dashboard-page">
    <div class="dashboard-head">
        <div>
            <h3 class="section-title">Dashboard</h3>
            <p class="section-subtitle">
                Ringkasan data kepegawaian berdasarkan informasi yang sudah tersimpan di database.
            </p>
        </div>

        <div class="sync-badge">
            <span class="sync-dot"></span>
            <span>Realtime database</span>
        </div>
    </div>

    <div class="hero-card">
        <div class="dashboard-hero-title">
            <h4>Informasi Utama Kepegawaian</h4>
        </div>

        <div class="hero-summary-grid">
            <div class="hero-summary-card">
                <div>
                    <div class="hero-summary-label">Jumlah Seluruh Pegawai</div>
                    <div class="hero-summary-value" id="totalPegawaiText">{{ $totalPegawai ?? 0 }}</div>
                </div>
                <div class="hero-summary-note">
                    Total seluruh data pegawai yang sudah tersimpan pada database karyawan.
                </div>
            </div>

            <div class="hero-summary-card">
                <div>
                    <div class="hero-summary-label">Jumlah Departemen</div>
                    <div class="hero-summary-value" id="jumlahDepartemenText">{{ $jumlahDepartemen ?? 0 }}</div>
                </div>
                <div class="hero-summary-note">
                    Total departemen unik yang sudah terisi pada data pegawai.
                </div>
            </div>

            <div class="hero-summary-card">
                <div>
                    <div class="hero-summary-label">Total Jabatan</div>
                    <div class="hero-summary-value" id="totalJabatanText">{{ $totalJabatan ?? 0 }}</div>
                </div>
                <div class="hero-summary-note">
                    Total data jabatan yang sudah tersimpan pada sistem.
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="chart-box">
                <div class="chart-box-head">
                    <div>
                        <h5 class="chart-box-title">Hubungan Kerja</h5>
                    </div>
                </div>
                <div class="chart-box-body">
                    <div class="chart-wrap-main">
                        <canvas id="hkChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="chart-box">
                <div class="chart-box-head">
                    <div>
                        <h5 class="chart-box-title">Staf Profesional</h5>
                    </div>
                </div>
                <div class="chart-box-body">
                    <div class="chart-wrap-main">
                        <canvas id="profChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="chart-box">
                <div class="chart-box-head">
                    <div>
                        <h5 class="chart-box-title">Status Pegawai</h5>
                    </div>
                </div>
                <div class="chart-box-body">
                    <div class="chart-wrap-detail">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="chart-box">
                <div class="chart-box-head">
                    <div>
                        <h5 class="chart-box-title">Lokasi Kerja</h5>
                    </div>
                </div>
                <div class="chart-box-body">
                    <div class="chart-wrap-detail">
                        <canvas id="lokasiChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const statsUrl = @json($dashboardStatsRoute);
    const initialData = @json($dashboardInitialData);

    const softGreen  = '#6f7f59';
    const softGreen2 = '#87956f';
    const softGold   = '#f3c94b';
    const softRed    = '#d66b6b';
    const softOlive  = '#9aa67a';
    const ink        = '#334027';
    const gridColor  = 'rgba(51,64,39,.10)';

    Chart.defaults.font.family = 'Segoe UI, Arial, sans-serif';
    Chart.defaults.color = ink;

    const commonTooltip = {
        backgroundColor: '#1f2b16',
        titleColor: '#fff',
        bodyColor: '#fff',
        cornerRadius: 10,
        padding: 10
    };

    const horizontalBarOptions = {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: {
            legend: { display: false },
            tooltip: commonTooltip
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    precision: 0,
                    color: ink,
                    font: { size: 11 }
                },
                grid: { color: gridColor }
            },
            y: {
                ticks: {
                    color: ink,
                    font: { size: 11, weight: '700' }
                },
                grid: { display: false }
            }
        }
    };

    const doughnutLabelPlugin = {
        id: 'doughnutLabelPlugin',
        afterDraw(chart) {
            const config = chart.config.options.plugins.centerText;

            if (!config || !config.display) {
                return;
            }

            const { ctx, chartArea } = chart;

            if (!chartArea) {
                return;
            }

            const x = (chartArea.left + chartArea.right) / 2;
            const y = (chartArea.top + chartArea.bottom) / 2;

            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';

            ctx.fillStyle = '#4f5e41';
            ctx.font = '700 12px Segoe UI';
            ctx.fillText(config.label || '', x, y - 10);

            ctx.fillStyle = '#1f2b16';
            ctx.font = '800 26px Segoe UI';
            ctx.fillText(String(config.value ?? 0), x, y + 14);

            ctx.restore();
        }
    };

    Chart.register(doughnutLabelPlugin);

    let hkChart = null;
    let profChart = null;
    let statusChart = null;
    let lokasiChart = null;

    function toNumber(value) {
        const numberValue = Number(value);

        return Number.isFinite(numberValue) ? numberValue : 0;
    }

    function getProfessionalCounts(data) {
        const source = data.staffProfesional || {};

        return {
            Core: toNumber(source.Core ?? data.profCore ?? 0),
            Subcore: toNumber(source.Subcore ?? data.profSubcore ?? 0),
            Support: toNumber(source.Support ?? data.profSupport ?? 0)
        };
    }

    function getProfessionalLabels() {
        return ['Core', 'Subcore', 'Support'];
    }

    function getProfessionalValues(data) {
        const counts = getProfessionalCounts(data);

        return [
            counts.Core,
            counts.Subcore,
            counts.Support
        ];
    }

    function getProfessionalTotal(data) {
        return getProfessionalValues(data).reduce((sum, value) => {
            return sum + toNumber(value);
        }, 0);
    }

    function getHubunganKerjaTotal(data) {
        return toNumber(data.pwt) + toNumber(data.pwtt);
    }

    function createCharts(data) {
        const hkEl = document.getElementById('hkChart');

        if (hkEl) {
            hkChart = new Chart(hkEl, {
                type: 'doughnut',
                data: {
                    labels: ['PWT', 'PWTT'],
                    datasets: [{
                        data: [
                            toNumber(data.pwt),
                            toNumber(data.pwtt)
                        ],
                        backgroundColor: [softRed, softGold],
                        borderWidth: 0,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                boxWidth: 10,
                                padding: 18,
                                color: ink,
                                font: { weight: '700' }
                            }
                        },
                        tooltip: commonTooltip,
                        centerText: {
                            display: true,
                            label: 'Total Hubungan Kerja',
                            value: String(getHubunganKerjaTotal(data))
                        }
                    }
                }
            });
        }

        const profEl = document.getElementById('profChart');

        if (profEl) {
            profChart = new Chart(profEl, {
                type: 'doughnut',
                data: {
                    labels: getProfessionalLabels(),
                    datasets: [{
                        data: getProfessionalValues(data),
                        backgroundColor: [softGreen2, softOlive, softGold],
                        borderWidth: 0,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                boxWidth: 10,
                                padding: 18,
                                color: ink,
                                font: { weight: '700' }
                            }
                        },
                        tooltip: commonTooltip,
                        centerText: {
                            display: true,
                            label: 'Total Profesional',
                            value: String(getProfessionalTotal(data))
                        }
                    }
                }
            });
        }

        const statusEl = document.getElementById('statusChart');

        if (statusEl) {
            statusChart = new Chart(statusEl, {
                type: 'bar',
                data: {
                    labels: data.statusPegawaiLabels || [],
                    datasets: [{
                        label: 'Jumlah Pegawai',
                        data: data.statusPegawaiData || [],
                        backgroundColor: softGreen,
                        borderRadius: 0,
                        borderSkipped: false,
                        barThickness: 22
                    }]
                },
                options: horizontalBarOptions
            });
        }

        const lokEl = document.getElementById('lokasiChart');

        if (lokEl) {
            lokasiChart = new Chart(lokEl, {
                type: 'bar',
                data: {
                    labels: ['Jakarta', 'Pekanbaru', 'Zamrud', 'Pedada', 'West Area'],
                    datasets: [{
                        label: 'Jumlah Pegawai',
                        data: [
                            toNumber(data.lokasiKerja?.Jakarta),
                            toNumber(data.lokasiKerja?.Pekanbaru),
                            toNumber(data.lokasiKerja?.Zamrud),
                            toNumber(data.lokasiKerja?.Pedada),
                            toNumber(data.lokasiKerja?.['West Area'])
                        ],
                        backgroundColor: softGreen,
                        borderRadius: 0,
                        borderSkipped: false,
                        barThickness: 22
                    }]
                },
                options: horizontalBarOptions
            });
        }
    }

    function updateText(data) {
        const setText = (id, value) => {
            const el = document.getElementById(id);

            if (el) {
                el.textContent = toNumber(value);
            }
        };

        setText('totalPegawaiText', data.totalPegawai);
        setText('totalJabatanText', data.totalJabatan);
        setText('jumlahDepartemenText', data.jumlahDepartemen);
    }

    function updateCharts(data) {
        if (hkChart) {
            hkChart.data.datasets[0].data = [
                toNumber(data.pwt),
                toNumber(data.pwtt)
            ];

            hkChart.options.plugins.centerText.value = String(getHubunganKerjaTotal(data));
            hkChart.update();
        }

        if (profChart) {
            profChart.data.labels = getProfessionalLabels();
            profChart.data.datasets[0].data = getProfessionalValues(data);
            profChart.options.plugins.centerText.value = String(getProfessionalTotal(data));
            profChart.update();
        }

        if (statusChart) {
            statusChart.data.labels = data.statusPegawaiLabels || [];
            statusChart.data.datasets[0].data = data.statusPegawaiData || [];
            statusChart.update();
        }

        if (lokasiChart) {
            lokasiChart.data.datasets[0].data = [
                toNumber(data.lokasiKerja?.Jakarta),
                toNumber(data.lokasiKerja?.Pekanbaru),
                toNumber(data.lokasiKerja?.Zamrud),
                toNumber(data.lokasiKerja?.Pedada),
                toNumber(data.lokasiKerja?.['West Area'])
            ];

            lokasiChart.update();
        }
    }

    async function refreshDashboard() {
        try {
            const response = await fetch(statsUrl, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            updateText(data);
            updateCharts(data);
        } catch (error) {
            console.error('Gagal refresh dashboard:', error);
        }
    }

    createCharts(initialData);
    setInterval(refreshDashboard, 10000);
});
</script>
@endpush