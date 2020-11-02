<?php


namespace App\Formatter;


use App\Exceptions\MyThrownException;

class PdfFormatter
{
    private $targetPdfDirectory;
    private $targetThumbnailDirectory;

//    public function __construct($targetPdfDirectory, $targetThumbnailDirectory)
//    {
//        $this->targetPdfDirectory = $targetPdfDirectory;
//        $this->targetThumbnailDirectory = $targetThumbnailDirectory;
//    }

    public function format(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = [
                'original_filename' => $item['filename_original'],
                'md5_filename' => $item['filename_MD5'],
                'file_path' => $this->getTargetDir() . $item['filename_MD5'],
                'thumbnail_path' => $this->getTargetThumbnailDir() . $item['thumbnail_filename'],
                'size_in_KB' => round($item['size_in_bytes'] / 1024, 2),
                'page_cnt' => $item['page_cnt'],
                'uploaded_at' => $item['uploaded_at']->format('Y-m-d H:i:s')
            ];
        }

        return $result;
    }

    public function getTargetDir(): string
    {
        return $this->targetPdfDirectory;
    }

    public function setTargetDir(string $targetDir)
    {
        $this->targetPdfDirectory = $targetDir;
    }

    public function getTargetThumbnailDir(): string
    {
        return $this->targetThumbnailDirectory;
    }

    public function setTargetThumbnailDir(string $targetDir)
    {
        $this->targetThumbnailDirectory = $targetDir;
    }
}