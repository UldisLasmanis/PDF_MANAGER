<?php


namespace App\Service;


abstract class Uploader implements IFileUploader
{
    protected string $targetDir;
    protected string $filename;
}