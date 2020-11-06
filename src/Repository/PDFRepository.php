<?php

namespace App\Repository;

use App\Entity\PDF;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PDF|null find($id, $lockMode = null, $lockVersion = null)
 * @method PDF|null findOneBy(array $criteria, array $orderBy = null)
 * @method PDF[]    findAll()
 * @method PDF[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PDFRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PDF::class);
    }

    public function findEntitiesByOffset($offset, $limit = 20)
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.uploaded_at', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getLinkedAttachment(string $filename)
    {
        return $this->createQueryBuilder('p')
            ->select('a')
            ->join(
                'App\Entity\Attachment',
                'a',
                Join::WITH,
                'p.id = a.pdf_id'
            )
            ->where('p.filename_hash = :filename')
            ->setParameter('filename', $filename)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param array $whereClauses
     * @return PDF
     * @throws NonUniqueResultException|NoResultException
     */
    public function getEntityBy(array $whereClauses): PDF
    {
        $request = $this->createQueryBuilder('p');

        if (!empty($whereClauses)) {
            foreach ($whereClauses as $colName => $colValue) {
                $request
                    ->andWhere('p.' . $colName . ' = :' . $colName)
                    ->setParameter($colName, $colValue)
                ;
            }
        }

        return $request
            ->getQuery()
            ->getSingleResult()
        ;
    }

    /**
     * @param string $filename
     * @return int|mixed|string
     */
    public function getLinkedPdfEntities(string $filename)
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->join(
                'App\Entity\Attachment',
                'a',
                Join::WITH,
                'p.id = a.pdf_id'
            )
            ->where('p.filename_hash = :filename')
            ->setParameter('filename', $filename)
            ->getQuery()
            ->getResult();
    }
}
