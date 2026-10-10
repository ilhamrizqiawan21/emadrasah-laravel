<?php

namespace Tests\Unit;

use App\Support\ThemePalette;
use PHPUnit\Framework\TestCase;

/**
 * Menjaga token warna tema di sumber Sass. Bug nyata yang pernah lolos: tiga shade palet
 * merujuk dirinya sendiri (var(--x) di dalam --x) dan kanal -rgb tidak terdefinisi, sehingga
 * tema bawaan diam-diam kehilangan tint dan bayangan sementara tema kustom (yang menimpa
 * variabel itu) tampak normal.
 */
class ThemeTokensTest extends TestCase
{
    private function root(): string
    {
        $css = file_get_contents(dirname(__DIR__, 2).'/resources/sass/_custom.scss');
        preg_match('/:root\s*\{(.*?)\n\}/s', $css, $m);

        return $m[1] ?? '';
    }

    public function test_every_palette_shade_has_a_literal_default_value(): void
    {
        $root = $this->root();

        foreach (array_keys(ThemePalette::scale(ThemePalette::DEFAULT)) as $shade) {
            $this->assertMatchesRegularExpression(
                "/--em-green-{$shade}\\s*:\\s*#[0-9a-fA-F]{6}/",
                $root,
                "--em-green-{$shade} harus bernilai hex di tema bawaan"
            );
        }
    }

    public function test_rgb_channels_exist_and_match_the_hex_palette(): void
    {
        $root = $this->root();
        $expected = [
            '700' => ThemePalette::rgb('#047857'),
            '600' => ThemePalette::rgb('#059669'),
            '500' => ThemePalette::rgb('#10b981'),
        ];

        foreach ($expected as $shade => $rgb) {
            $this->assertStringContainsString(
                "--em-green-{$shade}-rgb: ".implode(', ', $rgb),
                $root,
                "--em-green-{$shade}-rgb harus terdefinisi dan sesuai hex shade {$shade}"
            );
        }
    }

    public function test_no_css_variable_is_defined_in_terms_of_itself(): void
    {
        $offenders = [];

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(dirname(__DIR__, 2).'/resources/sass', \FilesystemIterator::SKIP_DOTS)) as $file) {
            foreach (file($file->getPathname()) as $i => $line) {
                if (preg_match('/(--[a-z0-9-]+)\s*:\s*var\(\1\)/', $line)) {
                    $offenders[] = $file->getFilename().':'.($i + 1).' '.trim($line);
                }
            }
        }

        $this->assertSame([], $offenders, "Variabel yang merujuk dirinya sendiri tidak valid:\n".implode("\n", $offenders));
    }

    public function test_every_used_rgb_channel_variable_is_defined_somewhere(): void
    {
        $used = [];
        $defined = [];

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(dirname(__DIR__, 2).'/resources/sass', \FilesystemIterator::SKIP_DOTS)) as $file) {
            $text = file_get_contents($file->getPathname());
            preg_match_all('/var\((--[a-z0-9-]+-rgb)\)/', $text, $u);
            preg_match_all('/(--[a-z0-9-]+-rgb)\s*:/', $text, $d);
            $used = array_merge($used, $u[1]);
            $defined = array_merge($defined, $d[1]);
        }

        $this->assertSame([], array_values(array_diff(array_unique($used), array_unique($defined))));
    }
}
