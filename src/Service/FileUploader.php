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
    }

    public function getTargetDir(): string
    {
        return $this->targetDir;
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
