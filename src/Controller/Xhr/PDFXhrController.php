<?php


namespace App\Controller\Xhr;


use App\Exceptions\MyThrownException;
use App\Formatter\ThumbnailFormatter;
use App\Repository\ThumbnailRepository;
use App\Service\FileRemover;
use App\Service\FileUploadManager;
use App\Service\PdfDataRetriever;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PDFXhrController extends AbstractController
{
    private $dataRetriever;
    private $uploadManager;
    private $fileRemover;

    public function __construct(PdfDataRetriever $dataRetriever, FileUploadManager $uploadManager, FileRemover $fileRemover)
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
     * @Route ("/documents", name="upload_document", methods={"POST"})
     * @param Request $request
     * @throws MyThrownException
     * @return JsonResponse
     */
    public function uploadDocument(Request $request): JsonResponse
    {
        $uploadedFile = $request->files->get('file');
        if (null === $uploadedFile) {
            throw new MyThrownException('No file received');
        }

        try {
            $uploadResponse = $this->uploadManager->upload($uploadedFile);
            $this->dataRetriever->save($uploadedFile, $uploadResponse);
        } catch (FileException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Upload failed with error: ' . $e->getMessage()
            ]);
        } catch (\Exception $e) {
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
     * @Route("/documents/{document}", name="view_document", methods={"GET"})
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function viewDocument(Request $request): BinaryFileResponse
    {
        if (empty($request->get('document'))) {
            dd('Missing parameter `document`');
        }

        $filename = $request->get('document');
        $fullPath = $this->getParameter('upload_pdf_dir') . viewAllThumbnails . '.pdf';

        if (!file_exists($fullPath)) {
            dd('No file found!');
        }

        $response = new BinaryFileResponse($fullPath);
        $response->headers->set('Content-type','application/pdf');

        $response->send();

        return $response;
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
            $this->fileRemover->deleteMultiple($filePaths);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'PDF document and linked thumbnails deleted!'
        ]);
    }

    /**
     * @Route("/documents/{document}/attachment/previews", name="view_all_attachments", methods={"GET"})
     * @param Request $request
     * @param ThumbnailFormatter $formatter
     * @throws \Exception
     * @return JsonResponse
     */
    public function viewAllThumbnails(Request $request): JsonResponse
    {
        try {
            $response = $this->dataRetriever->getThumbnails($request);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

        dd($response);

        return new JsonResponse([
            'data' => [
                'items' => [1,2,3,4,]
            ]
        ]);
        $filename = $request->get('document');
        if (null === $filename) {
            throw new \Exception('Parameter `document` not received');
        }

//        $servie = new MySer();
//        $ser->validate($request)
//
//        $filenameWithExtension = $filename . '.pdf';
//
//        /** @var PDFRepository $pdfRepository */
//        $pdfRepository = $this->getDoctrine()->getRepository('App:PDF');
//
//        $entity = $pdfRepository->getEntityBy(['filename_MD5' => $filenameWithExtension]);
//        if (null === $entity) {
//            throw new \Exception('PDF record with filename ' . $filename . ' not found!');
//        }
//
//        $pdfId = $entity->getId();
//
//        /** @var ThumbnailRepository $thumbnailRepository */
//        $thumbnailRepository = $this->getDoctrine()->getRepository('App:Thumbnail');
//
//        $thumbnails = $thumbnailRepository->getRecordsBy(['pdf_id' => $pdfId]);
//        $items = $formatter->format($thumbnails);
//
//        $thumbnailService = new ThumnailService();
//        if ($errors = $thumbnailsService->validate($request)) {
//            return JsonResponse::error([
//                succes: false,
//                'data' message errors..
//
//            ])
//        }


//        return $this->render('attachment_preview.html.twig', ['items' =>  $formattter->prepareResponse($humbnailService->retrieveThumbnails()));
//        ]);
    }

    /**
     * @Route("/documents/{document}/attachment/previews/{preview}", name="view_attachment", methods={"GET"})
     * @param Request $request
     * @param ThumbnailFormatter $formatter
     * @throws \Exception
     * @return Response
     */
    public function viewOneThumbnail(Request $request, ThumbnailFormatter $formatter): Response
    {
        $filename = $request->get('document');
        if (null === $filename) {
            throw new \Exception('Parameter `document` not received');
        }

        $thumbnailName = $request->get('preview');
        if (null === $thumbnailName) {
            throw new \Exception('Parameter `preview` not received');
        }

        $thumbnailNameWithExtension = $thumbnailName . '.jpg';

        /** @var ThumbnailRepository $thumbnailRepository */
        $thumbnailRepository = $this->getDoctrine()->getRepository('App:Thumbnail');

        $thumbnail = $thumbnailRepository->getSingleRecordBy(['filename' => $thumbnailNameWithExtension]);
        $item = $formatter->formatOne($thumbnail);

        return $this->render('attachment_preview.html.twig', ['items' => [$item]]);
    }
}