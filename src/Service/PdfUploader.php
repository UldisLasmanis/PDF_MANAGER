<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PdfUploader implements IFileUploader
{
    private $targetDir;
//
//    public function __construct($targetDirectory)
//    {
//        $this->targetDirectory = $targetDirectory;
//    }

    public function upload(UploadedFile $file)
    {
        $fileName = md5(uniqid()) . '.' . $file->guessExtension();

        try {
            $this->createDirIfNotExists();
            $file->move($this->getTargetDir(), $fileName);
        } catch (FileException $e) {
            return $e->getMessage();
        }

        return [
            'filename' => $fileName,
            'path' => $this->getTargetDir() . '/' . $fileName
        ];
    }

    public function setTargetDir(string $targetDir)
    {
        $this->targetDir = $targetDir;
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
}
