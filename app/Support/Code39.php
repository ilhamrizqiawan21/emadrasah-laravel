<?php

namespace App\Support;

/**
 * Barcode Code 39 sebagai gambar SVG (data URI) untuk kartu pelajar. Tiap karakter terdiri dari 9 elemen
 * (batang dan spasi bergantian, diawali batang); "1" berarti elemen lebar, "0" sempit.
 */
class Code39
{
    /** Pola tiap karakter, termasuk "*" sebagai tanda awal dan akhir. */
    public const POLA = [
        '0' => '000110100', '1' => '100100001', '2' => '001100001', '3' => '101100000', '4' => '000110001',
        '5' => '100110000', '6' => '001110000', '7' => '000100101', '8' => '100100100', '9' => '001100100',
        'A' => '100001001', 'B' => '001001001', 'C' => '101001000', 'D' => '000011001', 'E' => '100011000',
        'F' => '001011000', 'G' => '000001101', 'H' => '100001100', 'I' => '001001100', 'J' => '000011100',
        'K' => '100000011', 'L' => '001000011', 'M' => '101000010', 'N' => '000010011', 'O' => '100010010',
        'P' => '001010010', 'Q' => '000000111', 'R' => '100000110', 'S' => '001000110', 'T' => '000010110',
        'U' => '110000001', 'V' => '011000001', 'W' => '111000000', 'X' => '010010001', 'Y' => '110010000',
        'Z' => '011010000', '-' => '010000101', '.' => '110000100', ' ' => '011000100', '$' => '010101000',
        '/' => '010100010', '+' => '010001010', '%' => '000101010', '*' => '010010100',
    ];

    private const LEBAR = 3;

    /** Teks boleh dikodekan bila tidak kosong dan hanya berisi karakter Code 39 (huruf besar, angka, - . spasi $ / + %). */
    public static function bisa(string $teks): bool
    {
        return $teks !== '' && preg_match('/^[0-9A-Z\-. $\/+%]+$/', strtoupper($teks)) === 1;
    }

    public static function dataUri(string $teks): ?string
    {
        if (! self::bisa($teks)) {
            return null;
        }

        $x = 0;
        $batang = '';
        foreach (str_split('*'.strtoupper($teks).'*') as $i => $karakter) {
            if ($i > 0) {
                $x++;   // spasi sempit antar karakter
            }
            foreach (str_split(self::POLA[$karakter]) as $urutan => $elemen) {
                $lebar = $elemen === '1' ? self::LEBAR : 1;
                if ($urutan % 2 === 0) {
                    $batang .= "<rect x=\"{$x}\" y=\"0\" width=\"{$lebar}\" height=\"40\" fill=\"#000\"/>";
                }
                $x += $lebar;
            }
        }

        $svg = "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$x}\" height=\"40\" viewBox=\"0 0 {$x} 40\" preserveAspectRatio=\"none\">{$batang}</svg>";

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
