<?php


namespace App\Service;


class ThumbnailCreator
{
    private $targetDir;

    public function createFromPdf(string $sourcePath, int $pageCnt)
    {
        $result = [];
        if ($pageCnt <= 0) {
            return $result;
        }

        for ($i = 0; $i < $pageCnt; $i++) {
            $fileName = md5(uniqid()) . '.jpg';
            $fullPath = $this->getTargetDir() . $fileName;

            try {
                $image = new \Imagick;
                $image->readImage("{$sourcePath}[{$i}]");
                $image->setImageFormat('jpg');
                $image->scaleImage(240, 320);
                $image->setImageAlphaChannel(\Imagick::VIRTUALPIXELMETHOD_WHITE);
                $result[] = [
                    'resource' => $image,
                    'filename' => $fileName,
                    'targetPath' => $fullPath,
                    'page_nr' => $i + 1,
                    'size_in_bytes' => strlen($image->getImageBlob())
                ];
            } catch (\ImagickException $e) {
                return $e->getMessage();
            }
        }

        return $result;
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