<?php


namespace App\Service;


use Symfony\Component\HttpFoundation\File\UploadedFile;

interface IFileUploader
{
    public function getTargetDir();
    public function setTargetDir(string $targetDir);
    public function upload(UploadedFile $file);
    public function createDirIfNotExists();
}