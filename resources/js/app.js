// import './bootstrap'; // removed

document.addEventListener('DOMContentLoaded', () => {
    // 1. Inisialisasi Peta
    const map = L.map('map', {
        zoomControl: false // Kita taruh custom position nanti jika perlu
    }).setView([-8.1691, 113.7022], 14); // Koordinat Kec Sumbersari Jember

    // Basemap gaya "Blueprint" terang/gelap (CartoDB Positron cocok dengan --kertas)
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; CARTO',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);

    L.control.zoom({ position: 'topleft' }).addTo(map);

    let markers = [];

    // Fungsi Render Marker
    function renderMap(alternatives, topId = null) {
        // Hapus marker lama
        markers.forEach(m => map.removeLayer(m));
        markers = [];

        alternatives.forEach(alt => {
            const isTop = alt.id === parseInt(topId);
            const color = isTop ? '#E2A63B' : '#2F6E8E'; // ambar-sinyal vs cyan-cetak
            const size = isTop ? 16 : 12;

            const iconHtml = `
                <div style="
                    width: ${size}px; 
                    height: ${size}px; 
                    background-color: ${color}; 
                    border: 2px solid #16232E; 
                    border-radius: 50%;
                    box-shadow: 0 0 0 2px rgba(255,255,255,0.5);
                "></div>
            `;

            const icon = L.divIcon({
                className: 'custom-div-icon',
                html: iconHtml,
                iconSize: [size, size],
                iconAnchor: [size/2, size/2]
            });

            const marker = L.marker([alt.latitude, alt.longitude], { icon }).addTo(map);
            
            let popupContent = `<div class="font-body text-tinta"><strong class="font-display text-lg">${alt.name}</strong>`;
            if (alt.score !== undefined) {
                popupContent += `<br><span class="font-mono text-sm">Skor: ${alt.score.toFixed(3)}</span>`;
            }
            popupContent += `</div>`;
            
            marker.bindPopup(popupContent);
            markers.push(marker);
        });
    }

    // Render awal
    renderMap(window.initialAlternatives);

    // 2. Build Form AHP Berpasangan
    const criteria = window.initialCriteria;
    const container = document.getElementById('pairwise-container');
    
    // Kombinasi unik n(n-1)/2
    let pairs = [];
    for (let i = 0; i < criteria.length; i++) {
        for (let j = i + 1; j < criteria.length; j++) {
            pairs.push({ c1: criteria[i], c2: criteria[j] });
        }
    }

    pairs.forEach((pair, index) => {
        const div = document.createElement('div');
        div.className = 'flex flex-col gap-2';
        div.innerHTML = `
            <div class="flex justify-between text-xs font-mono text-tinta/80">
                <span class="w-1/3 text-left truncate" title="${pair.c1.name}">${pair.c1.name}</span>
                <span class="w-1/3 text-center font-bold text-cyan-cetak" id="val-display-${index}">1</span>
                <span class="w-1/3 text-right truncate" title="${pair.c2.name}">${pair.c2.name}</span>
            </div>
            <input type="range" min="-8" max="8" value="0" class="ahp-slider w-full" id="slider-${index}" name="pair[${pair.c1.id}][${pair.c2.id}]">
            <div class="flex justify-between text-[10px] text-tinta/40 font-mono px-1">
                <span>9</span><span>5</span><span>1</span><span>5</span><span>9</span>
            </div>
        `;
        container.appendChild(div);

        // Update angka saat slider digeser
        const slider = div.querySelector(`#slider-${index}`);
        const display = div.querySelector(`#val-display-${index}`);
        slider.addEventListener('input', (e) => {
            let val = parseInt(e.target.value);
            let displayVal = val === 0 ? 1 : (val < 0 ? Math.abs(val) + 1 : val + 1);
            display.textContent = displayVal;
            
            if (val < 0) {
                display.className = 'w-1/3 text-center font-bold text-tinta'; // condong kiri
            } else if (val > 0) {
                display.className = 'w-1/3 text-center font-bold text-tinta'; // condong kanan
            } else {
                display.className = 'w-1/3 text-center font-bold text-cyan-cetak'; // tengah
            }
        });
    });

    // 3. Handle Submit
    const form = document.getElementById('ahp-form');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Convert slider values to AHP matrix format (1/9 to 9)
        const matrix = {};
        criteria.forEach(c => matrix[c.id] = {});
        criteria.forEach(c => matrix[c.id][c.id] = 1);

        pairs.forEach((pair, index) => {
            const sliderVal = parseInt(document.getElementById(`slider-${index}`).value);
            let ahpVal;
            if (sliderVal === 0) {
                ahpVal = 1;
            } else if (sliderVal < 0) {
                ahpVal = Math.abs(sliderVal) + 1; // 2 to 9 favored C1
            } else {
                ahpVal = 1 / (sliderVal + 1); // 1/2 to 1/9 favored C2
            }
            
            matrix[pair.c1.id][pair.c2.id] = ahpVal;
            matrix[pair.c2.id][pair.c1.id] = 1 / ahpVal;
        });

        try {
            const response = await fetch('/calculate-ahp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ 
                    matrix,
                    business_id: document.querySelector('input[name="business_id"]').value 
                })
            });

            const data = await response.json();
            
            const warningEl = document.getElementById('consistency-warning');
            const crEl = document.getElementById('cr-value');
            
            crEl.textContent = data.meta.cr.toFixed(3);
            if (!data.meta.is_consistent) {
                warningEl.classList.remove('hidden');
            } else {
                warningEl.classList.add('hidden');
            }

            // Render Hasil
            const resSection = document.getElementById('results-section');
            const resContainer = document.getElementById('results-container');
            resSection.classList.remove('hidden');
            resContainer.innerHTML = '';

            let topId = data.results.length > 0 ? data.results[0].id : null;

            data.results.forEach((res, idx) => {
                const card = document.createElement('div');
                const isTop = idx === 0;
                card.className = `p-3 flex items-center gap-4 bg-white/50 border ${isTop ? 'border-ambar-sinyal/50' : 'border-tinta/10'} rounded-sm`;
                
                card.innerHTML = `
                    <div class="font-display text-2xl font-bold ${isTop ? 'text-ambar-sinyal' : 'text-tinta/30'} w-8 text-center">
                        ${idx + 1}
                    </div>
                    <div class="flex-1">
                        <div class="font-body font-semibold text-tinta">${res.name}</div>
                        <div class="font-mono text-xs text-tinta/60">Skor: ${res.score.toFixed(4)}</div>
                    </div>
                `;
                resContainer.appendChild(card);
            });

            // Update Map
            renderMap(data.results, topId);

        } catch (error) {
            console.error(error);
            alert('Terjadi kesalahan perhitungan.');
        }
    });
});
