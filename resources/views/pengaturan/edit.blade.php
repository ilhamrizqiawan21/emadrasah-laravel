@extends('layouts.app')

@section('title', 'Pengaturan')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h2 class="em-page-title">Pengaturan</h2>
        <p class="text-muted">Atur identitas dan tampilan madrasah. Perubahan langsung berlaku di seluruh aplikasi dan dokumen cetak.</p>
    </div>
</div>

<form action="{{ route('pengaturan.update') }}" method="POST" enctype="multipart/form-data" novalidate>
    @csrf
    @method('PUT')

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Pengaturan belum tersimpan.</strong> Periksa kembali isian yang ditandai merah.
        </div>
    @endif

    {{-- ── Identitas ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent"><h5 class="mb-0">Identitas Madrasah</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nama" class="form-label">Nama madrasah <span class="text-danger">*</span></label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama', $madrasah->nama) }}"
                           class="form-control @error('nama') is-invalid @enderror" required maxlength="150">
                    <div class="form-text">Tampil di dashboard, halaman login, dan footer.</div>
                    @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="nama_pendek" class="form-label">Nama singkat <span class="text-danger">*</span></label>
                    <input type="text" id="nama_pendek" name="nama_pendek" value="{{ old('nama_pendek', $madrasah->nama_pendek) }}"
                           class="form-control @error('nama_pendek') is-invalid @enderror" required maxlength="60">
                    <div class="form-text">Tampil di menu samping, contoh: MTs Al-Ihsan.</div>
                    @error('nama_pendek') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-8">
                    <label for="nama_lengkap" class="form-label">Nama resmi <span class="text-danger">*</span></label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $madrasah->nama_lengkap) }}"
                           class="form-control @error('nama_lengkap') is-invalid @enderror" required maxlength="200">
                    <div class="form-text">Dipakai di kop dan dokumen cetak (PDF).</div>
                    @error('nama_lengkap') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="npsn" class="form-label">NPSN</label>
                    <input type="text" id="npsn" name="npsn" value="{{ old('npsn', $madrasah->npsn) }}" inputmode="numeric"
                           class="form-control @error('npsn') is-invalid @enderror" maxlength="8" placeholder="8 digit">
                    @error('npsn') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ── Alamat & kontak ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent"><h5 class="mb-0">Alamat &amp; Kontak</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea id="alamat" name="alamat" rows="2" maxlength="500"
                              class="form-control @error('alamat') is-invalid @enderror">{{ old('alamat', $madrasah->alamat) }}</textarea>
                    @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="telepon" class="form-label">Telepon</label>
                    <input type="text" id="telepon" name="telepon" value="{{ old('telepon', $madrasah->telepon) }}"
                           class="form-control @error('telepon') is-invalid @enderror" maxlength="30">
                    @error('telepon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $madrasah->email) }}"
                           class="form-control @error('email') is-invalid @enderror" maxlength="150">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="website" class="form-label">Website</label>
                    <input type="url" id="website" name="website" value="{{ old('website', $madrasah->website) }}"
                           class="form-control @error('website') is-invalid @enderror" maxlength="200" placeholder="https://">
                    @error('website') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ── Kepala madrasah ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent"><h5 class="mb-0">Kepala Madrasah</h5></div>
        <div class="card-body">
            <p class="text-muted small">Dicetak pada tanda tangan dokumen.</p>
            <div class="row g-3">
                <div class="col-md-8">
                    <label for="kepala_nama" class="form-label">Nama lengkap dan gelar</label>
                    <input type="text" id="kepala_nama" name="kepala_nama" value="{{ old('kepala_nama', $madrasah->kepala_nama) }}"
                           class="form-control @error('kepala_nama') is-invalid @enderror" maxlength="150">
                    @error('kepala_nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="kepala_nip" class="form-label">NIP</label>
                    <input type="text" id="kepala_nip" name="kepala_nip" value="{{ old('kepala_nip', $madrasah->kepala_nip) }}"
                           class="form-control @error('kepala_nip') is-invalid @enderror" maxlength="30">
                    @error('kepala_nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tampilan ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent"><h5 class="mb-0">Tampilan</h5></div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <label for="logo" class="form-label">Logo</label>
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <img src="{{ $madrasah->logoUrl() }}" alt="Logo saat ini" class="em-brand-preview" id="logoPreview">
                        <input type="file" id="logo" name="logo" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp"
                               class="form-control @error('logo') is-invalid @enderror">
                    </div>
                    <div class="form-text">PNG, JPG, atau WebP. Maksimal 1 MB, 64 sampai 2000 piksel. Disarankan persegi dengan latar transparan.</div>
                    @error('logo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    @if ($madrasah->get('logo'))
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="hapus_logo" value="1" id="hapus_logo">
                            <label class="form-check-label" for="hapus_logo">Kembali ke logo bawaan</label>
                        </div>
                    @endif
                </div>

                <div class="col-md-6">
                    <label for="favicon" class="form-label">Favicon (ikon tab browser)</label>
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <img src="{{ $madrasah->faviconUrl() }}" alt="Favicon saat ini" class="em-favicon-preview">
                        <input type="file" id="favicon" name="favicon" accept=".png,.ico,image/png,image/x-icon"
                               class="form-control @error('favicon') is-invalid @enderror">
                    </div>
                    <div class="form-text">PNG atau ICO, maksimal 256 KB. Jika kosong, logo dipakai sebagai favicon.</div>
                    @error('favicon') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    @if ($madrasah->get('favicon'))
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="hapus_favicon" value="1" id="hapus_favicon">
                            <label class="form-check-label" for="hapus_favicon">Hapus favicon khusus</label>
                        </div>
                    @endif
                </div>

                <div class="col-12">
                    @php($warna = old('warna_utama', $madrasah->warnaUtama()))
                    <label for="warna_utama" class="form-label">Warna utama</label>
                    <div class="em-swatches mb-3">
                        @foreach ($presets as $namaWarna => $hex)
                            <button type="button" class="em-swatch {{ strtolower($warna) === $hex ? 'is-selected' : '' }}"
                                    data-color="{{ $hex }}" style="--swatch: {{ $hex }}"
                                    title="{{ $namaWarna }}" aria-label="Warna {{ $namaWarna }}"></button>
                        @endforeach
                    </div>
                    <div class="d-flex align-items-center gap-2" style="max-width: 280px">
                        <input type="color" id="warnaPicker" value="{{ $warna }}" class="form-control form-control-color" aria-label="Pilih warna">
                        <input type="text" id="warna_utama" name="warna_utama" value="{{ $warna }}" maxlength="7"
                               class="form-control @error('warna_utama') is-invalid @enderror" placeholder="#047857">
                    </div>
                    @error('warna_utama') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    <div class="form-text">Dipakai untuk menu, tombol, dan banner. Warna yang terlalu terang akan ditolak agar teks tetap terbaca.</div>

                    <div class="em-color-preview mt-3 d-flex align-items-center gap-3">
                        <span class="small text-muted">Contoh:</span>
                        <span id="previewButton" class="btn btn-sm text-white" style="background: {{ $warna }}">Tombol</span>
                        <span id="previewText" class="fw-semibold" style="color: {{ $warna }}">Teks tautan</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5">
        <a href="{{ route('dashboard') }}" class="btn btn-light border">Batal</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Pengaturan</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const text = document.getElementById('warna_utama');
    const picker = document.getElementById('warnaPicker');
    const btn = document.getElementById('previewButton');
    const label = document.getElementById('previewText');
    const swatches = document.querySelectorAll('.em-swatch');
    const isHex = v => /^#[0-9a-fA-F]{6}$/.test(v);

    function apply(value) {
        if (!isHex(value)) return;
        btn.style.background = value;
        label.style.color = value;
        picker.value = value;
        swatches.forEach(s => s.classList.toggle('is-selected', s.dataset.color.toLowerCase() === value.toLowerCase()));
    }

    swatches.forEach(s => s.addEventListener('click', () => { text.value = s.dataset.color; apply(s.dataset.color); }));
    picker.addEventListener('input', () => { text.value = picker.value; apply(picker.value); });
    text.addEventListener('input', () => apply(text.value));

    // Pratinjau logo yang baru dipilih, sebelum disimpan.
    document.getElementById('logo').addEventListener('change', function () {
        if (this.files && this.files[0]) {
            document.getElementById('logoPreview').src = URL.createObjectURL(this.files[0]);
        }
    });
});
</script>
@endpush
