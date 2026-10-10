<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class KataSandiTest extends TestCase
{
    use RefreshDatabase;

    private const PESAN_UMUM = 'Jika email terdaftar dan akunnya aktif, tautan pengaturan ulang kata sandi sudah dikirim.';

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->user = User::where('role', 'guru')->firstOrFail();
    }

    // ── Lupa kata sandi ───────────────────────────────────────────────────

    public function test_login_page_links_to_forgot_password_and_form_loads(): void
    {
        $this->get('/login')->assertSee('Lupa password?')->assertSee(route('password.request'), false);
        $this->get('/lupa-sandi')->assertOk()->assertSee('Lupa Kata Sandi');
    }

    public function test_reset_link_is_sent_for_active_users_with_a_generic_answer(): void
    {
        Notification::fake();

        $this->post('/lupa-sandi', ['email' => $this->user->email])->assertRedirect()->assertSessionHas('status', self::PESAN_UMUM);

        Notification::assertSentTo($this->user, ResetPassword::class);
    }

    public function test_unknown_and_inactive_accounts_get_the_same_answer_but_no_mail(): void
    {
        Notification::fake();
        $this->user->update(['is_active' => false]);

        $this->post('/lupa-sandi', ['email' => $this->user->email])->assertSessionHas('status', self::PESAN_UMUM);
        $this->post('/lupa-sandi', ['email' => 'tidak-ada@contoh.id'])->assertSessionHas('status', self::PESAN_UMUM);

        Notification::assertNothingSent();
    }

    public function test_a_mail_failure_does_not_reveal_that_the_account_exists(): void
    {
        Password::shouldReceive('sendResetLink')->andThrow(new \RuntimeException('SMTP mati'));

        $this->post('/lupa-sandi', ['email' => $this->user->email])->assertRedirect()->assertSessionHas('status', self::PESAN_UMUM);
    }

    public function test_invalid_email_format_is_rejected_and_requests_are_throttled(): void
    {
        $this->post('/lupa-sandi', ['email' => 'bukan-email'])->assertSessionHasErrors('email');

        Notification::fake();
        for ($i = 0; $i < 3; $i++) {
            $this->post('/lupa-sandi', ['email' => $this->user->email])->assertRedirect();
        }
        $this->post('/lupa-sandi', ['email' => $this->user->email])->assertStatus(429);
    }

    public function test_the_email_is_in_indonesian_and_links_to_the_reset_form(): void
    {
        $mail = (new ResetPassword('token-uji'))->toMail($this->user);
        $teks = $mail->subject.' '.implode(' ', array_map(fn ($l) => is_string($l) ? $l : '', array_merge($mail->introLines, $mail->outroLines))).' '.$mail->actionText;

        $this->assertStringContainsString('Atur ulang kata sandi', $mail->subject);
        $this->assertStringContainsString('60 menit', $teks);
        $this->assertStringContainsString('/reset-sandi/token-uji', $mail->actionUrl);
        $this->assertStringContainsString(urlencode($this->user->email), $mail->actionUrl);
    }

    // ── Atur ulang lewat tautan ───────────────────────────────────────────

    private function reset(string $token, array $override = [])
    {
        return $this->post('/reset-sandi', array_merge([
            'token' => $token, 'email' => $this->user->email,
            'password' => 'KataSandiBaru-2026', 'password_confirmation' => 'KataSandiBaru-2026',
        ], $override));
    }

    public function test_reset_form_opens_with_the_token(): void
    {
        $this->get('/reset-sandi/abc123?email='.urlencode($this->user->email))->assertOk()->assertSee('value="abc123"', false);
    }

    public function test_valid_token_changes_the_password_once_and_does_not_log_in(): void
    {
        $token = Password::createToken($this->user);

        $this->reset($token)->assertRedirect('/login')->assertSessionHas('status');
        $this->assertGuest();
        $this->assertTrue(Hash::check('KataSandiBaru-2026', $this->user->fresh()->password));

        // token sekali pakai
        $this->reset($token, ['password' => 'LainLagi-2026-x', 'password_confirmation' => 'LainLagi-2026-x'])->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('KataSandiBaru-2026', $this->user->fresh()->password));

        $this->post('/login', ['email' => $this->user->email, 'password' => 'KataSandiBaru-2026'])->assertRedirect('/dashboard');
    }

    public function test_wrong_token_or_inactive_account_cannot_reset(): void
    {
        $awal = $this->user->password;

        $this->reset('token-salah')->assertSessionHasErrors('email');

        $token = Password::createToken($this->user);
        $this->user->update(['is_active' => false]);
        $this->reset($token)->assertSessionHasErrors('email');

        $this->assertSame($awal, $this->user->fresh()->password);
    }

    public function test_weak_passwords_are_rejected(): void
    {
        $token = Password::createToken($this->user);
        $awal = $this->user->password;

        $this->reset($token, ['password' => 'pendek', 'password_confirmation' => 'pendek'])->assertSessionHasErrors('password');
        $this->reset($token, ['password' => 'password123', 'password_confirmation' => 'password123'])->assertSessionHasErrors('password');
        $this->reset($token, ['password' => 'KataSandiBaru-2026', 'password_confirmation' => 'beda'])->assertSessionHasErrors('password');
        $this->reset($token, ['password' => $this->user->email, 'password_confirmation' => $this->user->email])->assertSessionHasErrors('password');

        $this->assertSame($awal, $this->user->fresh()->password);
    }

    // ── Ganti kata sandi sendiri ──────────────────────────────────────────

    private function ganti(array $override = [])
    {
        return $this->put('/akun/sandi', array_merge([
            'current_password' => 'guru123', 'password' => 'KataSandiBaru-2026', 'password_confirmation' => 'KataSandiBaru-2026',
        ], $override));
    }

    public function test_user_changes_own_password_with_the_current_one(): void
    {
        $this->actingAs($this->user);
        $this->get('/akun/sandi')->assertOk()->assertSee('Ganti Kata Sandi');

        $this->ganti()->assertRedirect()->assertSessionHas('success');
        $this->assertTrue(Hash::check('KataSandiBaru-2026', $this->user->fresh()->password));
    }

    public function test_changing_password_requires_the_right_current_password_and_a_strong_new_one(): void
    {
        $this->actingAs($this->user);
        $awal = $this->user->password;

        $this->ganti(['current_password' => 'salah'])->assertSessionHasErrors('current_password');
        $this->ganti(['password' => 'guru123', 'password_confirmation' => 'guru123'])->assertSessionHasErrors('password');
        $this->ganti(['password' => 'pendek', 'password_confirmation' => 'pendek'])->assertSessionHasErrors('password');
        $this->ganti(['password_confirmation' => 'beda'])->assertSessionHasErrors('password');

        $this->assertSame($awal, $this->user->fresh()->password);
    }

    public function test_every_role_can_change_own_password_but_guests_cannot(): void
    {
        $this->get('/akun/sandi')->assertRedirect('/login');
        $this->put('/akun/sandi', [])->assertRedirect('/login');

        foreach (['admin', 'operator', 'guru'] as $role) {
            $this->actingAs(User::where('role', $role)->firstOrFail())->get('/akun/sandi')->assertOk();
        }
    }
}
