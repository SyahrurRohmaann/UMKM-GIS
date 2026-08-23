@extends('layouts.app')

@section('title', 'Peta Rekomendasi')

@push('styles')
<style>
    .ranking-item { 
        cursor: pointer; 
        transition: all 0.2s ease-in-out;
        border-left: 4px solid transparent;
    }
    .ranking-item:hover { 
        background: var(--color-background); 
        border-left-color: var(--color-primary);
        transform: translateX(4px);
    }
    
    .step-box {
        animation: fadeIn 0.4s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    #btnRecenter {
        position: absolute;
        bottom: 40px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
        background: white;
        border: 2px solid rgba(0,0,0,0.1);
        border-radius: 20px;
        padding: 8px 16px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        color: var(--color-foreground);
        font-size: 14px;
    }
    #btnRecenter.visible { opacity: 1; visibility: visible; }
    #btnRecenter:hover { background: #f8f9fa; }
    
    /* Progress indicator */
    .step-indicator {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        font-size: 12px;
        color: #6c757d;
    }
    .step-dot {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 8px;
        font-weight: bold;
    }
    .step-active .step-dot {
        background: var(--color-primary);
        color: white;
    }
    .step-active {
        color: var(--color-foreground);
        font-weight: 600;
    }
    
    /* Input & Select Focus override for Primary color */
    .form-select:focus, .form-control:focus {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 0.25rem rgba(5, 150, 105, 0.25);
    }
    
    /* Ensure disabled button has primary tint */
    .btn-primary:disabled {
        background-color: var(--color-primary);
        border-color: var(--color-primary);
        opacity: 0.65;
    }
</style>
@endpush

@section('sidebar')
    <h5 class="fw-bold text-dark mb-3">Sistem Pemilihan Lokasi</h5>
    
    <!-- STEP 1: AHP & JENIS USAHA -->
    <div id="step1-ahp" class="step-box">
        <div class="step-indicator step-active">
            <div class="step-dot">1</div> Setup Usaha
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Pilih Jenis Usaha</label>
            <select class="form-select shadow-sm border-primary" id="jenisUsaha" style="border-color: var(--color-primary);">
                <option value="" selected disabled>-- Pilih --</option>
                @foreach($jenisUsaha as $ju)
                    <option value="{{ $ju->id }}">{{ $ju->nama }}</option>
                @endforeach
            </select>
        </div>
        
        <button id="btnHitung" class="btn btn-primary bg-primary w-100 shadow-sm" style="border-color: var(--color-primary);" disabled>
            <span class="spinner-border spinner-border-sm d-none me-1" id="spinHitung"></span>
            Mulai Analisis
        </button>
    </div>

    <!-- STEP 2: PILIH MODE -->
    <div id="step2-mode" class="step-box d-none">
        <div class="step-indicator step-active">
            <div class="step-dot">2</div> Mode Pencarian
        </div>
        <div class="card border-0 shadow-sm mb-3" style="border: 1px solid var(--color-primary) !important;">
            <div class="card-body">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="radio" name="modePencarian" id="modeSistem" value="sistem" checked>
                    <label class="form-check-label fw-medium" for="modeSistem">
                        Rekomendasi Sistem
                        <small class="d-block text-muted">Sistem memilih 5 lokasi terbaik secara otomatis.</small>
                    </label>
                </div>
                <hr>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="modePencarian" id="modeManual" value="manual">
                    <label class="form-check-label fw-medium" for="modeManual">
                        Pilih Manual di Peta
                        <small class="d-block text-muted">Anda memilih maksimal 4 titik lokasi untuk dibandingkan.</small>
                    </label>
                </div>
            </div>
        </div>
        <button id="btnLanjutMode" class="btn btn-primary w-100 shadow-sm">Lanjutkan</button>
    </div>

    <!-- STEP 3: MANUAL SELECTION -->
    <div id="step3-manual" class="step-box d-none">
        <div class="step-indicator step-active">
            <div class="step-dot">3</div> Pilih Kandidat
        </div>
        <p class="small text-muted mb-2">Klik marker abu-abu di peta. Tahan <b>SHIFT + Klik Kiri</b> untuk lokasi baru.</p>
        
        <ul id="selectedLocationsList" class="list-group list-group-flush mb-3 small border-top border-bottom py-2">
            <!-- List goes here -->
        </ul>
        
        <div id="opsiIkutSistemBox" class="form-check mb-3 d-none">
            <input class="form-check-input" type="checkbox" id="chkIkutSistem" checked>
            <label class="form-check-label small" for="chkIkutSistem">
                Sertakan 3 rekomendasi sistem
            </label>
        </div>

        <button id="btnProsesManual" class="btn btn-success w-100 mb-2 shadow-sm" disabled>
            <span class="spinner-border spinner-border-sm d-none me-1" id="spinProsesManual"></span>
            Bandingkan
        </button>
    </div>

    <!-- STEP 4: RESULT -->
    <div id="step4-result" class="step-box d-none">
        <div class="step-indicator step-active">
            <div class="step-dot">4</div> Hasil
        </div>
        
        <ul class="list-group mb-3 shadow-sm border-0" id="rankingList">
            <!-- Ranking list generated by JS -->
        </ul>
    </div>

    <hr class="mt-4">
    <button id="btnReset" class="btn btn-outline-danger w-100 d-none">Mulai Ulang</button>

@endsection

@section('content')
    <div id="map" style="width: 100%; height: 100%; position: relative;"></div>
    <button id="btnRecenter" title="Kembali ke Sumbersari">
        <i class="bi bi-geo-fill text-primary"></i> Kembali ke Pusat
    </button>
@endsection

@stack('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const DEFAULT_CENTER = [-8.165, 113.716];
    const DEFAULT_ZOOM = 13;
    const map = L.map('map', { zoomControl: false }).setView(DEFAULT_CENTER, DEFAULT_ZOOM);
    L.control.zoom({ position: 'topright' }).addTo(map);
    
    // Minimalist CartoDB Positron
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; CARTO',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);

    fetch('/assets/geojson/sumbersari_admin.geojson')
        .then(response => response.json())
        .then(data => {
            L.geoJSON(data, {
                style: { color: '#10B981', weight: 2, opacity: 0.5, fillOpacity: 0.05, dashArray: '5, 5' },
                interactive: false
            }).addTo(map);
        }).catch(() => {});

    let markers = [];
    let bufferCircle = null;
    let allScoredLocations = [];
    let selectedManualIds = [];
    let customLocationsData = [];

    const btnRecenter = document.getElementById('btnRecenter');
    map.on('moveend', () => {
        if (map.getCenter().distanceTo(L.latLng(DEFAULT_CENTER[0], DEFAULT_CENTER[1])) > 2000) {
            btnRecenter.classList.add('visible');
        } else {
            btnRecenter.classList.remove('visible');
        }
    });
    btnRecenter.addEventListener('click', () => map.flyTo(DEFAULT_CENTER, DEFAULT_ZOOM));

    const createNumberedIcon = (number, isTop = false) => L.divIcon({
        className: 'custom-div-icon',
        html: `
            <div style="position: relative; width: 36px; height: 36px; transition: transform 0.2s;" class="marker-wrapper">
                <img src="/assets/marker.svg" style="width: 100%; height: 100%; filter: ${isTop ? 'hue-rotate(330deg)' : 'hue-rotate(200deg)'};" />
                <div style="position: absolute; top: 40%; left: 50%; transform: translate(-50%, -50%); color: white; font-weight: bold; font-family: 'Fira Code', monospace; font-size: 14px;">${number}</div>
            </div>`,
        iconSize: [36, 36],
        iconAnchor: [18, 36]
    });

    const createManualIcon = (selected = false) => L.divIcon({
        className: 'custom-div-icon',
        html: `<div style="width: 24px; height: 24px;"><img src="/assets/marker.svg" style="width: 100%; height: 100%; filter: ${selected ? 'hue-rotate(100deg) saturate(2)' : 'grayscale(1) opacity(0.5)'};" /></div>`,
        iconSize: [24, 24], iconAnchor: [12, 24]
    });

    function clearMap() {
        markers.forEach(m => map.removeLayer(m));
        if(bufferCircle) map.removeLayer(bufferCircle);
        markers = [];
    }

    // Step 1
    $('#jenisUsaha').change(function() { $('#btnHitung').prop('disabled', !$(this).val()); });

    $('#btnHitung').click(function() {
        const btn = $(this);
        btn.prop('disabled', true);
        $('#spinHitung').removeClass('d-none');
        $('#mapLoading').css('display', 'flex');

        $.ajax({
            url: '/api/recommendations/generate',
            method: 'POST',
            data: { jenis_usaha_id: $('#jenisUsaha').val() },
            success: function(recRes) {
                allScoredLocations = recRes.data;
                $('#step1-ahp').addClass('d-none');
                $('#step2-mode').removeClass('d-none');
                $('#btnReset').removeClass('d-none');
            },
            complete: function() {
                btn.prop('disabled', false);
                $('#spinHitung').addClass('d-none');
                $('#mapLoading').hide();
            }
        });
    });

    // Step 2
    $('#btnLanjutMode').click(function() {
        $('#step2-mode').addClass('d-none');
        if ($('input[name="modePencarian"]:checked').val() === 'sistem') {
            renderHasil(allScoredLocations.slice(0, 5));
        } else {
            $('#step3-manual').removeClass('d-none');
            tampilkanMarkerManual();
        }
    });

    // Step 3 (Manual)
    function tampilkanMarkerManual() {
        clearMap();
        selectedManualIds = [];
        updateManualUI();

        allScoredLocations.forEach(lokasi => {
            let marker = L.marker([lokasi.latitude, lokasi.longitude], { icon: createManualIcon(false) }).addTo(map);
            marker.bindTooltip(lokasi.nama, { offset: [0, -20] });
            marker.on('click', function() {
                const idx = selectedManualIds.indexOf(lokasi.id);
                if (idx > -1) {
                    selectedManualIds.splice(idx, 1);
                    marker.setIcon(createManualIcon(false));
                } else {
                    if (selectedManualIds.length < 4) {
                        selectedManualIds.push(lokasi.id);
                        marker.setIcon(createManualIcon(true));
                    } else { alert("Maksimal 4 lokasi!"); }
                }
                updateManualUI();
            });
            markers.push(marker);
        });
    }

    map.on('click', function(e) {
        if (!e.originalEvent.shiftKey || $('#step3-manual').hasClass('d-none')) return;
        if (selectedManualIds.length >= 4) return alert("Maksimal 4 lokasi!");
        $('#c_lat').val(e.latlng.lat);
        $('#c_lng').val(e.latlng.lng);
        $('#customLocModal').modal('show');
    });

    $('#btnSaveCustom').click(function() {
        const btn = $(this);
        btn.prop('disabled', true);
        $('#spinSaveCustom').removeClass('d-none');

        $.ajax({
            url: '/api/locations/simulate-score',
            method: 'POST',
            data: {
                lat: $('#c_lat').val(), lng: $('#c_lng').val(),
                jenis_usaha_id: $('#jenisUsaha').val(),
                nama_lokasi: $('#c_nama').val(),
                harga_sewa: $('#c_sewa').val(),
                skor_keamanan: $('#c_aman').val()
            },
            success: function(res) {
                $('#customLocModal').modal('hide');
                const newLoc = res.data;
                customLocationsData.push(newLoc);
                allScoredLocations.push(newLoc); 
                selectedManualIds.push(newLoc.id);
                
                let marker = L.marker([newLoc.latitude, newLoc.longitude], { icon: createManualIcon(true) }).addTo(map);
                marker.bindTooltip(newLoc.nama, { offset: [0, -20] });
                markers.push(marker);

                updateManualUI();
                map.flyTo([newLoc.latitude, newLoc.longitude], 15);
            },
            complete: function() {
                btn.prop('disabled', false);
                $('#spinSaveCustom').addClass('d-none');
            }
        });
    });

    function updateManualUI() {
        $('#selectedLocationsList').html(selectedManualIds.map(id => {
            const loc = allScoredLocations.find(l => l.id === id);
            return `<li class="list-group-item bg-transparent px-0 py-1 border-0"><i class="bi bi-geo-alt text-primary"></i> ${loc.nama}</li>`;
        }).join(''));

        $('#btnProsesManual').prop('disabled', selectedManualIds.length === 0);
        $('#opsiIkutSistemBox').toggleClass('d-none', selectedManualIds.length < 2);
    }

    $('#btnProsesManual').click(function() {
        const btn = $(this);
        btn.prop('disabled', true);
        $('#spinProsesManual').removeClass('d-none');
        $('#mapLoading').css('display', 'flex');

        let idsToCompare = selectedManualIds.filter(id => typeof id === 'number');
        if (selectedManualIds.length === 1 || $('#chkIkutSistem').is(':checked')) {
            idsToCompare.push(...allScoredLocations.filter(l => !selectedManualIds.includes(l.id)).slice(0, 3).map(l => l.id));
        }

        $.ajax({
            url: '/api/recommendations/generate',
            method: 'POST',
            data: {
                jenis_usaha_id: $('#jenisUsaha').val(),
                custom_locations: customLocationsData,
                selected_ids: idsToCompare
            },
            success: function(recRes) {
                let freshScores = recRes.data.map((loc, i) => ({
                    ...loc, ranking_komparasi: i + 1, is_pilihan_user: selectedManualIds.includes(loc.id)
                }));
                $('#step3-manual').addClass('d-none');
                renderHasil(freshScores, true);
            },
            complete: function() {
                btn.prop('disabled', false);
                $('#spinProsesManual').addClass('d-none');
                $('#mapLoading').hide();
            }
        });
    });

    // Step 4
    function renderHasil(dataLokasi, isManualMode = false) {
        clearMap();
        $('#step4-result').removeClass('d-none');
        
        let listHtml = '';
        dataLokasi.forEach((lokasi, idx) => {
            const rank = isManualMode ? lokasi.ranking_komparasi : lokasi.ranking;
            const badgeClass = (isManualMode && lokasi.is_pilihan_user) ? 'bg-success' : 'bg-primary';
            const userTag = (isManualMode && lokasi.is_pilihan_user) ? '<small class="d-block text-success" style="font-size:10px">Pilihan Anda</small>' : '';

            // Render List
            listHtml += `
                <li class="list-group-item d-flex justify-content-between align-items-center ranking-item border-0 mb-1 rounded" data-lat="${lokasi.latitude}" data-lng="${lokasi.longitude}" data-idx="${idx}">
                    <div>
                        <span class="fw-bold text-dark">#${rank}</span> ${lokasi.nama} ${userTag}
                    </div>
                    <span class="badge ${badgeClass} rounded-pill fira-code">${parseFloat(lokasi.skor_akhir).toFixed(4)}</span>
                </li>
            `;

            // Render Marker
            let marker = L.marker([lokasi.latitude, lokasi.longitude], { icon: createNumberedIcon(rank, rank === 1) }).addTo(map);
            
            // Simpan referensi marker untuk hover effect
            marker.listIndex = idx;
            
            marker.bindPopup(`
                <div class="fira-code">
                    <b>${lokasi.nama}</b><br>
                    Skor: ${parseFloat(lokasi.skor_akhir).toFixed(4)}<br>
                    Sewa: Rp ${Number(lokasi.nilai_sewa).toLocaleString()}<br>
                    Padat: ${lokasi.nilai_penduduk} | Komp: ${lokasi.nilai_kompetitor}
                </div>
                <hr class="my-2">
                <div class="buffer-control fira-code" style="font-size:11px">
                    <label class="d-block mb-1">Radius Buffer: <b><span class="buffer-radius-label">500</span> m</b></label>
                    <input type="range" class="form-range buffer-radius-input" min="100" max="2000" step="50" value="500"
                        data-lat="${lokasi.latitude}" data-lng="${lokasi.longitude}">
                    <div class="mt-1">Kompetitor dalam radius: <b class="buffer-comp-count">-</b></div>
                    <button class="btn btn-sm btn-outline-primary btn-buffer w-100 mt-1" data-lat="${lokasi.latitude}" data-lng="${lokasi.longitude}">Tampilkan Buffer</button>
                </div>
            `, { offset: [0, -25] });
            markers.push(marker);
        });

        $('#rankingList').html(listHtml);
        if(dataLokasi.length > 0) map.flyTo([dataLokasi[0].latitude, dataLokasi[0].longitude], 14);
        
        // Cek desktop vs mobile
        if(window.innerWidth < 992) {
             // Jika mobile, sembunyikan sidebar supaya peta kelihatan utuh
             const bsOffcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('sidebarOffcanvas'));
             if(bsOffcanvas) bsOffcanvas.hide();
        }
    }

    // Hover List -> Highlight Marker
    $(document).on('mouseenter', '.ranking-item', function() {
        const idx = $(this).data('idx');
        if(markers[idx]) {
            const el = markers[idx].getElement();
            if(el) el.querySelector('.marker-wrapper').style.transform = 'scale(1.3) translateY(-5px)';
        }
    }).on('mouseleave', '.ranking-item', function() {
        const idx = $(this).data('idx');
        if(markers[idx]) {
            const el = markers[idx].getElement();
            if(el) el.querySelector('.marker-wrapper').style.transform = 'scale(1) translateY(0)';
        }
    }).on('click', '.ranking-item', function() {
        map.flyTo([$(this).data('lat'), $(this).data('lng')], 16);
        const idx = $(this).data('idx');
        if(markers[idx]) markers[idx].openPopup();
        
        // Auto-close offcanvas on mobile
        if(window.innerWidth < 992) {
             const bsOffcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('sidebarOffcanvas'));
             if(bsOffcanvas) bsOffcanvas.hide();
        }
    });

    function drawBuffer(lat, lng, radius) {
        if(bufferCircle) map.removeLayer(bufferCircle);
        bufferCircle = L.circle([lat, lng], { color: 'var(--color-primary)', fillColor: 'var(--color-primary)', fillOpacity: 0.1, radius: radius }).addTo(map);
        map.flyTo([lat, lng], 15);
    }

    function fetchCompetitorCount(lat, lng, radius, $label) {
        $.ajax({
            url: '/api/locations/competitors-radius',
            method: 'POST',
            data: {
                lat: lat, lng: lng,
                radius_meter: radius,
                jenis_usaha_id: $('#jenisUsaha').val()
            },
            success: function(res) { $label.text(res.count); },
            error: function() { $label.text('?'); }
        });
    }

    // Slider: update label, redraw buffer if shown, refresh competitor count live
    $(document).on('input', '.buffer-radius-input', function() {
        const $ctrl = $(this).closest('.buffer-control');
        const lat = $(this).data('lat'), lng = $(this).data('lng');
        const radius = parseInt($(this).val(), 10);
        $ctrl.find('.buffer-radius-label').text(radius);
        if(bufferCircle) drawBuffer(lat, lng, radius);
        fetchCompetitorCount(lat, lng, radius, $ctrl.find('.buffer-comp-count'));
    });

    $(document).on('click', '.btn-buffer', function() {
        const lat = $(this).data('lat'), lng = $(this).data('lng');
        const $ctrl = $(this).closest('.buffer-control');
        const radius = parseInt($ctrl.find('.buffer-radius-input').val(), 10) || 500;
        drawBuffer(lat, lng, radius);
        fetchCompetitorCount(lat, lng, radius, $ctrl.find('.buffer-comp-count'));
    });

    $('#btnReset').click(function() {
        clearMap();
        $('#step4-result, #step3-manual, #step2-mode').addClass('d-none');
        $('#step1-ahp').removeClass('d-none');
        $(this).addClass('d-none');
        map.setView(DEFAULT_CENTER, DEFAULT_ZOOM);
        allScoredLocations = []; selectedManualIds = []; customLocationsData = [];
    });
});
</script>
