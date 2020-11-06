<?php

namespace App\Repository;

use App\Entity\Image;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Image|null find($id, $lockMode = null, $lockVersion = null)
 * @method Image|null findOneBy(array $criteria, array $orderBy = null)
 * @method Image[]    findAll()
 * @method Image[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Image::class);
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
}
