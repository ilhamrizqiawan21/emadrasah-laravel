<?php

namespace App\Support\Ekspor;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** Unduhan xlsx satu lembar. Teks ditulis sebagai teks (bukan rumus), jadi aman dari injeksi rumus. */
class Xlsx
{
    /**
     * @param  list<string>  $header
     * @param  iterable<list<scalar|null>>  $rows
     */
    public static function unduh(string $namaBerkas, array $header, iterable $rows, string $namaLembar = 'Data'): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'eks').'.xlsx';

        $options = new Options;
        $options->setColumnWidth(18, ...range(1, count($header)));
        $writer = new Writer($options);
        $writer->openToFile($path);
        $writer->getCurrentSheet()->setName($namaLembar);
        $writer->addRow(Row::fromValuesWithStyle($header, (new Style)->withFontBold(true)->withBackgroundColor('E2E8F0')));
        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues($row));
        }
        $writer->close();

        return response()->download($path, $namaBerkas, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }
}
