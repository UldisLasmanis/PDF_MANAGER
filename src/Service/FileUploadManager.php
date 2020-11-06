<?php


namespace App\Service;


use App\Helper\FileNameUrlizer;
use App\Helper\PdfPageCounter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;


class FileUploadManager extends AbstractController
{
    public function upload(File $uploadedFile): array
    {
        $fileUploader = new FileUploader();
        $fileUploader->setTargetDir($this->getParameter('upload_pdf_dir'));
        $fileUploader->setFilename(FileNameUrlizer::urlizeFile($uploadedFile));
        $pdfFileInfo = $fileUploader->upload($uploadedFile);

        $imageUploader = new ImageUploader();
        $imageUploader->setTargetDir($this->getParameter('upload_image_dir'));
        $imageUploader->setFilename(FileNameUrlizer::urlizeImagick());
        $ImageInfo = $imageUploader->upload($pdfFileInfo);

        $pageCounter = new PdfPageCounter();
        $pageCount = $pageCounter->count($pdfFileInfo->getRealPath());

        return [
            'image' => $ImageInfo,
            'pdf' => $pdfFileInfo,
            'page_count' => $pageCount
        ];
    }

    public function uploadAttachment(File $uploadedFile): File
    {
        $fileUploader = new FileUploader();
        $fileUploader->setTargetDir($this->getParameter('upload_attachment_dir'));
        $fileUploader->setFilename(FileNameUrlizer::urlizeFile($uploadedFile));

        return $fileUploader->upload($uploadedFile);
    }
}
