@extends('layouts.admin')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.businesses.index') }}" class="text-tinta/60 hover:text-cyan-cetak text-sm">&larr; Kembali</a>
    <h2 class="font-display text-2xl font-bold mt-2">{{ isset($business) ? 'Edit' : 'Tambah' }} Jenis Usaha</h2>
</div>

<div class="bg-white p-6 border border-tinta/20 w-full max-w-lg">
    <form action="{{ isset($business) ? route('admin.businesses.update', $business) : route('admin.businesses.store') }}" method="POST" class="space-y-4">
        @csrf
        @if(isset($business)) @method('PUT') @endif

        <div>
            <label class="block font-semibold mb-1 text-sm">Nama Usaha</label>
            <input type="text" name="name" value="{{ old('name', $business->name ?? '') }}" class="w-full border border-tinta/30 p-2 focus:outline-none focus:border-cyan-cetak" required>
            @error('name') <span class="text-karat text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="bg-cyan-cetak text-kertas px-6 py-2 font-mono text-sm uppercase tracking-wide hover:bg-cyan-cetak/90">
            Simpan
        </button>
    </form>
</div>
@endsection