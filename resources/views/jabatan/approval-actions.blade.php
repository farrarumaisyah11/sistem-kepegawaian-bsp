@php
    $prefix = auth()->user()->role ?? 'hcm';
    $canManageApproval = in_array($prefix, ['admin', 'hcm'], true);
@endphp

<div class="d-flex gap-2 flex-wrap align-items-center justify-content-end">
    <a href="{{ route($prefix.'.jabatan.index') }}" class="btn btn-light">
        Kembali
    </a>

    @if($canManageApproval)
        <a href="{{ route($prefix.'.jabatan.approval-page', $jabatan->id_jabatan) }}" class="btn btn-outline-primary">
            Halaman Approval
        </a>
    @endif

    @if(($prefix === 'hcm') && ($jabatan->approval_flow_status ?? null) === 'waiting_hcm_confirmation' && $jabatan->pendingVersion)
        <form method="POST"
              action="{{ route('hcm.jabatan.approval.confirm-final-from-show', $jabatan->id_jabatan) }}"
              style="margin:0;"
              onsubmit="return finalHcmSubmitOnce(this);">
            @csrf
            <input type="hidden" name="hcm_confirmation_catatan" value="">
            <button type="submit" class="btn btn-success">
                Approve Final HCM
            </button>
        </form>
    @endif

    @if($jabatan->activeVersion && !$jabatan->pendingVersion && in_array($prefix, ['admin', 'hcm'], true))
        <form method="POST"
              action="{{ route($prefix.'.jabatan.apply-approved-version', $jabatan->id_jabatan) }}"
              style="margin:0;">
            @csrf
            <button type="submit"
                    class="btn btn-success"
                    onclick="return confirm('Terapkan versi approved terbaru ke seluruh pegawai aktif yang memegang jabatan ini? Riwayat versi lama pegawai tetap disimpan.');">
                Terapkan Job Description Terbaru ke Pegawai
            </button>
        </form>
    @endif
</div>

@if(($prefix === 'hcm') && ($jabatan->approval_flow_status ?? null) === 'waiting_hcm_confirmation' && $jabatan->pendingVersion)
    <div class="alert alert-warning mt-3 mb-0">
        Approval awal sudah dilakukan oleh
        <strong>{{ $jabatan->proposed_approved_by_name ?? '-' }}</strong>
        pada
        <strong>{{ optional($jabatan->proposed_approved_at)->locale('id')->translatedFormat('d F Y H:i') ?? '-' }}</strong>.
        Dokumen tinggal menunggu approval final dari HCM.
    </div>
@endif

@push('scripts')
<script>
function finalHcmSubmitOnce(form){
    const button = form.querySelector('button[type="submit"]');
    if (button) {
        button.disabled = true;
        button.textContent = 'Memproses Final Approval...';
    }
    return true;
}
</script>
@endpush
 