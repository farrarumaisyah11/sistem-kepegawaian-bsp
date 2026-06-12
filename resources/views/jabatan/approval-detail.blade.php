@extends('layouts.approval')
@section('title', 'Detail Job Description A4')

@section('content')
@php
    $version = $jabatan->pendingVersion ?: $jabatan->activeVersion;
    $source = $version ?: $jabatan;

    $formatTanggalIndonesia = function ($date) {
        if (!$date) return '-';
        try {
            return \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('d F Y H:i');
        } catch (\Throwable $e) {
            return $date;
        }
    };

    $toList = function ($value) {
        if (!$value) return [];
        if (is_array($value)) return array_values(array_filter(array_map('trim', $value)));
        $decoded = json_decode((string) $value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_values(array_filter(array_map('trim', $decoded)));
        }
        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $value))));
    };

    $watermark = (auth()->user()->username ?? auth()->user()->name ?? auth()->user()->nama ?? 'USER') . ' · ' . now()->format('Y-m-d H:i:s');
@endphp

<style>
    body { background:#e5e7eb !important; }
    .approval-a4-toolbar { max-width:210mm; margin:0 auto 14px; display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; }
    .approval-a4-page { width:210mm; min-height:297mm; margin:0 auto; background:#fff; color:#111827; padding:18mm 16mm; box-shadow:0 16px 42px rgba(15,23,42,.18); position:relative; overflow:hidden; user-select:none; -webkit-user-select:none; }
    .approval-a4-page::before { content:"{{ $watermark }}"; position:fixed; top:45%; left:50%; transform:translate(-50%,-50%) rotate(-28deg); font-size:34px; font-weight:900; letter-spacing:1px; color:rgba(39,57,87,.055); white-space:nowrap; z-index:0; pointer-events:none; }
    .a4-content { position:relative; z-index:1; }
    .a4-header { border-bottom:3px solid #273957; padding-bottom:12px; margin-bottom:14px; display:flex; justify-content:space-between; gap:14px; }
    .a4-company { font-size:12px; font-weight:900; color:#6b775c; letter-spacing:1px; text-transform:uppercase; }
    .a4-title { font-size:22px; font-weight:900; color:#273957; margin:4px 0; text-transform:uppercase; }
    .a4-subtitle { font-size:11px; color:#6b7280; font-weight:700; }
    .a4-status { text-align:right; font-size:11px; font-weight:800; }
    .a4-section-title { margin-top:14px; background:#273957; color:#fff; padding:7px 9px; font-size:12px; font-weight:900; text-transform:uppercase; }
    .a4-table { width:100%; border-collapse:collapse; font-size:11px; }
    .a4-table th, .a4-table td { border:1px solid #d1d5db; padding:7px 8px; vertical-align:top; }
    .a4-table th { width:32%; background:#f3f4f6; color:#374151; text-align:left; }
    .a4-list { margin:7px 0 0 18px; padding:0; font-size:11px; }
    .a4-list li { margin-bottom:4px; }
    .a4-footer { margin-top:18px; display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    .a4-sign { min-height:78px; border:1px solid #d1d5db; padding:9px; font-size:11px; }
    .a4-sign strong { display:block; margin-bottom:8px; color:#273957; }
    @media print { body { background:#fff !important; } .approval-a4-toolbar { display:none !important; } .approval-a4-page { box-shadow:none; margin:0; width:210mm; min-height:297mm; padding:15mm; } }
</style>

<div class="approval-a4-toolbar no-print">
    <a href="{{ route('jabatan.approval.scan', ['jabatan' => $jabatan->id_jabatan, 'token' => $token]) }}" class="approval-btn">Kembali</a>
    <button type="button" class="approval-btn primary" onclick="window.print()">Print / Download PDF</button>
</div>

<div class="approval-a4-page" oncontextmenu="return false;">
    <div class="a4-content">
        <div class="a4-header">
            <div>
                <div class="a4-company">PT Bumi Siak Pusako</div>
                <div class="a4-title">Job Description</div>
                <div class="a4-subtitle">Dokumen approval elektronik · akses terbatas</div>
            </div>
            <div class="a4-status">
                Status:<br>
                @if($jabatan->is_approval_final)
                    APPROVED FINAL
                @elseif($jabatan->is_waiting_hcm_final)
                    MENUNGGU FINAL HCM
                @else
                    PENDING APPROVAL
                @endif
                <br><br>
                Versi: {{ $version ? $version->version_number : '-' }}
            </div>
        </div>

        <div class="a4-section-title">Identitas Jabatan</div>
        <table class="a4-table">
            <tr><th>Nama Jabatan</th><td>{{ $source->nama_jabatan ?? '-' }}</td></tr>
            <tr><th>Departemen</th><td>{{ $source->departemen ?? $jabatan->departemenMaster->nama_departemen ?? '-' }}</td></tr>
            <tr><th>Parent Jabatan</th><td>{{ $jabatan->parent?->nama_jabatan ?? $source->parent_jabatan ?? '-' }}</td></tr>
            <tr><th>Golongan Jabatan</th><td>{{ $source->gol_jabatan ?? '-' }}</td></tr>
            <tr><th>Home Base</th><td>{{ $source->home_base ?? '-' }}</td></tr>
            <tr><th>Lokasi Kerja</th><td>{{ $source->lokasi_kerja ?? '-' }}</td></tr>
        </table>

        <div class="a4-section-title">Tujuan Jabatan</div>
        <table class="a4-table"><tr><td>{{ $source->tujuan_jabatan ?? '-' }}</td></tr></table>

        <div class="a4-section-title">Tanggung Jawab</div>
        @php($items = $toList($source->tanggung_jawab ?? null))
        @if(count($items))
            <ol class="a4-list">@foreach($items as $item)<li>{{ $item }}</li>@endforeach</ol>
        @else
            <table class="a4-table"><tr><td>-</td></tr></table>
        @endif

        <div class="a4-section-title">Tantangan Jabatan</div>
        @php($items = $toList($source->tantangan_jabatan ?? null))
        @if(count($items))
            <ol class="a4-list">@foreach($items as $item)<li>{{ $item }}</li>@endforeach</ol>
        @else
            <table class="a4-table"><tr><td>-</td></tr></table>
        @endif

        <div class="a4-section-title">Dimensi & Relasi Kerja</div>
        <table class="a4-table">
            <tr><th>Dimensi Keuangan</th><td>{{ $source->dim_keuangan ?? '-' }}</td></tr>
            <tr><th>Dimensi Non Keuangan</th><td>{{ $source->dim_nonkeuangan ?? '-' }}</td></tr>
            <tr><th>Bawahan Langsung</th><td>{{ $source->bawahan_langsung ?? '-' }}</td></tr>
            <tr><th>Internal Perusahaan</th><td>{{ $source->internal_perusahaan ?? '-' }}</td></tr>
            <tr><th>External Perusahaan</th><td>{{ $source->external_perusahaan ?? '-' }}</td></tr>
        </table>

        <div class="a4-section-title">Kompetensi</div>
        <table class="a4-table">
            <tr><th>Pengetahuan & Keterampilan</th><td>{{ $source->pengetahuan_keterampilan ?? '-' }}</td></tr>
            <tr><th>Kompetensi</th><td>{{ $source->kompetensi ?? '-' }}</td></tr>
            <tr><th>Syarat Kompetensi Jabatan</th><td>{{ $source->syarat_kompetensi_jabatan ?? '-' }}</td></tr>
        </table>

        <div class="a4-section-title">Approval</div>
        <table class="a4-table">
            <tr><th>Approval Awal</th><td>{{ $jabatan->proposed_approved_by_name ?? '-' }} · {{ $jabatan->proposed_approved_by_jabatan ?? '-' }} · {{ $formatTanggalIndonesia($jabatan->proposed_approved_at ?? null) }}</td></tr>
            <tr><th>Final HCM</th><td>{{ $jabatan->hcm_confirmed_by_name ?? '-' }} · {{ $formatTanggalIndonesia($jabatan->hcm_confirmed_at ?? null) }}</td></tr>
            <tr><th>Catatan Approval</th><td>{{ $jabatan->approval_catatan ?? '-' }}</td></tr>
            <tr><th>Catatan HCM</th><td>{{ $jabatan->hcm_confirmation_catatan ?? '-' }}</td></tr>
        </table>

        <div class="a4-footer">
            <div class="a4-sign">
                <strong>Approver Awal</strong>
                {{ $jabatan->proposed_approved_by_name ?? '-' }}<br>
                {{ $jabatan->proposed_approved_by_jabatan ?? '-' }}
            </div>
            <div class="a4-sign">
                <strong>Final Approval HCM</strong>
                {{ $jabatan->hcm_confirmed_by_name ?? '-' }}<br>
                {{ $formatTanggalIndonesia($jabatan->hcm_confirmed_at ?? null) }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('contextmenu', e => e.preventDefault());
document.addEventListener('keydown', function(e){
    const key = (e.key || '').toLowerCase();
    if ((e.ctrlKey || e.metaKey) && ['s','u','c'].includes(key)) {
        e.preventDefault();
    }
});
</script>
@endpush
