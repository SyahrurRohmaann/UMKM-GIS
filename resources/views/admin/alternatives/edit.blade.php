@extends('layouts.admin')

@section('content')
<div class="mb-6 flex justify-between items-end">
    <div>
        <a href="{{ route('admin.businesses.show', $business) }}" class="text-tinta/60 hover:text-cyan-cetak text-sm">&larr; Kembali</a>
        <h2 class="font-display text-2xl font-bold mt-2">{{ isset($alternative) ? 'Edit' : 'Tambah' }} Alternatif Lokasi</h2>
    </div>
    @if(isset($alternative))
    <form action="{{ route('admin.alternatives.destroy', $alternative) }}" method="POST" onsubmit="return confirm('Yakin hapus lokasi ini?');">
        @csrf @method('DELETE')
        <button type="submit" class="text-karat text-sm font-semibold hover:underline">Hapus Lokasi</button>
    </form>
    @endif
</div>

<form action="{{ isset($alternative) ? route('admin.alternatives.update', $alternative) : route('admin.businesses.alternatives.store', $business) }}" method="POST" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    @csrf
    @if(isset($alternative)) @method('PUT') @endif

    <!-- Data Dasar & Koordinat -->
    <div class="space-y-6">
        <div class="bg-white p-6 border border-tinta/20 space-y-4">
            <h3 class="font-display font-bold text-lg mb-4">Informasi Dasar</h3>
            <div>
                <label class="block font-semibold mb-1 text-sm">Nama Lokasi</label>
                <input type="text" name="name" value="{{ old('name', $alternative->name ?? '') }}" class="w-full border border-tinta/30 p-2 focus:outline-none focus:border-cyan-cetak" required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold mb-1 text-sm">Latitude</label>
                    <input type="text" id="lat" name="latitude" value="{{ old('latitude', $alternative->latitude ?? '-8.1691') }}" class="w-full border border-tinta/30 p-2 font-mono text-xs focus:outline-none focus:border-cyan-cetak" required readonly>
                </div>
                <div>
                    <label class="block font-semibold mb-1 text-sm">Longitude</label>
                    <input type="text" id="lng" name="longitude" value="{{ old('longitude', $alternative->longitude ?? '113.7022') }}" class="w-full border border-tinta/30 p-2 font-mono text-xs focus:outline-none focus:border-cyan-cetak" required readonly>
                </div>
            </div>
            <p class="text-xs text-tinta/60">Geser marker pada peta untuk menentukan koordinat.</p>
        </div>

        <div id="map-picker" class="w-full h-64 border border-tinta/20"></div>
    </div>

    <!-- Nilai Kriteria -->
    <div class="bg-white p-6 border border-tinta/20">
        <h3 class="font-display font-bold text-lg mb-4 border-b border-tinta/10 pb-2">Nilai Atribut Kriteria</h3>
        <p class="text-xs text-tinta/60 mb-6">Masukkan nilai murni (angka) lokasi ini terhadap tiap kriteria. Normalisasi akan dihitung otomatis oleh sistem.</p>
        
        <div class="space-y-4">
            @foreach((isset($criteria) ? $criteria : $business->criteria) as $c)
            <div>
                <label class="block font-semibold mb-1 text-sm">{{ $c->name }} <span class="font-mono text-xs text-tinta/50">({{ $c->type }})</span></label>
                <input type="number" step="any" name="scores[{{ $c->id }}]" value="{{ old('scores.'.$c->id, $scoresMap[$c->id] ?? '') }}" class="w-full border border-tinta/30 p-2 focus:outline-none focus:border-cyan-cetak" required>
            </div>
            @endforeach
        </div>

        <div class="mt-8 pt-6 border-t border-tinta/10">
            <button type="submit" class="w-full bg-cyan-cetak text-kertas px-6 py-3 font-mono text-sm uppercase tracking-wide hover:bg-cyan-cetak/90">
                Simpan Lokasi
            </button>
        </div>
    </div>
</form>
@endsection

@stack('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    let initialLat = parseFloat(document.getElementById('lat').value);
    let initialLng = parseFloat(document.getElementById('lng').value);

    const map = L.map('map-picker').setView([initialLat, initialLng], 14);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; CARTO'
    }).addTo(map);

    const marker = L.marker([initialLat, initialLng], {
        draggable: true
    }).addTo(map);

    marker.on('dragend', function (e) {
        document.getElementById('lat').value = marker.getLatLng().lat.toFixed(6);
        document.getElementById('lng').value = marker.getLatLng().lng.toFixed(6);
    });

    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        document.getElementById('lat').value = e.latlng.lat.toFixed(6);
        document.getElementById('lng').value = e.latlng.lng.toFixed(6);
    });
});
</script>
