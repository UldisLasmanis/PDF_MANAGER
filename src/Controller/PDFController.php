<?php


namespace App\Controller;


use App\Entity\PDF;
use App\Entity\Thumbnail;
use App\Formatter\PdfFormatter;
use App\Formatter\ThumbnailFormatter;
use App\Helper\PdfPageCounter;
use App\Repository\PDFRepository;
use App\Repository\ThumbnailRepository;
use App\Service\PdfFileRemover;
use App\Service\PdfUploader;
use App\Service\ThumbnailCreator;
use App\Service\ThumbnailFileRemover;
use App\Service\ThumbnailUploader;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PDFController extends AbstractController
{
    /**
     * @Route ("/documents", name="documents", methods={"GET"})
     * @param Request $request
     * @param PDFRepository $repository
     * @param PdfFormatter $formatter
     * @return Response
     */
    public function getDocuments(Request $request, PDFRepository $repository, PdfFormatter $formatter): Response
    {
        $pageNr = $request->get('page', 0);
        $offset = $pageNr * 20; //limit should be 20

        $response = $repository->findByOffset($offset);
        $list = [];
        if (false === empty($response)) {
            $list = $formatter->format($response);
        }

        return $this->render('index.html.twig', ['items' => $list]);
    }

    /**
     * @Route ("/documents", name="upload_document", methods={"POST"})
     * @param Request $request
     * @param PdfUploader $fileUploader
     * @param ThumbnailUploader $thumbnailUploader
     * @param PdfPageCounter $pageCounter
     * @param ThumbnailCreator $thumbnailCreator
     * @return Response
     */
    public function uploadDocument(Request $request, PdfUploader $fileUploader, ThumbnailUploader $thumbnailUploader, PdfPageCounter $pageCounter, ThumbnailCreator $thumbnailCreator): JsonResponse
    {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');
        if (null === $uploadedFile) {
            dd('No file received');
        }

        $pdfFileInfo = $fileUploader->upload($uploadedFile);
        $pageCnt = $pageCounter->count($pdfFileInfo['path']);

        $thumbnailResources = $thumbnailCreator->createFromPdf($pdfFileInfo['path'], $pageCnt);
        $thumbnailUploader->uploadMulti($thumbnailResources);
        $thumbnailFilename = $thumbnailResources[0]['filename'];

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var PDF $entity */
        $pdfEntity = new PDF();
        $pdfEntity->setFilenameOriginal($uploadedFile->getClientOriginalName());
        $pdfEntity->setFilenameMD5($pdfFileInfo['filename']);
        $pdfEntity->setSizeInBytes($uploadedFile->getSize());
        $pdfEntity->setPageCnt($pageCnt);
        $pdfEntity->setUploadedAt(new \DateTime());
        $pdfEntity->setThumbnailFilename($thumbnailFilename);

        $em->persist($pdfEntity);
        $em->flush();

        $lastId = $pdfEntity->getId();

        $batchSize = 20;
        $i = 0;
        foreach ($thumbnailResources as $thumbnailResource) {
            $thumbnailEntity = new Thumbnail();
            $thumbnailEntity->setFilename($thumbnailResource['filename']);
            $thumbnailEntity->setSizeInBytes($thumbnailResource['size_in_bytes']);
            $thumbnailEntity->setUploadedAt(new \DateTime());
            $thumbnailEntity->setPageNr($thumbnailResource['page_nr']);
            $thumbnailEntity->setPdfId($lastId);

            $em->persist($thumbnailEntity);

            $i++;
            if ($i % $batchSize === 0) {
                $em->flush();
            }
        }
        $em->flush();

        return new JsonResponse([
            'status' => true,
            'message' => 'File uploaded'
        ]);
    }

    /**
     * @Route("/documents/{document}", name="view_document", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function viewDocument(Request $request): BinaryFileResponse
    {
        if (empty($request->get('document'))) {
            dd('Missing parameter `document`');
        }

        $filename = $request->get('document');
        $fullPath = $this->getParameter('upload_pdf_dir') . $filename . '.pdf';

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
     * @param PdfFileRemover $pdfFileRemover
     * @param ThumbnailFileRemover $thumbnailFileRemover
     * @return Response
     */
    public function deleteDocument(Request $request, PdfFileRemover $pdfFileRemover, ThumbnailFileRemover $thumbnailFileRemover): Response
    {
        if (empty($request->get('document'))) {
            dd('Missing parameter `document`');
        }

        $filename = $request->get('document');
        $fullPath = $this->getParameter('upload_pdf_dir') . $filename;

        if (!file_exists($fullPath)) {
            dd('No file found!');
        }

        /** @var PDFRepository $repository */
        $repository = $this->getDoctrine()->getRepository('App:PDF');
        $pdfEntity = $repository->getEntityBy(['filename_MD5' => $filename]);

        /** @var Thumbnail[] $thumbnails */
        $thumbnailEntities = $repository->getLinkedThumbnails($filename);

        $pdfFileRemover->deleteFile($filename);
        $thumbnailFileRemover->deleteMultiple($thumbnailEntities);

        //DELETE somewhere else
        $em = $this->getDoctrine()->getManager();
        foreach ($thumbnailEntities as $thumbnailEntity) {
            $em->remove($thumbnailEntity);
        }
        $em->remove($pdfEntity);
        $em->flush();
        //

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
     * @return Response
     */
    public function viewAllThumbnails(Request $request, ThumbnailFormatter $formatter)
    {
        $filename = $request->get('document');
        if (null === $filename) {
            throw new \Exception('Parameter `document` not received');
        }

        $filenameWithExtension = $filename . '.pdf';

        /** @var PDFRepository $pdfRepository */
        $pdfRepository = $this->getDoctrine()->getRepository('App:PDF');

        $entity = $pdfRepository->getEntityBy(['filename_MD5' => $filenameWithExtension]);
        if (null === $entity) {
            throw new \Exception('PDF record with filename ' . $filename . ' not found!');
        }

        $pdfId = $entity->getId();

        /** @var ThumbnailRepository $thumbnailRepository */
        $thumbnailRepository = $this->getDoctrine()->getRepository('App:Thumbnail');

        $thumbnails = $thumbnailRepository->getRecordsBy(['pdf_id' => $pdfId]);
        $items = $formatter->format($thumbnails);

        return $this->render('attachment_preview.html.twig', ['items' => $items]);
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