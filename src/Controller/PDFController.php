<?php


namespace App\Controller;


use App\Entity\PDF;
use App\Entity\Thumbnail;
use App\Formatter\PdfFormatter;
use App\Helper\PdfPageCounter;
use App\Repository\PDFRepository;
use App\Service\PdfUploader;
use App\Service\ThumbnailCreator;
use App\Service\ThumbnailUploader;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}