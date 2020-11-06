<?php


namespace App\Service;


use App\Helper\FileNameUrlizer;
use App\Helper\PdfPageCounter;
use Exception;
use Imagick;
use ImagickException;
use Symfony\Component\HttpFoundation\File\File;

class ImageUploader extends Uploader
{
    /**
     * @param File $file
     * @return array
     * @throws Exception
     */
    public function upload(File $file): array
    {
        $response = [];

        $pageCounter = new PdfPageCounter();

        $pageCount = $pageCounter->count($file->getPathname());

        for ($i = 1; $i <= $pageCount; $i++) {
            $response[] = $this->uploadOne($file->getPathname(), $i);
        }

        return $response;
    }

    public function uploadOne(string $pdfPath, $pageNr = 1)
    {
        try {
            $filename = FileNameUrlizer::urlizeImagick();
            $this->setFilename($filename);
            $uploadPath = $this->getTargetDir() . $this->getFilename();
            $pageIndex = $pageNr - 1;

            $image = new Imagick;
            $image->readImage("{$pdfPath}[{$pageIndex}]");
            $image->setImageFormat('jpg');
            $image->setImageCompressionQuality(100);
            $image->setImageAlphaChannel(Imagick::VIRTUALPIXELMETHOD_WHITE);
            $image->writeImage($uploadPath);
            $sizeInBytes = strlen($image->getImageBlob());

            $image->clear();
            $image->destroy();

        } catch (ImagickException $e) {
            return 'File saving exited with error: ' . $e->getMessage();
        }

        return [
            'size_in_bytes' => $sizeInBytes,
            'filename' => $filename,
            'page_nr' => $pageNr
        ];
    }

    public function setFilename(string $filename)
    {
        $this->filename = $filename;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setTargetDir(string $targetDir)
    {
        $this->targetDir = $targetDir;
    }

    public function getTargetDir(): string
    {
        return $this->targetDir;
    }
}