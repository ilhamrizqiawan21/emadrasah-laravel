<?php

namespace Database\Seeders;

use App\Models\AgendaGuru;
use App\Models\ArsipAkademik;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\OrangTuaWali;
use App\Models\PemeliharaanSarana;
use App\Models\PeminjamanSarana;
use App\Models\PerkembanganSiswa;
use App\Models\RaportEkskul;
use App\Models\RaportKehadiran;
use App\Models\RaportNilai;
use App\Models\RaportPrestasi;
use App\Models\SaranaPrasarana;
use App\Models\Siswa;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\TahunPelajaran;
use App\Models\Task;
use App\Models\TaskLog;
use App\Models\TemplateSurat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Tambahan (Operator & Guru)
        $operator = User::firstOrCreate(
            ['email' => 'operator@madrasah.id'],
            [
                'name' => 'Operator Madrasah',
                'password' => Hash::make('operator123'),
                'role' => 'operator',
                'is_active' => true,
            ]
        );

        $guruUser = User::firstOrCreate(
            ['email' => 'guru@madrasah.id'],
            [
                'name' => 'Ahmad Fauzi, S.Pd.',
                'password' => Hash::make('guru123'),
                'role' => 'guru',
                'is_active' => true,
            ]
        );

        // 2. Tahun Pelajaran
        $tp24 = TahunPelajaran::firstOrCreate(
            ['kode' => '2024/2025'],
            ['nama' => 'Tahun Ajaran 2024/2025', 'is_aktif' => false]
        );
        $tp25 = TahunPelajaran::firstOrCreate(
            ['kode' => '2025/2026'],
            ['nama' => 'Tahun Ajaran 2025/2026', 'is_aktif' => true]
        );

        // 3. Guru & Tenaga Pendidik
        $gurusData = [
            ['kode' => 'G01', 'nama' => 'Ahmad Fauzi, S.Pd.I', 'nip' => '198501152010011001', 'bidang_studi' => 'Al-Quran Hadits', 'user_id' => $guruUser->id, 'email' => 'guru@madrasah.id'],
            ['kode' => 'G02', 'nama' => 'Siti Nurhaliza, S.Pd.', 'nip' => '198803222015022002', 'bidang_studi' => 'Bahasa Indonesia', 'email' => 'siti.nurhaliza@madrasah.id'],
            ['kode' => 'G03', 'nama' => 'Budi Santoso, M.Pd.', 'nip' => '198207102008011003', 'bidang_studi' => 'Matematika', 'email' => 'budi.santoso@madrasah.id'],
            ['kode' => 'G04', 'nama' => 'Dewi Sartika, S.Si.', 'nip' => '199011052019032004', 'bidang_studi' => 'Ilmu Pengetahuan Alam', 'email' => 'dewi.sartika@madrasah.id'],
            ['kode' => 'G05', 'nama' => 'M. Rizky Pratama, S.Pd.', 'nip' => '199204182020011005', 'bidang_studi' => 'Pendidikan Jasmani (PJOK)', 'email' => 'rizky.pratama@madrasah.id'],
            ['kode' => 'G06', 'nama' => 'Fatimah Az-Zahra, Lc.', 'nip' => '199308122022032006', 'bidang_studi' => 'Bahasa Arab', 'email' => 'fatimah.zahra@madrasah.id'],
            ['kode' => 'G07', 'nama' => 'Hendra Wijaya, S.Kom.', 'nip' => '199106202018011007', 'bidang_studi' => 'Informatika', 'email' => 'hendra.wijaya@madrasah.id'],
            ['kode' => 'G08', 'nama' => 'Nurul Hidayah, S.Ag.', 'nip' => '198609252011022008', 'bidang_studi' => 'Fikih', 'email' => 'nurul.hidayah@madrasah.id'],
        ];

        $guruMap = [];
        foreach ($gurusData as $g) {
            $guru = Guru::firstOrCreate(
                ['kode' => $g['kode']],
                [
                    'nama' => $g['nama'],
                    'nip' => $g['nip'],
                    'bidang_studi' => $g['bidang_studi'],
                    'email' => $g['email'] ?? null,
                    'user_id' => $g['user_id'] ?? null,
                    'status' => 'aktif',
                    'beban_jp' => 24,
                ]
            );
            $guruMap[$g['kode']] = $guru;
        }

        // 4. Kelas
        $kelasList = [
            ['nama_kelas' => '7A', 'tingkat' => '7', 'fase' => 'D', 'ruangan' => 'R.01', 'guru_kode' => 'G01'],
            ['nama_kelas' => '7B', 'tingkat' => '7', 'fase' => 'D', 'ruangan' => 'R.02', 'guru_kode' => 'G02'],
            ['nama_kelas' => '8A', 'tingkat' => '8', 'fase' => 'D', 'ruangan' => 'R.03', 'guru_kode' => 'G03'],
            ['nama_kelas' => '8B', 'tingkat' => '8', 'fase' => 'D', 'ruangan' => 'R.04', 'guru_kode' => 'G04'],
            ['nama_kelas' => '9A', 'tingkat' => '9', 'fase' => 'D', 'ruangan' => 'R.05', 'guru_kode' => 'G05'],
            ['nama_kelas' => '9B', 'tingkat' => '9', 'fase' => 'D', 'ruangan' => 'R.06', 'guru_kode' => 'G06'],
        ];

        $kelasMap = [];
        foreach ($kelasList as $k) {
            $wali = $guruMap[$k['guru_kode']] ?? null;
            $kelas = Kelas::firstOrCreate(
                ['nama_kelas' => $k['nama_kelas']],
                [
                    'tingkat' => $k['tingkat'],
                    'fase' => $k['fase'],
                    'ruangan' => $k['ruangan'],
                    'guru_pembimbing_id' => $wali?->id,
                    'kapasitas' => 36,
                ]
            );
            $kelasMap[$k['nama_kelas']] = $kelas;
        }

        // 5. Mata Pelajaran
        $mapelsData = [
            ['nama_mapel' => "Al-Qur'an Hadits", 'jp_per_sesi' => 2],
            ['nama_mapel' => 'Akidah Akhlak', 'jp_per_sesi' => 2],
            ['nama_mapel' => 'Fikih', 'jp_per_sesi' => 2],
            ['nama_mapel' => 'Sejarah Kebudayaan Islam', 'jp_per_sesi' => 2],
            ['nama_mapel' => 'Bahasa Arab', 'jp_per_sesi' => 3],
            ['nama_mapel' => 'Bahasa Indonesia', 'jp_per_sesi' => 4],
            ['nama_mapel' => 'Matematika', 'jp_per_sesi' => 4],
            ['nama_mapel' => 'Ilmu Pengetahuan Alam (IPA)', 'jp_per_sesi' => 4],
            ['nama_mapel' => 'Ilmu Pengetahuan Sosial (IPS)', 'jp_per_sesi' => 3],
            ['nama_mapel' => 'Bahasa Inggris', 'jp_per_sesi' => 3],
            ['nama_mapel' => 'Pendidikan Jasmani (PJOK)', 'jp_per_sesi' => 3],
            ['nama_mapel' => 'Informatika', 'jp_per_sesi' => 2],
        ];

        $mapelMap = [];
        foreach ($mapelsData as $m) {
            $mapel = Mapel::firstOrCreate(
                ['nama_mapel' => $m['nama_mapel']],
                ['jp_per_sesi' => $m['jp_per_sesi']]
            );
            $mapelMap[$m['nama_mapel']] = $mapel;
        }

        // 6. Jam Pelajaran (Sesi Senin s.d. Jumat)
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $sesiList = [
            ['sesi_ke' => 1, 'mulai' => '07:00:00', 'selesai' => '07:40:00'],
            ['sesi_ke' => 2, 'mulai' => '07:40:00', 'selesai' => '08:20:00'],
            ['sesi_ke' => 3, 'mulai' => '08:20:00', 'selesai' => '09:00:00'],
            ['sesi_ke' => 4, 'mulai' => '09:30:00', 'selesai' => '10:10:00'],
            ['sesi_ke' => 5, 'mulai' => '10:10:00', 'selesai' => '10:50:00'],
            ['sesi_ke' => 6, 'mulai' => '10:50:00', 'selesai' => '11:30:00'],
        ];

        $jamMap = [];
        foreach ($hariList as $hari) {
            foreach ($sesiList as $s) {
                $jp = JamPelajaran::firstOrCreate(
                    [
                        'hari' => $hari,
                        'sesi_ke' => $s['sesi_ke'],
                    ],
                    [
                        'jam_mulai' => $s['mulai'],
                        'jam_selesai' => $s['selesai'],
                    ]
                );
                $jamMap[$hari.'_'.$s['sesi_ke']] = $jp;
            }
        }

        // 7. Jadwal Pelajaran Sample
        $jadwalSetup = [
            ['kelas' => '7A', 'hari' => 'Senin', 'sesi' => 1, 'mapel' => "Al-Qur'an Hadits", 'guru' => 'G01', 'ruang' => '7A'],
            ['kelas' => '7A', 'hari' => 'Senin', 'sesi' => 2, 'mapel' => "Al-Qur'an Hadits", 'guru' => 'G01', 'ruang' => '7A'],
            ['kelas' => '7A', 'hari' => 'Senin', 'sesi' => 4, 'mapel' => 'Bahasa Indonesia', 'guru' => 'G02', 'ruang' => '7A'],
            ['kelas' => '7A', 'hari' => 'Senin', 'sesi' => 5, 'mapel' => 'Bahasa Indonesia', 'guru' => 'G02', 'ruang' => '7A'],
            ['kelas' => '7A', 'hari' => 'Selasa', 'sesi' => 1, 'mapel' => 'Matematika', 'guru' => 'G03', 'ruang' => '7A'],
            ['kelas' => '7A', 'hari' => 'Selasa', 'sesi' => 2, 'mapel' => 'Matematika', 'guru' => 'G03', 'ruang' => '7A'],
            ['kelas' => '7A', 'hari' => 'Rabu', 'sesi' => 1, 'mapel' => 'Ilmu Pengetahuan Alam (IPA)', 'guru' => 'G04', 'ruang' => 'Lab IPA'],
            ['kelas' => '7A', 'hari' => 'Kamis', 'sesi' => 1, 'mapel' => 'Pendidikan Jasmani (PJOK)', 'guru' => 'G05', 'ruang' => 'Lapangan'],
            ['kelas' => '7A', 'hari' => 'Jumat', 'sesi' => 1, 'mapel' => 'Fikih', 'guru' => 'G08', 'ruang' => '7A'],

            ['kelas' => '8A', 'hari' => 'Senin', 'sesi' => 1, 'mapel' => 'Matematika', 'guru' => 'G03', 'ruang' => '8A'],
            ['kelas' => '8A', 'hari' => 'Senin', 'sesi' => 4, 'mapel' => 'Bahasa Arab', 'guru' => 'G06', 'ruang' => '8A'],
            ['kelas' => '8A', 'hari' => 'Selasa', 'sesi' => 1, 'mapel' => 'Informatika', 'guru' => 'G07', 'ruang' => 'Lab Komputer'],
            ['kelas' => '8A', 'hari' => 'Rabu', 'sesi' => 1, 'mapel' => 'Bahasa Indonesia', 'guru' => 'G02', 'ruang' => '8A'],

            ['kelas' => '9A', 'hari' => 'Senin', 'sesi' => 1, 'mapel' => 'Ilmu Pengetahuan Alam (IPA)', 'guru' => 'G04', 'ruang' => '9A'],
            ['kelas' => '9A', 'hari' => 'Selasa', 'sesi' => 4, 'mapel' => "Al-Qur'an Hadits", 'guru' => 'G01', 'ruang' => '9A'],
        ];

        foreach ($jadwalSetup as $js) {
            $k = $kelasMap[$js['kelas']] ?? null;
            $m = $mapelMap[$js['mapel']] ?? null;
            $g = $guruMap[$js['guru']] ?? null;
            $jp = $jamMap[$js['hari'].'_'.$js['sesi']] ?? null;

            if ($k && $m && $g && $jp) {
                Jadwal::firstOrCreate(
                    [
                        'kelas_id' => $k->id,
                        'hari' => $js['hari'],
                        'jam_pelajaran_id' => $jp->id,
                    ],
                    [
                        'guru_id' => $g->id,
                        'mapel_id' => $m->id,
                        'jam_mulai' => $jp->jam_mulai,
                        'jam_selesai' => $jp->jam_selesai,
                        'ruang' => $js['ruang'],
                        'tahun_pelajaran_kode' => '2025/2026',
                    ]
                );
            }
        }

        // 8. Data Siswa, Orang Tua & Perkembangan (Buku Induk)
        $siswaData = [
            [
                'nis' => '2425001', 'nisn' => '0091234561', 'nik' => '3201014502090001',
                'nama_lengkap' => 'Muhammad Rayhan Pratama', 'jenis_kelamin' => 'L',
                'tempat_lahir' => 'Bandung Barat', 'tanggal_lahir' => '2011-04-15',
                'kelas' => '7A', 'alamat' => 'Kp. Batujajar Tengah RT 02/05', 'hp' => '081234567891',
                'ayah' => 'Dedi Pratama', 'pekerjaan_ayah' => 'Wiraswasta',
                'ibu' => 'Siti Aisyah', 'pekerjaan_ibu' => 'Ibu Rumah Tangga',
                'asal_madrasah' => 'MI Al-Ihsan Batujajar',
            ],
            [
                'nis' => '2425002', 'nisn' => '0091234562', 'nik' => '3201014603100002',
                'nama_lengkap' => 'Aisyah Putri Azzahra', 'jenis_kelamin' => 'P',
                'tempat_lahir' => 'Cimahi', 'tanggal_lahir' => '2011-06-20',
                'kelas' => '7A', 'alamat' => 'Jl. Raya Batujajar No. 45', 'hp' => '081234567892',
                'ayah' => 'Rahmat Hidayat', 'pekerjaan_ayah' => 'Karyawan Swasta',
                'ibu' => 'Nurhayati', 'pekerjaan_ibu' => 'Guru',
                'asal_madrasah' => 'SDN Batujajar 1',
            ],
            [
                'nis' => '2425003', 'nisn' => '0091234563', 'nik' => '3201014705110003',
                'nama_lengkap' => 'Alif Ramadhan Hakim', 'jenis_kelamin' => 'L',
                'tempat_lahir' => 'Bandung', 'tanggal_lahir' => '2011-09-02',
                'kelas' => '7B', 'alamat' => 'Kp. Babakan Sari RT 01/03', 'hp' => '081234567893',
                'ayah' => 'Lukman Hakim', 'pekerjaan_ayah' => 'PNS',
                'ibu' => 'Farida', 'pekerjaan_ibu' => 'Karyawan Swasta',
                'asal_madrasah' => 'MI Nurul Iman',
            ],
            [
                'nis' => '2324001', 'nisn' => '0081234564', 'nik' => '3201014807120004',
                'nama_lengkap' => 'Zahra Amelia Ramadhani', 'jenis_kelamin' => 'P',
                'tempat_lahir' => 'Bandung Barat', 'tanggal_lahir' => '2010-08-14',
                'kelas' => '8A', 'alamat' => 'Ds. Galanggang RT 03/02', 'hp' => '081234567894',
                'ayah' => 'Agus Ramadhan', 'pekerjaan_ayah' => 'Pedagang',
                'ibu' => 'Dewi Yuliani', 'pekerjaan_ibu' => 'Ibu Rumah Tangga',
                'asal_madrasah' => 'SDN Batujajar 2',
            ],
            [
                'nis' => '2324002', 'nisn' => '0081234565', 'nik' => '3201014908130005',
                'nama_lengkap' => 'Fathan Mubina Akbar', 'jenis_kelamin' => 'L',
                'tempat_lahir' => 'Cimahi', 'tanggal_lahir' => '2010-10-18',
                'kelas' => '8B', 'alamat' => 'Kp. Cangkorah RT 04/01', 'hp' => '081234567895',
                'ayah' => 'H. Syamsudin', 'pekerjaan_ayah' => 'Wiraswasta',
                'ibu' => 'Hj. Mardiah', 'pekerjaan_ibu' => 'Wiraswasta',
                'asal_madrasah' => 'MI Al-Ihsan Batujajar',
            ],
            [
                'nis' => '2223001', 'nisn' => '0071234566', 'nik' => '3201015009140006',
                'nama_lengkap' => 'Nabila Syakira Anwar', 'jenis_kelamin' => 'P',
                'tempat_lahir' => 'Bandung', 'tanggal_lahir' => '2009-02-11',
                'kelas' => '9A', 'alamat' => 'Jl. Babakan Pari RT 02/04', 'hp' => '081234567896',
                'ayah' => 'Anwar Sanusi', 'pekerjaan_ayah' => 'PNS',
                'ibu' => 'Enok Rohaeti', 'pekerjaan_ibu' => 'Ibu Rumah Tangga',
                'asal_madrasah' => 'MI Al-Hidayah',
            ],
        ];

        $createdSiswa = [];
        foreach ($siswaData as $sd) {
            $k = $kelasMap[$sd['kelas']] ?? null;
            $siswa = Siswa::firstOrCreate(
                ['nis' => $sd['nis']],
                [
                    'nisn' => $sd['nisn'],
                    'nik' => $sd['nik'],
                    'nama_lengkap' => $sd['nama_lengkap'],
                    'jenis_kelamin' => $sd['jenis_kelamin'],
                    'tempat_lahir' => $sd['tempat_lahir'],
                    'tanggal_lahir' => $sd['tanggal_lahir'],
                    'kelas_id' => $k?->id,
                    'alamat' => $sd['alamat'],
                    'hp' => $sd['hp'],
                    'status' => 'Aktif',
                    'tahun_pelajaran_id' => $tp25->id,
                    'agama' => 'Islam',
                ]
            );
            $createdSiswa[] = $siswa;

            OrangTuaWali::updateOrCreate(
                ['siswa_id' => $siswa->id],
                [
                    'nama_ayah' => $sd['ayah'],
                    'pekerjaan_ayah' => $sd['pekerjaan_ayah'],
                    'nama_ibu' => $sd['ibu'],
                    'pekerjaan_ibu' => $sd['pekerjaan_ibu'],
                ]
            );

            PerkembanganSiswa::updateOrCreate(
                ['siswa_id' => $siswa->id],
                [
                    'asal_madrasah' => $sd['asal_madrasah'],
                    'no_ijazah_asal' => 'DN-02/D-MI/13/'.rand(100000, 999999),
                ]
            );
        }

        // 9. Raport Nilai Sample untuk Siswa Pertama
        if (! empty($createdSiswa)) {
            $siswaSample = $createdSiswa[0];
            $daftarMapel = Mapel::all();
            foreach ($daftarMapel as $idx => $mpl) {
                $nilaiAngka = rand(82, 95);
                RaportNilai::updateOrCreate(
                    [
                        'siswa_id' => $siswaSample->id,
                        'tahun_pelajaran_id' => $tp25->id,
                        'semester' => 1,
                        'mapel_id' => $mpl->id,
                    ],
                    [
                        'nilai_akhir' => $nilaiAngka,
                        'kktp' => 75,
                        'deskripsi' => 'Menunjukkan pemahaman yang sangat baik dalam memahami materi serta mampu menyelesaikan tugas dengan mandiri.',
                    ]
                );
            }

            // Kehadiran Raport
            RaportKehadiran::updateOrCreate(
                [
                    'siswa_id' => $siswaSample->id,
                    'tahun_pelajaran_id' => $tp25->id,
                    'semester' => 1,
                ],
                [
                    'sakit' => 1,
                    'ijin' => 0,
                    'tanpa_keterangan' => 0,
                ]
            );

            // Ekskul Raport
            RaportEkskul::updateOrCreate(
                [
                    'siswa_id' => $siswaSample->id,
                    'tahun_pelajaran_id' => $tp25->id,
                    'semester' => 1,
                    'nama_ekskul' => 'Pramuka',
                ],
                [
                    'keterangan' => 'Aktif dalam kegiatan kepramukaan dan menunjukkan jiwa kepemimpinan.',
                    'nilai' => 'A',
                    'urut' => 1,
                ]
            );
            RaportEkskul::updateOrCreate(
                [
                    'siswa_id' => $siswaSample->id,
                    'tahun_pelajaran_id' => $tp25->id,
                    'semester' => 1,
                    'nama_ekskul' => 'Tahfidz Al-Qur\'an',
                ],
                [
                    'keterangan' => 'Telah menyelesaikan hafalan Juz 30 dengan tartil dan tajwid yang baik.',
                    'nilai' => 'A',
                    'urut' => 2,
                ]
            );

            // Prestasi Raport
            RaportPrestasi::updateOrCreate(
                [
                    'siswa_id' => $siswaSample->id,
                    'tahun_pelajaran_id' => $tp25->id,
                    'semester' => 1,
                    'jenis_prestasi' => 'Juara 2 MTQ Tingkat Kecamatan',
                ],
                [
                    'keterangan' => 'Lomba Tilawatil Qur\'an dalam rangka Hari Amal Bakti Kemenag.',
                    'nilai' => 'Tingkat Kecamatan',
                    'urut' => 1,
                ]
            );
        }

        // 10. Agenda & Absensi Guru (7 Hari Terakhir)
        $allGurus = Guru::all();
        for ($i = 6; $i >= 0; $i--) {
            $tgl = today()->subDays($i);
            // Lewati hari Minggu
            if ($tgl->isSunday()) {
                continue;
            }
            foreach ($allGurus as $gIdx => $guru) {
                $status = ($gIdx === 3 && $i === 1) ? 'izin' : (($gIdx === 5 && $i === 2) ? 'sakit' : 'hadir');
                $ket = $status === 'izin' ? 'Keperluan dinas luar' : ($status === 'sakit' ? 'Sakit flu dan demam' : null);
                AgendaGuru::updateOrCreate(
                    ['tanggal' => $tgl, 'guru_id' => $guru->id],
                    ['status' => $status, 'keterangan' => $ket]
                );
            }
        }

        // 11. Surat Masuk
        $suratMasukList = [
            [
                'nomor_agenda' => 'SM-2026-001',
                'asal_surat' => 'Kantor Kementerian Agama Kab. Bandung Barat',
                'nomor_surat' => 'B-120/Kk.10.19/2/PP.00/01/2026',
                'perihal' => 'Sosialisasi Asesmen Bakat Minat (ABM) Madrasah Tahun 2026',
                'tanggal_terima' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'tanggal_surat' => Carbon::now()->subDays(12)->format('Y-m-d'),
                'disposisi' => 'Harap dikoordinasikan dengan Wakamad Kurikulum dan Guru BK.',
                'status' => 'selesai',
            ],
            [
                'nomor_agenda' => 'SM-2026-002',
                'asal_surat' => 'Puskesmas Batujajar',
                'nomor_surat' => '440/082/PKM-BTJ/2026',
                'perihal' => 'Jadwal Pemeriksaan Kesehatan Berkala & Imunisasi Siswa',
                'tanggal_terima' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'tanggal_surat' => Carbon::now()->subDays(7)->format('Y-m-d'),
                'disposisi' => 'Koordinasikan jadwal dengan wali kelas 7 dan pembina UKS.',
                'status' => 'diproses',
            ],
            [
                'nomor_agenda' => 'SM-2026-003',
                'asal_surat' => 'Kelompok Kerja Madrasah Tsanawiyah (KKM-MTs)',
                'nomor_surat' => '045/KKM-MTs.03/I/2026',
                'perihal' => 'Undangan Rapat Koordinasi Persiapan Ujian Madrasah',
                'tanggal_terima' => Carbon::now()->subDays(2)->format('Y-m-d'),
                'tanggal_surat' => Carbon::now()->subDays(3)->format('Y-m-d'),
                'disposisi' => 'Kepala Madrasah dan Waka Kurikulum hadir pada jadwal tertera.',
                'status' => 'diterima',
            ],
        ];

        foreach ($suratMasukList as $sm) {
            SuratMasuk::firstOrCreate(['nomor_agenda' => $sm['nomor_agenda']], $sm);
        }

        // 12. Surat Keluar
        $suratKeluarList = [
            [
                'nomor_surat' => '089/MTs.Al-Ihsan/PP.005/01/2026',
                'tujuan' => 'Orang Tua / Wali Murid Kelas 7, 8, dan 9',
                'perihal' => 'Pemberitahuan Pelaksanaan Penilaian Tengah Semester (PTS) Genap',
                'tanggal_kirim' => Carbon::now()->subDays(8)->format('Y-m-d'),
                'lampiran' => '1 Berkas Jadwal',
            ],
            [
                'nomor_surat' => '090/MTs.Al-Ihsan/PP.005/01/2026',
                'tujuan' => 'Kantor Kemenag Kab. Bandung Barat',
                'perihal' => 'Laporan Mutasi Masuk dan Keluar Peserta Didik Semester Ganjil',
                'tanggal_kirim' => Carbon::now()->subDays(4)->format('Y-m-d'),
                'lampiran' => '1 Bundel Rekap EMIS',
            ],
        ];

        foreach ($suratKeluarList as $sk) {
            SuratKeluar::firstOrCreate(['nomor_surat' => $sk['nomor_surat']], $sk);
        }

        // 13. Template Surat
        $templates = [
            [
                'nama_template' => 'Surat Keterangan Aktif Siswa',
                'konten' => "Yang bertanda tangan di bawah ini Kepala MTs Al-Ihsan Batujajar, dengan ini menerangkan bahwa:\n\nNama: [NAMA_SISWA]\nNIS / NISN: [NIS] / [NISN]\nKelas: [KELAS]\n\nAdalah benar peserta didik aktif pada MTs Al-Ihsan Batujajar Tahun Pelajaran [TAHUN_PELAJARAN]. Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.",
            ],
            [
                'nama_template' => 'Surat Undangan Rapat Komite',
                'konten' => "Kepada Yth. Bapak/Ibu Orang Tua/Wali Murid di Tempat.\n\nAssalamu'alaikum Wr. Wb.\nMengharap kehadiran Bapak/Ibu pada:\nHari/Tanggal: [HARI_TANGGAL]\nWaktu: 08.30 WIB - Selesai\nTempat: Aula MTs Al-Ihsan Batujajar\nAcara: Rapat Koordinasi Komite Madrasah.\n\nWassalamu'alaikum Wr. Wb.",
            ],
            [
                'nama_template' => 'Surat Tugas Pendidik & Tenaga Kependidikan',
                'konten' => "SURAT TUGAS\nNomor: [NOMOR_SURAT]\n\nKepala Madrasah menugaskan kepada:\nNama: [NAMA_GURU]\nNIP: [NIP]\nJabatan: [JABATAN]\n\nUntuk mengikuti kegiatan [NAMA_KEGIATAN] pada tanggal [TANGGAL_KEGIATAN] bertempat di [TEMPAT_KEGIATAN].",
            ],
        ];

        foreach ($templates as $t) {
            TemplateSurat::firstOrCreate(['nama_template' => $t['nama_template']], $t);
        }

        // 14. Tasks (Manajemen Tugas TU)
        $adminUser = User::where('role', 'admin')->first();
        $taskList = [
            [
                'judul' => 'Rekapitulasi Absensi Bulanan Guru & Staff',
                'deskripsi' => 'Cetak laporan absensi guru bulan berjalan untuk lampiran laporan tunjangan profesi.',
                'assigned_to' => $operator->id,
                'prioritas' => 'tinggi',
                'deadline' => Carbon::now()->addDays(2)->format('Y-m-d'),
                'status' => 'proses',
                'kategori' => 'Kepegawaian',
                'progress_persen' => 60,
                'created_by' => $adminUser?->id,
            ],
            [
                'judul' => 'Verifikasi Data Brankas Dokumen Siswa Baru (Buku Induk)',
                'deskripsi' => 'Pengecekan kelengkapan NIK, KK, dan Akta Kelahiran pada buku induk digital.',
                'assigned_to' => $operator->id,
                'prioritas' => 'sedang',
                'deadline' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'status' => 'antrean',
                'kategori' => 'Kesiswaan',
                'progress_persen' => 20,
                'created_by' => $adminUser?->id,
            ],
            [
                'judul' => 'Pemeriksaan Inventaris Alat Lab Komputer',
                'deskripsi' => 'Cek kelayakan proyektor dan PC laboratorium sebelum ujian semester.',
                'assigned_to' => $adminUser?->id,
                'prioritas' => 'tinggi',
                'deadline' => Carbon::now()->addDays(3)->format('Y-m-d'),
                'status' => 'proses',
                'kategori' => 'Sarpras',
                'progress_persen' => 45,
                'created_by' => $adminUser?->id,
            ],
            [
                'judul' => 'Penyusunan Kalender Akademik Semester Genap',
                'deskripsi' => 'Menyusun agenda libur, ujian madrasah, dan kegiatan ekstra bersama guru.',
                'assigned_to' => $adminUser?->id,
                'prioritas' => 'sedang',
                'deadline' => Carbon::now()->subDays(1)->format('Y-m-d'),
                'status' => 'selesai',
                'kategori' => 'Kurikulum',
                'progress_persen' => 100,
                'created_by' => $adminUser?->id,
            ],
        ];

        foreach ($taskList as $tk) {
            $task = Task::firstOrCreate(['judul' => $tk['judul']], $tk);
            TaskLog::firstOrCreate(
                ['task_id' => $task->id, 'action' => 'Tugas dibuat'],
                [
                    'user_id' => $adminUser?->id ?? 1,
                    'keterangan' => 'Inisiasi tugas: '.$task->judul,
                    'created_at' => now(),
                ]
            );
        }

        // 15. Peminjaman & Pemeliharaan Sarana
        $proyektor = SaranaPrasarana::where('kode_sarana', 'ELK-001')->first();
        $laptop = SaranaPrasarana::where('kode_sarana', 'ELK-002')->first();
        if ($proyektor) {
            PeminjamanSarana::firstOrCreate(
                [
                    'sarana_id' => $proyektor->id,
                    'peminjam' => 'Ahmad Fauzi, S.Pd.I',
                    'tanggal_pinjam' => Carbon::now()->subDays(1)->format('Y-m-d'),
                ],
                [
                    'tipe_peminjam' => 'guru',
                    'status' => 'dipinjam',
                ]
            );
            PeminjamanSarana::firstOrCreate(
                [
                    'sarana_id' => $proyektor->id,
                    'peminjam' => 'Muhammad Rayhan Pratama',
                    'tanggal_pinjam' => Carbon::now()->subDays(4)->format('Y-m-d'),
                ],
                [
                    'tipe_peminjam' => 'siswa',
                    'tanggal_kembali' => Carbon::now()->subDays(4)->format('Y-m-d'),
                    'status' => 'dikembalikan',
                ]
            );
        }

        if ($laptop) {
            PemeliharaanSarana::firstOrCreate(
                [
                    'sarana_id' => $laptop->id,
                    'tanggal_pemeliharaan' => Carbon::now()->subDays(3)->format('Y-m-d'),
                ],
                [
                    'biaya' => 250000,
                    'keterangan' => 'Ganti pasta pendingin prosesor dan install ulang sistem operasi',
                    'status' => 'selesai',
                    'tanggal_selesai' => Carbon::now()->subDays(1)->format('Y-m-d'),
                    'teknisi' => 'CV Media Sarana Bandung',
                ]
            );
        }

        // 16. Arsip Akademik Sample
        $kelas7A = Kelas::where('nama_kelas', '7A')->first();
        if ($kelas7A && $tp25) {
            ArsipAkademik::firstOrCreate(
                [
                    'nama_arsip' => 'Buku Leger Nilai Semester Ganjil 7A 2025-2026',
                    'tahun_pelajaran_id' => $tp25->id,
                    'kelas_id' => $kelas7A->id,
                ],
                [
                    'semester' => '1',
                    'tipe' => 'Leger',
                    'file_path' => 'arsip-akademik/sample_leger_7a.pdf',
                ]
            );
            ArsipAkademik::firstOrCreate(
                [
                    'nama_arsip' => 'Rekapitulasi Rapor Digital Madrasah (RDM) 7A',
                    'tahun_pelajaran_id' => $tp25->id,
                    'kelas_id' => $kelas7A->id,
                ],
                [
                    'semester' => '1',
                    'tipe' => 'RDM',
                    'file_path' => 'arsip-akademik/sample_rdm_7a.xlsx',
                ]
            );
        }
    }
}
