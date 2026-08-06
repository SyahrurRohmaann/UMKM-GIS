@extends('layouts.app')

@section('title', 'Peta Rekomendasi')

@push('styles')
<style>
    .ranking-item { cursor: pointer; transition: background 0.2s; }
    .ranking-item:hover { background: #f0f8ff; }
    
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
        background-clip: padding-box;
        border-radius: 20px;
        padding: 10px 20px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }
    
    #btnRecenter.visible {
        opacity: 1;
        visibility: visible;
    }
    
    #btnRecenter:hover {
        background: #f4f4f4;
    }
</style>
@endpush

@section('sidebar')
    <h4>Sistem Pemilihan Lokasi</h4>
    <hr>
    
    @auth
        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-dark w-100"><i class="bi bi-speedometer2"></i> Kembali ke Dashboard Admin</a>
        </div>
    @endauth
    
    <!-- STEP 1: AHP & JENIS USAHA -->
    <div id="step1-ahp">
        <div class="mb-3">
            <label class="form-label fw-bold">1. Pilih Jenis Usaha</label>
            <select class="form-select" id="jenisUsaha">
                @foreach($jenisUsaha as $ju)
                    <option value="{{ $ju->id }}">{{ $ju->nama }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">2. Bobot Kriteria AHP</label>
            <div class="card p-2 mb-2">
                <small class="d-block mb-1">Sewa vs Kepadatan Penduduk</small>
                <select class="form-select form-select-sm" id="ahp_sewa_penduduk">
                    <option value="1">1 - Sama Penting</option>
                    <option value="3">3 - Sewa Lebih Penting</option>
                    <option value="0.333">1/3 - Penduduk Lebih Penting</option>
                </select>
            </div>
            <div class="card p-2 mb-2">
                <small class="d-block mb-1">Sewa vs Kompetitor</small>
                <select class="form-select form-select-sm" id="ahp_sewa_komp">
                    <option value="1">1 - Sama Penting</option>
                    <option value="5">5 - Sewa Lebih Penting</option>
                    <option value="0.2">1/5 - Kompetitor Lebih Penting</option>
                </select>
            </div>
            <div class="card p-2 mb-3">
                <small class="d-block mb-1">Penduduk vs Keamanan</small>
                <select class="form-select form-select-sm" id="ahp_penduduk_keamanan">
                    <option value="1">1 - Sama Penting</option>
                    <option value="3">3 - Penduduk Lebih Penting</option>
                    <option value="0.333">1/3 - Keamanan Lebih Penting</option>
                </select>
            </div>
            
            <button id="btnHitung" class="btn btn-primary w-100">Simpan Bobot & Lanjut</button>
        </div>
    </div>

    <!-- STEP 2: PILIH MODE -->
    <div id="step2-mode" class="d-none">
        <h5 class="fw-bold">3. Pilih Mode Pencarian</h5>
        <div class="form-check mb-2">
            <input class="form-check-input" type="radio" name="modePencarian" id="modeSistem" value="sistem" checked>
            <label class="form-check-label" for="modeSistem">
                Rekomendasi Sistem (Otomatis)
            </label>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="radio" name="modePencarian" id="modeManual" value="manual">
            <label class="form-check-label" for="modeManual">
                Pilih Manual (Tandai di Peta)
            </label>
        </div>
        <button id="btnLanjutMode" class="btn btn-success w-100">Lanjutkan</button>
    </div>

    <!-- STEP 3: MANUAL SELECTION -->
    <div id="step3-manual" class="d-none">
        <h5 class="fw-bold">Pilih Lokasi di Peta</h5>
        <p class="small text-muted">Klik marker abu-abu untuk memilih lokasi tersimpan.</p>
        <div class="alert alert-info py-1 px-2 small mb-2">
            <b>Tip:</b> Tahan <b>SHIFT + Klik Kiri</b> di peta untuk menambahkan kandidat lokasi baru Anda sendiri!
        </div>
        
        <ul id="selectedLocationsList" class="list-group mb-3 small">
            <!-- List goes here -->
        </ul>
        
        <div id="opsiIkutSistemBox" class="form-check mb-3 d-none">
            <input class="form-check-input" type="checkbox" id="chkIkutSistem" checked>
            <label class="form-check-label small" for="chkIkutSistem">
                Ikutsertakan rekomendasi terbaik sistem sebagai perbandingan?
            </label>
        </div>

        <button id="btnProsesManual" class="btn btn-success w-100 mb-2" disabled>Bandingkan Lokasi</button>
    </div>

    <!-- STEP 4: RESULT -->
    <div id="step4-result" class="d-none">
        <h5 class="text-success fw-bold">Hasil Rekomendasi</h5>
        <div id="ahpMetrics" class="small mb-2 text-muted"></div>
        <ul class="list-group mb-3" id="rankingList">
            <!-- Ranking list generated by JS -->
        </ul>
    </div>

    <hr>
    <button id="btnReset" class="btn btn-outline-danger w-100 d-none">Mulai Ulang (Reset)</button>

@endsection

@section('content')
    <div id="map" style="width: 100%; height: 100%; position: relative;"></div>
    <button id="btnRecenter" title="Kembali ke Sumbersari">
        <i class="bi bi-geo-fill text-primary"></i> Kembali ke Sumbersari
    </button>
@endsection

@stack('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const DEFAULT_CENTER = [-8.165, 113.716];
    const DEFAULT_ZOOM = 13;
    const map = L.map('map').setView(DEFAULT_CENTER, DEFAULT_ZOOM);
    
    // Minimalist CartoDB Positron theme for a cleaner map matching the minimalist design
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);

    // Load GeoJSON Sumbersari Admin
    fetch('/assets/geojson/sumbersari_admin.geojson')
        .then(response => response.json())
        .then(data => {
            L.geoJSON(data, {
                style: function (feature) {
                    return {
                        color: '#FFB049',
                        weight: 3,
                        opacity: 0.8,
                        fillColor: '#FFB049',
                        fillOpacity: 0.05,
                        dashArray: '5, 5'
                    };
                },
                interactive: false
            }).addTo(map);
        })
        .catch(error => console.error('Error loading GeoJSON:', error));

    let markers = [];
    let bufferCircle = null;
    
    let allScoredLocations = [];
    let selectedManualIds = [];
    let currentWeights = [];
    let customLocationsData = []; // Menyimpan atribut mentah titik kustom

    // --- RECENTER BUTTON LOGIC ---
    const btnRecenter = document.getElementById('btnRecenter');
    
    function checkMapCenter() {
        const currentCenter = map.getCenter();
        const dist = currentCenter.distanceTo(L.latLng(DEFAULT_CENTER[0], DEFAULT_CENTER[1]));
        // Munculkan tombol jika jarak pusat peta lebih dari 2000 meter dari default
        if (dist > 2000) {
            btnRecenter.classList.add('visible');
        } else {
            btnRecenter.classList.remove('visible');
        }
    }

    map.on('moveend', checkMapCenter);

    btnRecenter.addEventListener('click', function() {
        map.flyTo(DEFAULT_CENTER, DEFAULT_ZOOM);
    });

    // --- ICONS ---
    const createNumberedIcon = (number, isTop = false) => L.divIcon({
        className: 'custom-div-icon',
        html: `
            <div style="position: relative; width: 36px; height: 36px;">
                <img src="/assets/marker.svg" style="width: 100%; height: 100%; filter: ${isTop ? 'hue-rotate(330deg)' : 'hue-rotate(200deg)'};" />
                <div style="position: absolute; top: 40%; left: 50%; transform: translate(-50%, -50%); color: white; font-weight: bold; font-family: 'IBM Plex Mono', monospace; font-size: 14px; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">${number}</div>
            </div>`,
        iconSize: [36, 36],
        iconAnchor: [18, 36]
    });

    const createManualIcon = (selected = false) => L.divIcon({
        className: 'custom-div-icon',
        html: `
            <div style="width: 24px; height: 24px;">
                <img src="/assets/marker.svg" style="width: 100%; height: 100%; filter: ${selected ? 'hue-rotate(100deg) saturate(2)' : 'grayscale(1) opacity(0.5)'};" />
            </div>`,
        iconSize: [24, 24],
        iconAnchor: [12, 24]
    });

    // --- HELPER: Clear Map ---
    function clearMap() {
        markers.forEach(m => map.removeLayer(m));
        if(bufferCircle) map.removeLayer(bufferCircle);
        markers = [];
    }

    // --- STEP 1: Hitung Bobot AHP ---
    $('#btnHitung').click(function() {
        // Simulasi matrix dari input (hardcoded MVP)
        const matrix = [
            [1,   1/3, 2,   1/2],
            [3,   1,   4,   2  ],
            [0.5, 0.25, 1,  1/3],
            [2,   0.5,  3,  1  ],
        ];

        $.ajax({
            url: '/api/ahp/calculate',
            method: 'POST',
            data: { matrix: matrix },
            success: function(ahpRes) {
                if(ahpRes.data.is_consistent) {
                    $('#ahpMetrics').html(`CR: ${ahpRes.data.cr} (Konsisten)`);
                    currentWeights = ahpRes.data.weights;
                    
                    // Lanjut fetch semua data berskor dari backend untuk diolah UI
                    $.ajax({
                        url: '/api/recommendations/generate',
                        method: 'POST',
                        data: {
                            jenis_usaha_id: $('#jenisUsaha').val(),
                            weights: currentWeights
                        },
                        success: function(recRes) {
                            allScoredLocations = recRes.data;
                            
                            // UI Transisi
                            $('#step1-ahp').addClass('d-none');
                            $('#step2-mode').removeClass('d-none');
                            $('#btnReset').removeClass('d-none');
                        }
                    });
                }
            },
            error: function(err) {
                alert(err.responseJSON?.message || 'Error perhitungan AHP');
            }
        });
    });

    // --- STEP 2: Pilih Mode ---
    $('#btnLanjutMode').click(function() {
        const mode = $('input[name="modePencarian"]:checked').val();
        $('#step2-mode').addClass('d-none');

        if (mode === 'sistem') {
            prosesRekomendasiSistem();
        } else {
            $('#step3-manual').removeClass('d-none');
            tampilkanMarkerManual();
        }
    });

    // --- LOGIC: Mode Sistem ---
    function prosesRekomendasiSistem() {
        // Ambil Top 5 misalnya
        let displayData = allScoredLocations.slice(0, 5);
        renderHasil(displayData);
    }

    // --- LOGIC: Mode Manual ---
    function tampilkanMarkerManual() {
        clearMap();
        selectedManualIds = [];
        updateManualUI();

        allScoredLocations.forEach(lokasi => {
            let marker = L.marker([lokasi.latitude, lokasi.longitude], {
                icon: createManualIcon(false)
            }).addTo(map);

            marker.bindTooltip(lokasi.nama, {
                offset: [0, -20]
            });

            marker.on('click', function() {
                const idx = selectedManualIds.indexOf(lokasi.id);
                if (idx > -1) {
                    selectedManualIds.splice(idx, 1);
                    marker.setIcon(createManualIcon(false));
                } else {
                    if (selectedManualIds.length < 4) {
                        selectedManualIds.push(lokasi.id);
                        marker.setIcon(createManualIcon(true));
                    } else {
                        alert("Maksimal 4 lokasi!");
                    }
                }
                updateManualUI();
            });
            markers.push(marker);
        });
    }

    // Event Klik Peta untuk Lokasi Baru (Shift + Click)
    map.on('click', function(e) {
        if (!e.originalEvent.shiftKey || $('#step3-manual').hasClass('d-none')) {
            return; // Hanya jalan di step manual + Shift
        }

        if (selectedManualIds.length >= 4) {
            alert("Anda sudah memilih batas maksimal 4 lokasi!");
            return;
        }

        $('#c_lat').val(e.latlng.lat);
        $('#c_lng').val(e.latlng.lng);
        $('#customLocModal').modal('show');
    });

    $('#btnSaveCustom').click(function() {
        $('#customLocModal').modal('hide');
        const customNama = $('#c_nama').val();
        
        $.ajax({
            url: '/api/locations/simulate-score',
            method: 'POST',
            data: {
                lat: $('#c_lat').val(),
                lng: $('#c_lng').val(),
                jenis_usaha_id: $('#jenisUsaha').val(),
                nama_lokasi: customNama,
                harga_sewa: $('#c_sewa').val(),
                skor_keamanan: $('#c_aman').val()
            },
            success: function(res) {
                const newLoc = res.data;
                // Simpan data kustom mentah, kita akan passing ke generate endpoint
                customLocationsData.push(newLoc);
                
                // Supaya muncul di UI sementara sebagai pilihan
                allScoredLocations.push(newLoc); 
                selectedManualIds.push(newLoc.id);
                
                let marker = L.marker([newLoc.latitude, newLoc.longitude], {
                    icon: createManualIcon(true)
                }).addTo(map);
                marker.bindTooltip(newLoc.nama, {
                    offset: [0, -20]
                });
                markers.push(marker);

                updateManualUI();
                map.flyTo([newLoc.latitude, newLoc.longitude], 15);
            },
            error: function(err) {
                alert("Gagal memproses titik lokasi");
            }
        });
    });

    function updateManualUI() {
        $('#selectedLocationsList').empty();
        selectedManualIds.forEach(id => {
            const loc = allScoredLocations.find(l => l.id === id);
            $('#selectedLocationsList').append(`<li class="list-group-item py-1">${loc.nama}</li>`);
        });

        if (selectedManualIds.length > 0) {
            $('#btnProsesManual').prop('disabled', false);
        } else {
            $('#btnProsesManual').prop('disabled', true);
        }

        if (selectedManualIds.length >= 2) {
            $('#opsiIkutSistemBox').removeClass('d-none');
        } else {
            $('#opsiIkutSistemBox').addClass('d-none');
        }
    }

    $('#btnProsesManual').click(function() {
        // Tembak ulang API generate untuk menghitung skor SAW absolut dengan menyertakan titik kustom
        $.ajax({
            url: '/api/recommendations/generate',
            method: 'POST',
            data: {
                jenis_usaha_id: $('#jenisUsaha').val(),
                weights: currentWeights,
                custom_locations: customLocationsData
            },
            success: function(recRes) {
                let freshScores = recRes.data;
                
                let displayData = [];
                let userLocs = freshScores.filter(l => selectedManualIds.includes(l.id));
                let systemLocs = freshScores.filter(l => !selectedManualIds.includes(l.id));

                if (selectedManualIds.length === 1) {
                    displayData = [...userLocs, ...systemLocs.slice(0, 3)];
                } else {
                    const ikutSistem = $('#chkIkutSistem').is(':checked');
                    if (ikutSistem) {
                        displayData = [...userLocs, ...systemLocs.slice(0, Math.max(1, 5 - userLocs.length))];
                    } else {
                        displayData = [...userLocs];
                    }
                }

                displayData.sort((a, b) => b.skor_akhir - a.skor_akhir);

                displayData = displayData.map((loc, i) => {
                    loc.ranking_komparasi = i + 1;
                    loc.is_pilihan_user = selectedManualIds.includes(loc.id);
                    return loc;
                });

                $('#step3-manual').addClass('d-none');
                renderHasil(displayData, true);
            }
        });
    });

    // --- STEP 4: Render Hasil ---
    function renderHasil(dataLokasi, isManualMode = false) {
        clearMap();
        $('#rankingList').empty();
        $('#step4-result').removeClass('d-none');

        dataLokasi.forEach((lokasi) => {
            const rank = isManualMode ? lokasi.ranking_komparasi : lokasi.ranking;
            const badgeClass = (isManualMode && lokasi.is_pilihan_user) ? 'bg-success' : 'bg-primary';
            const userTag = (isManualMode && lokasi.is_pilihan_user) ? '<small class="text-success">(Pilihan Anda)</small>' : '';

            // Render List Sidebar
            $('#rankingList').append(`
                <li class="list-group-item d-flex justify-content-between align-items-center ranking-item" data-lat="${lokasi.latitude}" data-lng="${lokasi.longitude}">
                    <div>
                        <b>#${rank}</b> ${lokasi.nama} <br>${userTag}
                    </div>
                    <span class="badge ${badgeClass} rounded-pill">${parseFloat(lokasi.skor_akhir).toFixed(4)}</span>
                </li>
            `);

            // Render Marker Numbered
            let marker = L.marker([lokasi.latitude, lokasi.longitude], {
                icon: createNumberedIcon(rank, rank === 1)
            }).addTo(map);
            
            let popupHtml = `
                <b>${lokasi.nama} (Rank #${rank})</b><br>
                Skor: ${parseFloat(lokasi.skor_akhir).toFixed(4)}<br>
                Sewa: Rp ${Number(lokasi.nilai_sewa).toLocaleString()}<br>
                Kepadatan: ${lokasi.nilai_penduduk}<br>
                Kompetitor 500m: ${lokasi.nilai_kompetitor}<br>
                Keamanan: Skala ${lokasi.nilai_keamanan}
                <hr>
                <button class="btn btn-sm btn-info btn-buffer" data-lat="${lokasi.latitude}" data-lng="${lokasi.longitude}">Cek Radius 500m</button>
            `;
            marker.bindPopup(popupHtml, {
                offset: [0, -25]
            });
            markers.push(marker);
        });

        // Fit Bounds / Zoom ke titik terbaik
        if(dataLokasi.length > 0) {
            map.flyTo([dataLokasi[0].latitude, dataLokasi[0].longitude], 14);
        }
    }

    // --- EVENTS ---
    // Event Klik List -> Fokus Marker
    $(document).on('click', '.ranking-item', function() {
        const lat = $(this).data('lat');
        const lng = $(this).data('lng');
        map.flyTo([lat, lng], 16);
    });

    // Event Klik Buffer
    $(document).on('click', '.btn-buffer', function() {
        const lat = $(this).data('lat');
        const lng = $(this).data('lng');
        if(bufferCircle) map.removeLayer(bufferCircle);
        bufferCircle = L.circle([lat, lng], {
            color: 'red', fillColor: '#f03', fillOpacity: 0.2, radius: 500
        }).addTo(map);
        map.flyTo([lat, lng], 15);
    });

    // Event Reset
    $('#btnReset').click(function() {
        clearMap();
        $('#step4-result, #step3-manual, #step2-mode').addClass('d-none');
        $('#step1-ahp').removeClass('d-none');
        $('#btnReset').addClass('d-none');
        map.setView(DEFAULT_CENTER, DEFAULT_ZOOM);
        allScoredLocations = [];
        selectedManualIds = [];
        currentWeights = [];
        customLocationsData = [];
    });
});
</script>

