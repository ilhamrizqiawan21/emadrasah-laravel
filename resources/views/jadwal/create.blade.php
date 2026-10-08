@extends('layouts.app')

@section('title', 'Tambah Jadwal Manual')

@section('content')
<x-page-header title="Tambah Jadwal Pelajaran">
    <x-slot:actions>
        <a href="{{ route('jadwal.index') }}" class="btn btn-light border">Kembali</a>
    </x-slot:actions>
</x-page-header>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('jadwal.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kelas <span class="text-danger">*</span></label>
                    <select name="kelas_id" class="form-select @error('kelas_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                    @error('kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Hari <span class="text-danger">*</span></label>
                    <select name="hari" id="hari_select" class="form-select @error('hari') is-invalid @enderror" required>
                        <option value="">-- Pilih Hari --</option>
                        <option value="Senin" {{ old('hari')=='Senin' ? 'selected' : '' }}>Senin</option>
                        <option value="Selasa" {{ old('hari')=='Selasa' ? 'selected' : '' }}>Selasa</option>
                        <option value="Rabu" {{ old('hari')=='Rabu' ? 'selected' : '' }}>Rabu</option>
                        <option value="Kamis" {{ old('hari')=='Kamis' ? 'selected' : '' }}>Kamis</option>
                        <option value="Jumat" {{ old('hari')=='Jumat' ? 'selected' : '' }}>Jumat</option>
                    </select>
                    @error('hari')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Sesi <span class="text-danger">*</span></label>
                    <select name="sesi_id" id="sesi_select" class="form-select" required>
                        <option value="">-- Pilih Hari Terlebih Dahulu --</option>
                    </select>
                    <div class="form-text">Pilih sesi, otomatis mengisi jam mulai & selesai</div>
                </div>

                {{-- Hidden / readonly fields for jam --}}
                <div class="col-md-3 mb-3">
                    <label class="form-label">Jam Mulai</label>
                    <input type="text" name="jam_mulai" id="jam_mulai" class="form-control" readonly required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Jam Selesai</label>
                    <input type="text" name="jam_selesai" id="jam_selesai" class="form-control" readonly required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Guru <span class="text-danger">*</span></label>
                    <select name="guru_id" class="form-select @error('guru_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Guru --</option>
                        @foreach($gurus as $g)
                            <option value="{{ $g->id }}" {{ old('guru_id') == $g->id ? 'selected' : '' }}>{{ $g->kode }} - {{ $g->nama }}</option>
                        @endforeach
                    </select>
                    @error('guru_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                    <select name="mapel_id" class="form-select @error('mapel_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Mapel --</option>
                        @foreach($mapels as $m)
                            <option value="{{ $m->id }}" {{ old('mapel_id') == $m->id ? 'selected' : '' }}>{{ $m->nama_mapel }}</option>
                        @endforeach
                    </select>
                    @error('mapel_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Ruang</label>
                    <input type="text" name="ruang" class="form-control" value="{{ old('ruang') }}" placeholder="Opsional">
                </div>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Data jam pelajaran dari server
    const jamPelajaranData = @json($jamPelajaran);
    const hariSelect = document.getElementById('hari_select');
    const sesiSelect = document.getElementById('sesi_select');
    const jamMulai = document.getElementById('jam_mulai');
    const jamSelesai = document.getElementById('jam_selesai');

    function populateSesi() {
        const selectedHari = hariSelect.value;
        sesiSelect.innerHTML = '<option value="">-- Pilih Sesi --</option>';
        jamMulai.value = '';
        jamSelesai.value = '';
        if (!selectedHari) return;

        const filtered = jamPelajaranData.filter(jp => jp.hari === selectedHari);
        filtered.forEach(jp => {
            const option = document.createElement('option');
            option.value = jp.id;
            option.textContent = `Sesi ${jp.sesi_ke} (${jp.jam_mulai.slice(0,5)} - ${jp.jam_selesai.slice(0,5)})`;
            option.dataset.mulai = jp.jam_mulai;
            option.dataset.selesai = jp.jam_selesai;
            sesiSelect.appendChild(option);
        });
    }

    sesiSelect.addEventListener('change', function() {
        const selectedOption = sesiSelect.options[sesiSelect.selectedIndex];
        if (selectedOption && selectedOption.dataset.mulai) {
            jamMulai.value = selectedOption.dataset.mulai.slice(0,5);
            jamSelesai.value = selectedOption.dataset.selesai.slice(0,5);
        } else {
            jamMulai.value = '';
            jamSelesai.value = '';
        }
    });

    hariSelect.addEventListener('change', populateSesi);
    // Initial populate jika ada old value
    if (hariSelect.value) {
        populateSesi();
        const oldSesi = '{{ old("sesi_id") }}';
        if (oldSesi) {
            setTimeout(() => {
                sesiSelect.value = oldSesi;
                sesiSelect.dispatchEvent(new Event('change'));
            }, 100);
        }
    }
</script>
@endsection