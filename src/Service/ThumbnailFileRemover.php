<?php


namespace App\Service;


class ThumbnailFileRemover implements IFileRemover
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

    public function deleteMultiple(array $items)
    {
        foreach ($items as $item) {
            $this->deleteFile($item->getFilename());
        }
    }

    public function getDirectory(): string
    {
        return $this->targetDir;
    }
}