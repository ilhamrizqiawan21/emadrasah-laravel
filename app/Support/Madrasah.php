<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Single access point for the madrasah identity (name, address, logo, theme…).
 *
 * Values come from the `settings` table, which the admin edits on the
 * Pengaturan page. Anything not stored yet falls back to config/madrasah.php,
 * so a fresh installation works before anyone opens the settings page.
 *
 * Shared with every view as `$madrasah`, e.g. {{ $madrasah->nama }}.
 */
class Madrasah
{
    public const CACHE_KEY = 'madrasah.settings';

    /** Plain text settings. */
    public const TEXT_KEYS = [
        'nama', 'nama_pendek', 'nama_lengkap', 'npsn', 'alamat', 'telepon',
        'email', 'website', 'kepala_nama', 'kepala_nip',
    ];

    /** Every key stored in the settings table. */
    public const KEYS = [...self::TEXT_KEYS, 'warna_utama', 'logo', 'favicon'];

    /** Ready-made brand colours offered on the settings page (all pass the contrast check). */
    public const PRESETS = [
        'Emerald' => '#047857',
        'Teal' => '#0f766e',
        'Biru' => '#1d4ed8',
        'Indigo' => '#4338ca',
        'Ungu' => '#7e22ce',
        'Mawar' => '#be123c',
        'Amber' => '#b45309',
        'Slate' => '#334155',
    ];

    /** @var array<string,?string>|null */
    private ?array $resolved = null;

    /** @return array<string,?string> */
    public function all(): array
    {
        if ($this->resolved === null) {
            $stored = $this->stored();
            $this->resolved = [];

            foreach (self::KEYS as $key) {
                $this->resolved[$key] = $stored[$key] ?? config("madrasah.{$key}");
            }
        }

        return $this->resolved;
    }

    public function get(string $key, ?string $default = null): ?string
    {
        return $this->all()[$key] ?? $default;
    }

    public function __get(string $name): ?string
    {
        return $this->get($name);
    }

    /** @param array<string,?string> $values */
    public function save(array $values): void
    {
        foreach ($values as $key => $value) {
            if (in_array($key, self::KEYS, true)) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }

        $this->forget();
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->resolved = null;
    }

    /**
     * Stored path of an uploaded branding file. The favicon falls back to the
     * logo so uploading one logo is enough.
     */
    public function brandingPath(string $type): ?string
    {
        $path = $this->get($type) ?: ($type === 'favicon' ? $this->get('logo') : null);

        return $path && str_starts_with($path, 'branding/') && ! str_contains($path, '..') ? $path : null;
    }

    public function logoUrl(): string
    {
        return $this->brandingUrl('logo');
    }

    public function faviconUrl(): string
    {
        return $this->brandingUrl('favicon');
    }

    /** Logo as a data URI, for PDF rendering where remote URLs are not reliable. */
    public function logoDataUri(): ?string
    {
        $path = $this->brandingPath('logo');
        $disk = Storage::disk('local');

        if ($path && $disk->exists($path)) {
            return 'data:'.$disk->mimeType($path).';base64,'.base64_encode($disk->get($path));
        }

        $default = public_path('images/logo.png');

        return is_file($default) ? 'data:image/png;base64,'.base64_encode(file_get_contents($default)) : null;
    }

    /** Theme override CSS, empty when the default colour is in use. */
    public function themeCss(): string
    {
        return ThemePalette::css($this->get('warna_utama'));
    }

    public function warnaUtama(): string
    {
        $color = $this->get('warna_utama');

        return ThemePalette::isHex($color) ? strtolower($color) : ThemePalette::DEFAULT;
    }

    private function brandingUrl(string $type): string
    {
        // Berkas unggahan memakai namanya (acak, berubah tiap unggah). Berkas bawaan memakai waktu ubahnya,
        // supaya browser yang menyimpan URL ini (cache immutable) memuat ulang saat berkas bawaan diganti.
        $path = $this->brandingPath($type);
        $default = public_path($type === 'logo' ? 'images/logo.png' : 'favicon.ico');
        $version = substr(md5($path ?? 'default:'.(is_file($default) ? filemtime($default) : '')), 0, 8);

        return route('branding.show', ['type' => $type, 'v' => $version]);
    }

    /** @return array<string,?string> */
    private function stored(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, fn () => Setting::pluck('value', 'key')->all());
        } catch (Throwable) {
            // The settings table may not exist yet (fresh install before migrate).
            return [];
        }
    }
}
