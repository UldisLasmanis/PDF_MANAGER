<?php


namespace App\Service;


use Symfony\Component\HttpFoundation\File\UploadedFile;

interface IFileUploader
{
    public function getTargetDirectory();
    public function upload(UploadedFile $file);
    public function createDirIfNotExists();
}