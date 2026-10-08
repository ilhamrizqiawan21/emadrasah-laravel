@extends('layouts.app')

@section('title', 'Tambah Surat Keluar')

@section('content')
<x-page-header title="Form Tambah Surat Keluar">
    <x-slot:actions>
        <a href="{{ route('surat-keluar.index') }}" class="btn btn-light border">Kembali</a>
    </x-slot:actions>
</x-page-header>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('surat-keluar.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label" for="cariSiswa">Cari Siswa (Opsional)</label>
                    <div class="position-relative">
                        <input type="search" id="cariSiswa" class="form-control" autocomplete="off"
                               placeholder="Ketik nama atau NIS siswa (minimal 2 huruf)…"
                               aria-controls="hasilSiswa" aria-expanded="false">
                        <div id="hasilSiswa" class="list-group position-absolute w-100 shadow-sm" style="z-index: 20;" hidden></div>
                    </div>
                    <small class="text-muted">Memilih siswa akan otomatis mengisi kolom "Tujuan" di bawah. Menampilkan maksimal 10 hasil.</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Nomor Surat <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_surat" class="form-control @error('nomor_surat') is-invalid @enderror" value="{{ old('nomor_surat') }}" placeholder="Contoh: 001/MTs/XI/2025" required>
                    @error('nomor_surat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tujuan <span class="text-danger">*</span></label>
                    <input type="text" name="tujuan" class="form-control @error('tujuan') is-invalid @enderror" value="{{ old('tujuan') }}" required>
                    @error('tujuan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Perihal <span class="text-danger">*</span></label>
                    <input type="text" name="perihal" class="form-control @error('perihal') is-invalid @enderror" value="{{ old('perihal') }}" required>
                    @error('perihal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Kirim <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_kirim" class="form-control @error('tanggal_kirim') is-invalid @enderror" value="{{ old('tanggal_kirim', date('Y-m-d')) }}" required>
                    @error('tanggal_kirim')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Lampiran</label>
                    <input type="text" name="lampiran" class="form-control @error('lampiran') is-invalid @enderror" value="{{ old('lampiran') }}" placeholder="Contoh: 2 lembar">
                    @error('lampiran')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">File Draft (PDF/DOC)</label>
                    <input type="file" name="file_draft" class="form-control @error('file_draft') is-invalid @enderror" accept=".pdf,.doc,.docx">
                    @error('file_draft')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Maksimal 2MB</small>
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('cariSiswa');
        const list = document.getElementById('hasilSiswa');
        const tujuan = document.getElementsByName('tujuan')[0];
        const url = @json(route('siswa.cari'));
        let timer = null;
        let sequence = 0;

        const hide = () => { list.hidden = true; list.replaceChildren(); input.setAttribute('aria-expanded', 'false'); };

        function show(items) {
            list.replaceChildren();
            if (!items.length) {
                const empty = document.createElement('div');
                empty.className = 'list-group-item text-muted small';
                empty.textContent = 'Siswa tidak ditemukan.';
                list.append(empty);
            }
            items.forEach((item) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'list-group-item list-group-item-action';
                button.textContent = item.label; // textContent: nama siswa tidak pernah dianggap HTML
                button.addEventListener('click', () => { tujuan.value = item.label; input.value = ''; hide(); tujuan.focus(); });
                list.append(button);
            });
            list.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        }

        input.addEventListener('input', () => {
            clearTimeout(timer);
            const q = input.value.trim();
            if (q.length < 2) { hide(); return; }
            timer = setTimeout(async () => {
                const mine = ++sequence; // abaikan jawaban lama yang datang terlambat
                try {
                    const response = await fetch(url + '?q=' + encodeURIComponent(q), { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                    if (response.ok && mine === sequence) show(await response.json());
                } catch (e) { /* jaringan putus: biarkan daftar kosong */ }
            }, 250);
        });

        document.addEventListener('click', (e) => { if (!list.contains(e.target) && e.target !== input) hide(); });
        input.addEventListener('keydown', (e) => { if (e.key === 'Escape') hide(); });
    })();
</script>
@endpush