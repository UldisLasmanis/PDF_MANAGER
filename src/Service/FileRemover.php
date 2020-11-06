<?php


namespace App\Service;

use Exception;

class FileRemover
{
    private PdfFileRemover $pdfFileRemover;

    private ImageFileRemover $imageFileRemover;

    private AttachmentFileRemover $attachmentFileRemover;

    public function setPdfFileRemover(PdfFileRemover $pdfFileRemover)
    {
        $this->pdfFileRemover = $pdfFileRemover;
    }

    public function setImageFileRemover(ImageFileRemover $imageFileRemover)
    {
        $this->imageFileRemover = $imageFileRemover;
    }

    public function setAttachmentFileRemover(AttachmentFileRemover $attachmentFileRemover)
    {
        $this->attachmentFileRemover = $attachmentFileRemover;
    }

    /**
     * @param array $filenames
     * @return bool
     * @throws Exception
     */
    public function deleteMultiple(array $filenames)
    {
        if (empty($filenames)) {
            throw new Exception('Received empty $filenames array');
        }

        if (!empty($filenames['pdf'])) {
            if (!isset($this->pdfFileRemover)) {
                throw new Exception('Could not remove pdf');
            }

            $this->pdfFileRemover->deleteMultiple($filenames['pdf']);
        }

        if (!empty($filenames['image'])) {
            if (!isset($this->imageFileRemover)) {
                throw new Exception("Could not remove image");
            }
            $this->imageFileRemover->deleteMultiple($filenames['image']);
        }

        if (!empty($filenames['attachment'])) {
            if (!isset($this->attachmentFileRemover)) {
                throw new Exception("Could not remove attachment");
            }
            $this->attachmentFileRemover->deleteMultiple($filenames['attachment']);
        }

        return true;
    }
}
