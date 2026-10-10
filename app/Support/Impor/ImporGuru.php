<?php

namespace App\Support\Impor;

use App\Models\Guru;

class ImporGuru extends Pengimpor
{
    public function kolom(): array
    {
        return [
            'kode' => ['label' => 'Kode', 'wajib' => true],
            'nama' => ['label' => 'Nama', 'alias' => ['Nama Lengkap', 'Nama Guru'], 'wajib' => true],
            'nip' => ['label' => 'NIP'],
            'bidang_studi' => ['label' => 'Bidang Studi'],
            'email' => ['label' => 'Email'],
            'phone' => ['label' => 'No. HP', 'alias' => ['HP', 'Telepon']],
            'beban_jp' => ['label' => 'Beban JP'],
        ];
    }

    public function petunjuk(): array
    {
        return [
            'Isi data mulai baris ke-2 pada lembar "Data". Jangan mengubah atau menghapus baris judul (baris 1).',
            'Kolom wajib: Kode dan Nama. Kode harus unik (mis. G01).',
            'Beban JP = jumlah jam pelajaran per minggu; dikosongkan = 24.',
            'Guru yang Kode, NIP, atau Email-nya sudah terdaftar dilewati; data lama tidak diubah.',
            'Akun login guru dibuat terpisah lewat menu Pengguna.',
            'Maksimal '.LembarImpor::MAKS_BARIS.' baris per berkas.',
        ];
    }

    protected function siapkan(array $d): array
    {
        $data = [
            'kode' => $this->teks($d['kode'] ?? null),
            'nama' => $this->teks($d['nama'] ?? null),
            'nip' => $this->teks($d['nip'] ?? null),
            'bidang_studi' => $this->teks($d['bidang_studi'] ?? null),
            'email' => $this->teks($d['email'] ?? null),
            'phone' => $this->teks($d['phone'] ?? null),
        ];
        $beban = $this->teks($d['beban_jp'] ?? null);
        if ($beban !== null) {
            $data['beban_jp'] = $beban; // dikosongkan = pakai bawaan database (24)
        }

        return $data;
    }

    protected function aturan(): array
    {
        return [
            'kode' => 'required|string|max:255',
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50',
            'bidang_studi' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'beban_jp' => 'nullable|integer|min:0|max:100',
        ];
    }

    protected function kolomUnik(): array
    {
        return ['kode' => 'Kode', 'nip' => 'NIP', 'email' => 'Email'];
    }

    protected function yangSudahAda(): array
    {
        $ambil = fn (string $kolom) => Guru::whereNotNull($kolom)->pluck($kolom)
            ->mapWithKeys(fn ($v) => [mb_strtolower((string) $v) => true])->all();

        return ['kode' => $ambil('kode'), 'nip' => $ambil('nip'), 'email' => $ambil('email')];
    }

    protected function simpan(array $d): void
    {
        Guru::create($d);
    }
}
