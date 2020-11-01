<?php


namespace App\Service;


use Symfony\Component\HttpFoundation\File\UploadedFile;

class ThumbnailUploader
{
    private $targetDirectory;

    public function __construct(string $targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
        $this->createDirIfNotExists();
    }

    public function createDirIfNotExists()
    {
        if (!file_exists($this->getTargetDirectory()) && !is_dir($this->getTargetDirectory())) {
            mkdir($this->getTargetDirectory(), 0775, true);
        }
    }

    public function uploadMulti(array $thumbnails): void
    {
        foreach ($thumbnails as $thumbnail) {
            /** @var \Imagick $image */
            $image = $thumbnail['resource'];
            try {
                $image->writeImage($thumbnail['targetPath']);
                $image->clear();
                $image->destroy();
            } catch (\ImagickException $e) {
                dd($e->getMessage());
            }
        }
    }

    //FIX THIS, NEED TO USE INTERFACE
    public function upload(UploadedFile $file, string $pdfFileFullPath)
    {
        $fileName = md5(uniqid()) . '.jpg';
        $fullPath = $this->getTargetDirectory() . $fileName;

        try {
            $image = new \Imagick;
            $image->readImage("{$pdfFileFullPath}[0]");
            $image->setImageFormat('jpg');
            $image->scaleImage(240, 320);
            $image->setImageAlphaChannel(\Imagick::VIRTUALPIXELMETHOD_WHITE);
            $image->writeImage($fullPath);
            $image->clear();
            $image->destroy();
        } catch (\ImagickException $e) {
            dd($e->getMessage());
        }

        return $fileName;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}