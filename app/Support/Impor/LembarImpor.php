<?php

namespace App\Support\Impor;

use DateTimeInterface;
use OpenSpout\Reader\CSV\Options as CsvOptions;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use Throwable;

/** Membaca lembar pertama berkas xlsx/csv menjadi baris berkunci sesuai definisi kolom. */
class LembarImpor
{
    public const MAKS_BARIS = 1000;

    /** Huruf kecil tanpa spasi/tanda baca: "No. HP" dan "no hp" dianggap sama. */
    public static function normal(string $label): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower($label));
    }

    /**
     * @param  array<string, array{label: string, alias?: list<string>, wajib?: bool}>  $kolom
     * @return list<array{baris: int, data: array<string, mixed>}>
     */
    public static function baca(string $path, string $ext, array $kolom): array
    {
        $semua = [];
        try {
            foreach (self::barisMentah($path, $ext) as $nomor => $nilai) {
                $semua[$nomor] = $nilai;
            }
        } catch (ImporGagal $e) {
            throw $e;
        } catch (Throwable) {
            throw new ImporGagal('Berkas tidak dapat dibaca. Pastikan berkas xlsx atau csv yang valid dan tidak rusak.');
        }

        // Baris pertama yang berisi = header.
        $header = null;
        $hasil = [];
        foreach ($semua as $nomor => $cells) {
            if (self::kosong($cells)) {
                continue;
            }
            if ($header === null) {
                $header = self::petakan($cells, $kolom);

                continue;
            }

            $data = [];
            foreach ($header as $indeks => $key) {
                $data[$key] = self::bersihkan($cells[$indeks] ?? null);
            }
            $hasil[] = ['baris' => $nomor, 'data' => $data];
            if (count($hasil) > self::MAKS_BARIS) {
                throw new ImporGagal('Berkas berisi lebih dari '.self::MAKS_BARIS.' baris data. Pecah menjadi beberapa berkas.');
            }
        }

        if ($header === null || $hasil === []) {
            throw new ImporGagal('Berkas tidak berisi baris data.');
        }

        return $hasil;
    }

    /** @return iterable<int, list<mixed>> nomor baris (1 = baris pertama berkas) => nilai sel */
    private static function barisMentah(string $path, string $ext): iterable
    {
        if ($ext === 'csv') {
            $baris = fgets($fh = fopen($path, 'r')) ?: '';
            fclose($fh);
            $reader = new CsvReader(new CsvOptions(FIELD_DELIMITER: substr_count($baris, ';') > substr_count($baris, ',') ? ';' : ','));
        } else {
            $reader = new XlsxReader;
        }

        $reader->open($path);
        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $nomor => $row) {
                    yield $nomor => $row->toArray();
                }
                break; // hanya lembar pertama
            }
        } finally {
            $reader->close();
        }
    }

    /** @return array<int, string> indeks kolom => key */
    private static function petakan(array $cells, array $kolom): array
    {
        $peta = [];
        foreach ($kolom as $key => $def) {
            foreach ([$def['label'], ...($def['alias'] ?? [])] as $nama) {
                $peta[self::normal($nama)] = $key;
            }
        }

        $header = [];
        foreach ($cells as $i => $cell) {
            $normal = self::normal((string) $cell);
            if ($normal !== '' && isset($peta[$normal]) && ! in_array($peta[$normal], $header, true)) {
                $header[$i] = $peta[$normal];
            }
        }

        $hilang = [];
        foreach ($kolom as $key => $def) {
            if (($def['wajib'] ?? false) && ! in_array($key, $header, true)) {
                $hilang[] = $def['label'];
            }
        }
        if ($hilang) {
            throw new ImporGagal('Kolom wajib tidak ditemukan: '.implode(', ', $hilang).'. Gunakan template dari halaman ini.');
        }

        return $header;
    }

    private static function kosong(array $cells): bool
    {
        foreach ($cells as $c) {
            if ($c instanceof DateTimeInterface || trim((string) $c) !== '') {
                return false;
            }
        }

        return true;
    }

    /** Teks dirapikan; angka bulat dari Excel menjadi string tanpa notasi ilmiah; tanggal tetap objek. */
    private static function bersihkan(mixed $v): mixed
    {
        if ($v instanceof DateTimeInterface) {
            return $v;
        }
        if (is_float($v) && floor($v) === $v && abs($v) < 9e15) {
            return (string) (int) $v;
        }

        $teks = trim((string) $v);

        return $teks === '' ? null : $teks;
    }
}
