<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PdfUploader implements IFileUploader
{
    private $targetDirectory;

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function upload(UploadedFile $file)
    {
        $fileName = md5(uniqid()) . '.' . $file->guessExtension();

        try {
            $this->createDirIfNotExists();
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            dd($e->getMessage());
        }

        return [
            'filename' => $fileName,
            'path' => $this->getTargetDirectory() . '/' . $fileName
        ];
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }

    public function createDirIfNotExists(): void
    {
        if (!file_exists($this->getTargetDirectory()) && !is_dir($this->getTargetDirectory())) {
            mkdir($this->getTargetDirectory(), 0775, true);
        }
    }
}
