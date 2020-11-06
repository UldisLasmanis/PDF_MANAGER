<?php


namespace App\Formatter;


use App\Entity\PDF;

class PdfFormatter
{
    private string $targetPdfDirectory;
    private string $targetImageDirectory;

    public function format(array $entities): array
    {
        $result = [];
        foreach ($entities as $entity) {
            $result[] = $this->formatOne($entity);
        }

        return $result;
    }

    public function formatOne(PDF $entity): array
    {
        return [
            'filename_original' => $entity->getFilenameOriginal(),
            'filename_hash' => $entity->getFilenameHash(),
            'file_path' => $this->getTargetDir() . $entity->getFilenameHash(),
            'preview_image_path' => $this->getTargetImageDir() . $entity->getPreviewImageFilename(),
            'size_in_KB' => round($entity->getSizeInBytes() / 1024, 2),
            'page_cnt' => $entity->getPageCnt(),
            'uploaded_at' => $entity->getUploadedAt()->format('Y-m-d H:i:s'),
            'attachment_id' => $entity->getAttachmentId()
        ];
    }

    public function getTargetDir(): string
    {
        return $this->targetPdfDirectory;
    }

    public function setTargetDir(string $targetDir)
    {
        $this->targetPdfDirectory = $targetDir;
    }

    public function getTargetImageDir(): string
    {
        return $this->targetImageDirectory;
    }

    public function setTargetImageDir(string $targetDir)
    {
        $this->targetImageDirectory = $targetDir;
    }
}