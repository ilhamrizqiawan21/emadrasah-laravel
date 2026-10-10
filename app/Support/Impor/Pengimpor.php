<?php

namespace App\Support\Impor;

use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Kerangka impor: tiap baris divalidasi sendiri-sendiri. Baris bermasalah dilewati dengan alasan,
 * baris valid tetap masuk (semuanya dalam satu transaksi). Data yang sudah ada tidak pernah ditimpa.
 */
abstract class Pengimpor
{
    /** @return array<string, array{label: string, alias?: list<string>, wajib?: bool}> */
    abstract public function kolom(): array;

    /** @return list<string> catatan untuk lembar "Petunjuk" di template */
    abstract public function petunjuk(): array;

    /** Siapkan nilai per baris (normalisasi) sebelum divalidasi. */
    abstract protected function siapkan(array $data): array;

    /** @return array<string, mixed> */
    abstract protected function aturan(): array;

    /** @return array<string, string> key kolom unik => nama untuk pesan ("NIS") */
    abstract protected function kolomUnik(): array;

    /** Nilai yang sudah ada di database per kolom unik. @return array<string, array<string, true>> */
    abstract protected function yangSudahAda(): array;

    /** Kesalahan khusus per jenis (mis. relasi tidak ditemukan); kembalikan pesan atau null. */
    protected function periksaTambahan(array &$data): ?string
    {
        return null;
    }

    abstract protected function simpan(array $data): void;

    /**
     * @param  list<array{baris: int, data: array<string, mixed>}>  $rows
     * @return array{diimpor: int, dilewati: list<array{baris: int, pesan: string}>, periksa: bool}
     */
    public function proses(array $rows, bool $periksa): array
    {
        $ada = $this->yangSudahAda();
        $terlihat = array_fill_keys(array_keys($this->kolomUnik()), []);
        $dilewati = [];
        $diterima = [];

        foreach ($rows as ['baris' => $baris, 'data' => $mentah]) {
            $data = $this->siapkan($mentah);

            $pesan = $this->periksaTambahan($data);
            $v = Validator::make($data, $this->aturan(), ['date_format' => ':attribute tidak valid (contoh: 2012-03-06 atau 06/03/2012)'], $this->namaKolom());
            if ($pesan === null && $v->fails()) {
                $pesan = implode('; ', $v->errors()->all());
            }

            foreach ($this->kolomUnik() as $key => $nama) {
                $nilai = $data[$key] ?? null;
                if ($pesan !== null || $nilai === null) {
                    continue;
                }
                $kunci = mb_strtolower((string) $nilai);
                if (isset($ada[$key][$kunci])) {
                    $pesan = "$nama $nilai sudah terdaftar";
                } elseif (isset($terlihat[$key][$kunci])) {
                    $pesan = "$nama $nilai muncul dua kali di berkas";
                }
            }

            if ($pesan !== null) {
                $dilewati[] = ['baris' => $baris, 'pesan' => $pesan];

                continue;
            }

            foreach ($this->kolomUnik() as $key => $nama) {
                if (($data[$key] ?? null) !== null) {
                    $terlihat[$key][mb_strtolower((string) $data[$key])] = true;
                }
            }
            $diterima[] = $data;
        }

        if (! $periksa && $diterima) {
            DB::transaction(function () use ($diterima) {
                foreach ($diterima as $data) {
                    $this->simpan($data);
                }
            });
        }

        return ['diimpor' => count($diterima), 'dilewati' => $dilewati, 'periksa' => $periksa];
    }

    /** @return array<string, string> */
    protected function namaKolom(): array
    {
        return array_map(fn ($d) => $d['label'], $this->kolom());
    }

    protected function teks(mixed $v): ?string
    {
        if ($v instanceof DateTimeInterface) {
            return $v->format('Y-m-d');
        }

        $t = trim((string) $v);

        return $t === '' ? null : $t;
    }

    /** Tanggal dari sel tanggal Excel atau teks (2012-03-06, 06/03/2012, 06-03-2012, 06.03.2012). */
    protected function tanggal(mixed $v): ?string
    {
        if ($v instanceof DateTimeInterface) {
            return $v->format('Y-m-d');
        }
        $t = $this->teks($v);
        if ($t === null) {
            return null;
        }
        // Nomor seri Excel (hari sejak 30-12-1899) bila kolom berformat "Umum"; rentang 1927-2118.
        if (ctype_digit($t) && (int) $t >= 10000 && (int) $t <= 80000) {
            return (new DateTimeImmutable('1899-12-30'))->modify("+{$t} days")->format('Y-m-d');
        }
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y'] as $format) {
            $d = DateTimeImmutable::createFromFormat('!'.$format, $t);
            if ($d && $d->format($format) === $t) {
                return $d->format('Y-m-d');
            }
        }

        return 'tidak-valid:'.$t; // ditolak validator 'date_format' dengan pesan jelas
    }
}
