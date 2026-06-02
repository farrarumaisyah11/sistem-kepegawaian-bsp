{{-- resources/views/jabatan/print.blade.php --}}
@extends('layouts.print')

@section('title', 'Job Description - '.$jabatan->nama_jabatan)

@section('head')
<style>
  :root{
    /* font formal (aman DomPDF/print) */
    --font-body: Calibri, "Segoe UI", Helvetica, Arial, "DejaVu Sans", sans-serif;
    --font-head: "Segoe UI Semibold", Calibri, Helvetica, Arial, "DejaVu Sans", sans-serif;

    /* palet brand (tanpa gradasi) */
    --ink:#0f172a; --muted:#64748b; --line:#e2e8f0;

    --brand:#6b775c;
    --brand-700:#505a45;   /* teks/aksen gelap */
    --brand-300:#ccd6c6;   /* garis lembut */
    --brand-200:#d9e2d1;
    --brand-150:#e3e9dd;   /* border lembut */
    --brand-100:#eef2ea;   /* panel lembut */
    --brand-050:#f5f7f3;   /* panel paling lembut */

    --band: var(--brand-100);      /* title band */
    --band-border: var(--brand-150);
    --hint-bg: var(--brand-100);   /* label tabel */
    --hint-br: var(--brand-150);

    /* tinggi header korporat ketika print (repeat) */
    --corp-h: 34mm;
  }

  /* ===== Latar & kertas A4 ===== */
  .doc-bg{ background:#f5f7f9; min-height:100vh; color:var(--ink); font-family:var(--font-body); }
  .a4-doc, .paper-a4{ width:210mm; margin:0 auto; background:#fff; border:1px solid var(--line); font-family:var(--font-body); }
  .page{ width:210mm; min-height:297mm; background:#fff; color:var(--ink); }
  .px-sheet, .px-paper{ padding-left:18mm; padding-right:18mm; }

  /* ===== Header perusahaan (sticky di layar, fixed & repeat saat print) ===== */
  .corp{
    position: sticky; top:0; z-index:50; background:#fff;
    margin:12mm 18mm 6mm; border:1px solid var(--brand-150); border-radius:10px; padding:6px 8px;
  }
  .corp .row1{ display:grid; grid-template-columns:120px 1fr 120px; align-items:center; gap:8px; }
  .corp .row1 img{ height:34px; object-fit:contain; }
  .corp .row1 .company{ text-align:center; font-family:var(--font-head); font-weight:700; color:var(--brand-700); letter-spacing:.1px; }
  .corp .row2{
    display:grid; grid-template-columns:repeat(4,1fr); margin-top:6px;
    border:1px solid var(--brand-150); border-radius:8px; overflow:hidden; background:#fff; font-size:11pt;
  }
  .corp .row2 > div{ border-right:1px solid var(--brand-150); padding:6px 8px; }
  .corp .row2 > div:last-child{ border-right:0; }
  .corp .row2 .lab{ color:#5b6a55; font-weight:600; display:block; }
  .corp .row2 .val{ font-weight:600; color:var(--brand-700); }

  /* ===== Title band ===== */
  .title-band{
    background:var(--band);
    border:1px solid var(--band-border);
    border-radius:12px;
    padding:14px 16px;
    text-align:center;
  }
  .title-main{ font-family:var(--font-head); font-weight:700; font-size:22pt; line-height:1.05; letter-spacing:.2px; color:var(--brand-700); }
  .title-sub{ font-weight:600; color:#475569; margin-top:4px; font-size:11pt; }

  /* ===== Section headings & blocks ===== */
  .sec-title{
    display:flex; align-items:center; gap:.5rem;
    font-family:var(--font-head); font-weight:700; font-size:12.5pt;
    margin:18px 0 10px; padding-bottom:8px;
    border-bottom:2px solid var(--brand-150);
    color:#0f172a;
    break-after: avoid; page-break-after: avoid;
  }
  .sec-ico{ font-size:1rem; color:var(--brand-700); }
  .subcap{ font-family:var(--font-head); font-weight:600; margin-bottom:6px; color:#0f172a; }
  .box{
    border:1px solid var(--brand-150);
    background:#fff;
    border-radius:10px;
    padding:12px 14px;
    line-height:1.6;
    break-inside: avoid; page-break-inside: avoid;
  }

  /* ===== Tabel ===== */
  .table{ font-size:11pt; width:100%; border-collapse:separate; border-spacing:0; }
  .table.table-bordered td, .table.table-bordered th{ border:1px solid var(--line); padding:6px 8px; vertical-align:middle; }
  .meta th{
    width:22%;
    background:var(--hint-bg);
    border-color:var(--hint-br) !important;
    color:var(--brand-700);
    text-transform:uppercase; letter-spacing:.04em; font-size:10pt; font-weight:700; vertical-align:middle;
    text-align:left;
  }
  .meta td{ font-weight:600; font-size:11pt; }
  .dim .dim-label{
    width:35%;
    background:var(--hint-bg);
    border-right:1px solid var(--hint-br);
    font-weight:700; color:var(--brand-700);
  }
  .dim td{ vertical-align:top; }

  /* ===== List ===== */
  .list-num{ margin:0; padding-left:18px; font-size:11pt; }
  .list-num>li{ margin-bottom:7px; break-inside:avoid; }

  .footnote{ color:var(--muted); font-size:10.5pt; }

  /* ===== Print overrides ===== */
  @media print{
    .d-print-none{ display:none !important; }
    .doc-bg{ background:#fff !important; }
    .a4-doc, .paper-a4{ border:0; box-shadow:none; padding-top: var(--corp-h); }
    .corp{
      position: fixed; top:0; left:0; right:0; margin:0; border-radius:0;
      border-width:0 0 1px 0; padding:8px 18mm; height: var(--corp-h);
      border-color: var(--brand-150);
    }
    .corp .row1{ grid-template-columns:120px 1fr 120px; }
    .paper-a4{ page-break-after:auto; }
  }
</style>
@endsection

@section('content')
@php($j = $jabatan)
<div class="doc-bg py-4">

  {{-- A4 single sheet --}}
  <div class="paper-a4 shadow-lg rounded-4 position-relative">

    {{-- ===== Header perusahaan (logo SKK Migas kiri, BSP kanan, meta bar) ===== --}}
    @includeIf('jabatan._corp_header', ['j' => $j])

    {{-- ===== Title band ===== --}}
    <div class="px-paper pt-3 pb-1">
      <div class="title-band">
        <div class="title-main">JOB DESCRIPTION</div>
      </div>
    </div>

    {{-- ===== Meta utama ===== --}}
    <div class="px-paper pt-3">
      <div class="table-responsive">
        <table class="table table-bordered align-middle m-0 meta">
          <tbody>
          <tr>
            <th><i class="bi bi-briefcase me-1"></i>Jabatan</th>
            <td class="fw-semibold">{{ $j->nama_jabatan ?? '-' }}</td>
            <th><i class="bi bi-grid-3x3-gap me-1"></i>Departemen</th>
            <td class="fw-semibold">{{ $j->departemen ?? '-' }}</td>
          </tr>
          <tr>
            <th><i class="bi bi-shield-check me-1"></i>Golongan</th>
            <td class="fw-semibold">{{ $j->gol_jabatan ?? '-' }}</td>
            <th><i class="bi bi-house-door me-1"></i>Home Base</th>
            <td class="fw-semibold">{{ $j->home_base ?? '-' }}</td>
          </tr>
          <tr>
            <th><i class="bi bi-geo-alt me-1"></i>Lokasi Kerja</th>
            <td class="fw-semibold">{{ $j->lokasi_kerja ?? '-' }}</td>
            <th><i class="bi bi-diagram-3 me-1"></i>Parent Jabatan</th>
            <td class="fw-semibold">{{ $j->parent_jabatan ?? '-' }}</td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>

    {{-- ===== BODY: berjalan terus ===== --}}
    <div class="px-paper py-4">

      {{-- JOB SUMMARY --}}
      <h5 class="sec-title"><i class="bi bi-file-earmark-text sec-ico"></i> JOB SUMMARY</h5>
      <div class="box">{!! nl2br(e($j->tujuan_jabatan)) !!}</div>

      {{-- RESPONSIBILITIES --}}
      <h5 class="sec-title"><i class="bi bi-ui-checks sec-ico"></i> JOB RESPONSIBILITIES</h5>
      <div class="box">
        <ol class="list-num">
          @forelse(($j->tanggung_jawab ?? '') ? preg_split('/\r\n|\r|\n/', $j->tanggung_jawab) : [] as $item)
            @if(trim($item)!=='') <li>{{ $item }}</li> @endif
          @empty
            <li>-</li>
          @endforelse
        </ol>
      </div>

      {{-- CHALLENGES --}}
      <h5 class="sec-title"><i class="bi bi-flag sec-ico"></i> ROLE CHALLENGES</h5>
      <div class="box">{!! nl2br(e($j->tantangan_jabatan)) !!}</div>

      {{-- DIMENSIONS --}}
      <h5 class="sec-title"><i class="bi bi-graph-up sec-ico"></i> ROLE DIMENSIONS</h5>
      <div class="table-responsive mb-3">
        <table class="table table-bordered align-middle m-0 dim">
          <tbody>
          <tr><td class="dim-label">Dimensi Keuangan</td><td>{{ $j->dim_keuangan ?? '-' }}</td></tr>
          <tr><td class="dim-label">Dimensi Non Keuangan</td><td>{{ $j->dim_nonkeuangan ?? '-' }}</td></tr>
          <tr><td class="dim-label">Bawahan Langsung</td><td>{{ $j->bawahan_langsung ?? '-' }}</td></tr>
          </tbody>
        </table>
      </div>

      {{-- RELATIONSHIPS --}}
      <h5 class="sec-title"><i class="bi bi-people sec-ico"></i> WORK RELATIONSHIPS</h5>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="box h-100">
            <div class="subcap">Internal Perusahaan</div>
            {!! nl2br(e($j->internal_perusahaan)) !!}
          </div>
        </div>
        <div class="col-md-6">
          <div class="box h-100">
            <div class="subcap">Eksternal Perusahaan</div>
            {!! nl2br(e($j->external_perusahaan)) !!}
          </div>
        </div>
      </div>

      {{-- AUTHORITIES --}}
      <h5 class="sec-title"><i class="bi bi-hammer sec-ico"></i> AUTHORITIES</h5>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="box h-100">
            <div class="subcap">Finansial</div>
            {!! nl2br(e($j->finansial ?? '-')) !!}
          </div>
        </div>
        <div class="col-md-6">
          <div class="box h-100">
            <div class="subcap">Non Finansial</div>
            {!! nl2br(e($j->non_finansial ?? '-')) !!}
          </div>
        </div>
      </div>

      {{-- QUALIFICATIONS --}}
      <h5 class="sec-title"><i class="bi bi-mortarboard sec-ico"></i> QUALIFICATIONS</h5>
      <div class="box mb-3">
        <div class="subcap">Pengetahuan &amp; Keterampilan</div>
        {!! nl2br(e($j->pengetahuan_keterampilan)) !!}
      </div>
      <div class="box mb-3">
        <div class="subcap">Kompetensi</div>
        {!! nl2br(e($j->kompetensi)) !!}
      </div>
      <div class="box mb-4">
        <div class="subcap">Syarat Kompetensi Jabatan</div>
        {!! nl2br(e($j->syarat_kompetensi_jabatan)) !!}
      </div>

      {{-- ORG CHART (opsional) --}}
      @if(!empty($j->struktur_file))
        <h5 class="sec-title"><i class="bi bi-diagram-3 sec-ico"></i> ORGANIZATION CHART</h5>
        <div class="box text-center">
          <img src="{{ asset('storage/'.$j->struktur_file) }}"
               alt="Struktur Organisasi"
               style="max-width:100%; max-height:560px; object-fit:contain;">
        </div>
      @endif

      <div class="footnote mt-4">
        Dicetak otomatis: {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d M Y H:i') }} • {{ config('app.company_name', 'PT. Bumi Siak Pusako') }}
      </div>
    </div>

  </div>
</div>
@endsection
