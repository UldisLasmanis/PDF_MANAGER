<?php

namespace App\Repository;

use App\Entity\PDF;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    public function findByOffset($offset, $limit = 20)
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.uploaded_at', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY)
        ;
    }

    public function getLinkedThumbnails(string $filename)
    {
        return $this->createQueryBuilder('p')
            ->select('t')
            ->join(
                'App\Entity\Thumbnail',
                't',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'p.id = t.pdf_id'
            )
            ->where('p.filename_MD5 = :filename')
            ->setParameter('filename', $filename)
            ->getQuery()
            ->getResult();
    }

    public function getEntityBy(array $whereClauses, bool $returnRecord = false)
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

        if (true === $returnRecord) {
            return $request
                ->getQuery()
                ->getSingleResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY)
            ;
        }

        return $request
            ->getQuery()
            ->getSingleResult()
        ;
    }

}
