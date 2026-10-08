<?php

namespace Tests\Unit;

use App\Support\ThemePalette;
use PHPUnit\Framework\TestCase;

class ThemePaletteTest extends TestCase
{
    public function test_hex_validation(): void
    {
        $this->assertTrue(ThemePalette::isHex('#047857'));
        $this->assertTrue(ThemePalette::isHex('#ABCDEF'));
        $this->assertFalse(ThemePalette::isHex('047857'));
        $this->assertFalse(ThemePalette::isHex('#04785'));
        $this->assertFalse(ThemePalette::isHex('red'));
        $this->assertFalse(ThemePalette::isHex('#047857;}body{display:none'));
        $this->assertFalse(ThemePalette::isHex(null));
    }

    public function test_rgb_parsing(): void
    {
        $this->assertSame([4, 120, 87], ThemePalette::rgb('#047857'));
    }

    public function test_contrast_with_white(): void
    {
        $this->assertEqualsWithDelta(1.0, ThemePalette::contrastWithWhite('#ffffff'), 0.01);
        $this->assertLessThan(ThemePalette::MIN_CONTRAST, ThemePalette::contrastWithWhite('#facc15'));
        $this->assertGreaterThanOrEqual(ThemePalette::MIN_CONTRAST, ThemePalette::contrastWithWhite('#047857'));
    }

    public function test_scale_keeps_brand_colour_and_orders_shades_by_lightness(): void
    {
        $scale = ThemePalette::scale('#1d4ed8');

        $this->assertSame('#1d4ed8', $scale['700']);

        $order = ['950', '900', '800', '700', '600', '500', '200', '100', '50'];
        $lightness = array_map(fn (string $shade) => array_sum(ThemePalette::rgb($scale[$shade])), $order);
        $sorted = $lightness;
        sort($sorted);

        $this->assertSame($sorted, $lightness, 'Shades must get lighter from 950 to 50');
    }

    public function test_scale_for_default_colour_is_close_to_builtin_palette(): void
    {
        $builtIn = ['800' => '#065f46', '600' => '#059669', '100' => '#d1fae5'];
        $scale = ThemePalette::scale(ThemePalette::DEFAULT);

        foreach ($builtIn as $shade => $expected) {
            foreach (ThemePalette::rgb($scale[$shade]) as $i => $channel) {
                $this->assertEqualsWithDelta(ThemePalette::rgb($expected)[$i], $channel, 12, "shade {$shade}");
            }
        }
    }

    public function test_css_is_empty_for_default_or_invalid_colour(): void
    {
        $this->assertSame('', ThemePalette::css(ThemePalette::DEFAULT));
        $this->assertSame('', ThemePalette::css('#047857'));
        $this->assertSame('', ThemePalette::css(null));
        $this->assertSame('', ThemePalette::css('red; } body { display:none'));
    }

    public function test_css_overrides_theme_and_bootstrap_variables(): void
    {
        $css = ThemePalette::css('#1d4ed8');

        $this->assertStringStartsWith(':root{', $css);
        $this->assertStringContainsString('--em-green-700:#1d4ed8', $css);
        $this->assertStringContainsString('--em-green-700-rgb:29, 78, 216', $css);
        $this->assertStringContainsString('--bs-primary:#1d4ed8', $css);
        $this->assertStringNotContainsString('<', $css);
    }
}
