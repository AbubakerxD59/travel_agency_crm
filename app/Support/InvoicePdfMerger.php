<?php

namespace App\Support;

use RuntimeException;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;

class InvoicePdfMerger
{
    /**
     * @param  list<string>  $sources
     */
    public function merge(array $sources): string
    {
        if ($sources === []) {
            throw new RuntimeException('At least one PDF source is required.');
        }

        $pdf = new Fpdi;

        foreach ($sources as $source) {
            $this->appendSource($pdf, $source);
        }

        return $pdf->Output('S');
    }

    private function appendSource(Fpdi $pdf, string $source): void
    {
        if (is_file($source)) {
            $pageCount = $pdf->setSourceFile($source);
        } else {
            $pageCount = $pdf->setSourceFile(StreamReader::createByString($source));
        }

        for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
            $templateId = $pdf->importPage($pageNumber);
            $size = $pdf->getTemplateSize($templateId);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId, 0, 0, $size['width'], $size['height']);
        }
    }
}
