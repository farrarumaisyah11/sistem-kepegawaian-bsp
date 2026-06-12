@php
    $holderCount = $node->pemangkuSaatIni ? $node->pemangkuSaatIni->count() : 0;
    $isFilled = $holderCount > 0;
    $hasChildren = $node->children && $node->children->count();
    $namaDepartemen = $node->departemenMaster->nama_departemen ?? $node->departemen ?? '-';
@endphp

<li>
    <div class="org-node-wrap">
        <div class="org-node {{ $isFilled ? 'is-filled' : 'is-vacant' }}">
            <div class="org-node-topline"></div>

            <div class="org-node-title">
                {{ $node->nama_jabatan ?? '-' }}
            </div>

            <div class="org-node-holder">
                @if($isFilled)
                    {{ $node->pemangkuSaatIni->pluck('nama')->join(', ') }}
                @else
                    <span class="org-vacant-text">Vacant</span>
                @endif
            </div>

            <div class="org-node-meta">
                <span class="org-node-dept">
                    {{ $namaDepartemen }}
                </span>

                <span class="org-node-count">
                    {{ $holderCount }}
                </span>
            </div>
        </div>

        @if(auth()->user()->role === 'hcm')
            <a href="{{ route('struktur-jabatan.show', $node->id_jabatan) }}" class="org-detail-link">
                Detail
            </a>
        @endif
    </div>

    @if($hasChildren)
        <ul>
            @foreach($node->children as $child)
                @include('struktur-jabatan.node', ['node' => $child])
            @endforeach
        </ul>
    @endif
</li>
