<?php


namespace App\Service;


class PdfFileRemover implements IFileRemover
{

    private $targetDir;

    public function deleteMultiple(array $filenames)
    {
        foreach ($filenames as $filename) {
            $this->deleteFile($filename);
        }
    }

    public function deleteFile(string $filename)
    {
        $fullPath = $this->getTargetDir() . $filename;
        unlink($fullPath);
    }

    public function setTargetDir(string $targetDir)
    {
        $this->targetDir = $targetDir;
    }

    public function getTargetDir(): string
    {
        return $this->targetDir;
    }
}