<?php


namespace App\Service;


use App\Entity\PDF;
use App\Entity\Thumbnail;
use App\Exceptions\MyThrownException;
use App\Helper\PdfPageCounter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class FileUploadManager extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function upload(UploadedFile $uploadedFile)
    {
        $pdfFileUploader = new PdfUploader();
        $pdfFileUploader->setTargetDir($this->getParameter('upload_pdf_dir'));
        $pdfFileInfo = $pdfFileUploader->upload($uploadedFile);

        $pageCounter = new PdfPageCounter();
        $pageCount = $pageCounter->count($pdfFileInfo['path']);

        $thumbnailCreator = new ThumbnailCreator();
        $thumbnailCreator->setTargetDir($this->getParameter('upload_thumbnail_dir'));
        $thumbnailResources = $thumbnailCreator->createFromPdf($pdfFileInfo['path'], $pageCount);

        $thumbnailUploader = new ThumbnailUploader();
        $thumbnailUploader->setTargetDir($this->getParameter('upload_thumbnail_dir'));
        $thumbnailUploader->uploadMulti($thumbnailResources);

        return [
            'thumbnails' => $thumbnailResources,
            'pdf_filename' => $pdfFileInfo['filename'],
            'page_count' => $pageCount
        ];
    }
}
