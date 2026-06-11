@extends('layouts.app')

@section('title', 'Edit Jadwal')

@section('content')
<div class="mb-4">
    <a href="{{ route('jadwal.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <h5>Edit Jadwal</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('jadwal.update', $jadwal) }}">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kelas</label>
                    <select name="kelas_id" class="form-select" required>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" {{ $k->id == $jadwal->kelas_id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Hari</label>
                    <select name="hari" id="hari_select" class="form-select" required>
                        <option value="Senin" {{ $jadwal->hari=='Senin' ? 'selected' : '' }}>Senin</option>
                        <option value="Selasa" {{ $jadwal->hari=='Selasa' ? 'selected' : '' }}>Selasa</option>
                        <option value="Rabu" {{ $jadwal->hari=='Rabu' ? 'selected' : '' }}>Rabu</option>
                        <option value="Kamis" {{ $jadwal->hari=='Kamis' ? 'selected' : '' }}>Kamis</option>
                        <option value="Jumat" {{ $jadwal->hari=='Jumat' ? 'selected' : '' }}>Jumat</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Sesi</label>
                    <select name="sesi_id" id="sesi_select" class="form-select" required>
                        <option value="">-- Pilih Sesi --</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Jam Mulai</label>
                    <input type="text" name="jam_mulai" id="jam_mulai" class="form-control" readonly required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Jam Selesai</label>
                    <input type="text" name="jam_selesai" id="jam_selesai" class="form-control" readonly required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Guru</label>
                    <select name="guru_id" class="form-select" required>
                        @foreach($gurus as $g)
                            <option value="{{ $g->id }}" {{ $g->id == $jadwal->guru_id ? 'selected' : '' }}>{{ $g->kode }} - {{ $g->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Mata Pelajaran</label>
                    <select name="mapel_id" class="form-select" required>
                        @foreach($mapels as $m)
                            <option value="{{ $m->id }}" {{ $m->id == $jadwal->mapel_id ? 'selected' : '' }}>{{ $m->nama_mapel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Ruang</label>
                    <input type="text" name="ruang" class="form-control" value="{{ $jadwal->ruang }}">
                </div>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
            </div>
        </form>
    </div>
</div>

<script>
    const jamPelajaranData = @json($jamPelajaran);
    const currentJamMulai = "{{ substr($jadwal->jam_mulai,0,5) }}";
    const currentJamSelesai = "{{ substr($jadwal->jam_selesai,0,5) }}";
    const currentHari = "{{ $jadwal->hari }}";

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

        // Set selected option berdasarkan jam mulai/selesai yang ada
        if (currentJamMulai && currentJamSelesai) {
            for (let i = 0; i < sesiSelect.options.length; i++) {
                const opt = sesiSelect.options[i];
                if (opt.dataset.mulai && opt.dataset.mulai.slice(0,5) === currentJamMulai && opt.dataset.selesai.slice(0,5) === currentJamSelesai) {
                    opt.selected = true;
                    jamMulai.value = opt.dataset.mulai.slice(0,5);
                    jamSelesai.value = opt.dataset.selesai.slice(0,5);
                    break;
                }
            }
        }
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

    hariSelect.addEventListener('change', function() {
        populateSesi();
    });

    // Initial population
    populateSesi();
</script>
@endsection