<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderFormType;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/order')]
final class OrderController extends AbstractController
{
    #[Route('', name: 'app_order', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            $orders = $orderRepository->findAll();
        } elseif ($this->isGranted('ROLE_BUSINESS')) {

            $orders = $orderRepository->createQueryBuilder('o')
                ->join('o.package', 'p')
                ->where('p.business = :business')
                ->setParameter('business', $user->getBusiness())
                ->getQuery()
                ->getResult();
        } elseif ($this->isGranted('ROLE_CONSUMER')) {
            $orders = $orderRepository->findBy(['consumer' => $user->getConsumer()]);
        } else {
            throw $this->createAccessDeniedException();
        }

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/new', name: 'app_order_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {

        $this->denyAccessUnlessGranted('ROLE_CONSUMER');

        $order = new Order();

        $order->setConsumer($this->getUser()->getConsumer());

        $form = $this->createForm(OrderFormType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($order);
            $entityManager->flush();

            return $this->redirectToRoute('app_order');
        }

        return $this->render('order/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_order_view', methods: ['GET'])]
    public function view(Order $order): Response
    {
        $this->checkOrderAccess($order);

        return $this->render('order/view.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_order_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        $this->checkOrderAccess($order);

        $form = $this->createForm(OrderFormType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_order');
        }

        return $this->render('order/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/remove', name: 'app_order_del', methods: ['GET'])]
    public function remove(Order $order, EntityManagerInterface $entityManager): Response
    {
        $this->checkOrderAccess($order);

        $entityManager->remove($order);
        $entityManager->flush();

        return $this->redirectToRoute('app_order');
    }

    private function checkOrderAccess(Order $order): void
    {
        $user = $this->getUser();

        if ($this->isGranted('ROLE_ADMIN')) {
            return;
        }

        if ($this->isGranted('ROLE_BUSINESS')) {
            if ($order->getPackage()->getBusiness() !== $user->getBusiness()) {
                throw $this->createAccessDeniedException('You do not have permission to view this order.');
            }
        } elseif ($this->isGranted('ROLE_CONSUMER')) {
            if ($order->getConsumer() !== $user->getConsumer()) {
                throw $this->createAccessDeniedException('You do not have permission to view this order.');
            }
        } else {
            throw $this->createAccessDeniedException('Access denied.');
        }
    }
}
