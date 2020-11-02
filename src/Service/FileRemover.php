<?php


namespace App\Service;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FileRemover extends AbstractController
{
    /**
     * @param array $filenames
     * @return bool
     * @throws \Exception
     */
    public function deleteMultiple(array $filenames)
    {
        if (empty($filenames)) {
            throw new \Exception('Received empty $filenames array');
        }

        if (!empty($filenames['pdf'])) {
            $pdfRemover = new PdfFileRemover();
            $pdfRemover->setTargetDir($this->getParameter('upload_pdf_dir'));
            $pdfRemover->deleteMultiple($filenames['pdf']);
        }

        if (!empty($filenames['thumbnails'])) {
            $thumbnailRemover = new ThumbnailFileRemover();
            $thumbnailRemover->setTargetDir($this->getParameter('upload_thumbnail_dir'));
            $thumbnailRemover->deleteMultiple($filenames['thumbnails']);
        }

        return true;
    }
}
