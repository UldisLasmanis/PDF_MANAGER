<?php


namespace App\Helper;


class PdfPageCounter implements IDocumentPageCounter
{

    public function count(string $fullPath): int
    {
        if (!file_exists($fullPath)) {
            throw new \Exception('File in path ' . $fullPath . ' does not exist');
        }

        $document = new \Imagick($fullPath);
        return $document->getNumberImages();
    }
}