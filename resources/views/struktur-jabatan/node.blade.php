@php
    $depth = $depth ?? 0;

    $holderCount = $node->pemangkuSaatIni ? $node->pemangkuSaatIni->count() : 0;
    $isFilled = $holderCount > 0;
    $hasChildren = $node->children && $node->children->count();

    $namaDepartemen = $node->departemenMaster->nama_departemen ?? $node->departemen ?? '-';
    $holders = $isFilled ? $node->pemangkuSaatIni->pluck('nama')->filter()->values() : collect();
@endphp

<li>
    <div class="org-node-wrap">
        <div class="org-node {{ $isFilled ? 'is-filled' : 'is-vacant' }}">
            <div class="org-node-topbar">
                <span class="org-node-status {{ $isFilled ? 'filled' : 'vacant' }}">
                    <span class="org-status-dot"></span>
                    {{ $isFilled ? 'Terisi' : 'Vacant' }}
                </span>
            </div>

            <div class="org-node-title">
                {{ $node->nama_jabatan ?? '-' }}
            </div>

            <div class="org-node-holder">
                @if($isFilled)
                    <div class="org-holder-list">
                        @foreach($holders->take(3) as $namaPemangku)
                            <span>{{ $namaPemangku }}</span>
                        @endforeach

                        @if($holders->count() > 3)
                            <span class="org-more-holder">
                                +{{ $holders->count() - 3 }} pemangku lain
                            </span>
                        @endif
                    </div>
                @else
                    <span class="org-vacant-text">
                        Belum Terisi
                    </span>
                @endif
            </div>

            <div class="org-node-meta">
                <span class="org-node-dept">
                    {{ $namaDepartemen }}
                </span>

                <span class="org-node-count">
                    <span>
                        {{ $holderCount }}
                        <small>orang</small>
                    </span>
                </span>
            </div>
        </div>

        @auth
            <a href="{{ route('struktur-jabatan.show', $node->id_jabatan) }}" class="org-detail-link">
                Detail
            </a>
        @endauth
    </div>

    @if($hasChildren)
        <ul>
            @foreach($node->children as $child)
                @include('struktur-jabatan.node', ['node' => $child, 'depth' => $depth + 1])
            @endforeach
        </ul>
    @endif
</li>