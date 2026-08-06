@extends('layouts.admin')

@section('content')
<div class="mb-6">
    <a href="{{ isset($business) ? route('admin.businesses.show', $business) : route('admin.businesses.show', $criterion->business_id) }}" class="text-tinta/60 hover:text-cyan-cetak text-sm">&larr; Kembali</a>
    <h2 class="font-display text-2xl font-bold mt-2">{{ isset($criterion) ? 'Edit' : 'Tambah' }} Kriteria</h2>
</div>

<div class="bg-white p-6 border border-tinta/20 w-full max-w-lg">
    <form action="{{ isset($criterion) ? route('admin.criteria.update', $criterion) : route('admin.businesses.criteria.store', $business) }}" method="POST" class="space-y-4">
        @csrf
        @if(isset($criterion)) @method('PUT') @endif

        <div>
            <label class="block font-semibold mb-1 text-sm">Kode Kriteria</label>
            <input type="text" name="code" value="{{ old('code', $criterion->code ?? '') }}" class="w-full border border-tinta/30 p-2 focus:outline-none focus:border-cyan-cetak font-mono" placeholder="C1" required>
            @error('code') <span class="text-karat text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-semibold mb-1 text-sm">Nama Kriteria</label>
            <input type="text" name="name" value="{{ old('name', $criterion->name ?? '') }}" class="w-full border border-tinta/30 p-2 focus:outline-none focus:border-cyan-cetak" required>
            @error('name') <span class="text-karat text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-semibold mb-1 text-sm">Tipe</label>
            <select name="type" class="w-full border border-tinta/30 p-2 focus:outline-none focus:border-cyan-cetak" required>
                <option value="benefit" {{ (old('type', $criterion->type ?? '') == 'benefit') ? 'selected' : '' }}>Benefit (Semakin Besar Semakin Baik)</option>
                <option value="cost" {{ (old('type', $criterion->type ?? '') == 'cost') ? 'selected' : '' }}>Cost (Semakin Kecil Semakin Baik)</option>
            </select>
        </div>

        <button type="submit" class="bg-cyan-cetak text-kertas px-6 py-2 font-mono text-sm uppercase tracking-wide hover:bg-cyan-cetak/90">
            Simpan
        </button>
    </form>

    @if(isset($criterion))
    <div class="mt-8 pt-4 border-t border-tinta/20">
        <form action="{{ route('admin.criteria.destroy', $criterion) }}" method="POST" onsubmit="return confirm('Yakin hapus kriteria ini?');">
            @csrf @method('DELETE')
            <button type="submit" class="text-karat text-sm hover:underline">Hapus Kriteria</button>
        </form>
    </div>
    @endif
</div>
@endsection