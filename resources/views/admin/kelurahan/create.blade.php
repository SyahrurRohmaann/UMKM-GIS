@extends('admin.layouts.app')

@section('title', isset($kelurahan) ? 'Edit Kelurahan' : 'Tambah Kelurahan Baru')

@section('content')
<div class="card-custom">
    <form action="{{ isset($kelurahan) ? route('admin.kelurahan.update', $kelurahan->id) : route('admin.kelurahan.store') }}" method="POST">
        @csrf
        @if(isset($kelurahan)) @method('PUT') @endif

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 32px;">
            <div>
                <label style="display: block; font-family: 'IBM Plex Mono', monospace; font-size: 12px; color: var(--text-secondary); margin-bottom: 8px;">Nama Kelurahan</label>
                <input type="text" name="nama" style="width: 100%; background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-primary); padding: 12px; border-radius: 8px; font-size: 14px;" value="{{ old('nama', $kelurahan->nama ?? '') }}" required>
                @error('nama') <span style="color: #ef4444; font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
            </div>
            <div>
                <label style="display: block; font-family: 'IBM Plex Mono', monospace; font-size: 12px; color: var(--text-secondary); margin-bottom: 8px;">Kepadatan Penduduk (jiwa/km²)</label>
                <input type="number" step="any" name="kepadatan_penduduk" style="width: 100%; background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-primary); padding: 12px; border-radius: 8px; font-size: 14px; font-family: 'IBM Plex Mono', monospace;" value="{{ old('kepadatan_penduduk', $kelurahan->kepadatan_penduduk ?? '') }}" required>
                @error('kepadatan_penduduk') <span style="color: #ef4444; font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="display: flex; gap: 12px;">
            <button type="submit" class="btn-custom">Simpan Data</button>
            <a href="{{ route('admin.kelurahan.index') }}" class="btn-custom btn-outline" style="line-height: 20px;">Batal</a>
        </div>
    </form>
</div>
@endsection
