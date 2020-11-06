<?php


namespace App\Service;


interface IFileRemover
{
    public function getTargetDir(): string;
    public function setTargetDir(string $path);
    public function deleteFile(string $fileName);
}