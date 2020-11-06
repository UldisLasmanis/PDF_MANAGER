<?php


namespace App\Service;


use App\Entity\Attachment;
use App\Entity\PDF;
use App\Entity\Image;
use App\Exceptions\AttachmentExistsException;
use App\Formatter\PdfFormatter;
use App\Formatter\ImageFormatter;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ObjectRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class PdfDataRetriever extends AbstractController
{
    private EntityManagerInterface $em;

    private ObjectRepository $pdfRepository;

    private ObjectRepository $imageRepository;

    private ObjectRepository $attachmentRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->pdfRepository = $this->em->getRepository('App:PDF');
        $this->imageRepository = $this->em->getRepository('App:Image');
        $this->attachmentRepository = $this->em->getRepository('App:Attachment');
    }

    public function getPdfDataByOffset(Request $request): array
    {
        $pageNr = $request->get('page', 0);
        $offset = $pageNr * 20;

        $formatter = new PdfFormatter();
        $formatter->setTargetDir($this->getParameter('public_pdf_dir'));
        $formatter->setTargetImageDir($this->getParameter('public_image_dir'));

        return $formatter->format($this->pdfRepository->findEntitiesByOffset($offset));
    }

    public function save(UploadedFile $uploadedFile, $uploadResponse)
    {
        $firstImageFilename = $uploadResponse['image'][0]['filename'];

        /** @var PDF $entity */
        $pdfEntity = new PDF();
        $pdfEntity->setFilenameOriginal($uploadedFile->getClientOriginalName());
        $pdfEntity->setFilenameHash($uploadResponse['pdf']->getFilename());
        $pdfEntity->setSizeInBytes($uploadedFile->getSize());
        $pdfEntity->setPageCnt($uploadResponse['page_count']);
        $pdfEntity->setUploadedAt(new DateTime());
        $pdfEntity->setPreviewImageFilename($firstImageFilename);

        $this->em->persist($pdfEntity);
        $this->em->flush();

        $lastId = $pdfEntity->getId();

        foreach ($uploadResponse['image'] as $image) {
            $this->saveImage($image, $lastId);
        }
    }

    public function saveImage(array $image, int $pdfId)
    {
        $imageEntity = new Image();
        $imageEntity->setFilename($image['filename']);
        $imageEntity->setSizeInBytes($image['size_in_bytes']);
        $imageEntity->setUploadedAt(new DateTime());
        $imageEntity->setPageNr($image['page_nr']);
        $imageEntity->setPdfId($pdfId);

        $this->em->persist($imageEntity);
        $this->em->flush();
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function manageDelete(Request $request)
    {
        if (empty($request->get('document'))) {
            throw new Exception('Missing argument `document`');
        }

        $filename = $request->get('document');
        $fullPath = $this->getParameter('upload_pdf_dir') . $filename;

        if (!file_exists($fullPath)) {
            throw new Exception('Can\'t find file in path: ' . $fullPath);
        }

        $pdfId = $this->deletePdfEntity($filename);
        $filenames['pdf'] = [$filename];
        $filenames['image'] = $this->deleteLinkedImageEntities($pdfId);

        return $filenames;
    }

    /**
     * @param string $filename
     * @return int|null
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function deletePdfEntity(string $filename): ?int
    {
        /** @var PDF $entity */
        $entity = $this->pdfRepository->getEntityBy(['filename_hash' => $filename]);
        $pdfId = $entity->getId();

        $this->em->remove($entity);
        $this->em->flush();

        return $pdfId;
    }

    /**
     * @param int $pdfId
     * @return array
     * @throws Exception
     */
    public function deleteLinkedImageEntities(int $pdfId)
    {
        /** @var Image[] $entities */
        $entities = $this->imageRepository->getEntitiesBy(['pdf_id' => $pdfId]);
        if (empty($entities)) {
            throw new Exception('Cant find image entities linked to PDF entity with ID: ' . $pdfId);
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
     * @throws Exception
     * @return array
     */
    public function getImages(Request $request): array
    {
        $filename = $request->get('document');
        if (null === $filename) {
            throw new Exception('Parameter `document` not received');
        }

        /** @var PDF $pdfEntity */
        $pdfEntity = $this->pdfRepository->getEntityBy(['filename_hash' => $filename]);
        if (null === $pdfEntity) {
            throw new Exception('PDF entity with filename ' . $filename . ' not found!');
        }

        $imageEntities = $this->imageRepository->getEntitiesBy(['pdf_id' => $pdfEntity->getId()]);
        if (empty($imageEntities)) {
            throw new Exception('No images found!');
        }

        $formatter = new ImageFormatter();
        $formatter->setTargetDir($this->getParameter('public_image_dir'));

        return $formatter->format($imageEntities);
    }

    public function getLinkedAttachmentFullPath(string $pdfHashFilename): string
    {
        /** @var Attachment $entity */
        $entity = $this->pdfRepository->getLinkedAttachment($pdfHashFilename);
        if (null === $entity) {
            throw new Exception('Cant find attachment in database');
        }

        return $this->getParameter('upload_attachment_dir') . $entity->getFilenameHash();
    }

    public function saveAttachment(File $uploadedFile, array $additionalData): array
    {
        /** @var PDF $pdfEntity */
        $pdfEntity = $this->pdfRepository->getEntityBy(['filename_hash' => $additionalData['pdf_hash_filename']]);

        $entity = new Attachment();
        $entity->setFilenameOriginal($additionalData['filename_original']);
        $entity->setFilenameHash($uploadedFile->getFilename());
        $entity->setUploadedAt(new DateTime());
        $entity->setSizeInBytes($uploadedFile->getSize());
        $entity->setPdfId($pdfEntity->getId());

        $this->em->persist($entity);
        $this->em->flush();

        return [
            'pdf_entity' => $pdfEntity,
            'attachment_id' => $entity->getId()
        ];
    }

    public function linkAttachmentToPdf(array $saveResponse)
    {
        /** @var PDF $entity */
        $entity = $saveResponse['pdf_entity'];

        $entity->setAttachmentId($saveResponse['attachment_id']);

        $this->em->persist($entity);
        $this->em->flush();
    }

    public function validateAttachmentNotExists(string $pdfFilename)
    {
        /** @var PDF $entities */
        $entities = $this->pdfRepository->getLinkedPdfEntities($pdfFilename);
        if (!empty($entities)) {
            throw new AttachmentExistsException('Attachment already exists - only one attachment allowed!');
        }
    }
}

