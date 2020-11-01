<?php


namespace App\Service;


class PdfFileRemover implements IFileRemover
{

    private $targetDir;

    public function __construct(string $targetDir)
    {
        $this->targetDir = $targetDir;
    }

    public function deleteFile(string $filename)
    {
        $fullPath = $this->getDirectory() . $filename;
        unlink($fullPath);
    }

    public function getDirectory(): string
    {
        return $this->targetDir;
    }
}