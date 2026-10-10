<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\RaportNilai;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Penjaga data: menghapus kelas/siswa tidak boleh menghilangkan arsip, semester hanya 1-2,
 * dan tahun pelajaran tidak boleh ditebak dari nilai yang tertulis di kode.
 */
class DataSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Storage::fake('local');
        $this->actingAs(User::where('role', 'operator')->firstOrFail());
    }

    public function test_class_with_students_cannot_be_deleted_and_students_survive(): void
    {
        $kelas = Kelas::create(['nama_kelas' => 'Kelas Berisi', 'tingkat' => '7', 'kapasitas' => 30]);
        Siswa::create(['nis' => '8880001', 'nama_lengkap' => 'Siswa Uji', 'kelas_id' => $kelas->id, 'jenis_kelamin' => 'L', 'status' => 'Aktif']);
        $jumlah = 1;

        $this->delete("/kelas/{$kelas->id}")->assertRedirect(route('kelas.index'))->assertSessionHas('error');

        $this->assertDatabaseHas('kelas', ['id' => $kelas->id]);
        $this->assertSame($jumlah, Siswa::where('kelas_id', $kelas->id)->count());
    }

    public function test_class_holding_only_archived_students_cannot_be_deleted_either(): void
    {
        $kelas = Kelas::create(['nama_kelas' => 'Kelas Arsip', 'tingkat' => '7', 'kapasitas' => 30]);
        $siswa = Siswa::create(['nis' => '8880002', 'nama_lengkap' => 'Siswa Arsip', 'kelas_id' => $kelas->id, 'jenis_kelamin' => 'L', 'status' => 'Aktif']);
        $siswa->delete(); // soft delete: baris tetap ada dan masih terhubung dengan cascade

        $this->delete("/kelas/{$kelas->id}")->assertRedirect(route('kelas.index'))->assertSessionHas('error');

        $this->assertDatabaseHas('kelas', ['id' => $kelas->id]);
        $this->assertSoftDeleted('siswa', ['id' => $siswa->id]);
    }

    public function test_class_without_students_can_still_be_deleted(): void
    {
        $kelas = Kelas::create(['nama_kelas' => 'Kelas Kosong', 'tingkat' => '7', 'kapasitas' => 30]);

        $this->delete("/kelas/{$kelas->id}")->assertSessionHas('success');
        $this->assertDatabaseMissing('kelas', ['id' => $kelas->id]);
    }

    public function test_deleting_a_student_is_soft_and_keeps_report_history(): void
    {
        $siswa = Siswa::firstOrFail();
        $tp = TahunPelajaran::firstOrFail();
        $mapel = Mapel::firstOrFail();
        RaportNilai::updateOrCreate(['siswa_id' => $siswa->id, 'tahun_pelajaran_id' => $tp->id, 'semester' => 1, 'mapel_id' => $mapel->id], ['nilai_akhir' => 80]);

        $this->delete("/siswa/{$siswa->id}")->assertRedirect(route('siswa.index'));

        $this->assertSoftDeleted('siswa', ['id' => $siswa->id]);
        $this->assertDatabaseHas('raport_nilai', ['siswa_id' => $siswa->id, 'mapel_id' => $mapel->id]);
        $this->get('/siswa')->assertDontSee($siswa->nama_lengkap);
    }

    public function test_deleted_student_nis_cannot_be_reused_silently(): void
    {
        $siswa = Siswa::firstOrFail();
        $nis = $siswa->nis;
        $this->delete("/siswa/{$siswa->id}");

        $this->post('/siswa', [
            'nis' => $nis, 'nama_lengkap' => 'Pendaftar Baru', 'kelas_id' => Kelas::firstOrFail()->id,
            'jenis_kelamin' => 'L', 'status' => 'Aktif',
        ])->assertSessionHasErrors('nis');
    }

    public function test_raport_semester_is_limited_to_one_or_two(): void
    {
        $siswa = Siswa::firstOrFail();
        $mapel = Mapel::firstOrFail();
        $base = ['tahun_pelajaran_id' => TahunPelajaran::firstOrFail()->id, 'nilai' => [$mapel->id => ['angka' => 80]]];

        $this->post("/raport/{$siswa->id}/store", $base + ['semester' => 3])->assertSessionHasErrors('semester');
        $this->post("/raport/{$siswa->id}/store", $base + ['semester' => 2])->assertSessionHasNoErrors();
    }

    public function test_schedule_is_refused_when_no_academic_year_is_active(): void
    {
        TahunPelajaran::query()->update(['is_aktif' => false]);

        $this->postJson('/jadwal/grid-store', [
            'kelas_id' => Kelas::firstOrFail()->id, 'hari' => 'Senin', 'jam_id' => 1, 'guru_id' => 1, 'mapel_id' => 1,
        ])->assertStatus(422)->assertJsonFragment(['message' => 'Belum ada tahun pelajaran aktif. Aktifkan satu di menu Tahun Pelajaran.']);
    }
}
