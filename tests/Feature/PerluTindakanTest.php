<?php

namespace Tests\Feature;

use App\Models\AbsensiSiswa;
use App\Models\AgendaGuru;
use App\Models\Guru;
use App\Models\IzinGuru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\SuratMasuk;
use App\Models\Task;
use App\Models\User;
use App\Support\PerluTindakan;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PerluTindakanTest extends TestCase
{
    use RefreshDatabase;

    private const SENIN = '2026-10-12 08:00:00';

    private const MINGGU = '2026-10-11 08:00:00';

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('role', 'admin')->firstOrFail();
        // Kosongkan data contoh agar hitungan hanya berasal dari data tes ini.
        AbsensiSiswa::query()->delete();
        AgendaGuru::query()->delete();
        Jadwal::query()->delete();
        Task::query()->delete();
        SuratMasuk::query()->delete();
        Siswa::query()->forceDelete();
        Kelas::query()->delete();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function item(string $kunci): ?array
    {
        return collect(PerluTindakan::untuk($this->admin)['item'])->firstWhere('kunci', $kunci);
    }

    private function kelasDenganSiswa(string $nama): Kelas
    {
        $kelas = Kelas::create(['nama_kelas' => $nama, 'tingkat' => '7', 'kapasitas' => 30]);
        Siswa::create(['nis' => 'T'.$kelas->id, 'nama_lengkap' => 'Siswa '.$nama, 'kelas_id' => $kelas->id, 'jenis_kelamin' => 'L', 'status' => 'Aktif']);

        return $kelas;
    }

    public function test_classes_without_attendance_today_are_listed_until_attendance_is_taken(): void
    {
        Carbon::setTestNow(self::SENIN);
        $a = $this->kelasDenganSiswa('7A');
        $b = $this->kelasDenganSiswa('7B');

        $this->assertSame(2, $this->item('kelas-belum-absen')['jumlah']);

        AbsensiSiswa::create(['tanggal' => today(), 'siswa_id' => Siswa::where('kelas_id', $a->id)->value('id'), 'kelas_id' => $a->id, 'status' => 'hadir']);

        $item = $this->item('kelas-belum-absen');
        $this->assertSame(1, $item['jumlah']);
        $this->assertSame(['7B'], $item['contoh']);
    }

    public function test_no_attendance_reminder_on_sunday_or_for_empty_classes(): void
    {
        $this->kelasDenganSiswa('7A');
        Kelas::create(['nama_kelas' => 'Kosong', 'tingkat' => '9', 'kapasitas' => 30]);

        Carbon::setTestNow(self::MINGGU);
        $this->assertSame(0, $this->item('kelas-belum-absen')['jumlah']);

        Carbon::setTestNow(self::SENIN);
        $this->assertSame(1, $this->item('kelas-belum-absen')['jumlah'], 'Kelas tanpa siswa aktif tidak perlu diabsen');
    }

    public function test_teachers_scheduled_today_without_an_agenda_are_counted(): void
    {
        Carbon::setTestNow(self::SENIN);
        $kelas = $this->kelasDenganSiswa('7A');
        $mapel = Mapel::create(['nama_mapel' => 'Fikih Uji', 'jp_per_sesi' => 2]);
        $sudah = Guru::create(['kode' => 'TA', 'nama' => 'Sudah Absen']);
        $belum = Guru::create(['kode' => 'TB', 'nama' => 'Belum Absen']);
        $libur = Guru::create(['kode' => 'TC', 'nama' => 'Tidak Mengajar Hari Ini']);

        $jam = JamPelajaran::create(['hari' => 'Senin', 'sesi_ke' => 1, 'jam_mulai' => '07:00', 'jam_selesai' => '07:40']);
        $jadwal = fn (Guru $g, string $hari) => Jadwal::create(['kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $g->id, 'jam_pelajaran_id' => $jam->id, 'hari' => $hari, 'jam_mulai' => '07:00', 'jam_selesai' => '07:40', 'status' => 'aktif']);
        $jadwal($sudah, 'Senin');
        $jadwal($belum, 'Senin');
        $jadwal($libur, 'Selasa');
        AgendaGuru::create(['tanggal' => today(), 'guru_id' => $sudah->id, 'status' => 'hadir']);

        $item = $this->item('guru-belum-absen');
        $this->assertSame(1, $item['jumlah']);
        $this->assertSame(['Belum Absen'], $item['contoh']);
    }

    public function test_overdue_tasks_and_waiting_letters_are_counted(): void
    {
        Carbon::setTestNow(self::SENIN);
        $dasar = ['prioritas' => 'sedang', 'created_by' => $this->admin->id];
        Task::create($dasar + ['judul' => 'Lewat', 'deadline' => today()->subDay(), 'status' => 'proses']);
        Task::create($dasar + ['judul' => 'Lewat tapi selesai', 'deadline' => today()->subDays(5), 'status' => 'selesai']);
        Task::create($dasar + ['judul' => 'Belum jatuh tempo', 'deadline' => today()->addDay(), 'status' => 'antrean']);

        $surat = fn (string $no, int $hariLalu, string $status) => SuratMasuk::create(['nomor_agenda' => $no, 'asal_surat' => 'Kemenag', 'nomor_surat' => $no, 'perihal' => 'Uji', 'tanggal_terima' => today()->subDays($hariLalu), 'status' => $status]);
        $surat('A1', 5, 'diterima');
        $surat('A2', 1, 'diterima');
        $surat('A3', 9, 'selesai');

        $this->assertSame(1, $this->item('tugas-terlambat')['jumlah']);
        $this->assertSame(1, $this->item('surat-menunggu')['jumlah']);
    }

    public function test_pending_teacher_leave_requests_are_listed(): void
    {
        $guru = Guru::create(['kode' => 'IZ', 'nama' => 'Guru Izin']);
        $buat = fn (string $status) => IzinGuru::create(['guru_id' => $guru->id, 'jenis' => 'izin', 'tanggal_mulai' => '2026-10-12', 'tanggal_selesai' => '2026-10-12', 'alasan' => 'Uji', 'status' => $status]);
        $buat('menunggu');
        $buat('disetujui');

        $item = $this->item('izin-menunggu');
        $this->assertSame(1, $item['jumlah']);
        $this->assertSame(['Guru Izin'], $item['contoh']);
    }

    public function test_backup_status_reports_missing_fresh_and_stale_backups(): void
    {
        $dir = sys_get_temp_dir().'/em-backup-'.uniqid();
        File::ensureDirectoryExists($dir);

        $this->assertNull(PerluTindakan::statusBackup($dir)['terakhir']);
        $this->assertTrue(PerluTindakan::statusBackup($dir)['bermasalah']);

        touch("{$dir}/backup-baru.zip", now()->subHours(5)->getTimestamp());
        $this->assertFalse(PerluTindakan::statusBackup($dir)['bermasalah']);

        File::delete("{$dir}/backup-baru.zip");
        touch("{$dir}/backup-lama.zip", now()->subDays(4)->getTimestamp());
        $this->assertTrue(PerluTindakan::statusBackup($dir)['bermasalah']);

        File::deleteDirectory($dir);
    }

    public function test_panel_is_shown_to_admin_and_operator_but_backup_only_to_admin(): void
    {
        $this->actingAs($this->admin)->get(route('dashboard'))->assertOk()->assertSee('Perlu Tindakan')->assertSee('Cadangan data');

        $operator = User::where('role', 'operator')->firstOrFail();
        $this->actingAs($operator)->get(route('dashboard'))->assertOk()->assertSee('Perlu Tindakan')->assertDontSee('Cadangan data');
    }

    public function test_teacher_attendance_chart_groups_the_last_seven_days_correctly(): void
    {
        Carbon::setTestNow(self::SENIN);
        $guru = fn (string $kode) => Guru::create(['kode' => $kode, 'nama' => "Guru {$kode}"]);
        [$a, $b, $c] = [$guru('K1'), $guru('K2'), $guru('K3')];

        AgendaGuru::create(['tanggal' => today(), 'guru_id' => $a->id, 'status' => 'hadir']);
        AgendaGuru::create(['tanggal' => today(), 'guru_id' => $b->id, 'status' => 'hadir']);
        AgendaGuru::create(['tanggal' => today(), 'guru_id' => $c->id, 'status' => 'izin']);
        AgendaGuru::create(['tanggal' => today()->subDay(), 'guru_id' => $a->id, 'status' => 'alpha']);
        AgendaGuru::create(['tanggal' => today()->subDays(7), 'guru_id' => $a->id, 'status' => 'hadir']);

        $chart = $this->actingAs($this->admin)->get(route('dashboard'))->viewData('kehadiran');

        $this->assertCount(7, $chart);
        $this->assertSame(['tanggal' => '12/10', 'hadir' => 2, 'tidak_hadir' => 1], $chart[6]);
        $this->assertSame(['tanggal' => '11/10', 'hadir' => 0, 'tidak_hadir' => 1], $chart[5]);
        $this->assertSame(2, array_sum(array_column($chart, 'tidak_hadir')));
        $this->assertSame(2, array_sum(array_column($chart, 'hadir')), 'Data 7 hari lalu berada di luar jendela');
    }

    public function test_teachers_do_not_see_the_admin_panel(): void
    {
        $guru = User::where('role', 'guru')->firstOrFail();

        $this->actingAs($guru)->get(route('dashboard'))->assertOk()->assertDontSee('Perlu Tindakan');
    }
}
