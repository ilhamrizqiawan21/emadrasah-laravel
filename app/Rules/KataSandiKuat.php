<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Aturan kata sandi: minimal 10 karakter, bukan kata sandi umum, dan bukan email akun itu sendiri. */
class KataSandiKuat implements ValidationRule
{
    private const UMUM = ['admin123', 'password', 'password123', '12345678', '1234567890', 'qwertyuiop', 'madrasah123', 'guru123', 'operator123', 'passw0rd123'];

    public function __construct(private ?string $email = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $nilai = (string) $value;

        if (mb_strlen($nilai) < 10) {
            $fail('Kata sandi minimal 10 karakter.');
        } elseif (in_array(mb_strtolower($nilai), self::UMUM, true)) {
            $fail('Kata sandi terlalu umum. Pilih yang lebih sulit ditebak.');
        } elseif ($this->email !== null && mb_strtolower($nilai) === mb_strtolower($this->email)) {
            $fail('Kata sandi tidak boleh sama dengan email.');
        }
    }
}
