@extends('admin.layouts.app')

@section('title', isset($lokasi) ? 'Edit Lokasi' : 'Tambah Lokasi Baru')

@section('content')
<div class="card-custom">
    <form action="{{ isset($lokasi) ? route('admin.alternatif.update', $lokasi->id) : route('admin.alternatif.store') }}" method="POST">
        @csrf
        @if(isset($lokasi)) @method('PUT') @endif
        
        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 24px; margin-bottom: 24px;">
            <div>
                <label style="display: block; font-family: 'IBM Plex Mono', monospace; font-size: 12px; color: var(--text-secondary); margin-bottom: 8px;">Nama Lokasi</label>
                <input type="text" name="nama_lokasi" style="width: 100%; background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-primary); padding: 12px; border-radius: 8px; font-size: 14px;" value="{{ old('nama_lokasi', $lokasi->nama_lokasi ?? '') }}" required>
                @error('nama_lokasi') <span style="color: #ef4444; font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
            </div>
            <div>
                <label style="display: block; font-family: 'IBM Plex Mono', monospace; font-size: 12px; color: var(--text-secondary); margin-bottom: 8px;">Jenis Usaha</label>
                <select name="jenis_usaha_id" style="width: 100%; background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-primary); padding: 12px; border-radius: 8px; font-size: 14px;" required>
                    @foreach($jenisUsaha as $ju)
                        <option value="{{ $ju->id }}" {{ (old('jenis_usaha_id', $lokasi->jenis_usaha_id ?? '') == $ju->id) ? 'selected' : '' }}>{{ $ju->nama }}</option>
                    @endforeach
                </select>
                @error('jenis_usaha_id') <span style="color: #ef4444; font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
            </div>
            <div>
                <label style="display: block; font-family: 'IBM Plex Mono', monospace; font-size: 12px; color: var(--text-secondary); margin-bottom: 8px;">Kelurahan</label>
                <select name="kelurahan_id" style="width: 100%; background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-primary); padding: 12px; border-radius: 8px; font-size: 14px;" required>
                    @foreach($kelurahan as $kel)
                        <option value="{{ $kel->id }}" {{ (old('kelurahan_id', $lokasi->kelurahan_id ?? '') == $kel->id) ? 'selected' : '' }}>{{ $kel->nama }}</option>
                    @endforeach
                </select>
                @error('kelurahan_id') <span style="color: #ef4444; font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 24px;">
            <div>
                <label style="display: block; font-family: 'IBM Plex Mono', monospace; font-size: 12px; color: var(--text-secondary); margin-bottom: 8px;">Latitude</label>
                <input type="text" name="latitude" style="width: 100%; background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-primary); padding: 12px; border-radius: 8px; font-size: 14px; font-family: 'IBM Plex Mono', monospace;" value="{{ old('latitude', $lokasi->latitude ?? '') }}" required>
                @error('latitude') <span style="color: #ef4444; font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
            </div>
            <div>
                <label style="display: block; font-family: 'IBM Plex Mono', monospace; font-size: 12px; color: var(--text-secondary); margin-bottom: 8px;">Longitude</label>
                <input type="text" name="longitude" style="width: 100%; background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-primary); padding: 12px; border-radius: 8px; font-size: 14px; font-family: 'IBM Plex Mono', monospace;" value="{{ old('longitude', $lokasi->longitude ?? '') }}" required>
                @error('longitude') <span style="color: #ef4444; font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
            </div>
            <div>
                <label style="display: block; font-family: 'IBM Plex Mono', monospace; font-size: 12px; color: var(--text-secondary); margin-bottom: 8px;">Harga Sewa / Tahun</label>
                <input type="number" name="harga_sewa_per_tahun" style="width: 100%; background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-primary); padding: 12px; border-radius: 8px; font-size: 14px; font-family: 'IBM Plex Mono', monospace;" value="{{ old('harga_sewa_per_tahun', $lokasi->harga_sewa_per_tahun ?? '0') }}" required>
                @error('harga_sewa_per_tahun') <span style="color: #ef4444; font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
            </div>
            <div>
                <label style="display: block; font-family: 'IBM Plex Mono', monospace; font-size: 12px; color: var(--text-secondary); margin-bottom: 8px;">Skor Keamanan</label>
                <select name="skor_keamanan" style="width: 100%; background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-primary); padding: 12px; border-radius: 8px; font-size: 14px;" required>
                    <option value="0" {{ (old('skor_keamanan', $lokasi->skor_keamanan ?? '') == 0) ? 'selected' : '' }}>0 - N/A (Kompetitor)</option>
                    <option value="1" {{ (old('skor_keamanan', $lokasi->skor_keamanan ?? '') == 1) ? 'selected' : '' }}>1 - Rawan</option>
                    <option value="2" {{ (old('skor_keamanan', $lokasi->skor_keamanan ?? '') == 2) ? 'selected' : '' }}>2 - Cukup Aman</option>
                    <option value="3" {{ (old('skor_keamanan', $lokasi->skor_keamanan ?? '') == 3) ? 'selected' : '' }}>3 - Aman</option>
                    <option value="4" {{ (old('skor_keamanan', $lokasi->skor_keamanan ?? '') == 4) ? 'selected' : '' }}>4 - Sangat Aman</option>
                </select>
                @error('skor_keamanan') <span style="color: #ef4444; font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="margin-bottom: 32px; background: rgba(255,255,255,0.02); padding: 16px; border-radius: 12px; border: 1px solid var(--border-color);">
            <label style="display: block; font-family: 'IBM Plex Mono', monospace; font-size: 12px; color: var(--text-secondary); margin-bottom: 12px;">Tipe Lokasi</label>
            <div style="display: flex; gap: 24px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: var(--text-primary);">
                    <input type="radio" name="adalah_kompetitor" value="0" {{ old('adalah_kompetitor', $lokasi->adalah_kompetitor ?? 0) == 0 ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: #F97316;">
                    <span>Kandidat <span style="color: var(--text-secondary); font-size: 12px;">(Masuk perangkingan)</span></span>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: #ef4444;">
                    <input type="radio" name="adalah_kompetitor" value="1" {{ old('adalah_kompetitor', $lokasi->adalah_kompetitor ?? 0) == 1 ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: #ef4444;">
                    <span>Kompetitor <span style="opacity: 0.8; font-size: 12px;">(Hanya untuk perhitungan Buffer Zone)</span></span>
                </label>
            </div>
            @error('adalah_kompetitor') <span style="color: #ef4444; font-size: 12px; margin-top: 8px; display: block;">{{ $message }}</span> @enderror
        </div>

        <div style="display: flex; gap: 12px;">
            <button type="submit" class="btn-custom">Simpan Data</button>
            <a href="{{ route('admin.alternatif.index') }}" class="btn-custom btn-outline" style="line-height: 20px;">Batal</a>
        </div>
    </form>
</div>
@endsection
