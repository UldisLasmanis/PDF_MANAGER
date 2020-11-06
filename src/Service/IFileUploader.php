<?php


namespace App\Service;


use Symfony\Component\HttpFoundation\File\File;

interface IFileUploader
{
    public function getTargetDir(): string;
    public function setTargetDir(string $targetDir);
    public function getFilename(): string;
    public function setFilename(string $filename);
    public function upload(File $file);
    public function createDirIfNotExists();
}