<?php

namespace App\Service;

use App\Entity\Business;
use App\Repository\OrderRepository;

class BusinessStatsService
{
    private OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function calculateForBusiness(Business $business): array
    {
        $orders = $this->orderRepository->createQueryBuilder('o')
            ->join('o.package', 'p')
            ->where('p.business = :business')
            ->setParameter('business', $business)
            ->getQuery()
            ->getResult();

        $totalRevenue = 0;
        foreach ($orders as $order) {
            $totalRevenue += $order->getPackage()->getPrice();
        }

        return [
            'packages_sold' => count($orders),
            'total_revenue' => $totalRevenue,
        ];
    }
}
