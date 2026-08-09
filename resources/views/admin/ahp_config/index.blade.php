@extends('admin.layouts.app')

@section('title', 'Konfigurasi Bobot AHP')

@section('content')
<div class="row" style="max-width: 800px;">
    <div class="col-12">
        <div class="card-custom">
            <form action="{{ route('admin.ahp_config.save') }}" method="POST">
                @csrf
                <div style="margin-bottom: 24px;">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-secondary); font-size: 14px;">Pilih Jenis Usaha</label>
                    <select name="jenis_usaha_id" class="form-select" style="width: 100%; padding: 12px; border-radius: 8px; background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-primary);" required onchange="window.location.href='?jenis_usaha_id='+this.value">
                        @foreach($jenisUsaha as $ju)
                            <option value="{{ $ju->id }}" {{ $selectedUsahaId == $ju->id ? 'selected' : '' }}>{{ $ju->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <hr style="border-color: var(--border-color); margin: 32px 0;">
                <h5 style="margin-bottom: 8px; color: var(--text-primary); font-size: 16px;">Matriks Perbandingan Berpasangan</h5>
                <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 24px; line-height: 1.5;">Beri nilai perbandingan (1-9). Angka bulat berarti kriteria kiri lebih penting, desimal (1/x) berarti kriteria kanan lebih penting.</p>

                @php
                    function isSelected($matrix, $i, $j, $val) {
                        $oldVal = old("matrix.{$i}.{$j}");
                        if ($oldVal !== null) {
                            return abs(floatval($oldVal) - floatval($val)) < 0.01 ? 'selected' : '';
                        }
                        if (!isset($matrix[$i][$j])) return $val == '1' ? 'selected' : '';
                        // Bandingkan dengan toleransi float kecil
                        return abs($matrix[$i][$j] - floatval($val)) < 0.01 ? 'selected' : '';
                    }
                @endphp

                <div style="display: grid; gap: 16px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; color: var(--text-secondary); font-size: 14px;">Sewa vs Kepadatan Penduduk</label>
                        <select name="matrix[0][1]" class="form-select" style="width: 100%; padding: 12px; border-radius: 8px; background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-primary);">
                            <option value="1" {{ isSelected($savedMatrix, 0, 1, '1') }}>1 - Sama Penting</option>
                            <option value="3" {{ isSelected($savedMatrix, 0, 1, '3') }}>3 - Sewa Sedikit Lebih Penting</option>
                            <option value="5" {{ isSelected($savedMatrix, 0, 1, '5') }}>5 - Sewa Lebih Penting</option>
                            <option value="0.333" {{ isSelected($savedMatrix, 0, 1, '0.333') }}>1/3 - Penduduk Sedikit Lebih Penting</option>
                            <option value="0.2" {{ isSelected($savedMatrix, 0, 1, '0.2') }}>1/5 - Penduduk Lebih Penting</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; color: var(--text-secondary); font-size: 14px;">Sewa vs Kompetitor</label>
                        <select name="matrix[0][2]" class="form-select" style="width: 100%; padding: 12px; border-radius: 8px; background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-primary);">
                            <option value="1" {{ isSelected($savedMatrix, 0, 2, '1') }}>1 - Sama Penting</option>
                            <option value="3" {{ isSelected($savedMatrix, 0, 2, '3') }}>3 - Sewa Sedikit Lebih Penting</option>
                            <option value="5" {{ isSelected($savedMatrix, 0, 2, '5') }}>5 - Sewa Lebih Penting</option>
                            <option value="0.333" {{ isSelected($savedMatrix, 0, 2, '0.333') }}>1/3 - Kompetitor Sedikit Lebih Penting</option>
                            <option value="0.2" {{ isSelected($savedMatrix, 0, 2, '0.2') }}>1/5 - Kompetitor Lebih Penting</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; color: var(--text-secondary); font-size: 14px;">Sewa vs Keamanan</label>
                        <select name="matrix[0][3]" class="form-select" style="width: 100%; padding: 12px; border-radius: 8px; background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-primary);">
                            <option value="1" {{ isSelected($savedMatrix, 0, 3, '1') }}>1 - Sama Penting</option>
                            <option value="3" {{ isSelected($savedMatrix, 0, 3, '3') }}>3 - Sewa Sedikit Lebih Penting</option>
                            <option value="5" {{ isSelected($savedMatrix, 0, 3, '5') }}>5 - Sewa Lebih Penting</option>
                            <option value="0.333" {{ isSelected($savedMatrix, 0, 3, '0.333') }}>1/3 - Keamanan Sedikit Lebih Penting</option>
                            <option value="0.2" {{ isSelected($savedMatrix, 0, 3, '0.2') }}>1/5 - Keamanan Lebih Penting</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; color: var(--text-secondary); font-size: 14px;">Penduduk vs Kompetitor</label>
                        <select name="matrix[1][2]" class="form-select" style="width: 100%; padding: 12px; border-radius: 8px; background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-primary);">
                            <option value="1" {{ isSelected($savedMatrix, 1, 2, '1') }}>1 - Sama Penting</option>
                            <option value="3" {{ isSelected($savedMatrix, 1, 2, '3') }}>3 - Penduduk Sedikit Lebih Penting</option>
                            <option value="5" {{ isSelected($savedMatrix, 1, 2, '5') }}>5 - Penduduk Lebih Penting</option>
                            <option value="0.333" {{ isSelected($savedMatrix, 1, 2, '0.333') }}>1/3 - Kompetitor Sedikit Lebih Penting</option>
                            <option value="0.2" {{ isSelected($savedMatrix, 1, 2, '0.2') }}>1/5 - Kompetitor Lebih Penting</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; color: var(--text-secondary); font-size: 14px;">Penduduk vs Keamanan</label>
                        <select name="matrix[1][3]" class="form-select" style="width: 100%; padding: 12px; border-radius: 8px; background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-primary);">
                            <option value="1" {{ isSelected($savedMatrix, 1, 3, '1') }}>1 - Sama Penting</option>
                            <option value="3" {{ isSelected($savedMatrix, 1, 3, '3') }}>3 - Penduduk Sedikit Lebih Penting</option>
                            <option value="5" {{ isSelected($savedMatrix, 1, 3, '5') }}>5 - Penduduk Lebih Penting</option>
                            <option value="0.333" {{ isSelected($savedMatrix, 1, 3, '0.333') }}>1/3 - Keamanan Sedikit Lebih Penting</option>
                            <option value="0.2" {{ isSelected($savedMatrix, 1, 3, '0.2') }}>1/5 - Keamanan Lebih Penting</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; color: var(--text-secondary); font-size: 14px;">Kompetitor vs Keamanan</label>
                        <select name="matrix[2][3]" class="form-select" style="width: 100%; padding: 12px; border-radius: 8px; background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-primary);">
                            <option value="1" {{ isSelected($savedMatrix, 2, 3, '1') }}>1 - Sama Penting</option>
                            <option value="3" {{ isSelected($savedMatrix, 2, 3, '3') }}>3 - Kompetitor Sedikit Lebih Penting</option>
                            <option value="5" {{ isSelected($savedMatrix, 2, 3, '5') }}>5 - Kompetitor Lebih Penting</option>
                            <option value="0.333" {{ isSelected($savedMatrix, 2, 3, '0.333') }}>1/3 - Keamanan Sedikit Lebih Penting</option>
                            <option value="0.2" {{ isSelected($savedMatrix, 2, 3, '0.2') }}>1/5 - Keamanan Lebih Penting</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top: 32px;">
                    <button type="submit" class="btn-custom" style="width: 100%; padding: 14px; font-size: 16px;">Simpan & Hitung Bobot AHP</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
