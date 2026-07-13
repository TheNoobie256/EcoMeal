<?php

namespace App\Repository;

use App\DTO\PackageSearchFilter;
use App\Entity\Package;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Package>
 */
class PackageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Package::class);
    }

    public function findByFilter(PackageSearchFilter $filter): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c');

        if($filter->name) {
            $qb->andWhere('p.name LIKE :name')
                ->setParameter('name', '%'.$filter->name.'%');
        }

        if($filter->minPrice) {
            $qb->andWhere('p.price >= :minPrice')
                ->setParameter('minPrice', $filter->minPrice);
        }

        if($filter->maxPrice) {
            $qb->andWhere('p.price <= :maxPrice')
                ->setParameter('maxPrice', $filter->maxPrice);
        }

        if($filter->category) {
            $qb->andWhere('p.category = :category')
                ->setParameter('category', $filter->category);
        }

        if ($filter->isAvailable !== null) {
            if ($filter->isAvailable) {
                $qb->andWhere('NOT EXISTS (SELECT 1 FROM App\Entity\Order o WHERE o.package = p)');
            } else {
                $qb->andWhere('EXISTS (SELECT 1 FROM App\Entity\Order o2 WHERE o2.package = p)');
            }
        }

        return $qb->getQuery()->getResult();
    }
}
