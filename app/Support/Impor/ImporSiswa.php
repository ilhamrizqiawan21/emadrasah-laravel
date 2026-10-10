<?php

namespace App\Support\Impor;

use App\Models\Kelas;
use App\Models\Siswa;

class ImporSiswa extends Pengimpor
{
    /** @var array<string, int>|null */
    private ?array $kelas = null;

    public function kolom(): array
    {
        return [
            'nis' => ['label' => 'NIS', 'wajib' => true],
            'nisn' => ['label' => 'NISN'],
            'nik' => ['label' => 'NIK'],
            'nama_lengkap' => ['label' => 'Nama Lengkap', 'alias' => ['Nama'], 'wajib' => true],
            'kelas' => ['label' => 'Kelas', 'wajib' => true],
            'jenis_kelamin' => ['label' => 'L/P', 'alias' => ['Jenis Kelamin', 'JK'], 'wajib' => true],
            'tempat_lahir' => ['label' => 'Tempat Lahir'],
            'tanggal_lahir' => ['label' => 'Tanggal Lahir'],
            'agama' => ['label' => 'Agama'],
            'alamat' => ['label' => 'Alamat'],
            'nama_orang_tua' => ['label' => 'Nama Orang Tua'],
            'hp' => ['label' => 'No. HP', 'alias' => ['HP', 'Telepon']],
            'status' => ['label' => 'Status'],
        ];
    }

    public function petunjuk(): array
    {
        return [
            'Isi data mulai baris ke-2 pada lembar "Data". Jangan mengubah atau menghapus baris judul (baris 1).',
            'Kolom wajib: NIS, Nama Lengkap, Kelas, L/P.',
            'Kelas harus sama dengan nama kelas yang sudah ada di aplikasi (huruf besar/kecil tidak masalah).',
            'L/P: isi L atau P (juga diterima: Laki-laki, Perempuan).',
            'Tanggal Lahir: 2012-03-06 atau 06/03/2012.',
            'NISN 10 digit dan NIK 16 digit. Jika Excel membuang angka 0 di depan NISN, aplikasi melengkapinya otomatis.',
            'Status: Aktif, Lulus, Pindah, atau Keluar. Dikosongkan = Aktif.',
            'Siswa yang NIS, NISN, atau NIK-nya sudah terdaftar dilewati; data lama tidak diubah.',
            'Maksimal '.LembarImpor::MAKS_BARIS.' baris per berkas.',
        ];
    }

    protected function siapkan(array $d): array
    {
        $nisn = $this->teks($d['nisn'] ?? null);
        if ($nisn !== null && ctype_digit($nisn) && strlen($nisn) < 10) {
            $nisn = str_pad($nisn, 10, '0', STR_PAD_LEFT);
        }

        return [
            'nis' => $this->teks($d['nis'] ?? null),
            'nisn' => $nisn,
            'nik' => $this->teks($d['nik'] ?? null),
            'nama_lengkap' => $this->teks($d['nama_lengkap'] ?? null),
            'kelas' => $this->teks($d['kelas'] ?? null),
            'jenis_kelamin' => $this->jenisKelamin($this->teks($d['jenis_kelamin'] ?? null)),
            'tempat_lahir' => $this->teks($d['tempat_lahir'] ?? null),
            'tanggal_lahir' => $this->tanggal($d['tanggal_lahir'] ?? null),
            'agama' => $this->teks($d['agama'] ?? null),
            'alamat' => $this->teks($d['alamat'] ?? null),
            'nama_orang_tua' => $this->teks($d['nama_orang_tua'] ?? null),
            'hp' => $this->teks($d['hp'] ?? null),
            'status' => ucfirst(mb_strtolower($this->teks($d['status'] ?? null) ?? 'Aktif')),
        ];
    }

    private function jenisKelamin(?string $v): ?string
    {
        return match (mb_strtolower($v ?? '')) {
            'l', 'lk', 'laki-laki', 'laki laki', 'pria' => 'L',
            'p', 'pr', 'perempuan', 'wanita' => 'P',
            default => $v,
        };
    }

    protected function aturan(): array
    {
        return [
            'nis' => 'required|string|max:50',
            'nisn' => 'nullable|digits:10',
            'nik' => 'nullable|digits:16',
            'nama_lengkap' => 'required|string|max:255',
            'kelas' => 'required',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date_format:Y-m-d',
            'agama' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'nama_orang_tua' => 'nullable|string|max:255',
            'hp' => 'nullable|string|max:20',
            'status' => 'required|in:Aktif,Lulus,Pindah,Keluar',
        ];
    }

    protected function periksaTambahan(array &$data): ?string
    {
        $this->kelas ??= Kelas::all()->mapWithKeys(fn ($k) => [mb_strtolower(trim($k->nama_kelas)) => $k->id])->all();

        if ($data['kelas'] === null) {
            return null; // dilaporkan oleh aturan 'required'
        }
        $id = $this->kelas[mb_strtolower($data['kelas'])] ?? null;
        if ($id === null) {
            return 'Kelas "'.$data['kelas'].'" tidak ditemukan. Tambahkan kelasnya lebih dulu di Data Kelas.';
        }
        $data['kelas_id'] = $id;

        return null;
    }

    protected function kolomUnik(): array
    {
        return ['nis' => 'NIS', 'nisn' => 'NISN', 'nik' => 'NIK'];
    }

    protected function yangSudahAda(): array
    {
        // Termasuk siswa yang sudah dihapus: nomor induknya tetap terkunci.
        $ambil = fn (string $kolom) => Siswa::withTrashed()->whereNotNull($kolom)->pluck($kolom)
            ->mapWithKeys(fn ($v) => [mb_strtolower((string) $v) => true])->all();

        return ['nis' => $ambil('nis'), 'nisn' => $ambil('nisn'), 'nik' => $ambil('nik')];
    }

    protected function simpan(array $d): void
    {
        unset($d['kelas']);
        Siswa::create($d);
    }
}
