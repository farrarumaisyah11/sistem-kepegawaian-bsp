@php
    /*
    |--------------------------------------------------------------------------
    | Header Print / PDF
    |--------------------------------------------------------------------------
    | Markup dan class mengikuti jd-paper-header pada Detail Jabatan.
    | Dengan begitu tampilan kop untuk Print dan PDF tidak lagi berbeda
    | dari tampilan Detail Jabatan yang sudah disetujui.
    */
    $left  = !empty($j->logo_kiri)  ? asset('storage/'.$j->logo_kiri)  : asset('images/logo skk migas.png');
    $right = !empty($j->logo_kanan) ? asset('storage/'.$j->logo_kanan) : asset('images/logo bsp.png');

    $headerApprovalStatus = $approvalStatus ?? ($j->approval_status ?? 'pending');
    $headerIsFinalApproval = $isFinalApproval ?? (($j->approval_status ?? null) === 'approved' && ($j->approval_flow_status ?? null) === 'approved_final' && empty($j->draft_version_id));
    $headerIsWaitingHcmFinal = $isWaitingHcmFinal ?? (($j->approval_flow_status ?? null) === 'waiting_hcm_confirmation' && !empty($j->draft_version_id));

    if (isset($approvalStatusText)) {
        $headerApprovalText = $approvalStatusText;
    } elseif ($headerIsFinalApproval) {
        $headerApprovalText = 'Approved Final';
    } elseif ($headerIsWaitingHcmFinal) {
        $headerApprovalText = 'Menunggu Approval Final HCM';
    } else {
        $headerApprovalText = match ($headerApprovalStatus) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Pending Approval',
        };
    }

    if (isset($approvalStatusClass)) {
        $headerApprovalClass = $approvalStatusClass;
    } elseif ($headerIsFinalApproval || $headerApprovalStatus === 'approved') {
        $headerApprovalClass = 'approved';
    } elseif ($headerApprovalStatus === 'rejected') {
        $headerApprovalClass = 'rejected';
    } else {
        $headerApprovalClass = 'pending';
    }

    $headerIsApproved = $isApproved ?? ($headerIsFinalApproval || $headerApprovalStatus === 'approved');
    $headerApprovedByName = $approvedByName ?? $j->approved_by_name ?? $j->proposed_approved_by_name ?? '-';
    $headerApprovalDate = $approvalDate ?? '-';
@endphp

<div class="jd-paper-header" data-export-header="true">
    <div class="jd-header-grid">
        <div class="jd-logo-box">
            <img src="{{ $left }}" alt="SKK Migas">
        </div>

        <div class="jd-company-box">
            <div class="jd-company-name">PT. BUMI SIAK PUSAKO</div>
            <div class="jd-company-unit">SISTEM INFORMASI SUMBER DAYA MANUSIA</div>
            <div class="jd-company-address">
                Gedung Surya Dumai Lt. 6, Jl. Jendral Sudirman No. 395 Pekanbaru 28116
            </div>
            <div class="jd-company-contact">
                Telepon: (62-761) 855764 | Facsimile: (62-761) 855765 | Website: www.bsp.co.id
            </div>
        </div>

        <div class="jd-logo-box">
            <img src="{{ $right }}" alt="BSP">
        </div>
    </div>

    <div class="jd-title-wrap">
        <div class="jd-title">JOB DESCRIPTION</div>
        <div class="jd-subtitle">Laporan Data Jabatan</div>

        <div class="jd-approval-mini {{ $headerApprovalClass }}">
            <span class="jd-approval-dot"></span>
            @if($headerIsApproved)
                Approved oleh {{ $headerApprovedByName }} pada {{ $headerApprovalDate }}
            @else
                {{ $headerApprovalText }}
            @endif
        </div>
    </div>
</div>
