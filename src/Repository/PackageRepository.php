<?php

namespace App\Repository;

use App\DTO\PackageSearchFilter;
use App\Entity\Business;
use App\Entity\Package;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
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

    public function findByFilter(PackageSearchFilter $filter, ?Collection $preferredCategories, ?Business $business): array
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

        if ($preferredCategories && !$preferredCategories->isEmpty() && !$filter->category) {
            $qb->andWhere('p.category IN (:preferredCategories)')
                ->setParameter('preferredCategories', $preferredCategories);
        }

        if ($business) {
            $qb->andWhere('p.business = :business')
                ->setParameter('business', $business);
        }

        return $qb->getQuery()->getResult();
    }
}
