<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\File;

class FileUploader extends Uploader
{
    public function upload(File $file): File
    {
        $file = $file->move($this->getTargetDir(), $this->getFilename());
        return $file;
    }

    public function setTargetDir(string $targetDir)
    {
        $this->targetDir = $targetDir;
        $this->createDirIfNotExists();
    }

    public function getTargetDir(): string
    {
        return $this->targetDir;
    }

    public function createDirIfNotExists(): void
    {
        if (!file_exists($this->getTargetDir()) && !is_dir($this->getTargetDir())) {
            mkdir($this->getTargetDir(), 0775, true);
        }
    }

    public function setFilename(string $filename)
    {
        $this->filename = $filename;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
}
