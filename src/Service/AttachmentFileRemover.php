<?php


namespace App\Service;


class AttachmentFileRemover extends Remover
{
    public function deleteFile(string $filename)
    {
        $fullPath = $this->getTargetDir() . $filename;
        unlink($fullPath);
    }

    public function deleteMultiple(array $filenames)
    {
        foreach ($filenames as $filename) {
            $this->deleteFile($filename);
        }
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