<?php


namespace App\Formatter;


class ThumbnailFormatter implements IFormatter
{
    private $targetDir;

    public function __construct(string $targetDir)
    {
        $this->targetDir = $targetDir;
    }

    public function format(array $list): array
    {
        $result = [];
        foreach ($list as $item) {
            $result[] = $this->formatOne($item);
        }

        return $result;
    }

    public function formatOne(array $item)
    {
        return [
            'file_path' => $this->getTargetDir() . $item['filename'],
            'filename' => $item['filename'],
            'size_in_KB' => round($item['size_in_bytes'] / 1024, 2),
            'page_nr' => $item['page_nr'],   //page_index starts with 0, but page_nr needs to start from 1
            'uploaded_at' => $item['uploaded_at']->format('Y-m-d H:i:s')
        ];
    }

    public function getTargetDir(): string
    {
        return $this->targetDir;
    }
}