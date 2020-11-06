<?php


namespace App\Formatter;


use App\Entity\Image;

class ImageFormatter implements IFormatter
{
    private string $targetDir;

    public function format(array $entities): array
    {
        $result = [];
        foreach ($entities as $entity) {
            $result[] = $this->formatOne($entity);
        }

        return $result;
    }

    public function formatOne(Image $entity)
    {
        return [
            'file_path' => $this->getTargetDir() . $entity->getFilename(),
            'filename' => $entity->getFilename(),
            'size_in_KB' => round($entity->getSizeInBytes() / 1024, 2),
            'page_nr' => $entity->getPageNr(),
            'uploaded_at' => $entity->getUploadedAt()->format('Y-m-d H:i:s')
        ];
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