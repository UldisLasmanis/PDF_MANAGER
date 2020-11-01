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

}
