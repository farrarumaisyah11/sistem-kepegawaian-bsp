@php
    // pakai logo dari DB kalau ada, kalau tidak pakai fallback di public/img
    $left  = !empty($j->logo_kiri)  ? asset('storage/'.$j->logo_kiri)  : asset('images/logo skk migas.png');
    $right = !empty($j->logo_kanan) ? asset('storage/'.$j->logo_kanan) : asset('images\logo bsp.png');
@endphp

<div class="corp">
  <div class="row1">
    <div class="left"><img src="{{ $left }}" alt="SKK Migas" style="height:34px;object-fit:contain"></div>
    <div class="company" style="text-align:center;font-weight:800;color:#111827">PT. BUMI SIAK PUSAKO</div>
    <div class="right" style="text-align:right"><img src="{{ $right }}" alt="BSP" style="height:34px;object-fit:contain"></div>
  </div>

  <div class="row2"
       style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;margin-top:6px;border:1px solid #d9dde6;border-radius:8px;overflow:hidden;background:#fff">
    <div style="border-right:1px solid #e5e7eb;padding:6px 8px">
      <span class="lab" style="color:#6b7280;font-weight:600;display:block">Jabatan</span>
      <span class="val" style="font-weight:800;color:#111827">{{ $j->nama_jabatan ?? '-' }}</span>
    </div>
    <div style="border-right:1px solid #e5e7eb;padding:6px 8px">
      <span class="lab" style="color:#6b7280;font-weight:600;display:block">Gol Jabatan</span>
      <span class="val" style="font-weight:800;color:#111827">{{ $j->gol_jabatan ?? '-' }}</span>
    </div>
    <div style="border-right:1px solid #e5e7eb;padding:6px 8px">
      <span class="lab" style="color:#6b7280;font-weight:600;display:block">Tmt Berlaku</span>
      <span class="val" style="font-weight:800;color:#111827">{{ $j->tmt_berlaku ?? '-' }}</span>
    </div>
    <div style="padding:6px 8px">
      <span class="lab" style="color:#6b7280;font-weight:600;display:block">Revisi ke</span>
      <span class="val" style="font-weight:800;color:#111827">{{ $j->revisi_ke ?? '0' }}</span>
    </div>
  </div>
</div>
