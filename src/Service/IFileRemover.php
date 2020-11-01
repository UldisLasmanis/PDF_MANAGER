<?php


namespace App\Service;


interface IFileRemover
{
    public function getDirectory(): string;
    public function deleteFile(string $fileName);
}