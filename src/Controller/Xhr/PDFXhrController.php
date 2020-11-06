<?php


namespace App\Controller\Xhr;


use App\Exceptions\AttachmentExistsException;
use App\Service\AttachmentFileRemover;
use App\Service\FileRemover;
use App\Service\FileUploadManager;
use App\Service\PdfDataRetriever;
use App\Service\PdfFileRemover;
use App\Service\ImageFileRemover;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\NoFileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class PDFXhrController extends AbstractController
{
    private PdfDataRetriever $dataRetriever;
    private FileUploadManager $uploadManager;
    private FileRemover $fileRemover;

    public function __construct(
        PdfDataRetriever $dataRetriever,
        FileUploadManager $uploadManager,
        FileRemover $fileRemover
    )
    {
        $this->dataRetriever = $dataRetriever;
        $this->uploadManager = $uploadManager;
        $this->fileRemover = $fileRemover;
    }

    /**
     * @Route ("/documents", name="documents", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getDocuments(Request $request)
    {
        return new JsonResponse([
            'status' => true,
            'data' => [
                'items' => $this->dataRetriever->getPdfDataByOffset($request)
            ]
        ]);
    }

    /**
     * @Route("/documents/{document}", name="view_document", methods={"GET"})
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function viewDocument(Request $request): BinaryFileResponse
    {
        $filename = $request->get('document');
        $fullPath = $this->getParameter('upload_pdf_dir') . $filename;

        if (!file_exists($fullPath)) {
            throw new NotFoundHttpException('File does not exist in specified path');
        }

        $response = new BinaryFileResponse($fullPath);
        $response->headers->set('Content-type','application/pdf');

        $response->send();

        return $response;
    }

    /**
     * @Route ("/documents", name="upload_document", methods={"POST"})
     * @param Request $request
     * @throws NoFileException
     * @return JsonResponse
     */
    public function uploadDocument(Request $request): JsonResponse
    {
        $uploadedFile = $request->files->get('file');
        if (null === $uploadedFile) {
            throw new NoFileException('No file received');
        }

        try {
            $uploadResponse = $this->uploadManager->upload($uploadedFile);
            $this->dataRetriever->save($uploadedFile, $uploadResponse);
        } catch (Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Upload failed with error: ' . $e->getMessage()
            ]);
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'File uploaded'
        ]);
    }

    /**
     * @Route("/documents/{document}", name="delete_document", methods={"DELETE"})
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteDocument(Request $request): JsonResponse
    {
        try {
            $filePaths = $this->dataRetriever->manageDelete($request);

            $pdfFileRemover = new PdfFileRemover();
            $pdfFileRemover->setTargetDir($this->getParameter('upload_pdf_dir'));

            $imageRemover = new ImageFileRemover();
            $imageRemover->setTargetDir($this->getParameter('upload_image_dir'));

            $attachmentFileRemover = new AttachmentFileRemover();
            $attachmentFileRemover->setTargetDir($this->getParameter('upload_attachment_dir'));

            $this->fileRemover->setPdfFileRemover($pdfFileRemover);
            $this->fileRemover->setImageFileRemover($imageRemover);
            $this->fileRemover->setAttachmentFileRemover($attachmentFileRemover);
            $this->fileRemover->deleteMultiple($filePaths);
        } catch (Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'PDF document and linked files deleted!'
        ]);
    }

    /**
     * @Route("/documents/{document}/attachment", name="download_attachment_resource", methods={"GET"})
     * @param Request $request
     * @throws Exception
     * @return BinaryFileResponse
     */
    public function downloadAttachmentResource(Request $request): BinaryFileResponse
    {
        $pdfHashName = $request->get('document');

        $fullPath = $this->dataRetriever->getLinkedAttachmentFullPath($pdfHashName);
        if (!file_exists($fullPath)) {
            throw new NotFoundHttpException('File does not exist in specified path: ' . $fullPath);
        }
        $response = new BinaryFileResponse($fullPath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $request->get('document'));

        return $response;
    }

    /**
     * @Route("/documents/{document}/attachment/previews", name="view_images", methods={"GET"})
     * @param Request $request
     * @throws Exception
     * @return JsonResponse
     */
    public function viewImages(Request $request): JsonResponse
    {
        try {
            $response = $this->dataRetriever->getImages($request);
        } catch (Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

        return new JsonResponse([
            'success' => true,
            'data' => [
                'items' => $response
            ]
        ]);
    }

    /**
     * @Route("/documents/{document}/attachment/previews/{preview}", name="download_image_resource", methods={"GET"})
     * @param Request $request
     * @throws Exception
     * @return BinaryFileResponse
     */
    public function downloadImageResource(Request $request): BinaryFileResponse
    {
        $imageName = $request->get('preview');
        $fullPath = $this->getParameter('upload_image_dir') . $imageName;
        $response = new BinaryFileResponse($fullPath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $imageName);

        return $response;
    }

    /**
     * @Route("/documents/{document}/attachment", name="upload_attachment", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws NoFileException
     */
    public function uploadAttachment(Request $request)
    {
        $pdfFilename = $request->get('document');
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');
        if (null === $uploadedFile) {
            throw new NoFileException('No file received');
        }

        try {
            $this->dataRetriever->validateAttachmentNotExists($pdfFilename);
            $uploadedFileModified = $this->uploadManager->uploadAttachment($uploadedFile);
            $additionalData = [
                'filename_original' => $uploadedFile->getClientOriginalName(),
                'pdf_hash_filename' => $pdfFilename
            ];
            $saveResponse = $this->dataRetriever->saveAttachment($uploadedFileModified, $additionalData);
            $this->dataRetriever->linkAttachmentToPdf($saveResponse);
        } catch (AttachmentExistsException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Upload failed with error: ' . $e->getMessage()
            ]);
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'File uploaded'
        ]);
    }
}