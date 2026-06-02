@php
    $departemenOptions = $departemenOptions ?? [
        'External Affairs',
        'Exploitation',
        'Corporate Secretary',
        'Human Resource Management',
        'Supply Chain Management',
        'Strategy, Planning & Risk Management',
        'Quality, Health, Safety & Environtment',
        'Exploration',
        'Finance & ICT',
        'Drilling & Workover',
        'Operation Support',
        'Production Operations',
        'Internal Audit',
        'General Manager',
        'Senior Operation',
        'Advisor',
    ];

    $jabatans = $jabatans ?? collect();
    $selectedDepartemen = $selectedDepartemen ?? '';
    $selectedIdJabatan = $selectedIdJabatan ?? '';

    $jabatanOptionsJson = collect($jabatans)->map(function ($item) {
        return [
            'id_jabatan'   => $item->id_jabatan ?? null,
            'nama_jabatan' => $item->nama_jabatan ?? '',
            'departemen'   => $item->departemen ?? '',
            'gol_jabatan'  => $item->gol_jabatan ?? '',
            'lokasi_kerja' => $item->lokasi_kerja ?? '',
        ];
    })->values()->toArray();
@endphp

<div class="col-md-6">
    <label class="form-label">Departemen</label>
    <select name="departemen" id="departemen_select" class="form-control">
        <option value="">Pilih Departemen</option>

        @if($selectedDepartemen && !in_array($selectedDepartemen, $departemenOptions))
            <option value="{{ $selectedDepartemen }}" selected>{{ $selectedDepartemen }}</option>
        @endif

        @foreach ($departemenOptions as $departemen)
            <option value="{{ $departemen }}" {{ $selectedDepartemen == $departemen ? 'selected' : '' }}>
                {{ $departemen }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-6">
    <label class="form-label">Jabatan</label>
    <select name="id_jabatan"
            id="id_jabatan_select"
            class="form-control"
            data-selected="{{ $selectedIdJabatan }}">
        <option value="">Pilih departemen terlebih dahulu</option>
    </select>
    <small class="text-muted">
        Jabatan hanya muncul sesuai departemen yang dipilih.
    </small>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const jabatanOptions = @json($jabatanOptionsJson);

    const departemenSelect = document.getElementById('departemen_select');
    const jabatanSelect = document.getElementById('id_jabatan_select');
    const golJabatanInput = document.querySelector('[name="gol_jabatan"]');
    const lokasiKerjaInput = document.querySelector('[name="lokasi_kerja"]');

    if (!departemenSelect || !jabatanSelect) return;

    function renderJabatanOptions(resetSelected = false) {
        const selectedDepartemen = departemenSelect.value;
        const selectedId = resetSelected ? '' : jabatanSelect.dataset.selected;

        jabatanSelect.innerHTML = '';

        if (!selectedDepartemen) {
            jabatanSelect.disabled = true;
            jabatanSelect.innerHTML = '<option value="">Pilih departemen terlebih dahulu</option>';
            return;
        }

        const filtered = jabatanOptions.filter(function (item) {
            return item.departemen === selectedDepartemen;
        });

        if (filtered.length === 0) {
            jabatanSelect.disabled = true;
            jabatanSelect.innerHTML = '<option value="">Belum ada jabatan pada departemen ini</option>';
            return;
        }

        jabatanSelect.disabled = false;
        jabatanSelect.insertAdjacentHTML('beforeend', '<option value="">Pilih Jabatan</option>');

        filtered.forEach(function (item) {
            const selected = String(item.id_jabatan) === String(selectedId) ? 'selected' : '';

            jabatanSelect.insertAdjacentHTML('beforeend', `
                <option value="${item.id_jabatan}" ${selected}
                        data-gol="${item.gol_jabatan || ''}"
                        data-lokasi="${item.lokasi_kerja || ''}">
                    ${item.nama_jabatan}
                </option>
            `);
        });

        syncJabatanDetail();
    }

    function syncJabatanDetail() {
        const selectedOption = jabatanSelect.options[jabatanSelect.selectedIndex];

        if (!selectedOption) return;

        const gol = selectedOption.getAttribute('data-gol') || '';
        const lokasi = selectedOption.getAttribute('data-lokasi') || '';

        if (golJabatanInput && gol) {
            golJabatanInput.value = gol;
        }

        if (lokasiKerjaInput && lokasi) {
            lokasiKerjaInput.value = lokasi;
        }
    }

    departemenSelect.addEventListener('change', function () {
        jabatanSelect.dataset.selected = '';
        renderJabatanOptions(true);
    });

    jabatanSelect.addEventListener('change', syncJabatanDetail);

    renderJabatanOptions(false);
});
</script>
@endpush