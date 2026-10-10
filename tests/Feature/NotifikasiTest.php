<?php

namespace Tests\Feature;

use App\Models\KategoriSarana;
use App\Models\Kelas;
use App\Models\PeminjamanSarana;
use App\Models\SaranaPrasarana;
use App\Models\Siswa;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class NotifikasiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $wali;

    private User $waliLain;

    private Siswa $anak;

    private Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('role', 'admin')->firstOrFail();
        $this->kelas = Kelas::create(['nama_kelas' => 'Uji 7A', 'tingkat' => '7', 'kapasitas' => 30]);
        $this->anak = Siswa::create(['nis' => '9990001', 'nama_lengkap' => 'Anak Notif', 'kelas_id' => $this->kelas->id, 'jenis_kelamin' => 'L', 'status' => 'Aktif']);
        $this->wali = $this->akun('wali_murid', 'wali@notif.test');
        $this->wali->anak()->attach($this->anak->id);
        $this->waliLain = $this->akun('wali_murid', 'lain@notif.test');
    }

    private function akun(string $role, string $email): User
    {
        return User::create(['name' => $email, 'email' => $email, 'password' => 'rahasia-panjang-1', 'role' => $role, 'is_active' => true]);
    }

    private function absen(string $status, ?string $tanggal = null)
    {
        return $this->actingAs($this->admin)->post(route('absensi-siswa.store'), [
            'kelas_id' => $this->kelas->id,
            'tanggal' => $tanggal ?? today()->toDateString(),
            'status' => [$this->anak->id => $status],
        ]);
    }

    // ── Absensi → wali ──────────────────────────────────────────────

    public function test_alpha_notifies_linked_wali_only(): void
    {
        $this->absen('alpha')->assertRedirect();

        $this->assertSame(1, $this->wali->notifications()->count());
        $this->assertSame(0, $this->waliLain->notifications()->count());

        $data = $this->wali->notifications()->first()->data;
        $this->assertStringContainsString('Anak Notif', $data['pesan']);
        $this->assertSame(route('wali.show', $this->anak), explode('?', $data['url'])[0]);
    }

    public function test_only_alpha_triggers_a_notification(): void
    {
        foreach (['hadir', 'izin', 'sakit'] as $status) {
            $this->absen($status);
        }

        $this->assertSame(0, $this->wali->notifications()->count());
    }

    public function test_saving_the_same_alpha_twice_does_not_notify_twice(): void
    {
        $this->absen('alpha');
        $this->absen('alpha');

        $this->assertSame(1, $this->wali->notifications()->count());
    }

    public function test_old_backfilled_dates_do_not_spam_wali(): void
    {
        $this->absen('alpha', today()->subDays(30)->toDateString());

        $this->assertSame(0, $this->wali->notifications()->count());
    }

    public function test_a_failing_notifier_never_breaks_saving_attendance(): void
    {
        $this->app->instance(Dispatcher::class, new class implements Dispatcher
        {
            public function send($notifiables, $notification): void
            {
                throw new \RuntimeException('SMTP mati');
            }

            public function sendNow($notifiables, $notification, ?array $channels = null): void
            {
                throw new \RuntimeException('SMTP mati');
            }
        });

        $this->absen('alpha')->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('absensi_siswa', ['siswa_id' => $this->anak->id, 'status' => 'alpha']);
    }

    // ── Kotak masuk ─────────────────────────────────────────────────

    public function test_inbox_lists_only_own_notifications_and_marks_read_on_open(): void
    {
        $this->absen('alpha');
        $notif = $this->wali->notifications()->first();

        $this->actingAs($this->wali)->get(route('notifikasi.index'))->assertOk()->assertSee('Anak Notif');
        $this->actingAs($this->waliLain)->get(route('notifikasi.index'))->assertOk()->assertDontSee('Anak Notif');

        $this->actingAs($this->wali)->post(route('notifikasi.baca', $notif->id))
            ->assertRedirect($notif->data['url']);
        $this->assertNotNull($notif->fresh()->read_at);
    }

    public function test_cannot_read_someone_elses_notification(): void
    {
        $this->absen('alpha');
        $notif = $this->wali->notifications()->first();

        $this->actingAs($this->waliLain)->post(route('notifikasi.baca', $notif->id))->assertNotFound();
        $this->assertNull($notif->fresh()->read_at);
    }

    public function test_mark_all_read(): void
    {
        $this->absen('alpha');

        $this->actingAs($this->wali)->post(route('notifikasi.baca-semua'))->assertRedirect();
        $this->assertSame(0, $this->wali->unreadNotifications()->count());
    }

    public function test_bell_shows_unread_count_in_layout(): void
    {
        $this->absen('alpha');

        $this->actingAs($this->wali)->get(route('wali.index'))->assertSee('data-notif-count="1"', false);
    }

    // ── Pengingat terjadwal ─────────────────────────────────────────

    public function test_reminder_notifies_assignee_of_due_and_overdue_unfinished_tasks_once_per_day(): void
    {
        $staf = $this->akun('operator', 'staf@notif.test');
        $dasar = ['assigned_to' => $staf->id, 'prioritas' => 'sedang', 'created_by' => $this->admin->id];
        Task::create($dasar + ['judul' => 'Besok', 'deadline' => today()->addDay(), 'status' => 'antrean']);
        Task::create($dasar + ['judul' => 'Terlambat', 'deadline' => today()->subDays(3), 'status' => 'proses']);
        Task::create($dasar + ['judul' => 'Masih lama', 'deadline' => today()->addDays(10), 'status' => 'antrean']);
        Task::create($dasar + ['judul' => 'Sudah beres', 'deadline' => today()->subDay(), 'status' => 'selesai']);

        Artisan::call('madrasah:pengingat');
        Artisan::call('madrasah:pengingat');

        $pesan = $staf->notifications()->get()->pluck('data.pesan')->implode(' | ');
        $this->assertSame(2, $staf->notifications()->count(), $pesan);
        $this->assertStringContainsString('Besok', $pesan);
        $this->assertStringContainsString('Terlambat', $pesan);
        $this->assertStringNotContainsString('Masih lama', $pesan);
        $this->assertStringNotContainsString('Sudah beres', $pesan);
    }

    public function test_reminder_tells_staff_about_sarana_not_returned_after_a_week(): void
    {
        $operator = $this->akun('operator', 'op@notif.test');
        $kategori = KategoriSarana::firstOrCreate(['nama_kategori' => 'Uji']);
        $sarana = SaranaPrasarana::create(['kode_sarana' => 'UJI-1', 'nama_sarana' => 'Proyektor Uji', 'kategori_id' => $kategori->id, 'jumlah' => 1, 'stok_tersedia' => 0]);
        PeminjamanSarana::create(['sarana_id' => $sarana->id, 'peminjam' => 'Bu Guru', 'tipe_peminjam' => 'guru', 'tanggal_pinjam' => today()->subDays(10), 'status' => 'dipinjam']);
        PeminjamanSarana::create(['sarana_id' => $sarana->id, 'peminjam' => 'Pak Baru', 'tipe_peminjam' => 'guru', 'tanggal_pinjam' => today()->subDays(2), 'status' => 'dipinjam']);
        PeminjamanSarana::create(['sarana_id' => $sarana->id, 'peminjam' => 'Sudah Balik', 'tipe_peminjam' => 'guru', 'tanggal_pinjam' => today()->subDays(20), 'tanggal_kembali' => today()->subDays(15), 'status' => 'dikembalikan']);

        Artisan::call('madrasah:pengingat');

        $pesan = $operator->notifications()->get()->pluck('data.pesan')->implode(' | ');
        $this->assertStringContainsString('Bu Guru', $pesan);
        $this->assertStringNotContainsString('Pak Baru', $pesan);
        $this->assertStringNotContainsString('Sudah Balik', $pesan);
        $this->assertSame(1, $this->admin->notifications()->count());
    }
}
