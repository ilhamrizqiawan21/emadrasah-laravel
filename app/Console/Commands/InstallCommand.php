<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Membuat akun admin pertama pada instalasi baru (produksi). Seeder demo tidak
 * membuat akun apa pun di produksi, jadi inilah satu-satunya cara masuk pertama kali.
 */
class InstallCommand extends Command
{
    protected $signature = 'madrasah:install
        {--name= : Nama admin}
        {--email= : Email admin}
        {--password= : Kata sandi (disarankan dikosongkan agar ditanya atau dibuat otomatis)}';

    protected $description = 'Buat akun admin pertama untuk instalasi baru';

    /** Kata sandi yang sangat umum dan tidak boleh dipakai. */
    private const WEAK = ['admin123', 'password', 'password123', '12345678', '1234567890', 'qwertyuiop', 'madrasah123'];

    public function handle(): int
    {
        if (User::where('role', 'admin')->exists()) {
            $this->error('Sudah ada akun admin. Perintah ini hanya untuk instalasi baru; kelola pengguna lewat menu Pengguna.');

            return self::FAILURE;
        }

        $name = $this->option('name') ?: $this->ask('Nama admin', 'Administrator');
        $email = $this->option('email') ?: $this->ask('Email admin');
        $password = $this->option('password');
        $generated = false;

        if (! $password && $this->input->isInteractive()) {
            $password = $this->secret('Kata sandi (kosongkan untuk dibuat otomatis)');
        }

        if (! $password) {
            $password = Str::password(16, symbols: false);
            $generated = true;
        }

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => ['required', 'string', 'min:10', 'max:255', function ($attribute, $value, $fail) {
                    if (in_array(Str::lower($value), self::WEAK, true)) {
                        $fail('Kata sandi terlalu umum.');
                    }
                }],
            ],
            ['password.min' => 'Kata sandi minimal 10 karakter.']
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->info("Admin dibuat: {$email}");

        if ($generated) {
            $this->warn("Kata sandi (hanya ditampilkan sekali, simpan sekarang): {$password}");
        }

        $this->line('Langkah berikutnya: masuk, lalu isi identitas madrasah di menu Pengaturan.');

        return self::SUCCESS;
    }
}
