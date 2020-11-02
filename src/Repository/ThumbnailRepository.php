<?php

namespace App\Repository;

use App\Entity\Thumbnail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Thumbnail|null find($id, $lockMode = null, $lockVersion = null)
 * @method Thumbnail|null findOneBy(array $criteria, array $orderBy = null)
 * @method Thumbnail[]    findAll()
 * @method Thumbnail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThumbnailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Thumbnail::class);
    }

    public function getEntitiesBy(array $whereClauses)
    {
        $request = $this->createQueryBuilder('t');

        if (!empty($whereClauses)) {
            foreach ($whereClauses as $colName => $colValue) {
                $request
                    ->andWhere('t.' . $colName . ' = :' . $colName)
                    ->setParameter($colName, $colValue)
                ;
            }
        }

        return $request
            ->orderBy('t.page_nr', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getSingleRecordBy(array $whereClauses)
    {
        $request = $this->createQueryBuilder('t');

        if (!empty($whereClauses)) {
            foreach ($whereClauses as $colName => $colValue) {
                $request
                    ->andWhere('t.' . $colName . ' = :' . $colName)
                    ->setParameter($colName, $colValue)
                ;
            }
        }

        return $request
            ->orderBy('t.page_nr', 'ASC')
            ->getQuery()
            ->getSingleResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
    }
}
