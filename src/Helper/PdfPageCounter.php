<?php


namespace App\Helper;


class PdfPageCounter implements IDocumentPageCounter
{

    public function count(string $fullPath): int
    {
        if (!file_exists($fullPath)) {
            throw new \Exception('File in path ' . $fullPath . ' does not exist');
        }

        try {
            $document = new \Imagick($fullPath);
            $count = $document->getNumberImages();
        } catch (\ImagickException $e) {
            return 'PDF page counting exited with error: ' . $e->getMessage();
        }

        return $count;
    }
}