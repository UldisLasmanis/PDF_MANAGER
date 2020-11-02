<?php


namespace App\Service;


use App\Entity\PDF;
use App\Entity\Thumbnail;
use App\Formatter\PdfFormatter;
use App\Formatter\ThumbnailFormatter;
use App\Repository\PDFRepository;
use App\Repository\ThumbnailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class PdfDataRetriever extends AbstractController
{
    private $em;
    /** @var PDFRepository $pdfRepository  */
    private $pdfRepository;
    /** @var ThumbnailRepository $thumbnailRepository */
    private $thumbnailRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->pdfRepository = $this->em->getRepository('App:PDF');
        $this->thumbnailRepository = $this->em->getRepository('App:Thumbnail');
    }

    public function getPdfDataByOffset(Request $request): array
    {
        $pageNr = $request->get('page', 0);
        $offset = $pageNr * 20;

        $formatter = new PdfFormatter();
        $formatter->setTargetDir($this->getParameter('public_pdf_dir'));
        $formatter->setTargetThumbnailDir($this->getParameter('public_thumbnail_dir'));

        return $formatter->format($this->pdfRepository->findByOffset($offset));
    }

    public function save($uploadedFile, $uploadResponse)
    {
        $firstThumbnailFilename = $uploadResponse['thumbnails'][0]['filename'];

        /** @var PDF $entity */
        $pdfEntity = new PDF();
        $pdfEntity->setFilenameOriginal($uploadedFile->getClientOriginalName());
        $pdfEntity->setFilenameMD5($uploadResponse['pdf_filename']);
        $pdfEntity->setSizeInBytes($uploadedFile->getSize());
        $pdfEntity->setPageCnt($uploadResponse['page_count']);
        $pdfEntity->setUploadedAt(new \DateTime());
        $pdfEntity->setThumbnailFilename($firstThumbnailFilename);

        $this->em->persist($pdfEntity);
        $this->em->flush();

        $lastId = $pdfEntity->getId();

        $batchSize = 20;
        $i = 0;
        foreach ($uploadResponse['thumbnails'] as $thumbnail) {
            $thumbnailEntity = new Thumbnail();
            $thumbnailEntity->setFilename($thumbnail['filename']);
            $thumbnailEntity->setSizeInBytes($thumbnail['size_in_bytes']);
            $thumbnailEntity->setUploadedAt(new \DateTime());
            $thumbnailEntity->setPageNr($thumbnail['page_nr']);
            $thumbnailEntity->setPdfId($lastId);

            $this->em->persist($thumbnailEntity);

            $i++;
            if ($i % $batchSize === 0) {
                $this->em->flush();
            }
        }
        $this->em->flush();
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function manageDelete(Request $request)
    {
        if (empty($request->get('document'))) {
            throw new \Exception('Missing argument `document`');
        }

        $filename = $request->get('document');
        $fullPath = $this->getParameter('upload_pdf_dir') . $filename;

        if (!file_exists($fullPath)) {
            throw new \Exception('Can\'t find file in path: ' . $fullPath);
        }

        $pdfId = $this->deletePdfEntity($filename);
        $filenames['pdf'] = [$filename];
        $filenames['thumbnails'] = $this->deleteLinkedThumbnailEntities($pdfId);

        return $filenames;
    }

    public function deletePdfEntity(string $filename)
    {
        /** @var PDF $entity */
        $entity = $this->pdfRepository->getEntityBy(['filename_MD5' => $filename]);
        $pdfId = $entity->getId();

        $this->em->remove($entity);
        $this->em->flush();

        return $pdfId;
    }

    public function deleteLinkedThumbnailEntities(int $pdfId)
    {
        /** @var ThumbnailRepository $repository */
        $repository = $this->em->getRepository('App:Thumbnail');
        /** @var Thumbnail[] $entities */
        $entities = $repository->getEntitiesBy(['pdf_id' => $pdfId]);
        if (empty($entities)) {
            throw new \Exception('Cant find thumbnail entities linked to PDF entity with ID: ' . $pdfId);
        }

        $filenames = [];

        foreach ($entities as $entity) {
            $filenames[] = $entity->getFilename();
            $this->em->remove($entity);
        }

        $this->em->flush();

        return $filenames;
    }

    /**
     * @param Request $request
     * @throws \Exception
     * @return array
     */
    public function getThumbnails(Request $request): array
    {
        $filename = $request->get('document');
        if (null === $filename) {
            throw new \Exception('Parameter `document` not received');
        }

        $filenameWithExtension = $filename . '.pdf';
        /** @var PDF $pdfEntity */
        $pdfEntity = $this->pdfRepository->getEntityBy(['filename_MD5' => $filenameWithExtension]);
        if (null === $pdfEntity) {
            throw new \Exception('PDF entity with filename ' . $filename . ' not found!');
        }

        $thumbnailEntities = $this->thumbnailRepository->getEntitiesBy(['pdf_id' => $pdfEntity->getId()]);
        if (empty($thumbnailEntities)) {
            throw new \Exception('No thumbnails found!');
        }

        $formatter = new ThumbnailFormatter();
        return $formatter->format($thumbnailEntities);
    }
}



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