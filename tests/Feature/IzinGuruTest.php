<?php

namespace Tests\Feature;

use App\Models\AgendaGuru;
use App\Models\Guru;
use App\Models\IzinGuru;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IzinGuruTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $guruUser;

    private Guru $guru;

    private User $guruLain;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-12 08:00:00'); // Senin
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('role', 'admin')->firstOrFail();
        $this->guruUser = User::where('role', 'guru')->firstOrFail();
        $this->guru = Guru::where('user_id', $this->guruUser->id)->firstOrFail();
        AgendaGuru::query()->delete();

        $this->guruLain = User::create(['name' => 'Guru Lain', 'email' => 'lain@izin.test', 'password' => 'rahasia-panjang-1', 'role' => 'guru', 'is_active' => true]);
        Guru::create(['kode' => 'GL', 'nama' => 'Guru Lain', 'user_id' => $this->guruLain->id]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function ajukan(array $override = [], ?User $oleh = null)
    {
        return $this->actingAs($oleh ?? $this->guruUser)->post(route('izin-guru.store'), $override + [
            'jenis' => 'sakit',
            'tanggal_mulai' => '2026-10-12',
            'tanggal_selesai' => '2026-10-14',
            'alasan' => 'Demam tinggi',
        ]);
    }

    private function buat(string $status = 'menunggu', ?Guru $guru = null, string $mulai = '2026-10-12', string $selesai = '2026-10-14', string $jenis = 'sakit'): IzinGuru
    {
        return IzinGuru::create([
            'guru_id' => ($guru ?? $this->guru)->id, 'jenis' => $jenis, 'tanggal_mulai' => $mulai,
            'tanggal_selesai' => $selesai, 'alasan' => 'Uji', 'status' => $status,
        ]);
    }

    public function test_guru_submits_a_request_and_staff_are_notified(): void
    {
        $this->ajukan()->assertRedirect(route('izin-guru.index'))->assertSessionHas('success');

        $izin = IzinGuru::firstOrFail();
        $this->assertSame($this->guru->id, $izin->guru_id);
        $this->assertSame('menunggu', $izin->status);
        $this->assertSame(1, $this->admin->notifications()->count());
        $this->assertStringContainsString($this->guru->nama, $this->admin->notifications()->first()->data['pesan']);
    }

    public function test_validation_rejects_bad_ranges_and_overlaps(): void
    {
        $this->ajukan(['tanggal_selesai' => '2026-10-10'])->assertSessionHasErrors('tanggal_selesai');
        $this->ajukan(['tanggal_selesai' => '2027-03-01'])->assertSessionHasErrors('tanggal_selesai');
        $this->ajukan(['jenis' => 'liburan'])->assertSessionHasErrors('jenis');
        $this->ajukan(['alasan' => ''])->assertSessionHasErrors('alasan');

        $this->buat('menunggu');
        $this->ajukan(['tanggal_mulai' => '2026-10-14', 'tanggal_selesai' => '2026-10-15'])->assertSessionHasErrors('tanggal_mulai');
        $this->assertSame(1, IzinGuru::count());
    }

    public function test_a_rejected_request_does_not_block_resubmission(): void
    {
        $this->buat('ditolak');

        $this->ajukan()->assertSessionHasNoErrors();
        $this->assertSame(2, IzinGuru::count());
    }

    public function test_guru_without_a_linked_teacher_record_cannot_submit(): void
    {
        $tanpaData = User::create(['name' => 'Tanpa Data', 'email' => 'x@izin.test', 'password' => 'rahasia-panjang-1', 'role' => 'guru', 'is_active' => true]);

        $this->ajukan([], $tanpaData)->assertForbidden();
    }

    public function test_approval_fills_teacher_attendance_on_working_days_without_overwriting_presence(): void
    {
        // Sabtu 17 s/d Selasa 20 Oktober: Sabtu, Senin, Selasa (Minggu dilewati); Senin sudah hadir.
        $izin = $this->buat('menunggu', null, '2026-10-17', '2026-10-20', 'cuti');
        AgendaGuru::create(['tanggal' => '2026-10-19', 'guru_id' => $this->guru->id, 'status' => 'hadir']);

        $this->actingAs($this->admin)->post(route('persetujuan-izin.putuskan', $izin), ['keputusan' => 'setuju', 'catatan' => 'Silakan'])
            ->assertRedirect()->assertSessionHas('success');

        $izin->refresh();
        $this->assertSame('disetujui', $izin->status);
        $this->assertSame($this->admin->id, $izin->diputuskan_oleh);

        $status = AgendaGuru::where('guru_id', $this->guru->id)->orderBy('tanggal')->get()
            ->mapWithKeys(fn ($a) => [$a->tanggal->format('Y-m-d') => $a->status])->all();
        $this->assertSame(['2026-10-17' => 'izin', '2026-10-19' => 'hadir', '2026-10-20' => 'izin'], $status);

        $this->assertSame(1, $this->guruUser->notifications()->count());
        $this->assertStringContainsString('disetujui', $this->guruUser->notifications()->first()->data['pesan']);
    }

    public function test_sick_leave_is_recorded_as_sakit(): void
    {
        $izin = $this->buat('menunggu', null, '2026-10-12', '2026-10-12', 'sakit');

        $this->actingAs($this->admin)->post(route('persetujuan-izin.putuskan', $izin), ['keputusan' => 'setuju']);

        $this->assertSame('sakit', AgendaGuru::where('guru_id', $this->guru->id)->value('status'));
    }

    public function test_rejection_changes_nothing_in_attendance_and_notifies_the_teacher(): void
    {
        $izin = $this->buat('menunggu');

        $this->actingAs($this->admin)->post(route('persetujuan-izin.putuskan', $izin), ['keputusan' => 'tolak', 'catatan' => 'Ada ujian'])->assertRedirect();

        $this->assertSame('ditolak', $izin->fresh()->status);
        $this->assertSame(0, AgendaGuru::count());
        $this->assertStringContainsString('ditolak', $this->guruUser->notifications()->first()->data['pesan']);
    }

    public function test_a_decided_request_cannot_be_decided_again(): void
    {
        $izin = $this->buat('disetujui');

        $this->actingAs($this->admin)->post(route('persetujuan-izin.putuskan', $izin), ['keputusan' => 'tolak'])->assertSessionHas('error');
        $this->assertSame('disetujui', $izin->fresh()->status);
    }

    public function test_guru_cannot_decide_requests(): void
    {
        $izin = $this->buat('menunggu', Guru::where('user_id', $this->guruLain->id)->first());

        $this->actingAs($this->guruUser)->post(route('persetujuan-izin.putuskan', $izin), ['keputusan' => 'setuju'])->assertForbidden();
        $this->assertSame('menunggu', $izin->fresh()->status);
    }

    public function test_guru_sees_only_their_own_requests_and_staff_see_all(): void
    {
        $this->buat('menunggu');
        $this->buat('menunggu', Guru::where('user_id', $this->guruLain->id)->first(), '2026-11-02', '2026-11-03');

        $this->actingAs($this->guruUser)->get(route('izin-guru.index'))->assertOk()->assertSee('2026')->assertDontSee('Guru Lain');
        $this->actingAs($this->admin)->get(route('izin-guru.index'))->assertOk()->assertSee('Guru Lain')->assertSee($this->guru->nama);
    }

    public function test_guru_can_cancel_only_their_own_pending_request(): void
    {
        $milik = $this->buat('menunggu');
        $diputus = $this->buat('disetujui', null, '2026-11-02', '2026-11-03');
        $orangLain = $this->buat('menunggu', Guru::where('user_id', $this->guruLain->id)->first(), '2026-12-01', '2026-12-02');

        $this->actingAs($this->guruUser)->delete(route('izin-guru.destroy', $orangLain))->assertNotFound();
        $this->actingAs($this->guruUser)->delete(route('izin-guru.destroy', $diputus))->assertSessionHas('error');
        $this->actingAs($this->guruUser)->delete(route('izin-guru.destroy', $milik))->assertRedirect();

        $this->assertNull(IzinGuru::find($milik->id));
        $this->assertNotNull(IzinGuru::find($diputus->id));
        $this->assertNotNull(IzinGuru::find($orangLain->id));
    }

    public function test_pending_scope_counts_only_waiting_requests(): void
    {
        $this->buat('menunggu');
        $this->buat('disetujui', null, '2026-11-02', '2026-11-03');

        $this->assertSame(1, IzinGuru::menunggu()->count());
    }
}
