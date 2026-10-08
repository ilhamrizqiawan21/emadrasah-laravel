<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaranaSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil id kategori
        $kategori = DB::table('kategori_sarana')->pluck('id', 'nama_kategori');

        $sarana = [
            ['kode_sarana' => 'ELK-001', 'nama_sarana' => 'Proyektor', 'kategori' => 'Elektronik', 'jumlah' => 5, 'kondisi' => 'baik', 'lokasi' => 'Ruang Kelas'],
            ['kode_sarana' => 'ELK-002', 'nama_sarana' => 'Laptop Guru', 'kategori' => 'Elektronik', 'jumlah' => 2, 'kondisi' => 'rusak_ringan', 'lokasi' => 'Kantor Guru'],
            ['kode_sarana' => 'FRN-001', 'nama_sarana' => 'Meja Belajar', 'kategori' => 'Furniture', 'jumlah' => 100, 'kondisi' => 'baik', 'lokasi' => 'Semua Kelas'],
            ['kode_sarana' => 'FRN-002', 'nama_sarana' => 'Kursi Guru', 'kategori' => 'Furniture', 'jumlah' => 30, 'kondisi' => 'baik', 'lokasi' => 'Kantor'],
            ['kode_sarana' => 'APR-001', 'nama_sarana' => 'Globe', 'kategori' => 'Alat Peraga', 'jumlah' => 10, 'kondisi' => 'baik', 'lokasi' => 'Lab IPS'],
            ['kode_sarana' => 'OLR-001', 'nama_sarana' => 'Bola Voli', 'kategori' => 'Olahraga', 'jumlah' => 15, 'kondisi' => 'rusak_berat', 'lokasi' => 'Gudang Olahraga'],
        ];

        foreach ($sarana as $s) {
            DB::table('sarana_prasarana')->updateOrInsert(
                ['kode_sarana' => $s['kode_sarana']],
                [
                    'nama_sarana' => $s['nama_sarana'],
                    'kategori_id' => $kategori[$s['kategori']] ?? 1,
                    'spesifikasi' => null,
                    'jumlah' => $s['jumlah'],
                    'kondisi' => $s['kondisi'],
                    'lokasi_ruang' => $s['lokasi'],
                    'tahun_pengadaan' => null,
                    'foto' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
