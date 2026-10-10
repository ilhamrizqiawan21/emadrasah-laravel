<?php

namespace App\Support\Impor;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;

/** Template xlsx: lembar "Data" (hanya judul kolom) dan lembar "Petunjuk". Mengembalikan path berkas sementara. */
class TemplateImpor
{
    public static function buat(Pengimpor $pengimpor): string
    {
        $path = tempnam(sys_get_temp_dir(), 'tpl').'.xlsx';
        $labels = array_column($pengimpor->kolom(), 'label');

        $options = new Options;
        $options->setColumnWidth(18, ...range(1, count($labels)));
        $writer = new Writer($options);
        $writer->openToFile($path);

        $writer->getCurrentSheet()->setName('Data');
        $judul = (new Style)->withFontBold(true)->withBackgroundColor('E2E8F0');
        $writer->addRow(Row::fromValuesWithStyle($labels, $judul));

        $writer->addNewSheetAndMakeItCurrent()->setName('Petunjuk');
        $writer->addRow(Row::fromValuesWithStyle(['Petunjuk pengisian'], $judul));
        foreach ($pengimpor->petunjuk() as $baris) {
            $writer->addRow(Row::fromValues([$baris]));
        }

        $writer->close();

        return $path;
    }
}
