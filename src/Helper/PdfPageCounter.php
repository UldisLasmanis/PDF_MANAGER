<?php


namespace App\Helper;


use Exception;
use Imagick;
use ImagickException;

class PdfPageCounter
{
    /**
     * @param string $fullPath
     * @return int
     * @throws Exception
     */
    public function count(string $fullPath): int
    {
        if (!file_exists($fullPath)) {
            throw new Exception('File in path ' . $fullPath . ' does not exist');
        }

        try {
            $document = new Imagick($fullPath);
            return $document->getNumberImages();
        } catch (ImagickException $e) {
            throw $e;
        }
    }
}