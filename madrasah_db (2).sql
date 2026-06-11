-- Adminer 5.3.0 MySQL 8.4.3 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `agenda_guru`;
CREATE TABLE `agenda_guru` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `guru_id` bigint unsigned NOT NULL,
  `status` enum('hadir','izin','sakit','alpha') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'hadir',
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agenda_guru_tanggal_guru_id_unique` (`tanggal`,`guru_id`),
  KEY `agenda_guru_guru_id_foreign` (`guru_id`),
  CONSTRAINT `agenda_guru_guru_id_foreign` FOREIGN KEY (`guru_id`) REFERENCES `gurus` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `agenda_guru` (`id`, `tanggal`, `guru_id`, `status`, `keterangan`, `created_at`, `updated_at`) VALUES
(1,	'2026-05-19',	55,	'hadir',	NULL,	'2026-05-19 07:44:27',	'2026-05-19 07:44:27'),
(2,	'2026-05-19',	46,	'sakit',	NULL,	'2026-05-19 07:44:27',	'2026-05-19 07:44:27'),
(3,	'2026-05-19',	53,	'hadir',	NULL,	'2026-05-19 07:44:27',	'2026-05-19 07:44:27'),
(4,	'2026-05-19',	48,	'hadir',	NULL,	'2026-05-19 07:44:27',	'2026-05-19 07:44:27'),
(5,	'2026-05-19',	52,	'hadir',	NULL,	'2026-05-19 07:44:27',	'2026-05-19 07:44:27'),
(6,	'2026-05-19',	57,	'hadir',	NULL,	'2026-05-19 07:44:27',	'2026-05-19 07:44:27'),
(7,	'2026-05-19',	35,	'hadir',	NULL,	'2026-05-19 07:44:27',	'2026-05-19 07:44:27'),
(8,	'2026-05-19',	33,	'hadir',	NULL,	'2026-05-19 07:44:28',	'2026-05-19 07:44:28'),
(9,	'2026-05-19',	37,	'hadir',	NULL,	'2026-05-19 07:44:28',	'2026-05-19 07:44:28'),
(10,	'2026-05-19',	42,	'hadir',	NULL,	'2026-05-19 07:44:28',	'2026-05-19 07:44:28'),
(11,	'2026-05-19',	45,	'hadir',	NULL,	'2026-05-19 07:44:28',	'2026-05-19 07:44:28'),
(12,	'2026-05-19',	34,	'hadir',	NULL,	'2026-05-19 07:44:28',	'2026-05-19 07:44:28'),
(13,	'2026-05-19',	39,	'hadir',	NULL,	'2026-05-19 07:44:28',	'2026-05-19 07:44:28'),
(14,	'2026-05-19',	44,	'hadir',	NULL,	'2026-05-19 07:44:28',	'2026-05-19 07:44:28'),
(15,	'2026-05-19',	49,	'hadir',	NULL,	'2026-05-19 07:44:28',	'2026-05-19 07:44:28'),
(16,	'2026-05-19',	40,	'hadir',	NULL,	'2026-05-19 07:44:28',	'2026-05-19 07:44:28'),
(17,	'2026-05-19',	58,	'hadir',	NULL,	'2026-05-19 07:44:29',	'2026-05-19 07:44:29'),
(18,	'2026-05-19',	54,	'hadir',	NULL,	'2026-05-19 07:44:29',	'2026-05-19 07:44:29'),
(19,	'2026-05-19',	56,	'hadir',	NULL,	'2026-05-19 07:44:29',	'2026-05-19 07:44:29'),
(20,	'2026-05-19',	50,	'hadir',	NULL,	'2026-05-19 07:44:29',	'2026-05-19 07:44:29'),
(21,	'2026-05-19',	36,	'hadir',	NULL,	'2026-05-19 07:44:29',	'2026-05-19 07:44:29'),
(22,	'2026-05-19',	43,	'hadir',	NULL,	'2026-05-19 07:44:29',	'2026-05-19 07:44:29'),
(23,	'2026-05-19',	38,	'hadir',	NULL,	'2026-05-19 07:44:29',	'2026-05-19 07:44:29'),
(24,	'2026-05-19',	41,	'hadir',	NULL,	'2026-05-19 07:44:29',	'2026-05-19 07:44:29'),
(25,	'2026-05-19',	47,	'hadir',	NULL,	'2026-05-19 07:44:29',	'2026-05-19 07:44:29'),
(26,	'2026-05-19',	51,	'hadir',	NULL,	'2026-05-19 07:44:30',	'2026-05-19 07:44:30'),
(27,	'2026-05-29',	55,	'sakit',	NULL,	'2026-05-29 05:30:37',	'2026-05-29 05:30:37'),
(28,	'2026-05-29',	46,	'hadir',	NULL,	'2026-05-29 05:30:39',	'2026-05-29 05:30:39'),
(29,	'2026-05-29',	53,	'hadir',	NULL,	'2026-05-29 05:30:39',	'2026-05-29 05:30:39'),
(30,	'2026-05-29',	48,	'hadir',	NULL,	'2026-05-29 05:30:39',	'2026-05-29 05:30:39'),
(31,	'2026-05-29',	52,	'hadir',	NULL,	'2026-05-29 05:30:39',	'2026-05-29 05:30:39'),
(32,	'2026-05-29',	57,	'hadir',	NULL,	'2026-05-29 05:30:39',	'2026-05-29 05:30:39'),
(33,	'2026-05-29',	35,	'hadir',	NULL,	'2026-05-29 05:30:39',	'2026-05-29 05:30:39'),
(34,	'2026-05-29',	33,	'hadir',	NULL,	'2026-05-29 05:30:39',	'2026-05-29 05:30:39'),
(35,	'2026-05-29',	37,	'hadir',	NULL,	'2026-05-29 05:30:39',	'2026-05-29 05:30:39'),
(36,	'2026-05-29',	42,	'hadir',	NULL,	'2026-05-29 05:30:39',	'2026-05-29 05:30:39'),
(37,	'2026-05-29',	45,	'hadir',	NULL,	'2026-05-29 05:30:39',	'2026-05-29 05:30:39'),
(38,	'2026-05-29',	34,	'hadir',	NULL,	'2026-05-29 05:30:39',	'2026-05-29 05:30:39'),
(39,	'2026-05-29',	39,	'hadir',	NULL,	'2026-05-29 05:30:39',	'2026-05-29 05:30:39'),
(40,	'2026-05-29',	44,	'hadir',	NULL,	'2026-05-29 05:30:39',	'2026-05-29 05:30:39'),
(41,	'2026-05-29',	49,	'hadir',	NULL,	'2026-05-29 05:30:39',	'2026-05-29 05:30:39'),
(42,	'2026-05-29',	40,	'hadir',	NULL,	'2026-05-29 05:30:40',	'2026-05-29 05:30:40'),
(43,	'2026-05-29',	58,	'hadir',	NULL,	'2026-05-29 05:30:40',	'2026-05-29 05:30:40'),
(44,	'2026-05-29',	54,	'hadir',	NULL,	'2026-05-29 05:30:40',	'2026-05-29 05:30:40'),
(45,	'2026-05-29',	56,	'hadir',	NULL,	'2026-05-29 05:30:40',	'2026-05-29 05:30:40'),
(46,	'2026-05-29',	50,	'hadir',	NULL,	'2026-05-29 05:30:40',	'2026-05-29 05:30:40'),
(47,	'2026-05-29',	36,	'hadir',	NULL,	'2026-05-29 05:30:40',	'2026-05-29 05:30:40'),
(48,	'2026-05-29',	43,	'hadir',	NULL,	'2026-05-29 05:30:40',	'2026-05-29 05:30:40'),
(49,	'2026-05-29',	38,	'hadir',	NULL,	'2026-05-29 05:30:40',	'2026-05-29 05:30:40'),
(50,	'2026-05-29',	41,	'hadir',	NULL,	'2026-05-29 05:30:40',	'2026-05-29 05:30:40'),
(51,	'2026-05-29',	47,	'hadir',	NULL,	'2026-05-29 05:30:40',	'2026-05-29 05:30:40'),
(52,	'2026-05-29',	51,	'hadir',	NULL,	'2026-05-29 05:30:40',	'2026-05-29 05:30:40');

DROP TABLE IF EXISTS `arsip_akademik`;
CREATE TABLE `arsip_akademik` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tahun_pelajaran_id` smallint unsigned NOT NULL,
  `kelas_id` bigint unsigned NOT NULL,
  `semester` tinyint unsigned NOT NULL COMMENT '1=Ganjil, 2=Genap',
  `nama_arsip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Contoh: Leger Kelas 7A, Raport Kolektif',
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe` enum('Leger','RDM','Lainnya') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Leger',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `arsip_akademik_tahun_pelajaran_id_foreign` (`tahun_pelajaran_id`),
  KEY `arsip_akademik_kelas_id_foreign` (`kelas_id`),
  CONSTRAINT `arsip_akademik_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `arsip_akademik_tahun_pelajaran_id_foreign` FOREIGN KEY (`tahun_pelajaran_id`) REFERENCES `tahun_pelajaran` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `guru_pengganti`;
CREATE TABLE `guru_pengganti` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agenda_guru_id` bigint unsigned NOT NULL,
  `jam_pelajaran_id` bigint unsigned NOT NULL,
  `guru_pengganti_id` bigint unsigned NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `agenda_guru_id` (`agenda_guru_id`),
  KEY `jam_pelajaran_id` (`jam_pelajaran_id`),
  KEY `guru_pengganti_id` (`guru_pengganti_id`),
  CONSTRAINT `guru_pengganti_ibfk_1` FOREIGN KEY (`agenda_guru_id`) REFERENCES `agenda_guru` (`id`) ON DELETE CASCADE,
  CONSTRAINT `guru_pengganti_ibfk_2` FOREIGN KEY (`jam_pelajaran_id`) REFERENCES `jam_pelajaran` (`id`),
  CONSTRAINT `guru_pengganti_ibfk_3` FOREIGN KEY (`guru_pengganti_id`) REFERENCES `gurus` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `gurus`;
CREATE TABLE `gurus` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('aktif','cuti','pensiun') COLLATE utf8mb4_unicode_ci DEFAULT 'aktif',
  `kode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bidang_studi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jam_tidak_tersedia` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gurus_kode_unique` (`kode`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `nip` (`nip`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `gurus` (`id`, `user_id`, `email`, `phone`, `nip`, `status`, `kode`, `nama`, `bidang_studi`, `jam_tidak_tersedia`, `created_at`, `updated_at`) VALUES
(33,	NULL,	NULL,	NULL,	NULL,	'aktif',	'1',	'Dra. Hj. Lina Nurhasanah',	'Kepala Madrasah',	NULL,	'2026-05-18 22:23:36',	'2026-05-18 22:23:36'),
(34,	NULL,	NULL,	NULL,	NULL,	'aktif',	'2',	'H. Dedi Sobana, S.Pd',	'PKM Kurikulum',	NULL,	'2026-05-18 22:23:37',	'2026-05-18 22:23:37'),
(35,	NULL,	NULL,	NULL,	NULL,	'aktif',	'3',	'Dra. Hj. Al Minarni',	'Bahasa Arab',	NULL,	'2026-05-18 22:23:37',	'2026-05-18 22:23:37'),
(36,	NULL,	NULL,	NULL,	NULL,	'aktif',	'4',	'Shinta Nuryatna, S.Pd',	'Bahasa Indonesia/ PIB',	NULL,	'2026-05-18 22:23:37',	'2026-05-18 22:23:37'),
(37,	NULL,	NULL,	NULL,	NULL,	'aktif',	'5',	'Ema Siti Maesaroh, S.Pd',	'Matematika',	NULL,	'2026-05-18 22:23:37',	'2026-05-18 22:23:37'),
(38,	NULL,	NULL,	NULL,	NULL,	'aktif',	'6',	'Siti Rahmah, S.Pd',	'Matematika',	NULL,	'2026-05-18 22:23:37',	'2026-05-18 22:23:37'),
(39,	NULL,	NULL,	NULL,	NULL,	'aktif',	'7',	'Hj. Evva Nurlatifah, S.Pd',	'Bahasa Indonesia',	NULL,	'2026-05-18 22:23:38',	'2026-05-18 22:23:38'),
(40,	NULL,	NULL,	NULL,	NULL,	'aktif',	'8',	'Otong Sunandar, S.Pd.',	'Bahasa Inggris / TIK',	NULL,	'2026-05-18 22:23:38',	'2026-05-18 22:23:38'),
(41,	NULL,	NULL,	NULL,	NULL,	'aktif',	'9',	'T.A Ekajaya, S.Pd.I.',	'Akidah Akhlak',	NULL,	'2026-05-18 22:23:38',	'2026-05-18 22:23:38'),
(42,	NULL,	NULL,	NULL,	NULL,	'aktif',	'10',	'Emi Wahyu Mulyani, S.Pd.',	'PKn',	NULL,	'2026-05-18 22:23:38',	'2026-05-18 22:23:38'),
(43,	NULL,	NULL,	NULL,	NULL,	'aktif',	'12',	'Siti Anisah, S.Pd.',	'Bahasa Inggris / ECON',	NULL,	'2026-05-18 22:23:38',	'2026-05-18 22:23:38'),
(44,	NULL,	NULL,	NULL,	NULL,	'aktif',	'13',	'Ika Sartika, S.Tr.T.',	'TIK',	NULL,	'2026-05-18 22:23:38',	'2026-05-18 22:23:38'),
(45,	NULL,	NULL,	NULL,	NULL,	'aktif',	'14',	'Ginanjar Rahayu Tresna, S.Pd.',	'SKI',	NULL,	'2026-05-18 22:23:38',	'2026-05-18 22:23:38'),
(46,	NULL,	NULL,	NULL,	NULL,	'aktif',	'15',	'Agus Subarna, S.Ag.',	'Bahasa Arab / PIB',	NULL,	'2026-05-18 22:23:38',	'2026-05-18 22:23:38'),
(47,	NULL,	NULL,	NULL,	NULL,	'aktif',	'16',	'Tintin Agustini, S.Ag.',	'Seni Budaya',	NULL,	'2026-05-18 22:23:39',	'2026-05-18 22:23:39'),
(48,	NULL,	NULL,	NULL,	NULL,	'aktif',	'17',	'Cepi Rizki Supardi, S.Pd.I.',	'Fikih',	NULL,	'2026-05-18 22:23:39',	'2026-05-18 22:23:39'),
(49,	NULL,	NULL,	NULL,	NULL,	'aktif',	'22',	'Ilham Rizqiawan, S.Pd.',	'Al-Qurán Hadits / Tahfidz Qurán',	NULL,	'2026-05-18 22:23:39',	'2026-05-18 22:23:39'),
(50,	NULL,	NULL,	NULL,	NULL,	'aktif',	'23',	'Rizki Taufik M., S.S.',	'ECON',	NULL,	'2026-05-18 22:23:39',	'2026-05-18 22:23:39'),
(51,	NULL,	NULL,	NULL,	NULL,	'aktif',	'24',	'Yashinta Ameliana, S.Pd.',	'Matematika /  Bahasa Sunda',	NULL,	'2026-05-18 22:23:39',	'2026-05-18 22:23:39'),
(52,	NULL,	NULL,	NULL,	NULL,	'aktif',	'25',	'Dedeh Ainun Paridah',	'PIB',	NULL,	'2026-05-18 22:23:39',	'2026-05-18 22:23:39'),
(53,	NULL,	NULL,	NULL,	NULL,	'aktif',	'26',	'Astri Yuliasari, S.Pd.Gr.',	'Bahasa Inggris',	NULL,	'2026-05-18 22:23:39',	'2026-05-18 22:23:39'),
(54,	NULL,	NULL,	NULL,	NULL,	'aktif',	'27',	'Riski Hadiansyah, S.Pd.',	'PJOK',	NULL,	'2026-05-18 22:23:39',	'2026-05-18 22:23:39'),
(55,	NULL,	NULL,	NULL,	NULL,	'aktif',	'28',	'Achmad Fathoni Hidayat, S.P., M.M.',	'IPA',	NULL,	'2026-05-18 22:23:39',	'2026-05-18 22:23:39'),
(56,	NULL,	NULL,	NULL,	NULL,	'aktif',	'29',	'Rismaya Rachmat Hidayat, S.Hum.',	'IPS',	NULL,	'2026-05-18 22:23:39',	'2026-05-18 22:23:39'),
(57,	NULL,	NULL,	NULL,	NULL,	'aktif',	'30',	'Dendi Herdiwan, S.Pd.Gr.',	'IPA',	NULL,	'2026-05-18 22:23:40',	'2026-05-18 22:23:40'),
(58,	NULL,	NULL,	NULL,	NULL,	'aktif',	'31',	'Putri Rasya Nabila',	'Tahfidz Qurán',	NULL,	'2026-05-18 22:23:40',	'2026-05-18 22:23:40');

DROP TABLE IF EXISTS `jadwals`;
CREATE TABLE `jadwals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kelas_id` bigint unsigned NOT NULL,
  `guru_id` bigint unsigned NOT NULL,
  `mapel_id` bigint unsigned NOT NULL,
  `hari` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `ruang` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `semester` int DEFAULT '1',
  `tahun_ajaran` varchar(9) COLLATE utf8mb4_unicode_ci DEFAULT '2025/2026',
  `status` enum('aktif','archived') COLLATE utf8mb4_unicode_ci DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jadwals_kelas_id_foreign` (`kelas_id`),
  KEY `jadwals_guru_id_foreign` (`guru_id`),
  KEY `jadwals_mapel_id_foreign` (`mapel_id`),
  KEY `idx_tahun_ajaran` (`tahun_ajaran`),
  KEY `idx_semester` (`semester`),
  CONSTRAINT `jadwals_guru_id_foreign` FOREIGN KEY (`guru_id`) REFERENCES `gurus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jadwals_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jadwals_mapel_id_foreign` FOREIGN KEY (`mapel_id`) REFERENCES `mapels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `jadwals` (`id`, `kelas_id`, `guru_id`, `mapel_id`, `hari`, `jam_mulai`, `jam_selesai`, `ruang`, `semester`, `tahun_ajaran`, `status`, `created_at`, `updated_at`) VALUES
(1,	1,	49,	11,	'Kamis',	'07:00:00',	'08:20:00',	'7A',	1,	'2025/2026',	'aktif',	'2026-05-18 22:28:40',	'2026-05-18 22:28:40'),
(2,	1,	36,	5,	'Jumat',	'07:00:00',	'08:10:00',	NULL,	1,	'2025/2026',	'aktif',	'2026-05-28 09:41:43',	'2026-05-28 09:41:43');

DROP TABLE IF EXISTS `jam_pelajaran`;
CREATE TABLE `jam_pelajaran` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hari` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sesi_ke` int NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `jam_pelajaran` (`id`, `hari`, `sesi_ke`, `jam_mulai`, `jam_selesai`, `created_at`, `updated_at`) VALUES
(1,	'Senin',	1,	'07:20:00',	'08:40:00',	'2026-05-19 04:20:18',	'2026-05-19 04:20:18'),
(2,	'Senin',	2,	'08:40:00',	'10:00:00',	'2026-05-19 04:20:18',	'2026-05-19 04:20:18'),
(3,	'Senin',	3,	'10:20:00',	'11:40:00',	'2026-05-19 04:20:18',	'2026-05-19 04:20:18'),
(4,	'Senin',	4,	'12:40:00',	'14:00:00',	'2026-05-19 04:20:18',	'2026-05-19 04:20:18'),
(5,	'Selasa',	1,	'07:00:00',	'08:20:00',	'2026-05-19 04:20:18',	'2026-05-19 04:20:18'),
(6,	'Selasa',	2,	'08:20:00',	'09:40:00',	'2026-05-19 04:20:18',	'2026-05-19 04:20:18'),
(7,	'Selasa',	3,	'10:00:00',	'11:20:00',	'2026-05-19 04:20:18',	'2026-05-19 04:20:18'),
(8,	'Selasa',	4,	'12:30:00',	'13:50:00',	'2026-05-19 04:20:18',	'2026-05-19 04:20:18'),
(9,	'Rabu',	1,	'07:00:00',	'08:20:00',	'2026-05-19 04:20:18',	'2026-05-19 04:20:18'),
(10,	'Rabu',	2,	'08:20:00',	'09:40:00',	'2026-05-19 04:20:18',	'2026-05-19 04:20:18'),
(11,	'Rabu',	3,	'10:00:00',	'11:20:00',	'2026-05-19 04:20:18',	'2026-05-19 04:20:18'),
(12,	'Rabu',	4,	'12:30:00',	'13:50:00',	'2026-05-19 04:20:18',	'2026-05-19 04:20:18'),
(13,	'Kamis',	1,	'07:00:00',	'08:20:00',	'2026-05-19 04:20:18',	'2026-05-19 04:20:18'),
(14,	'Kamis',	2,	'08:20:00',	'09:40:00',	'2026-05-19 04:20:18',	'2026-05-19 04:20:18'),
(15,	'Kamis',	3,	'10:00:00',	'11:20:00',	'2026-05-19 04:20:18',	'2026-05-19 04:20:18'),
(16,	'Kamis',	4,	'12:30:00',	'13:50:00',	'2026-05-19 04:20:18',	'2026-05-19 04:20:18'),
(17,	'Kamis',	5,	'13:50:00',	'15:00:00',	'2026-05-19 04:20:18',	'2026-05-19 04:20:18'),
(18,	'Jumat',	1,	'07:00:00',	'08:10:00',	'2026-05-19 04:20:18',	'2026-05-19 04:20:18'),
(19,	'Jumat',	2,	'08:10:00',	'09:20:00',	'2026-05-19 04:20:18',	'2026-05-19 04:20:18'),
(20,	'Jumat',	3,	'09:35:00',	'10:45:00',	'2026-05-19 04:20:18',	'2026-05-19 04:20:18'),
(21,	'Senin',	1,	'07:20:00',	'08:40:00',	'2026-05-27 07:41:28',	'2026-05-27 07:41:28'),
(22,	'Senin',	2,	'08:40:00',	'10:00:00',	'2026-05-27 07:41:28',	'2026-05-27 07:41:28'),
(23,	'Senin',	3,	'10:20:00',	'11:40:00',	'2026-05-27 07:41:28',	'2026-05-27 07:41:28'),
(24,	'Senin',	4,	'12:40:00',	'14:00:00',	'2026-05-27 07:41:28',	'2026-05-27 07:41:28'),
(25,	'Selasa',	1,	'07:00:00',	'08:20:00',	'2026-05-27 07:41:29',	'2026-05-27 07:41:29'),
(26,	'Selasa',	2,	'08:20:00',	'09:40:00',	'2026-05-27 07:41:29',	'2026-05-27 07:41:29'),
(27,	'Selasa',	3,	'10:00:00',	'11:20:00',	'2026-05-27 07:41:29',	'2026-05-27 07:41:29'),
(28,	'Selasa',	4,	'12:30:00',	'13:50:00',	'2026-05-27 07:41:29',	'2026-05-27 07:41:29'),
(29,	'Rabu',	1,	'07:00:00',	'08:20:00',	'2026-05-27 07:41:29',	'2026-05-27 07:41:29'),
(30,	'Rabu',	2,	'08:20:00',	'09:40:00',	'2026-05-27 07:41:29',	'2026-05-27 07:41:29'),
(31,	'Rabu',	3,	'10:00:00',	'11:20:00',	'2026-05-27 07:41:29',	'2026-05-27 07:41:29'),
(32,	'Rabu',	4,	'12:30:00',	'13:50:00',	'2026-05-27 07:41:29',	'2026-05-27 07:41:29'),
(33,	'Kamis',	1,	'07:00:00',	'08:20:00',	'2026-05-27 07:41:29',	'2026-05-27 07:41:29'),
(34,	'Kamis',	2,	'08:20:00',	'09:40:00',	'2026-05-27 07:41:29',	'2026-05-27 07:41:29'),
(35,	'Kamis',	3,	'10:00:00',	'11:20:00',	'2026-05-27 07:41:29',	'2026-05-27 07:41:29'),
(36,	'Kamis',	4,	'12:30:00',	'13:50:00',	'2026-05-27 07:41:29',	'2026-05-27 07:41:29'),
(37,	'Kamis',	5,	'13:50:00',	'15:00:00',	'2026-05-27 07:41:29',	'2026-05-27 07:41:29'),
(38,	'Jumat',	1,	'07:00:00',	'08:10:00',	'2026-05-27 07:41:30',	'2026-05-27 07:41:30'),
(39,	'Jumat',	2,	'08:10:00',	'09:20:00',	'2026-05-27 07:41:30',	'2026-05-27 07:41:30'),
(40,	'Jumat',	3,	'09:35:00',	'10:45:00',	'2026-05-27 07:41:30',	'2026-05-27 07:41:30');

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `kategori_sarana`;
CREATE TABLE `kategori_sarana` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `kategori_sarana` (`id`, `nama_kategori`, `created_at`, `updated_at`) VALUES
(1,	'Elektronik',	'2026-05-27 07:49:02',	'2026-05-27 07:49:02'),
(2,	'Furniture',	'2026-05-27 07:49:02',	'2026-05-27 07:49:02'),
(3,	'Olahraga',	'2026-05-27 07:49:03',	'2026-05-27 07:49:03'),
(4,	'Alat Peraga',	'2026-05-27 07:49:03',	'2026-05-27 07:49:03'),
(5,	'Kendaraan',	'2026-05-27 07:49:03',	'2026-05-27 07:49:03'),
(6,	'Laboratorium',	'2026-05-27 07:49:03',	'2026-05-27 07:49:03');

DROP TABLE IF EXISTS `kelas`;
CREATE TABLE `kelas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_kelas` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tingkat` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guru_pembimbing_id` bigint unsigned DEFAULT NULL,
  `kapasitas` int DEFAULT '40',
  `ruangan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_guru_pembimbing` (`guru_pembimbing_id`),
  CONSTRAINT `kelas_ibfk_1` FOREIGN KEY (`guru_pembimbing_id`) REFERENCES `gurus` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `kelas` (`id`, `nama_kelas`, `tingkat`, `guru_pembimbing_id`, `kapasitas`, `ruangan`, `created_at`, `updated_at`) VALUES
(1,	'7A',	'7',	NULL,	40,	NULL,	'2026-05-18 07:55:26',	'2026-05-18 07:55:26'),
(2,	'7B',	'7',	NULL,	40,	NULL,	'2026-05-18 07:55:26',	'2026-05-18 07:55:26'),
(3,	'7C',	'7',	NULL,	40,	NULL,	'2026-05-18 07:55:26',	'2026-05-18 07:55:26'),
(4,	'7D',	'7',	NULL,	40,	NULL,	'2026-05-18 07:55:26',	'2026-05-18 07:55:26'),
(5,	'8A',	'8',	NULL,	40,	NULL,	'2026-05-18 07:55:26',	'2026-05-18 07:55:26'),
(6,	'8B',	'8',	NULL,	40,	NULL,	'2026-05-18 07:55:26',	'2026-05-18 07:55:26'),
(7,	'8C',	'8',	NULL,	40,	NULL,	'2026-05-18 07:55:27',	'2026-05-18 07:55:27'),
(8,	'8D',	'8',	NULL,	40,	NULL,	'2026-05-18 07:55:27',	'2026-05-18 07:55:27'),
(9,	'8E',	'8',	NULL,	40,	NULL,	'2026-05-18 07:55:27',	'2026-05-18 07:55:27'),
(10,	'9A',	'9',	NULL,	40,	NULL,	'2026-05-18 07:55:27',	'2026-05-18 07:55:27'),
(11,	'9B',	'9',	NULL,	40,	NULL,	'2026-05-18 07:55:27',	'2026-05-18 07:55:27'),
(12,	'9C',	'9',	NULL,	40,	NULL,	'2026-05-18 07:55:27',	'2026-05-18 07:55:27'),
(13,	'9D',	'9',	NULL,	40,	NULL,	'2026-05-18 07:55:27',	'2026-05-18 07:55:27'),
(14,	'9E',	'9',	NULL,	40,	NULL,	'2026-05-18 07:55:27',	'2026-05-18 07:55:27'),
(19,	'7E',	'7',	NULL,	40,	NULL,	'2026-05-27 07:41:26',	'2026-05-27 07:41:26');

DROP TABLE IF EXISTS `mapels`;
CREATE TABLE `mapels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_mapel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `mapels` (`id`, `nama_mapel`, `created_at`, `updated_at`) VALUES
(1,	'Akidah Akhlak',	'2026-05-18 07:55:37',	'2026-05-18 07:55:37'),
(3,	'Fikih',	'2026-05-18 07:55:37',	'2026-05-18 07:55:37'),
(4,	'SKI',	'2026-05-18 07:55:37',	'2026-05-18 07:55:37'),
(5,	'Bahasa Indonesia',	'2026-05-18 07:55:37',	'2026-05-18 07:55:37'),
(6,	'Bahasa Inggris',	'2026-05-18 07:55:37',	'2026-05-18 07:55:37'),
(8,	'Bahasa SUnda',	'2026-05-18 07:55:37',	'2026-05-18 07:55:37'),
(9,	'IPA',	'2026-05-18 07:55:38',	'2026-05-18 07:55:38'),
(10,	'IPS',	'2026-05-18 07:55:38',	'2026-05-18 07:55:38'),
(11,	'PKn',	'2026-05-18 07:55:38',	'2026-05-18 07:55:38'),
(12,	'Matematika',	'2026-05-18 07:55:38',	'2026-05-18 07:55:38'),
(13,	'PJOK',	'2026-05-18 07:55:38',	'2026-05-18 07:55:38'),
(14,	'TIK',	'2026-05-18 07:55:38',	'2026-05-18 07:55:38'),
(15,	'Seni Budaya',	'2026-05-18 07:55:38',	'2026-05-18 07:55:38'),
(16,	'PIB',	'2026-05-18 07:55:38',	'2026-05-18 07:55:38'),
(17,	'Tahfidz Qurán',	'2026-05-18 07:55:38',	'2026-05-18 07:55:38'),
(18,	'ECON',	'2026-05-18 07:55:38',	'2026-05-18 07:55:38'),
(20,	'Al-Qur\'an Hadits',	'2026-05-27 07:41:27',	'2026-05-27 07:41:27'),
(21,	'Fikih',	'2026-05-27 07:41:27',	'2026-05-27 07:41:27'),
(22,	'SKI',	'2026-05-27 07:41:27',	'2026-05-27 07:41:27'),
(23,	'Bahasa Indonesia',	'2026-05-27 07:41:27',	'2026-05-27 07:41:27'),
(24,	'Bahasa Inggris',	'2026-05-27 07:41:27',	'2026-05-27 07:41:27'),
(25,	'Bahasa Arab',	'2026-05-27 07:41:27',	'2026-05-27 07:41:27'),
(26,	'Bahasa Sunda',	'2026-05-27 07:41:27',	'2026-05-27 07:41:27'),
(27,	'IPA',	'2026-05-27 07:41:27',	'2026-05-27 07:41:27'),
(28,	'IPS',	'2026-05-27 07:41:27',	'2026-05-27 07:41:27'),
(29,	'PKn',	'2026-05-27 07:41:28',	'2026-05-27 07:41:28'),
(30,	'Matematika',	'2026-05-27 07:41:28',	'2026-05-27 07:41:28'),
(31,	'PJOK',	'2026-05-27 07:41:28',	'2026-05-27 07:41:28'),
(32,	'TIK',	'2026-05-27 07:41:28',	'2026-05-27 07:41:28'),
(33,	'Seni Budaya',	'2026-05-27 07:41:28',	'2026-05-27 07:41:28'),
(34,	'PIB',	'2026-05-27 07:41:28',	'2026-05-27 07:41:28'),
(35,	'Tahfidz Qur\'an',	'2026-05-27 07:41:28',	'2026-05-27 07:41:28'),
(36,	'ECON',	'2026-05-27 07:41:28',	'2026-05-27 07:41:28');

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1,	'0001_01_01_000000_create_users_table',	1),
(2,	'0001_01_01_000001_create_cache_table',	1),
(3,	'0001_01_01_000002_create_jobs_table',	1),
(4,	'2026_05_18_142953_create_gurus_table',	2),
(5,	'2026_05_18_143002_create_kelas_table',	2),
(6,	'2026_05_18_143009_create_mapels_table',	2),
(7,	'2026_05_18_143015_create_jadwals_table',	2),
(8,	'2026_05_19_011322_create_jam_pelajaran_table',	3),
(9,	'2026_05_19_101035_create_surat_masuk_table',	4),
(10,	'2026_05_19_101314_create_surat_keluar_table',	4),
(11,	'2026_05_19_111654_create_template_surat_table',	4),
(12,	'2026_05_19_130520_create_tasks_table',	5),
(13,	'2026_05_19_130628_create_task_logs_table',	5),
(14,	'2026_05_19_143642_create_agenda_guru_table',	6),
(15,	'2026_05_27_144431_create_kategori_sarana_table',	7),
(16,	'2026_05_28_084311_create_sarana_prasarana_table',	8),
(17,	'2026_05_28_084333_create_peminjaman_sarana_table',	8),
(19,	'2026_05_28_084355_create_pemeliharaan_sarana_table',	9);

DROP TABLE IF EXISTS `orang_tua_wali`;
CREATE TABLE `orang_tua_wali` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `siswa_id` bigint unsigned NOT NULL,
  `nama_ayah` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pendidikan_ayah` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pekerjaan_ayah` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `penghasilan_ayah` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp_ayah` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_ayah` enum('Hidup','Meninggal','Tidak Diketahui') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Hidup',
  `nama_ibu` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pendidikan_ibu` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pekerjaan_ibu` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `penghasilan_ibu` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp_ibu` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_ibu` enum('Hidup','Meninggal','Tidak Diketahui') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Hidup',
  `nama_wali` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hubungan_wali` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pendidikan_wali` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pekerjaan_wali` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp_wali` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_ortu` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orang_tua_wali_siswa_id_unique` (`siswa_id`),
  CONSTRAINT `orang_tua_wali_siswa_id_foreign` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `pemeliharaan_sarana`;
CREATE TABLE `pemeliharaan_sarana` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sarana_id` bigint unsigned NOT NULL,
  `tanggal_pemeliharaan` date NOT NULL,
  `biaya` decimal(10,2) NOT NULL DEFAULT '0.00',
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `teknisi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pemeliharaan_sarana_sarana_id_foreign` (`sarana_id`),
  CONSTRAINT `pemeliharaan_sarana_sarana_id_foreign` FOREIGN KEY (`sarana_id`) REFERENCES `sarana_prasarana` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `peminjaman_sarana`;
CREATE TABLE `peminjaman_sarana` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sarana_id` bigint unsigned NOT NULL,
  `peminjam` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe_peminjam` enum('guru','siswa') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'guru',
  `tanggal_pinjam` date NOT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `denda` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('dipinjam','dikembalikan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dipinjam',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `peminjaman_sarana_sarana_id_foreign` (`sarana_id`),
  CONSTRAINT `peminjaman_sarana_sarana_id_foreign` FOREIGN KEY (`sarana_id`) REFERENCES `sarana_prasarana` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `perkembangan_siswa`;
CREATE TABLE `perkembangan_siswa` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `siswa_id` bigint unsigned NOT NULL,
  `asal_madrasah` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_madrasah_asal` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_ijazah_asal` date DEFAULT NULL,
  `no_ijazah_asal` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_masuk` enum('Baru','Pindahan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Baru',
  `tgl_diterima` date DEFAULT NULL,
  `dari_tingkat` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Jika pindahan',
  `no_surat_pindah` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Jika pindahan',
  `jenis_keluar` enum('Lulus','Pindah','Keluar','Meninggal') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thn_lulus` year DEFAULT NULL,
  `no_ijazah_lulus` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `melanjutkan_ke` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pindah_ke_madrasah` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pindah_tingkat` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alasan_keluar` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_keluar` date DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `perkembangan_siswa_siswa_id_unique` (`siswa_id`),
  CONSTRAINT `perkembangan_siswa_siswa_id_foreign` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `raport_nilai`;
CREATE TABLE `raport_nilai` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `siswa_id` bigint unsigned NOT NULL,
  `tahun_pelajaran_id` smallint unsigned NOT NULL,
  `semester` tinyint unsigned NOT NULL COMMENT '1=Ganjil Kl7, 2=Genap Kl7, …, 6=Genap Kl9',
  `mapel_id` bigint unsigned NOT NULL,
  `nilai_akhir` tinyint unsigned DEFAULT NULL COMMENT '0-100',
  `kktp` tinyint unsigned DEFAULT NULL COMMENT 'Kriteria Ketercapaian Tujuan Pembelajaran',
  `deskripsi` text COLLATE utf8mb4_unicode_ci COMMENT 'Capaian pembelajaran deskriptif',
  `updated_by` bigint unsigned DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `sarana_prasarana`;
CREATE TABLE `sarana_prasarana` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_sarana` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_sarana` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori_id` bigint unsigned NOT NULL,
  `spesifikasi` text COLLATE utf8mb4_unicode_ci,
  `jumlah` int NOT NULL DEFAULT '1',
  `kondisi` enum('baik','rusak_ringan','rusak_berat','hilang') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'baik',
  `lokasi_ruang` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tahun_pengadaan` year DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sarana_prasarana_kode_sarana_unique` (`kode_sarana`),
  KEY `sarana_prasarana_kategori_id_foreign` (`kategori_id`),
  CONSTRAINT `sarana_prasarana_kategori_id_foreign` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_sarana` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sarana_prasarana` (`id`, `kode_sarana`, `nama_sarana`, `kategori_id`, `spesifikasi`, `jumlah`, `kondisi`, `lokasi_ruang`, `tahun_pengadaan`, `foto`, `created_at`, `updated_at`) VALUES
(1,	'ELK-001',	'Proyektor',	1,	NULL,	5,	'baik',	'Ruang Kelas',	NULL,	NULL,	'2026-05-28 01:49:04',	'2026-05-28 01:49:04'),
(2,	'ELK-002',	'Laptop Guru',	1,	NULL,	2,	'rusak_ringan',	'Kantor Guru',	NULL,	NULL,	'2026-05-28 01:49:04',	'2026-05-28 01:49:04'),
(3,	'FRN-001',	'Meja Belajar',	2,	NULL,	100,	'baik',	'Semua Kelas',	NULL,	NULL,	'2026-05-28 01:49:04',	'2026-05-28 01:49:04'),
(4,	'FRN-002',	'Kursi Guru',	2,	NULL,	30,	'baik',	'Kantor',	NULL,	NULL,	'2026-05-28 01:49:04',	'2026-05-28 01:49:04'),
(5,	'APR-001',	'Globe',	4,	NULL,	10,	'baik',	'Lab IPS',	NULL,	NULL,	'2026-05-28 01:49:04',	'2026-05-28 01:49:04'),
(6,	'OLR-001',	'Bola Voli',	3,	NULL,	15,	'rusak_berat',	'Gudang Olahraga',	NULL,	NULL,	'2026-05-28 01:49:04',	'2026-05-28 01:49:04');

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('djGeVaEFgmz9ZVSwSjxDN0bLMX0ruG6PyNVJpa7c',	NULL,	'127.0.0.1',	'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',	'eyJfdG9rZW4iOiJmVU5CMWY5U2dGT05DSWJWQk1uSjRhdmNTbGdWZzlPTFhLNUVpQzMyIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9kYXNoYm9hcmQiLCJyb3V0ZSI6ImRhc2hib2FyZCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',	1779283779),
('FKuPCZHF7V8DkOwpuKSds5QPBsW247SNHOmGEu98',	NULL,	'127.0.0.1',	'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',	'eyJfdG9rZW4iOiJzcmEzcDBRWXVaV0QzTnNZeFV3dGxHNkFlVnBQR2tIRDljb0hWZTdWIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9qYWR3YWxcL2NyZWF0ZSIsInJvdXRlIjoiamFkd2FsLmNyZWF0ZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',	1779116596),
('gyhKJkf8gSgFh6TwP2T85b8Z3voEedkXuMmqbI3b',	NULL,	'127.0.0.1',	'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',	'eyJfdG9rZW4iOiJGQmFQc29UWjdMd2o3R1JvamI3STNEbVQ1VFc3ejgwcERRbGx3eWpxIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9qYWR3YWxcL2NyZWF0ZSIsInJvdXRlIjoiamFkd2FsLmNyZWF0ZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',	1779149377),
('hGa2PDS92pZUsIGv633BpXYTmT0TE0XEynmV5CJD',	NULL,	'127.0.0.1',	'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',	'eyJfdG9rZW4iOiI3a3lhaWtPTVd0Vng3eVJhZzlaWjRQMDROV3JlTjVsUHFYQTFNbGp3IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9kYXNoYm9hcmQiLCJyb3V0ZSI6ImRhc2hib2FyZCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',	1779632018),
('HwjnIe78OG8GcP16dzhwp5DNcs2Mmzam06NqZYUM',	3,	'127.0.0.1',	'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.121.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36',	'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZHB6MG9mdExrMVIwVEJGMVc0c1dKTGJtUmxYUHBMMHNvYXh1UTQ3TSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzI6Imh0dHA6Ly9lbWFkcmFzYWgyLnRlc3QvZGFzaGJvYXJkIjtzOjU6InJvdXRlIjtzOjk6ImRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM7fQ==',	1779754951),
('Ko711Nz4g3wvxkTz9u4bUPv24HZMbE79Bn8dYFWJ',	NULL,	'127.0.0.1',	'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',	'YTozOntzOjY6Il90b2tlbiI7czo0MDoicE9TVDM4Z29iWjZQNlg4dW50NGhIbTRsZGpzcURZOEswZFY2S0ZHNiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjI6Imh0dHA6Ly9lbWFkcmFzYWgyLnRlc3QiO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',	1779712209),
('kS8e0ZowLnmSkGendQDx96byVGHMZFcTXm3aYjk7',	NULL,	'127.0.0.1',	'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',	'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiemlqTHRBd2lxUlIwdkxmd2pINzU2VXBJQ0N1Z1dxZmxwQkhGcGRJRiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',	1779689931),
('KXHZ5C5KfT1G0YJTqypd3RNgTVPD8SnzSuu758eg',	3,	'127.0.0.1',	'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.121.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36',	'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiS1E4T29DVG1SN1lCZExjbHkybjBXOE5aNGxZME90SWJjRUlIQ0VwUCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzI6Imh0dHA6Ly9lbWFkcmFzYWgyLnRlc3QvZGFzaGJvYXJkIjtzOjU6InJvdXRlIjtzOjk6ImRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM7fQ==',	1779745641),
('L1nk51PlUJF8oYByVSSb1MlVLydofci14pRsEop2',	NULL,	'127.0.0.1',	'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',	'eyJfdG9rZW4iOiJpcGFjV1ZTdkRvQTV4eXNZSHJWUklwNFNuU2tKV3dxbjhwT1dCUlhVIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9qYWR3YWxcL2NyZWF0ZSIsInJvdXRlIjoiamFkd2FsLmNyZWF0ZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',	1779203659),
('pa4xcWA5ZtsKjRVCHosUgLvUcex54pNBluys85uk',	3,	'127.0.0.1',	'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',	'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZnczN0hFdWl0c2lhS0RNS2lhdjlaeks3Q080ZTdxT1lxMzNrOEdWWCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzI6Imh0dHA6Ly9lbWFkcmFzYWgyLnRlc3QvZGFzaGJvYXJkIjtzOjU6InJvdXRlIjtzOjk6ImRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM7fQ==',	1779715048),
('PuRLebZSQTTUPrzGk6TVaWxbhidJlr07pf3BW1vL',	NULL,	'127.0.0.1',	'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',	'eyJfdG9rZW4iOiJNUzlsaU9tNzRUV29IdlduWE1pbzdaRWlXYUpWMFBzR3J3MjdWc0pmIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9kYXNoYm9hcmQifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',	1779678642),
('qRcaHtFaXPs6k3FeOMYtWep4cI9NX3d9b1rRsQEF',	NULL,	'127.0.0.1',	'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',	'eyJfdG9rZW4iOiJwSGtrTjBoOFZoR1BWcDJJOEVkakJKQnBMSk05ZFZjZk5wZmU5bGh0IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9qYWR3YWxcL2NyZWF0ZSIsInJvdXRlIjoiamFkd2FsLmNyZWF0ZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',	1779168592),
('s8NkgBbZuJdb5yfxS1fGjRu7I5ORwCAw9QQsQga9',	3,	'127.0.0.1',	'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',	'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUFZTa1IyT1pFZ1VBZ2NWQUF3S05iVzJuVWJrWFhpdWtMQ3RpbGZUSyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzI6Imh0dHA6Ly9lbWFkcmFzYWgyLnRlc3QvZGFzaGJvYXJkIjtzOjU6InJvdXRlIjtzOjk6ImRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM7fQ==',	1779768204),
('XqUO1YBFSQdFTjuyFPqeGipRbRB2nZc6jxO3b3vK',	3,	'127.0.0.1',	'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.121.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36',	'YTo0OntzOjY6Il90b2tlbiI7czo0MDoid2JGVksxa1k1amx6MXcyd3JVOGlZVUowZ1lSTExQVDRWOTlRbXc3cyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzI6Imh0dHA6Ly9lbWFkcmFzYWgyLnRlc3QvZGFzaGJvYXJkIjtzOjU6InJvdXRlIjtzOjk6ImRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM7fQ==',	1779766281);

DROP TABLE IF EXISTS `siswa`;
CREATE TABLE `siswa` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `no_urut` smallint unsigned DEFAULT NULL COMMENT 'No. urut di buku induk',
  `nis` varchar(50) NOT NULL,
  `nisn` varchar(10) DEFAULT NULL COMMENT 'Nomor Induk Siswa Nasional',
  `nism` varchar(30) DEFAULT NULL COMMENT 'Nomor Induk Siswa Madrasah',
  `nik` varchar(16) DEFAULT NULL,
  `no_kk` varchar(16) DEFAULT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `nama_panggilan` varchar(50) DEFAULT NULL,
  `kelas_id` bigint unsigned NOT NULL,
  `tahun_pelajaran_id` bigint unsigned DEFAULT NULL,
  `status` enum('Aktif','Lulus','Pindah','Keluar','Meninggal') DEFAULT 'Aktif',
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `agama` varchar(20) DEFAULT NULL,
  `kewarganegaraan` varchar(30) DEFAULT 'Indonesia',
  `anak_ke` tinyint unsigned DEFAULT NULL,
  `saudara_kandung` tinyint unsigned DEFAULT '0',
  `saudara_tiri` tinyint unsigned DEFAULT '0',
  `saudara_angkat` tinyint unsigned DEFAULT '0',
  `status_anak` enum('Kandung','Tiri','Angkat') DEFAULT 'Kandung',
  `yatim_piatu` enum('Tidak','Yatim','Piatu','Yatim Piatu') DEFAULT 'Tidak',
  `bahasa_sehari_hari` varchar(50) DEFAULT NULL,
  `alamat` text,
  `rt` varchar(5) DEFAULT NULL,
  `rw` varchar(5) DEFAULT NULL,
  `desa_kelurahan` varchar(60) DEFAULT NULL,
  `kecamatan` varchar(60) DEFAULT NULL,
  `kabupaten_kota` varchar(60) DEFAULT NULL,
  `provinsi` varchar(50) DEFAULT NULL,
  `kode_pos` varchar(10) DEFAULT NULL,
  `nama_orang_tua` varchar(255) DEFAULT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `hp` varchar(20) DEFAULT NULL,
  `bertempat_tinggal_pada` varchar(60) DEFAULT NULL COMMENT 'Ortu/Saudara/Wali/Asrama/Kos',
  `jarak_ke_madrasah` varchar(10) DEFAULT NULL COMMENT 'km',
  `moda_transportasi` varchar(50) DEFAULT NULL,
  `golongan_darah` enum('A','B','AB','O','Tidak Tahu') DEFAULT 'Tidak Tahu',
  `penyakit_pernah_diderita` text,
  `kelainan_jasmani` varchar(100) DEFAULT NULL,
  `tinggi_badan_awal` decimal(5,2) DEFAULT NULL,
  `berat_badan_awal` decimal(5,2) DEFAULT NULL,
  `hobi_kesenian` varchar(100) DEFAULT NULL,
  `hobi_olahraga` varchar(100) DEFAULT NULL,
  `hobi_organisasi` varchar(100) DEFAULT NULL,
  `hobi_lain` varchar(100) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nis` (`nis`),
  UNIQUE KEY `siswa_nisn_unique` (`nisn`),
  UNIQUE KEY `nik` (`nik`),
  KEY `kelas_id` (`kelas_id`),
  CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `siswa_dokumen`;
CREATE TABLE `siswa_dokumen` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `siswa_id` bigint unsigned NOT NULL,
  `jenis_dokumen` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Akta, KK, Ijazah, KIP, dll',
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `siswa_dokumen_siswa_id_foreign` (`siswa_id`),
  CONSTRAINT `siswa_dokumen_siswa_id_foreign` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `surat_keluar`;
CREATE TABLE `surat_keluar` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nomor_surat` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tujuan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `perihal` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_kirim` date NOT NULL,
  `lampiran` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_draft` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `surat_keluar_nomor_surat_unique` (`nomor_surat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `surat_keluar` (`id`, `nomor_surat`, `tujuan`, `perihal`, `tanggal_kirim`, `lampiran`, `file_draft`, `created_at`, `updated_at`) VALUES
(1,	'SK-2026-001',	'1',	'tes',	'2026-05-19',	'1 lembar',	NULL,	'2026-05-19 05:59:24',	'2026-05-19 05:59:24'),
(2,	'SK-2026-002',	'x',	'a',	'2026-05-20',	'2 lembar',	NULL,	'2026-05-20 06:07:21',	'2026-05-20 06:07:21');

DROP TABLE IF EXISTS `surat_masuk`;
CREATE TABLE `surat_masuk` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nomor_agenda` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `asal_surat` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_surat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `perihal` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_terima` date NOT NULL,
  `tanggal_surat` date DEFAULT NULL,
  `disposisi` text COLLATE utf8mb4_unicode_ci,
  `file_scan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('diterima','diproses','selesai') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'diterima',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `surat_masuk_nomor_agenda_unique` (`nomor_agenda`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `surat_masuk` (`id`, `nomor_agenda`, `asal_surat`, `nomor_surat`, `perihal`, `tanggal_terima`, `tanggal_surat`, `disposisi`, `file_scan`, `status`, `created_at`, `updated_at`) VALUES
(1,	'SM-2026-001',	'a',	'a',	'a',	'2026-05-21',	'2026-05-18',	'a',	NULL,	'selesai',	'2026-05-20 06:06:41',	'2026-05-20 06:06:41'),
(2,	'SM-2026-002',	'c',	'23',	'df',	'2026-05-29',	NULL,	'a',	NULL,	'diterima',	'2026-05-28 20:08:54',	'2026-05-28 20:08:54');

DROP TABLE IF EXISTS `tahun_pelajaran`;
CREATE TABLE `tahun_pelajaran` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(9) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Contoh: 2024/2025',
  `nama` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_aktif` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tahun_pelajaran_kode_unique` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `task_logs`;
CREATE TABLE `task_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_logs_task_id_foreign` (`task_id`),
  KEY `task_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `task_logs_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `task_logs` (`id`, `task_id`, `user_id`, `action`, `keterangan`, `created_at`, `updated_at`) VALUES
(1,	4,	5,	'Tugas dibuat',	'Status awal: proses',	'2026-05-28 23:25:01',	'2026-05-28 23:25:01');

DROP TABLE IF EXISTS `tasks`;
CREATE TABLE `tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `prioritas` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sedang',
  `deadline` date DEFAULT NULL,
  `status` enum('antrean','proses','selesai') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'antrean',
  `kategori` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `progress_persen` int DEFAULT '0',
  `created_by` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_assigned_to_foreign` (`assigned_to`),
  KEY `tasks_created_by_foreign` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tasks` (`id`, `judul`, `deskripsi`, `assigned_to`, `prioritas`, `deadline`, `status`, `kategori`, `attachment`, `progress_persen`, `created_by`, `created_at`, `updated_at`) VALUES
(2,	'a',	'aaaa',	0,	'high',	'2026-05-21',	'proses',	NULL,	NULL,	0,	NULL,	'2026-05-19 07:24:49',	'2026-05-19 07:30:39'),
(3,	'HA',	'AAAZ',	0,	'high',	'2026-05-21',	'antrean',	NULL,	NULL,	0,	NULL,	'2026-05-19 07:26:48',	'2026-05-19 07:26:48'),
(4,	'a1',	'a2',	5,	'sedang',	'2026-05-30',	'proses',	NULL,	NULL,	0,	5,	'2026-05-28 23:24:58',	'2026-05-28 23:24:58');

DROP TABLE IF EXISTS `template_surat`;
CREATE TABLE `template_surat` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_template` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `konten` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','guru','wali_murid','siswa','operator') COLLATE utf8mb4_unicode_ci DEFAULT 'operator',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `phone`, `alamat`, `is_active`, `password`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
(3,	'Staf TU',	'staf@madrasah.test',	NULL,	NULL,	NULL,	1,	'$2y$12$nzmi5/qyHHnY2Rctp0HrjemN4boEhsHxuXX8MiFIatJUP7U/qK8fm',	'operator',	NULL,	'2026-05-19 06:20:13',	'2026-05-19 06:20:13'),
(5,	'Admin Madrasah',	'admin@madrasah.test',	'2026-05-27 07:41:24',	NULL,	NULL,	1,	'$2y$12$XfzuIZCRLdjJcYZTHeklQueCfiAkr5EgmWQYkCMxYCsVlytshg2Bi',	'operator',	'6ZiAa4GAfM6RTxmdRvr0titrG8BW5orTN59uEiMlrgtf7IyG394EuuB5lzA9',	'2026-05-27 07:41:25',	'2026-05-27 07:41:25'),
(6,	'Staf TU',	'tu@madrasah.test',	NULL,	NULL,	NULL,	1,	'$2y$12$zVMj/eJcPnWIwcfLq6dBWeLza.D0OoZBVAvCB7bzqxmewSnCvVCkW',	'operator',	NULL,	'2026-05-27 07:41:26',	'2026-05-27 07:41:26');

-- 2026-06-07 21:21:32 UTC
